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
	if (!function_exists('is_assoc'))
	{
		/**
		 * Finds whether a variable is an associative array
		 *
		 * An array is associative if:
		 * - It is a non-empty array
		 * - Its keys are not a linear range of integers
		 *
		 * Inspired by https://stackoverflow.com/a/173479/5255556
		 *
		 * @param mixed $array The variable being evaluated.
		 *
		 * @return bool Returns true if the variable is an associative array, false otherwise.
		 * @link https://stackoverflow.com/a/173479/5255556 Inspired by this Stackoverflow answer
		 * @version BETA-1.7.0
		 * @updated GAMMA-1.0.0
		 */
		function is_assoc(mixed $array): bool
		{
			return !empty($array)
			       && is_array($array)
			       && array_keys($array) !== range(0, count($array) - 1);
		}
	}

	if (!function_exists('is_seq_array'))
	{
		/**
		 * Finds whether a variable is a sequential array
		 *
		 * An array is sequential if:
		 * - It is a non-empty array
		 * - Its keys are a linear range of integers
		 *
		 * Inspired by https://stackoverflow.com/a/173479/5255556
		 *
		 * @param mixed $array The variable being evaluated.
		 *
		 * @return bool Returns true if the variable is an associative array, false otherwise.
		 * @link https://stackoverflow.com/a/173479/5255556 Inspired by this Stackoverflow answer
		 * @version BETA-1.7.0
		 * @updated GAMMA-1.0.0
		 */
		function is_seq_array(mixed $array): bool
		{
			return !empty($array)
			       && is_array($array)
			       && array_keys($array) === range(0, count($array) - 1);
		}
	}

	if (!function_exists('array_pull'))
	{
		/**
		 * Pull an element off an array and returns it
		 *
		 * Similar to array_pop() or array_shift(), but for a specific key.
		 *
		 * @return mixed Returns the requested element if found, null otherwise.
		 * @see array_shift()
		 * @see array_pop()
		 * @version BETA-1.8.1
		 * @updated GAMMA-1.0.0
		 */
		function array_pull(array &$array, mixed $key): mixed
		{
			if (!array_key_exists($key, $array)) return null;
			$value = $array[$key];
			unset($array[$key]);
			return $value;
		}
	}
}