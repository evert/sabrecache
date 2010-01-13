<?php

    /**
     * Sabre_Cache_MemCache
     * 
     * @package Sabre
     * @subpackage Cache
     * @version $Id: MemCache.php 15244 2009-10-23 15:12:02Z kevin $
     * @copyright Copyright (C) 2009 Rooftop Solutions. All rights reserved.
     * @author Evert Pot 
     */

    /**
     * The base class
     */
    require_once 'Sabre/Cache/Abstract.php';


    /**
     * Use this class for MemCached Cache backend
     */
    class Sabre_Cache_MemCache extends Sabre_Cache_Abstract {

        private $conn;

        /**
         * __construct 
         * 
         * @return void
         */
        public function __construct() {

            if ($this->isAvailable()) {
                $this->conn = new MemCache;
            }

        }

        public function addServers(array $servers) {

            foreach($servers as $server) $this->conn->addServer($server);
    
        }

        /**
         * store 
         * 
         * @param string $key 
         * @param mixed $data 
         * @param int $ttl 
         * @return mixed 
         */
        public function store( $key, $data, $ttl = -1 ) {
            if ( strlen ( $this->keyPrefix.$key ) >= 240 )
                throw new Sabre_Cache_Exception ( 'Key length too long, memcache keys must be < 240 characters, key was ' . strlen ( $this->keyPrefix.$key ) . " chars:\n" . $this->keyPrefix . $key );

            if ($ttl==-1)
                $ttl = $this->defaultTTL;

            // Memcache treats any expiry value greater than 30 days (2592000) as a Unix timestamp;
            // we don't want to force our users to deal with this so we'll convert expiry times
            // longer than 30 days to timestamps.
            if ( $ttl > 2592000 )
                $ttl = time () + $ttl;

            try {
                return $this->conn->set( $this->keyPrefix.$key,$data,0,$ttl );
            }
            catch ( Exception $ex ) {
                return false;
            }
        }

        /**
         * fetch 
         * 
         * @param string $key 
         * @return mixed 
         */
        public function fetch( $key ) {

            try {
                $data = $this->conn->get( $this->keyPrefix.$key );
            } catch (Sabre_PHP_Exception $e) {
                // This normally shouldn't happen and indicates a problem
                return null;

            }
            //$h = fopen('/tmp/memcachelog','a');
            //fwrite($h,($data?'Found:':'Not Found') . $key . "\n");
            //fclose($h);
            return $data;
        }

        /**
         * delete 
         * 
         * @param string $key 
         * @return bool 
         */
        public function delete( $key ) {

            return $this->conn->delete( $this->keyPrefix.$key );

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
         * Calls memcache's add function to only add a key-value pair if the key does not yet exist
         */
        public function add ( $key, $value, $ttl ) {
            if ( $ttl == -1 )
                $ttl = $this->defaultTTL;

            // Memcache treats any expiry value greater than 30 days (2592000) as a Unix timestamp;
            // we don't want to force our users to deal with this so we'll convert expiry times
            // longer than 30 days to timestamps.
            if ( $ttl > 2592000 )
                $ttl = time () + $ttl;

            try {
                return $this->conn->add ( $this->keyPrefix . $key, $value, 0, $ttl );
            }
            catch ( Exception $ex ) {
                return false;
            }
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

            return $this->conn->increment($this->keyPrefix.$key,$value);

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

            return $this->conn->decrement($this->keyPrefix.$key,$value);

        }

        public function getStats() {

            return $this->conn->getStats();

        }


        public function getExtendedStats() {

            return $this->conn->getExtendedStats();

        }
     

        public function getInfo() {

            $data = parent::getInfo();
            $data['title'] = 'Distributed Memory Cache';
            $data['supportsFlush'] = true;
            $data['hits'] = 0;
            $data['misses'] = 0;

            $mdata = ($this->conn->getExtendedStats());

            foreach($mdata as $server) {

                $data['totalItems'] += $server['total_items'];
                $data['totalBytes'] += $server['bytes'];
                $data['hits'] += $server['get_hits'];
                $data['misses'] += $server['get_misses'];

            }
            
            return $data;

        }

        public function flush() {

            return $this->conn->flush();

        }

    }
