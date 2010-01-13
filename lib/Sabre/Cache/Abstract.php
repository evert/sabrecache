<?php

    /**
     * Sabre_Cache_Abstract 
     *
     * @package Sabre
     * @subpackage Cache
     * @version $Id: Abstract.php 12600 2009-01-29 17:10:42Z evert $
     * @copyright Copyright (C) 2009 Rooftop Solutions. All rights reserved.
     * @author Evert Pot (http://www.rooftopsolutions.nl) 
     */

    /**
     * This is the abstract Cache class
     * Extend this class to build your custom cache engines
     */
    abstract class Sabre_Cache_Abstract {

        /**
         * defaultTTL
         * 
         * @var int 
         */
        protected $defaultTTL = 600;

        /**
         * Sets a prefix for a cache key
         *
         * This allows you to isolate caches for for example development branches sharing the same storage.
         * 
         * @var string
         */
        protected $keyPrefix='';

        /**
         * store 
         * 
         * @param string $key 
         * @param mixed $data 
         * @param int $ttl 
         * @return bool 
         */
        abstract function store($key,$data,$ttl=-1 );

        /**
         * fetch 
         * 
         * @param string $key 
         * @return mixed 
         */
        abstract function fetch( $key );

        /**
         * delete 
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
         * __get 
         * 
         * @param string $key 
         * @return mixed 
         */
        public function __get($key) {

            return $this->fetch($key);

        }

        /**
         * __set 
         * 
         * @param string $key 
         * @param mixed $value 
         * @return void
         */
        public function __set($key,$value) {

            $this->store($key,$value,$this->defaultTTL);

        }

        /**
         * __unset 
         * 
         * @param string $key 
         * @return void
         */
        public function __unset($key) {

            $this->delete($key);

        }

        /**
         * setDefaultTTL 
         * 
         * @param int $ttl 
         * @return void
         */
        public function setDefaultTTL($ttl) {

            $this->defaultTTL = $ttl;

        }

        /**
         * Returns lots of information about the cache engine
         *
         * @return array 
         */
        public function getInfo() {

            $data = array(
                'title'         => get_class($this),
                'class'         => get_class($this),
                'supportsFlush' => false,
                'totalItems'    => 0,
                'totalBytes'    => 0,
            );
            return $data;

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


