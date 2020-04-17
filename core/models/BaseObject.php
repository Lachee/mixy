<?php
namespace core\models;

use JsonSerializable;
use xve\schema\ArrayProperty;
use xve\schema\BooleanProperty;
use xve\schema\EnumProperty;
use xve\schema\IntegerProperty;
use xve\schema\NumberProperty;
use xve\schema\ObjectProperty;
use xve\schema\RefProperty;
use xve\schema\SchemaInterface;
use xve\schema\StringProperty;

class BaseObject implements SchemaInterface, JsonSerializable {
    
    /** @var string[] errors from validation */
    private $errors = null;

    
    /** Called after the constructor init the properties */
    protected function init() {}

    /** Before the load */
    protected function beforeLoad($data) {}
    /** After the load */
    protected function afterLoad($data, $success) {}

    /** Creates a new instance of the class */
    function __construct($properties = [])
    {
        foreach($properties as $key => $pair) {
            if (property_exists($this, $key)) {
                $this->{$key} = $pair;
            }
        }

        //Init our properties
        $this->init();
    }

    /** Loads the data into the object. Different to a regular construction because it bases the load of the schema properties.
     * @param array $data the data to read in.
     * @return bool if the read was succesful.
    */
    public function load($data = null) {
        if ($data == null) return false;

        $this->beforeLoad($data);
        $this->errors = null;
        $properties = get_called_class()::getSchemaProperties();
        foreach($properties as $property => $schema) {

            //Clean up missing properties
            if ($schema instanceof BooleanProperty && !isset($data[$property])) {
                $data[$property] = false;
            }

            //This field is required.
            if (!($schema instanceof ArrayProperty) && $schema->required && !isset($data[$property])) {
                $this->addError("{$property} is required");
                continue;
            }

            //Skip empty
            if (!isset($data[$property])) 
                continue;
                
            //Validate the individual property
            $this->loadSchemaProperty($schema, $property, $data[$property]);
        }

        $success = $this->errors == null || count($this->errors) == 0;
        $this->afterLoad($data, $success);
        return $success;
    }

