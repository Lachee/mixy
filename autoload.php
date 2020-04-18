<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//Define the autoload directory and file
define('KISS_AUTOLOAD_DIR', __DIR__);
define('KISS_AUTOLOAD_FILE', __FILE__);

//Register the vendor autoload
include 'vendor/autoload.php';

//Register the basic autoloader
spl_autoload_register(function ($name) 
{ 
    if ($name == false) return false;  
        
    //Try to find a map based of kiss
    if (class_exists('\\kiss\\Kiss', false)) {

        //Attempt to trim
        $base = \kiss\Kiss::$app->getBaseNamespace();
        if (strpos($name, $base) === 0) {
            $name = substr($name, strlen($base));            
        }
    }

    $file = __DIR__ . "/$name.php";
    $file = str_replace('\\', '/', $file);
    return @include_once($file);
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
$kiss = \kiss\models\BaseObject::new($config)