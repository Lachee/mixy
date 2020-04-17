<?php
    include 'vendor/autoload.php';

    spl_autoload_register(function ($name) 
    { 

        //$name = substr($name, 4);
        if ($name == false) return;
        $map = __DIR__ . "/$name.php";
        $map = str_replace('\\', '/', $map);
        if (!@include_once($map)) 
            throw new Exception("Failed to find {$name} as its file does not exist ({$map}).");
    });

    
    /** checks if the string starts with another substring */
    function startsWith (String $string, String  $startString) : bool
    { 
        $len = strlen($startString); 
        return (substr($string, 0, $len) === $startString); 
    } 

    /** checks if the string ends with another substring */
    function endsWith(String $string, String  $endString) : bool
    { 
        $len = strlen($endString); 
        if ($len == 0) { 
            return true; 
        } 
        return (substr($string, -$len) === $endString); 
    } 

    if (!function_exists('is_countable')) {
        function is_countable($c) {
            return is_array($c) || $c instanceof Countable;
        }    
    }

    include 'config.php';
    if (empty($config)) { $config = []; }
    $app = new App($config);