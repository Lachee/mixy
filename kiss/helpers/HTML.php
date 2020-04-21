<?php
namespace kiss\helpers;
class HTML {
    public static $title = 'KISS Dev';
    public static $route = '';

    /** Prepares a URL with special prefix:
     * http - indicates the full URL should be used
     * @    - relative route.
     * \    - a class route. 
     */
    public static function href($route, $excludeParameters = false) {
        $url    = null;
        $params = [];
        $query  = '';

        if (is_array($route)) {
            foreach($route as $key => $pair) {
                if ($key === 0) {
                    $url = $pair; 
                    continue; 
                }
                $params[$key] = $pair;
            }
        } else {
            $url = $route;
        }

        //Build the query
        if (!$excludeParameters && count($params) > 0) {
            $query = '?' . http_build_query($params);
        }
        
        //Absolute, so lets return the http.
        if (strpos($url, 'http') === 0) {
            return $url . $query;
        }

        //Relative from the current controller
        if (strpos($url, '@') === 0) {
            $url = substr($url, 1);
            $mod = 1;

            if (strpos($url, '/') === 0 && StringHelper::endsWith(self::$route, '/'))
                $mode = 0;

            $route = substr(self::$route, 0, strrpos(self::$route, "/") + $mod);
            $url = $route . substr($url, 1);

        }
        
        //Conver the class to a route
        if (strpos($url, '\\') !== false) {
            $url = $url::getRouting() . '/';
        }

        return $url . $query;
    }

    /** @return string encodes the content to be HTML safe */
    public static function encode($text) {
        return htmlspecialchars($text);
    }
}