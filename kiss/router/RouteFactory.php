<?php
namespace kiss\router;

use kiss\helpers\StringHelper;

class RouteFactory {

    const ARGUMENT_PREFIX = ":";
    private static $routes = [];

    /** @var RouteProperty */
    private $routing;

    private $className;
    
    public function __construct($routeClass) {
        $this->className = $routeClass;
        $this->routing = $routeClass::getRouting();
    }

    public function getRoute() { return $this->routing; }

    /** calculates the score represnting how close the segment matches this route. The higher the score the better. */
    public function calculateRouteScore($segments) {
        
        if (!is_array($segments)) 
            throw new \Exception("Segements must be an array");

        $selfSegments = explode('/', $this->routing);
        if (count($segments) != count($selfSegments)) return 0;

        $score = 0;
        for ($i = count($selfSegments) - 1; $i >= 0; $i--) {
            if (trim($selfSegments[$i]) == trim($segments[$i]))             $score += 20;   //We match exactly, bonus points
            else if (StringHelper::startsWith($selfSegments[$i], self::ARGUMENT_PREFIX))  $score += 1;    //We match in the argument, so some points
            else return 0;                                                                      //We stopped matching, so abort early.
        }

        return $score;
    }

    /** create will convert the provided URL segments into a new object with properties that match the routing settings.
     * IE: /apples/:count => /apples/4252 :
     * AppleRoute {
     *     count => 4252
     * }
     */
    public function create($segments) { 
        $selfSegments = explode('/', $this->routing);
        $object = new $this->className;

        for ($i = 0; $i < count($selfSegments); $i++) {
            if (StringHelper::startsWith($selfSegments[$i], self::ARGUMENT_PREFIX)) {
                $name = substr($selfSegments[$i], 1);
                $object->{$name} = $segments[$i];
            }
        }

        $object->segments = $segments;
        return $object;
    }


    /** Registers a route */
    public static function register($routeClass) {
        if (empty($routeClass::getRouting())) return false;
        self::$routes[] = new RouteFactory($routeClass);
        return true;
    }

    /** Registers the directory of routes. */
    public static function registerDirectory($directory, $filters = "*.php") {
        $count = 0;

        if (!is_array($filters)) 
            $filters = [$filters];

        foreach($filters as $filter) {
            //Scan the directory and include all the files
            foreach (glob($directory . $filter) as $filename)
            {
                include $filename;
                $count++;
            }
        }

        //Search all the declared classes and register them
        foreach(get_declared_classes() as $class) {
            if(is_subclass_of($class, Route::class)) 
                self::register($class);
        }

        //Return how many we found.
        return $count;
    }

    /**  route will find the appropriate route factory and instiate the route for it. */
    public static function route($segments) {
        $bestScore = 0;
        $bestRoute = null;
        foreach(self::$routes as $r) {
            $score = $r->calculateRouteScore($segments);
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestRoute = $r;
            }
        }

        if ($bestRoute == null) return null;
        return $bestRoute->create($segments);
    }

    /** Gets a list of routes and their supported methods. */
    public static function getRoutes() {
        $names = [];
        foreach(self::$routes as $r) {
            $path = $r->getRoute();
            $methods = [];
            $controller = new $r->className;
            if (method_exists($controller, 'get')) $methods[] = 'get'; 
            if (method_exists($controller, 'delete')) $methods[] = 'delete';
            if (method_exists($controller, 'put')) $methods[] = 'put';
            if (method_exists($controller, 'post')) $methods[] = 'post';
            $names[$path] = $methods;
        }
        return $names;
    }
}