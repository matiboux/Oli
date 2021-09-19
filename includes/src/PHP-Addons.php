<?php
/*\
|*|  ------------------------
|*|  --- [  PHP Addons  ] ---
|*|  --- [   for Oli    ] ---
|*|  ------------------------
|*|  
|*|  PHP Addons is a build-in addon for Oli.
|*|  It is meant to add more general functions to the native PHP ones.
|*|  
|*|  For more info on Oli, please see the its Github repository: https://github.com/matiboux/Oli/
\*/

namespace {

/**
 * Finds whether a variable is an associative array
 * 
 * Inspired by http://stackoverflow.com/a/173479/5255556
 * 
 * @version BETA-1.7.0
 * @updated BETA-2.0.0
 * @return mixed Returns true if the variable is an associative array, false otherwise.
 */
if(!function_exists('is_assoc')) {
	function is_assoc($array) {
		return !empty($array) AND is_array($array) AND array_keys($array) !== range(0, count($array) - 1);
	}
}

/**
 * Pulls an element off an array
 * 
 * Like array_pop() or array_shift(), but for a specific key.
 * 
 * @version BETA-1.8.1
 * @updated BETA-2.0.0
 * @return mixed Returns the requested element if found, null otherwise.
 */
if(!function_exists('array_pull')) {
	function array_pull(&$array, $key) {
		if(array_key_exists($key, $array)) {
			$value = $array[$key];
			unset($array[$key]);
			return $value;
		} else return null;
	}
}

}