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
*/


/**
 * Use this class for a Filesystem cache backend
 */
class Sabre_Cache_Filesystem extends Sabre_Cache_Abstract {

    /**
     * Basepath 
     * 
     * @var string 
     */
    private $basePath;

    /**
     * Creates the cache handler
     *
     * Basepath can be used to specify the base filename, for all cached files. 
     * The base path will simply be appended by the keyname, so you need to make sure the
     * path ends with a / if you're just specifying a directory.
     *
     * If nothing is specified /tmp/sabrecache_ will be used
     * 
     * @param string $basePath 
     */
    public function __construct($basePath = null) {

        if (!$basePath) {
            $basePath = '/tmp/sabrecache_';
        }

        $this->basePath = $basePath;

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
     * @return mixed 
     */
    public function store($key, $data, $ttl = null) {

        if (is_null($ttl)) $ttl = $this->defaultTTL;

        $h = fopen($this->getFileName($key),'a+');
        flock($h,LOCK_EX);
        fseek($h,0);
        ftruncate($h,0);
        fwrite($h,serialize(array(time()+$ttl,$data)));
        fclose($h);

        return true;
        
        
    }


    /**
     * Grabs an item from the cache.
     * This function will return null if the item was not available
     * 
     * @param string $key 
     * @return mixed 
     */
    public function fetch($key) {

        $filename = $this->getFileName($key);
        if (!file_exists($filename)) return null;
        
        $h = fopen($filename,'r');
        if(!flock($h,LOCK_SH | LOCK_NB)) return null;

        $data = stream_get_contents($h); 
        fclose($h);
        
        $data = unserialize($data);

        if (time() > $data[0]) {

            unlink($filename);
            return null;

        }
        return $data[1];
      
    }

    /**
     * This method will only fetch a file if it's
     * not older than the specified number of seconds. 
     *
     * By default it will also delete the cache entry if it is older than the 
     * this number. 
     *
     * This method will be much faster than fetch for items that might
     * be expired, because the file itself does not need to be opened.
     *
     * @param string $key 
     * @param int $maxAge 
     * @return mixed 
     */
    public function fetchIfModifiedSince($key, $maxAge, $deleteExpired = true) {

        $filename = $this->getFileName($key);
        if (!file_exists($filename)) return null; 

        if (filemtime($filename)+$maxage < time()) {
            if ($deleteExpired) unlink($filename);
            return null;
        }
        
        $h = fopen($filename,'r');
        if (!flock($h,LOCK_SH | LOCK_NB)) return null;

        $data = stream_get_contents($h);
        fclose($h);
        
        $data = unserialize($data);

        if (time() > $data[0]) {

            unlink($filename);
            return null;

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

        $filename = $this->getFileName($key);
        if (file_exists($filename)) {
            return unlink($filename);
        } else {
            return false;
        }

    }

    /**
     * Returns true if the Filesystem cache is available.
     * This is tested by seeing if we're allowed to write to the specified
     * basePath, using a test-key.
     * 
     * @return bool 
     */
    public function isAvailable() {

        $r = is_writable(dirname($this->getFileName('teststring')));
        return $r; 

    }

    /**
     * Flushes the cache 
     * 
     * @return bool 
     */
    public function flush() {
       
        $files = glob($this->basePath . '*');
        foreach($files as $file) {
            unlink($path . '/' . $file);
        }
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

}

