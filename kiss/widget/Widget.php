<?php
namespace kiss\widget;

use kiss\models\BaseObject;

class Widget extends BaseObject {

    /** Adds the start tags for the widget */
    public function begin() {}

    /** Adds the end tags for the widget */
    public function end() {}

    /** echos out the instance immediately */
    public function run() { 
        $this->begin();
        $this->end();
    }

    /** Creates a new wiget instance. */
    public static function widget($options = []) {
        $class = get_called_class();
        $obj = new $class($options);
        $obj->run();
        return '';
    }
}