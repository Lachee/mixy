<?php namespace kiss\cache;

use kiss\models\BaseObject;

/** Short-Term storage of values */
interface ICache {
    function setString($key, $value);
    function getString($key, $value, $default = null);
}