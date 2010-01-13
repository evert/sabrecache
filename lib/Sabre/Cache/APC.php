<?php

/**
 * APC cache backend 
 * 
 * @package Sabre
 * @subpackage Cache
 * @version $Id: APC.php 12600 2009-01-29 17:10:42Z evert $
 * @copyright Copyright (C) 2009-2010 Rooftop Solutions. All rights reserved.
 * @author Evert Pot (http://www.rooftopsolutions.nl/)
 * @license http://code.google.com/p/sabrecache/wiki/License Modified BSD License
 */

/**
 * Use this class for APC Cache backend
 */
class Sabre_Cache_APC extends Sabre_Cache_Abstract {

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

        if (is_null($ttl)) $ttl = $this->defaultTTL; 
        return apc_store( $this->keyPrefix.$key,$data,$ttl );
        
    }

    /**
     * Grabs an item from the cache.
     * This function will return null if the item was not available
     * 
     * @param string $key 
     * @return mixed 
     */
    public function fetch( $key ) {
        
        $data = apc_fetch($this->keyPrefix.$key);
        return $data;
        
    }

    /**
     * Deletes an item from the cache. 
     * 
     * @param string $key 
     * @return bool 
     */
    public function delete( $key ) {

        return apc_delete( $this->keyPrefix.$key );

    }

    /**
     * Returns true if the APC cache is available. 
     * 
     * @return bool 
     */
    public function isAvailable() {

        return function_exists('apc_store');

    }

    /**
     * Flushes all items from the cache. 
     * 
     * @return bool 
     */
    public function flush() {

        apc_clear_cache();
        return true;

    }

    
}

