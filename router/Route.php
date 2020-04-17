<?php
namespace router;

use exception\HttpException;
use helpers\HTTP;

class Route {
    public $segments;
    public static function getRouting() { 
        $class = get_called_class();
        return str_replace('\\', '/', $class);
    }
}