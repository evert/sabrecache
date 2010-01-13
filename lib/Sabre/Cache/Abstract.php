<?php

/**
 * Abstract Cache class
 *
 * @package Sabre
 * @subpackage Cache
 * @version $Id: APC.php 12600 2009-01-29 17:10:42Z evert $
 * @copyright Copyright (C) 2009-2010 Rooftop Solutions. All rights reserved.
 * @author Evert Pot (http://www.rooftopsolutions.nl/)
 * @license http://code.google.com/p/sabrecache/wiki/License Modified BSD License

/**
 * This is the abstract Cache class.
 *
 * This class provides some default implementations for Cache functions.
 * It should be used to implement custom Cache engines.
 */
abstract class Sabre_Cache_Abstract {

    /**
     * Default TTL for cache items. 
     * 
     * @var int 
     */
    protected $defaultTTL = 600;

    /**
     * Prefix for all Cache keys used in the engine.  
     *
     * This allows you to isolate caches for for example development branches sharing the same storage for caching.
     * 
     * @var string
     */
    protected $keyPrefix='';

    /**
     * Stores a new item in the cache.
     *
     * The TTL is used to determine when the item is supposed
     * to expire. If null is given for a TTL value, the item should generally
     * only get erased when the cache is full.
     * 
     * @param string $key 
     * @param mixed $data 
     * @param int $ttl 
     * @return bool 
     */
    abstract function store($key,$data,$ttl=null);

    /**
     * Fetches an item from the cache.
     *
     * If the item was not found, null is returned.
     * 
     * @param string $key 
     * @return null 
     */
    abstract function fetch( $key );

    /**
     * Deletes an item from the cache. 
     * 
     * @param string $key 
     * @return bool 
     */
    abstract function delete( $key );


    /**
     * Returns true if the caching backend is available 
     * 
     * @return bool 
     */
    public function isAvailable() {

        return true;

    }

    /**
     * Changes the default TTL. 
     * 
     * @param int $ttl 
     * @return void
     */
    public function setDefaultTTL($ttl) {

        $this->defaultTTL = $ttl;

    }

    /**
     * Flushes the entire cache
     *
     * @return bool
     */
    public function flush() {

        return false;

    }

    /**
     * increments an integer value 
     *
     * If the key didn't not exist, it will not be created
     * If the key could not be converted to an integer, value will be placed in the item
     * Note that this is not an atomic operation, which the memcached implementation does provide
     * This method will return the new value 
     * 
     * @param string $key 
     * @param int $value 
     * @return int 
     */
    public function increment($key,$value = null) {

        $oldvalue = $this->fetch($key);
        if (!is_null($oldvalue)) {
            $newvalue = (is_numeric($oldvalue))?$oldvalue+1:$value; 
            $this->store($key,(int)$newvalue);
            return $newvalue;
        }

    }

    /**
     * decrements an integer valu
     *
     * If the key didn't not exist, it will not be created
     * If the key could not be converted to an integer, value will be placed in the item
     * The new items value will never be less than 0
     * Note that this is not an atomic operation, which the memcached implementation does provide
     *
     * This method will return the new value
     * 
     * @param string $key 
     * @param int $value 
     * @return int 
     */
    public function decrement($key,$value = null) {

        $oldvalue = $this->fetch($key);
        if (!is_null($oldvalue)) {
            $newvalue = (is_numeric($oldvalue))?$oldvalue-1:$value; 
            if ($newvalue<1) $newvalue = 0;
            $this->store($key,(int)$newvalue);
            return $newvalue;
        }

    }

    /**
     * This method allows you to get multiple cache entries through one call
     *
     * This method will return cache-misses as null-entries
     * The returned array will use the cache keys as array-keys.
     *
     * Implementing classes could choose to override this method to write a faster 
     * engine-specific implementation.
     * 
     * @param array $keys 
     * @return void
     */
    public function fetchMulti(array $keys) {

        $data=array();
        foreach($keys as $key) {

            $data[$key] = $this->fetch($key);

        }

        return $data;


    }


}


