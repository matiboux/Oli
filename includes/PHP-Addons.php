<?php
/**
 * PHP Addons for Oli
 * 
 * Adds more functions to native PHP ones
 * Please check OliCore.php file for more info on this framework
 * 
 * @see OliFramework/OliCore for more info on this framework
 * @copyright 2015 Matiboux
 */

namespace {

/**
  * Check if an array is associative or not
  * 
  * @param array $array Array to check
  * 
  * @since 1.7.0
  * @see http://stackoverflow.com/a/173479/5255556 Answer where this function comes from
  * @return bool
  */
function is_assoc($array) {
	return is_array($array) ? !empty($array) AND array_keys($array) !== range(0, count($array) - 1) : false;
}

}