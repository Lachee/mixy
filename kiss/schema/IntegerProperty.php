<?php
namespace kiss\schema;


class IntegerProperty extends Property {

    /** {@inheritdoc} */
    public $type = 'integer';


    /** {@inheritdoc} */
    public function __construct($description, $default = null, $properties = [])
    {
        parent::__construct($properties);
        $this->description = $description;
        $this->default = $default;
    }
}