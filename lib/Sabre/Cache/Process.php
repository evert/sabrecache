<?php

    /**
     * Sabre_Cache_Process
     * 
     * @package Sabre
     * @subpackage Cache
     * @version $Id: Process.php 12600 2009-01-29 17:10:42Z evert $
     * @copyright Copyright (C) 2009 Rooftop Solutions. All rights reserved.
     * @author Evert Pot (http://www.rooftopsolutions.nl) 
     */

    /**
     * The base class
     */
    require_once 'Sabre/Cache/Abstract.php';
  
    /**
     * The base cache exception
     */
    require_once 'Sabre/Cache/Exception.php';
  
    /**
     * Use this class if no other cache systems are available
     * The cache will only last for the current process
     */
    class Sabre_Cache_Process  extends Sabre_Cache_Abstract {

        private $data = array();

        /**
         * store 
         * 
         * @param string $key 
         * @param mixed $data 
         * @param int $ttl 
         * @return mixed 
         */
        function store( $key, $data, $ttl = -1 ) {

            if ($ttl=-1) $ttl = $this->defaultTTL;

            $this->data[$this->keyPrefix.$key] = array(time()+$ttl,$data);
            return true;
            
            
        }

        /**
         * fetch 
         * 
         * @param string $key 
         * @return mixed 
         */
        function fetch( $key, $maxage = -1 ) {

            if (!isset($this->data[$this->keyPrefix.$key])) return false;
            
            $data = $this->data[$this->keyPrefix.$key]; 
            if (time() > $data[0]) {

                unset($this->data[$this->keyPrefix.$key]);
                return false;

            }
            return $data[1];
          
        }

        /**
         * delete 
         * 
         * @param string $key 
         * @return bool 
         */
        function delete( $key ) {

            if (isset($this->data[$this->keyPrefix.$key])) unset($this->data[$this->keyPrefix.$key]);

        }

        /**
         * Returns true if the filecacher is available 
         * 
         * @return bool 
         */
        function isAvailable() {

            return true; 

        }

        function getInfo() {

            $data = parent::getInfo();
            $data['title']      = 'Process Cache (fallback cache engine)';
            $data['totalItems'] = count($this->data);

            return $data;

        }
        
    }




