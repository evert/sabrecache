<?php

    /**
     * Sabre_Cache_APC 
     * 
     * @package Sabre
     * @subpackage Cache
     * @version $Id: APC.php 12600 2009-01-29 17:10:42Z evert $
     * @copyright Copyright (C) 2009 Rooftop Solutions. All rights reserved.
     * @author Evert Pot 
     */

    /**
     * The base class
     */
    require_once 'Sabre/Cache/Abstract.php';
   
    /**
     * Use this class for Alternative PHP Cache backend
     */
    class Sabre_Cache_APC extends Sabre_Cache_Abstract {

        /**
         * store 
         * 
         * @param string $key 
         * @param mixed $data 
         * @param int $ttl 
         * @return mixed 
         */
        function store( $key, $data, $ttl = -1 ) {

            if ($ttl = -1) $ttl = $this->defaultTTL; 
            return apc_store( $this->keyPrefix.$key,$data,$ttl );
            
        }

        /**
         * fetch 
         * 
         * @param string $key 
         * @return mixed 
         */
        function fetch( $key ) {
            
            $data = apc_fetch($this->keyPrefix.$key);
            return $data;
            
        }

        /**
         * delete 
         * 
         * @param string $key 
         * @return bool 
         */
        function delete( $key ) {

            return apc_delete( $this->keyPrefix.$key );

        }

        /**
         * Returns true if APC is available 
         * 
         * @return bool 
         */
        function isAvailable() {

            return function_exists('apc_store');

        }

        function getInfo() {

            $data = parent::getInfo();

            $apcInfo = apc_cache_info('user');

            $data['title'] = 'Advanced PHP Cache';
            $data['supportsFlush'] = true;
            $data['hits'] = $apcInfo['num_hits'];
            $data['misses'] = $apcInfo['num_misses'];
            
            return $data;

        }

        function flush() {

            apc_clear_cache();
            return true;

        }

        
    }




