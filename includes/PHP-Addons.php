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
|*|  For more info on Oli, please see the its Github repository: https://github.com/OliFramework/Oli/
\*/

namespace {

/** Is Array Associative? */
// Inspirated by http://stackoverflow.com/a/173479/5255556
function is_assoc($array) {
	return is_array($array) ? !empty($array) AND array_keys($array) !== range(0, count($array) - 1) : false;
}

/** Array Pull */
// Like an array_pop or array_shift, but for a specific key
// Inspirated by http://php.net/manual/fr/function.array-slice.php#81973 (user note)
function array_pull(&$array, $key){
    $holding = [];
    foreach($array as $eachKey => $eachValue) {
        if($eachKey == $key) {
			$holding[$eachKey] = $eachValue;
			break;
		}
    }
	$array = array_diff_assoc($array, $holding);
    return $holding[$key];
}

}