    /** Loads individual properties */
    private function loadSchemaProperty($schema, $property, $value, $append = false) {
        if ($schema instanceof ArrayProperty) {
            if (!is_array($value)) {
                $this->addError("{$property} expects an array.");
                return;
            }

            $count = count($value);
            if ($schema->maxItems != null && $count > $schema->maxItems) {
                $this->addError("{$property} has too many items. Expect {$schema->maxItems} but got {$count}.");
                return;
            }

            if ($schema->minItems != null && $count < $schema->minItems) {
                $this->addError("{$property} has too few items. Expect {$schema->minItems} but got {$count}.");
                return;
            }

            //Iterate over every item and load them
            $this->{$property} = [];
            foreach($value as $val) {
                $this->loadSchemaProperty($schema->items, $property, $val, true);
            }

        } else {
            $result = null;

            // String Value
            if ($schema instanceof StringProperty) {
                if (!is_string($value)) {
                    $this->addError("{$property} is not a string.");
                    return;
                }
                $result = $value;
            }

            // Number Value
            if ($schema instanceof NumberProperty) {

                $result = filter_var($value, FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE);
                if ($result == null){
                    $this->addError("{$property} is not a float value.");
                    return;
                }
                $result = floatval($result);
            }

            // Int Value
            if ($schema instanceof IntegerProperty) {

                $result = filter_var($value, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
                if ($result == null){
                    $this->addError("{$property} is not a int value.");
                    return;
                }
                $result = intval($result);
            }

            // Bool Value
            if ($schema instanceof BooleanProperty) {
                $val = $value === '' ? 'false' : $value;
                $result = filter_var($val, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                if ($result === null){
                    $this->addError("{$property} is not a boolean value.");
                    return;
                }
                $result = boolval($result);
            }

            if ($schema instanceof EnumProperty) {

                if ($schema->assoc) {

                    //Make sure we are within the list
                    $isFound = false;
                    foreach($schema->enum as $index => $enumValue) {
                        if ($index == $value) {
                            $isFound = true;
                            break;
                        }
                    }

                    if (!$isFound) {
                        $this->addError("{$property} is not set to an enum value.");
                        return;
                    }

                    //If we are an integer enum, then upate the key
                    if ($schema->type == 'integer') { 
                        $result = intval($value);
                    }

                } else {

                    //Make sure we are within the list
                    $isFound = false;
                    foreach($schema->enum as $index => $enumValue) {
                        if ($enumValue == $value) {
                            $isFound = true;
                            break;
                        }
                    }

                    if (!$isFound) {
                        $this->addError("{$property} is not set to an enum value.");
                        return;
                    }
                }
            }

            if ($schema instanceof ObjectProperty) {
                $this->addError("{$property} cannot parse ObjectProperty");
                return;
            }

            // Object Value
            if ($schema instanceof RefProperty) {
                
                // apple: 10, mango: 30, orange: 3
                $class = $schema->getReferenceClassName();
                if (empty($class)) {
                    $this->addError("{$property} has a invalid RefProperty as it has no class.");
                    return;
                }

                if (!method_exists($class, "load")) {
                    if (is_subclass_of($class, \xve\configuration\Configurable::class) || is_subclass_of($class, BaseObject::class)) {
                        $result = new $class($value);
                    } else {
                        $this->addError("{$property} has a invalid RefProperty as the class does not have a load() definition or implement a loadable.");
                        return;
                    }
                } else {
                    $result = new $class();
                    $result->load($value);
                }
            }

            //Set the properties value. If we are an array then append.
            if ($append) { 
                $this->{$property}[] = $result;
            } else {
                $this->{$property} = $result;
            }
        }
    }

    /** Adds an error */
    protected function addError($error) { 
        if ($this->errors == null) $this->errors = [];
        $this->errors[] = $error;
        return $this;
    }

    /** @return string[] errors that have been generated */
    public function errors() { return $this->errors ?: []; }

    /** Gets the name of the current class */
    public function className() {
        return get_called_class();
    }
   
    /**
     * Get all the properties of the object
     * @param bool $skipNull skip null values.
     * @return array
     */
    public function getProperties($skipNull = true) {
        $all_properties = get_object_vars($this);
        $properties = [];

        foreach($all_properties as $full_name => $value) {
            if ($skipNull && $value == null) continue;
            $full_name_components = explode("\0", $full_name);
            $property_name = array_pop($full_name_components);
            if ($property_name && isset($value)) 
                $properties[$property_name] = $value;
        }

        return $properties;
    }

    /** @return array the default properties */
    public static function getPropertyDefaults() {        
        $class = get_called_class();
        return get_class_vars($class);
    }

    /** Gets the type of the property */
    public static function getPropertyType($property) {
        $schema = get_called_class()::getSchemaProperties();
        if (isset($schema[$property])) {
            
            $p = $schema[$property];
            if ($p instanceof ArrayProperty) {
                $p = $p->items;
            }

            if ($p instanceof RefProperty) {
                return $p->getReferenceClassName();
            }

            return $p->type;
        } else {
            $defaults = get_called_class()::getPropertyDefaults();
            if (isset($defaults[$property])){ 
                if (is_string($defaults[$property])) return 'string';
                if (is_float($defaults[$property])) return 'float';
                if (is_integer($defaults[$property])) return 'integer';
                if (is_bool($defaults[$property])) return 'boolean';
                return 'object';
            }
        }

        return null;
    }


    /** Gets the entire schema and resolves the definitions.
     * @return ObjectProperty 
     */
    public static function getJsonSchema($options = []) {
     
        //Prepare the title
        $class  = get_called_class();
        $index  = strrpos($class, '\\');
        $title  = substr($class, $index+1);

        //Get the schema
        $schema = new ObjectProperty($title, $class, [
            'options' => $options,
            'class' => $class
        ]);     

        //Add the definitions
        if (isset($options['definitions']))
            $schema->addDefinitions($options['definitions']);

        //Return the schema
        return $schema;
    }

    /** Gets the schema of an object's properties 
     * @return Property[] Associative array of properties*/
    public static function getSchemaProperties($options = []) {
        
        $variables    =  self::getPropertyDefaults();
        $properties = [];

        foreach($variables as $name => $value) {
            $resp = self::getValueSchemaProperty($value);
            if ($resp !== false) $properties[$name] = $resp;
        }

        return $properties;
    }

    /** Gets the default schema value for the given value. */
    private static function getValueSchemaProperty($value) {
        if ($value === null) return false;            
        
        if (is_float($value)) { 
            return new NumberProperty(null, $value); 
        }
        else if (is_numeric($value)) { 
            return new IntegerProperty(null, $value); 
        }
        else if (is_string($value)) { 
            return new StringProperty(null, $value); 
        } 
        else if (is_array($value)) {
            $first = reset($value); 
            return self::getValueSchemaProperty($first);
        }
        else if (is_object($value)) {
            //Get the object's class and make sure its an SchemaInterface
            $valueClass = get_class($value);
            if (in_array(SchemaInterface::class, class_implements($valueClass))) {
                return new RefProperty($valueClass);
            }
        } 
        
        return false;
    }

    /** {@inheritdoc} */
    function jsonSerialize() {
        //Only serializing what is available in the schema properties
        // This is really basic. Probably should do a more indepth version but meh
        // If we have sub BaseObject, they will get called themselves and will exclude the shit
        $properties = [];
        $schema = get_called_class()::getSchemaProperties();
        foreach($schema as $name => $property) {
            $properties[$name] = $this->{$name};
        }
        return $properties;
    }
}