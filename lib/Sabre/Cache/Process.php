<?php

/**
 * Process cache
 *
 * @package Sabre
 * @subpackage Cache
 * @copyright Copyright (C) 2009-2010 Rooftop Solutions. All rights reserved.
 * @author Evert Pot (http://www.rooftopsolutions.nl/)
 * @license http://code.google.com/p/sabrecache/wiki/License Modified BSD License
 */


/**
 * This cache just serves as a dummy cache class.
 * All data is stored in an array, so everything will be gone as soon as
 * the script is stopped.
 *
 * This is useful as a drop-in replacement when working with for example
 * shell scripts.
 * 
 */
class Sabre_Cache_Process extends Sabre_Cache_Abstract {

    /**
     * The data array 
     * 
     * @var array
     */
    private $data = array();

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
    public function store($key, $data, $ttl = null) {

        if (is_null($ttl)) $ttl = $this->defaultTTL;

        $this->data[$this->keyPrefix.$key] = array(time()+$ttl,$data);
        return true;
        
    }

    /**
     * Fetches an item from the cache 
     * 
     * @param string $key 
     * @return mixed 
     */
    public function fetch($key) {

        if (!isset($this->data[$this->keyPrefix.$key])) return null; 
        
        $data = $this->data[$this->keyPrefix.$key]; 
        if (time() > $data[0]) {

            unset($this->data[$this->keyPrefix.$key]);
            return false;

        }
        return $data[1];
      
    }

    /**
     * Deletes an item from the cache 
     * 
     * @param string $key 
     * @return bool 
     */
    public function delete($key) {

        if (isset($this->data[$this->keyPrefix.$key])) {
            unset($this->data[$this->keyPrefix.$key]);
            return true;
        } else {
            return false;
        }

    }

    /**
     * Returns true if this caching engine is available.
     * 
     * @return bool 
     */
    public function isAvailable() {

        return true; 

    }

    /**
     * increments an integer value 
     *
     * If the key didn't not exist, it will not be created
     * If the key could not be converted to an integer, value will be placed in the item
     * This method will return the new value 
     * 
     * @param string $key 
     * @param int $value 
     * @return int 
     */
    public function increment($key,$value = null) {

        if (!isset($this->data[$this->keyPrefix.$key])) return null;
        $oldvalue = $this->data[$this->keyPrefix.$key][1];
        if (is_int($oldvalue) || ctype_digit($oldvalue)) {
            return ++$this->data[$this->keyPrefix.$key][1];
        } else {
            $this->data[$this->keyPrefix.$key][1] = $value;
            return $value;
        }

    }

    /**
     * decrements an integer value
     *
     * If the key didn't not exist, it will not be created
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

        if (!isset($this->data[$this->keyPrefix.$key])) return null;
        $oldvalue = $this->data[$this->keyPrefix.$key][1];
        if (is_int($oldvalue) || ctype_digit($oldvalue)) {
            return ++$this->data[$this->keyPrefix.$key][1];
        } else {
            $this->data[$this->keyPrefix.$key][1] = $value;
            return $value;
        }

    }

}

