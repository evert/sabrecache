<?php

    /**
     * Sabre_Cache_PageCache
     * 
     * @package Sabre
     * @subpackage Cache
     * @version $Id: PageCache.php 12600 2009-01-29 17:10:42Z evert $
     * @copyright Copyright (C) 2006, 2009 Rooftop Solutions. All rights reserved.
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
    class Sabre_Cache_PageCache extends Sabre_Cache_Abstract {

        /**
         * store 
         * 
         * @param string $key 
         * @param mixed $data 
         * @param int $ttl 
         * @return mixed 
         */
        function store( $key, $data, $ttl = -1 ) {

            if (!is_string($data)) throw new Sabre_Cache_Exception('The data has to be a string for PageCache');
            if ($ttl=-1) $ttl = $this->defaultTTL;

            $h = fopen($this->getFileName($key),'a+');
            if (!$h) {
                throw new Sabre_Cache_Exception('Could not write to cachefile: ' . $this->getFileName($key));
            }
            flock($h,LOCK_EX);
            fseek($h,0);
            ftruncate($h,0);
            if (fwrite($h,$data) === false) {
                throw new Sabre_Cache_Exception('Could not write to cachefile: ' . $this->getFileName($key));
            }
            fclose($h);

            // Now for gzip
            $h = fopen($this->getFileName($key) . '.gz','a+');
            if (!$h) {
                throw new Sabre_Cache_Exception('Could not write to cachefile: ' . $this->getFileName($key));
            }
            flock($h,LOCK_EX);
            fseek($h,0);
            ftruncate($h,0);
            if (fwrite($h,gzencode($data)) === false) {
                throw new Sabre_Cache_Exception('Could not write to cachefile: ' . $this->getFileName($key) . '.gz');
            }
            fclose($h);

            @chmod($this->getFileName($key),0666);
            @chmod($this->getFileName($key) . '.gz',0666);
            return true;
            
            
        }

        /**
         * Returns the filename for a specific cache key 
         * 
         * @param string $key 
         * @return string 
         */
        private function getFileName($key) {

            $s = ini_get('session.save_path');
            $s = $s?$s:'/tmp';
            return $s . '/s_pcache' . md5($this->keyPrefix.$key);

        }

        /**
         * fetch 
         * 
         * @param string $key 
         * @return mixed 
         */
        function fetch( $key, $maxage = -1 ) {

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
            
            if (!$data) {
                unlink($filename);
                return false;
            }

            return $data;
          
        }

        /**
         * fetch 
         * 
         * @param string $key 
         * @return mixed 
         */
        function fetchEcho( $key, $maxage = -1, $gzipped = false ) {

            $filename = $this->getFileName($key);
            if ($gzipped) $filename.='.gz';
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
            if ($gzipped) header('Content-Encoding: gzip');
            fpassthru($h);
            fclose($h);
            return true; 
          
        }

        /**
         * delete 
         * 
         * @param string $key 
         * @return bool 
         */
        function delete( $key ) {

            $filename = $this->getFileName($key);
            if (file_exists($filename)) {
                return unlink($filename);
            } else {
                return false;
            }

        }

        /**
         * Returns true if the filecacher is available 
         * 
         * @return bool 
         */
        function isAvailable() {

            $r =is_writable(dirname($this->getFileName('teststring')));
            return $r; 

        }

        /**
         * Returns information about this engine 
         * 
         * @return array 
         */
        function getInfo() {

            $path = dirname($this->getFileName('nothing'));

            $data = parent::getInfo();

            $data['title'] = 'Filesystem Page Cache';
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
        function flush() {
            
            $path = dirname($this->getFileName('nothing'));

            foreach(scandir($path) as $file) {

                if (strpos($file,'s_pcache')===0) {

                    unlink($path . '/' . $file);

                }

            }
            return true;

        }
        
    }




