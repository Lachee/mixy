<?php
namespace kiss\schema;

use JsonSerializable;

class RefProperty extends Property {

    /** {@inheritdoc} */
    public $type = null;
    /** {@inheritdoc} */
    public $title = null;
    /** {@inheritdoc} */
    public $description = null;
    /** {@inheritdoc} */
    public $default = null;
    /** {@inheritdoc} */
    public $format = null;

    /** @var string|SchemaInterface The referenced class name. */
    public $reference;

    public function __construct($reference, $description = null, $properties = [])
    {
        parent::__construct($properties);
        $this->description = $description;
        $this->reference = $reference;
    }

    /** @return string Referenced name */
    public function getReferenceClassName() {
        if (is_string($this->reference)) return $this->reference;
        return get_class($this->reference);
    }

    /** Gets the schema properties for the referenced type. 
     * @return Property[string] referenced properties
    */
    public function getReferenceProperties($options = []) {
        $reference = $this->getReferenceClassName();

        if (empty($reference) || !in_array(SchemaInterface::class, class_implements($reference)))
            throw new \Exception("{$reference} does not implement SchemaInterface.");

        return $reference::getSchemaProperties($options);
    }

    /** {@inheritdoc} */
    function jsonSerialize() {
        return [ '$ref' => '#/definitions/' . $this->getReferenceClassName() ];
    }
}