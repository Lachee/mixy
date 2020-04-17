<?php
namespace kiss\router;

use kiss\models\BaseObject;

class Route extends BaseObject {
    public $segments;
    public static function getRouting() { 
        $class = get_called_class();
        return str_replace('\\', '/', $class);
    }
}