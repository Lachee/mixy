<?php

//Define the autoload directory and file
define('KISS_AUTOLOAD_DIR', __DIR__);
define('KISS_AUTOLOAD_FILE', __FILE__);

//Register the vendor autoload
include 'vendor/autoload.php';

//Register the autoloader
spl_autoload_register(function ($name) 
{ 
    //$name = substr($name, 4);
    if ($name == false) return;
    $map = __DIR__ . "/$name.php";
    $map = str_replace('\\', '/', $map);
    if (!@include_once($map)) 
        throw new Exception("Failed to find {$name} as its file does not exist ({$map}).");
});

//Implement functions for older versions of PHP
if (!function_exists('is_countable')) {
    function is_countable($c) {
        return is_array($c) || $c instanceof Countable;
    }    
}

//Configure and create instance
include 'config.php';
if (empty($config)) { $config = []; }

Global $kiss;
$kiss = new \kiss\Kiss($config);