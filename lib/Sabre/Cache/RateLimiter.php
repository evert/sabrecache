<?php

require_once 'Sabre/Cache/Factory.php';

/**
 * This class can be used to generate rate limiters
 * 
 * @package Sabre
 * @subpackage Cache
 * @version $Id: RateLimiter.php 12646 2009-02-03 17:45:43Z evert $
 * @copyright Copyright (C) 2009 Rooftop Solutions. All rights reserved.
 * @author Evert Pot (http://www.rooftopsolutions.nl/) 
 * @license licence http://www.freebsd.org/copyright/license.html  BSD License (4 Clause)
 */
class Sabre_Cache_RateLimiter {

    /**
     * Cache engine 
     * 
     * @var Sabre_Cache_Abstract 
     */
    private $cache;

    /**
     * Creates the object
     *
     * If the cache engine is ommitted, the standard will be used from Sabre_Cache_Factory
     * 
     * @param Sabre_Cache_Abstract $cache 
     */
    public function __construct(Sabre_Cache_Abstract $cache = null) {

        if (is_null($cache)) $cache = Sabre_Cache_Factory::fetch();
        $this->cache = $cache;

    }

    /**
     * Checks if a limiter with a unique key is exceeded.
     *
     * The timeLimit is specified in seconds, but it is rounded
     * to minutes and only accepts integers in the range 60-600 inclusive.
     * 
     * This method will return true if the limit has been reached 
     *
     * @param string $uniqueKey Cache key-prefix to use 
     * @param int $maxHits Maximum number of allowed 'hits'
     * @param int $timeLimit Timerange (in seconds) 
     * @return bool 
     */
    public function limitHit($uniqueKey,$maxHits=100,$timeLimit=300) {

        $timeLimit = round($timeLimit/60); 
        if ($timeLimit<1) $timeLimit = 1;
        if ($timeLimit>10) $timeLimit = 10;

        $dt = new DateTime('now');

        // Now we need to fetch the information from the last few minutes
        $keys = array();
        for($i=0;$i<=$timeLimit;$i++) {

            $keys[] = $uniqueKey . ':' . $dt->format('Y-m-j-G:i');
            $dt->modify('-1 minute');


        }

        $count = array_sum($this->cache->fetchMulti($keys)); 

        return ($count>=$maxHits);

    }

    /**
     * Increases the hits for a specific rate limiter
     * 
     * @param string $uniqueKey 
     * @return void
     */
    public function addHit($uniqueKey) {

        $dt = new DateTime('now');
        $key = $uniqueKey . ':' . $dt->format('Y-m-j-G:i');

        // First we increment the hitcount for the current minute
        $counter = $this->cache->increment($key,1);

        // If the counter didn't exist yet, we create it
        if (!$counter) $this->cache->store($key,600);

    }

}
