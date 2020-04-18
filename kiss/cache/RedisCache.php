<?php namespace kiss\cache;

class RedisCache extends \Predis\Client  { 
    public function setString($key, $value) {}
}