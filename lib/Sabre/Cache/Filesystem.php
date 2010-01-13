<?php

    /**
     * Sabre_Cache_Filesystem
     * 
     * @package Sabre
     * @subpackage Cache
     * @version $Id: Filesystem.php 12600 2009-01-29 17:10:42Z evert $
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
     * Use this class for Alternative PHP Cache backend
     */
    class Sabre_Cache_Filesystem extends Sabre_Cache_Abstract {

        private $basePath;

        /**
         * Creates the cache handler
         *
         * Basepath can be used to specify the base filename, for all cached files. 
         * The base path will simply be appended by a keyname, so make sure you include a / if you use a custom path
         *
         * If nothing is specified php.ini's session.save_path will be used, appended by /s_cache
         * If session_save_path doesn't exist /tmp/s_cache will be used
         * 
         * @param string $basePath 
         */
        public function __construct($basePath = null) {

            if (!$basePath) {

                $basePath = ini_get('session.save_path');
                if (!$basePath) $basePath = '/tmp';
                $basePath.='/s_cache';

            }

            $this->basePath = $basePath;

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

            if ($ttl=-1) $ttl = $this->defaultTTL;

            $h = fopen($this->getFileName($key),'a+');
            if (!$h) {
                throw new Sabre_Cache_Exception('Could not write to cachefile: ' . $this->getFileName($key));
            }
            flock($h,LOCK_EX);
            fseek($h,0);
            ftruncate($h,0);
            if (fwrite($h,serialize(array(time()+$ttl,$data))) === false) {
                throw new Sabre_Cache_Exception('Could not write to cachefile: ' . $this->getFileName($key));
            }
            fclose($h);
            @chmod($this->getFileName($key),0666);
            return true;
            
            
        }

        /**
         * Returns the base path used for caching files 
         * 
         * @return string 
         */
        public function getBasePath() {

            return $this->basePath;

        }

        /**
         * Returns the filename for a specific cache key 
         * 
         * @param string $key 
         * @return string 
         */
        public function getFileName($key) {

            return $this->basePath.md5($this->keyPrefix.$key); 

        }

        /**
         * fetch 
         * 
         * @param string $key 
         * @return mixed 
         */
        public function fetch( $key, $maxage = -1 ) {

            $filename = $this->getFileName($key);
            if (!file_exists($filename)) return false;
            if ($maxage>0 && (filemtime($filename)+$maxage) < time()) {
                try {
                    unlink($filename);
                } catch (Exception $e) {
                    return false;
                }
                return false;
            }
            
            $h = fopen($filename,'r');
            flock($h,LOCK_SH);
            if (!$h) return false;
            $data = '';
            while(!feof($h)) $data.=fread($h,4096);
            fclose($h);
            
            $data = @unserialize($data);
            if (!$data) {
                try {
                    unlink($filename);
                } catch (Exception $e) {}
                return false;
            }
            if ($maxage<0 && time() > $data[0]) {

                try {
                    @unlink($filename);
                } catch (Exception $e) {}
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
        public function delete( $key ) {

            $filename = $this->getFileName($key);
            if (file_exists($filename)) {
                try {
                    return unlink($filename);
                } catch (Exception $e) {
                    return false;
                }
            } else {
                return false;
            }

        }

        public function exists($key) {

            $fileName = $this->getFileName($key);
            return file_exists($fileName);

        }

        /**
         * Returns true if the filecacher is available 
         * 
         * @return bool 
         */
        public function isAvailable() {

            $r =is_writable(dirname($this->getFileName('teststring')));
            return $r; 

        }

        /**
         * Returns information about this engine 
         * 
         * @return array 
         */
        public function getInfo() {

            $path = dirname($this->getFileName('nothing'));

            $data = parent::getInfo();

            $data['title'] = 'Filesystem cache';
            $data['savePath'] = $path;
            $data['supportsFlush'] = true;

            try {
                foreach(scandir($path) as $file) {

                    if (strpos($file,'s_cache')===0) {

                        $data['totalItems']++;
                        $data['totalBytes']+= filesize($path . '/' . $file);

                    }

                }
            } catch (Sabre_PHP_Exception $e) { }

            return $data;

        }

        /**
         * Flushes the cache 
         * 
         * @return bool 
         */
        public function flush() {
            
            $path = dirname($this->getFileName('nothing'));

            foreach(scandir($path) as $file) {

                if (strpos($file,'s_cache')===0) {

                    unlink($path . '/' . $file);

                }

            }
            return true;

        }

        /**
         * Returns the free disk space
         * 
         * @return float 
         */
        public function getFreeSpace() {

            return disk_free_space(dirname($this->basePath));

        }


        /**
         * Returns the total disk space 
         * 
         * @return float 
         */
        public function getTotalSpace() {

            return disk_total_space(dirname($this->basePath));

        }

        /**
         * Locks a file based on its cache key
         *
         * This method returns a filehandle, which should be passwed to unlock 
         * 
         * @param string $key 
         * @return resource 
         */
        public function lock($key) {

            $handle = fopen($this->getFileName($key),'a+');
            flock($handle,LOCK_EX);

            return $handle;

        }

        /**
         * Unlocks a file
         *
         * This method expects a filehandle, which will be retrieved from lock
         * 
         * @param resource $handle 
         * @return bool 
         */
        public function unlock($handle) {
            
            fclose($handle);

        }
        
    }




