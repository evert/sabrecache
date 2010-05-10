<?php

/**
 * Memcache backend
 * 
 * @package Sabre
 * @subpackage Cache
 * @copyright Copyright (C) 2009-2010 Rooftop Solutions. All rights reserved.
 * @author Evert Pot (http://www.rooftopsolutions.nl/)
 * @license http://code.google.com/p/sabrecache/wiki/License Modified BSD License
 */

/**
 * Use this class for MemCached Cache backend
 */
class Sabre_Cache_MemCache extends Sabre_Cache_Abstract {

    /**
     * Memcache backend  
     * 
     * @var MemCache 
     */
    private $memcache;

    /**
     * Creates the object. You should pass a working 
     * Memcache object, which we'll use as the backend.
     * 
     * @return void
     */
    public function __construct(MemCache $memcache) {

        $this->memcache = $memcache;

    }

    /**
     * Stores a value in the cache.
     *
     * Use the ttl value to specify when to expire the item
     * specify null for the TTL to use the default TTL.
     * 
     * @param string $key 
     * @param mixed $data 
     * @param int $ttl 
     * @return bool
     */
    public function store($key, $data, $ttl = null) {

        if (strlen($this->keyPrefix.$key) >= 240)
            throw new Sabre_Cache_Exception('Key length too long, memcache keys must be less than 240 characters, key was ' . strlen($this->keyPrefix.$key) . ' chars (' . $this->keyPrefix . $key . ')');

        if (is_null($ttl==-1))
            $ttl = $this->defaultTTL;

        // Memcache treats any expiry value greater than 30 days (2592000) as a Unix timestamp.
        // longer than 30 days to timestamps.
        if ($ttl > 2592000)
            $ttl = time() + $ttl;

        return $this->memcache->set($this->keyPrefix.$key,$data,0,$ttl);

    }

    /**
     * Fetches an item from the cache 
     * 
     * @param string $key 
     * @return mixed 
     */
    public function fetch($key) {

        $data = $this->memcache->get( $this->keyPrefix.$key );
        if ($data===false) return null;
        return $data;

    }

    /**
     * Deletes an item from the cache 
     * 
     * @param string $key 
     * @return bool 
     */
    public function delete($key) {

        return $this->memcache->delete($this->keyPrefix.$key);

    }
   
    /**
     * Returns true if memcached is available 
     * 
     * @return bool 
     */
    public function isAvailable() {

        return function_exists('memcache_connect');

    }

    /**
     * increments an integer value in memcached
     *
     * If the key did not exist, it will not be created
     * If the key could not be converted to an integer, value will be placed in the item
     *
     * This method will return the new value 
     * 
     * @param string $key 
     * @param int $value 
     * @return int 
     */
    public function increment($key,$value = null) {

        return $this->memcache->increment($this->keyPrefix.$key,$value);

    }

    /**
     * decrements an integer value in memcached
     *
     * If the key did not exist, it will not be created
     * If the key could not be converted to an integer, value will be placed in the item
     * The new items value will never be less than 0
     *
     * This method will return the new value
     * 
     * @param string $key 
     * @param int $value 
     * @return int 
     */
    public function decrement($key,$value = null) {

        return $this->memcache->decrement($this->keyPrefix.$key,$value);

    }

    /**
     * Flushes memcache servers 
     * 
     * @return bool 
     */
    public function flush() {

        return $this->memcache->flush();

    }

}
