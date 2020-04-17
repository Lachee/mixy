<?php
namespace db;

use core\models\BaseObject;

class ActiveRecord extends BaseObject{

    private $_columns = null;

    /** The name of the table */
    public static function tableName() 
    {
        $parts = explode('\\', get_called_class());
        return "$" . strtolower(end($parts)); 
    }

    /** The ID of the table */
    public static function tableKey() { return ['id']; }

    /** Before the load */
    protected function beforeQueryLoad($data) {}
    /** After the load */
    protected function afterQueryLoad($data) {}

    /** Before the save */
    protected function beforeSave() {}

    /** After the save */
    protected function afterSave() {}

    /** An array of fields */
    public function fields() { 
        if (!empty($this->_columns)) 
            return $this->_columns;
        
        $keys = array_keys(get_object_vars($this));
        $fields = [];
        foreach($keys as $k) { 
            if (strpos($k, "_") !== 0) {
                $fields[] = $k;
            }
        }

        return $fields;
    }
    
    /** The columns that have been loaded in the last Load(); */
    public function columns() { return $this->_columns; }

    /** Will load the active record with the given tableKey. Will define the properties of the object and store the loaded columns. 
     * {@inheritdoc}
    */
    public function load($data = null) {
        
        //Load via the schema
        if ($data != null) {
            return parent::load($data);
        }

        //Otherwise load it regularly
        $this->beforeLoad($data);
        $tableKeys = self::tableKey();

        //Prepare the query
        $query = \App::$xve->db()->createQuery()->select(self::tableName())->limit(1);

        //Add the table keys as the where condition
        if (is_string($tableKeys)) {
            $query->andWhere([$tableKeys, $this->{$tableKeys}]);
        } else {
            foreach($tableKeys as $key) {
                $query->andWhere([$key, $this->{$key}]);
            }
        }

        //Execute the query
        $result = $query->execute();
        if ($result !== false) {

            //Make sure we have the correct amount of values
            if (count($result) == 0) {
                $this->afterLoad([], false);
                return false;
            }
            
            //Copy all the values over
            assert(count($result) == 1, 'Only 1 item found.');
            $this->setQueryResult($result[0]);
            $this->afterLoad($result[0], true);
            return true;
        }

        //We didn't load anything
        $this->afterLoad([], false);
        return false;
    }

    /** Saves the active record using the tableFields. Returns false if unsuccessful.*/
    public function save() {
        
        $this->beforeSave();

        //Prepare all the values
        $values = [];
        foreach ($this->fields() as $key) {
            $values[$key] = $this->{$key};
        }

        $class = get_called_class();
        $table = $class::tableName();

        //Prepare the query and execute
        $query = \App::$xve->db()->createQuery()->insertOrUpdate($values, $table);
        
        //Execute the query, returning false if it didn't work
        $result = $query->execute();
        if ($result === false) {
            $this->addError('Failed to execute the save query.');
            return false;
        }

        //Update our ID
        $tableKeys = self::tableKey();
        if (is_string($tableKeys)) {
            $this->{$tableKeys} = $result;
        } else {            
            $this->{$tableKeys[0]} = $result;
        }

        //Return the last auto incremented id
        $this->afterSave();
        return true;
    }

    /** Sets the results from the query */
    public function setQueryResult($result) {
        $this->beforeQueryLoad($result);
        foreach($result as $key => $pair) {
            $type = self::getPropertyType($key);
            switch($type) {
                default:
                case 'string':
                    $this->{$key} = "{$pair}";
                    break;
                case 'integer':
                    $this->{$key} = intval($pair);
                    break;
                case 'float':
                    $this->{$key} = floatval($pair);
                    break;
                case 'boolean':
                    $this->{$key} = boolval($pair);
                    break;
                    
                //TODO: Add support for refs here.
            }

            $this->_columns[] = $key;
        }
        $this->afterQueryLoad($result);
    }

    /** Prepares an ActiveQuery statement for the class 
     * @return ActiveQuery
    */
    public static function find() {
        return new ActiveQuery(\App::$xve->db(), get_called_class());
    }

    /** Finds the query by keys.
     * @param mixed $keys either a key, op, keys or a string of value.
     * @return ActiveQuery
     */
    public static function findByKey($keys) {
        
        $tableKeys = self::tableKey();
        if (is_string($tableKeys)) {
            $tableKeys = [ self::tableKey() ];
        }

        $condition = [];

        if (!is_array($keys)) {
            $condition = [ $tableKeys[0], '=', $keys ];
        } else {
            foreach ($keys as $key => $value) {
                if (is_numeric($key)) {
                    $condition[] = [ $tableKeys[$key], '=', $value ];
                } else {
                    $condition[] = [$key, '=', $value ];
                }
            }
        }

        return self::find()->where($condition);
    }
}