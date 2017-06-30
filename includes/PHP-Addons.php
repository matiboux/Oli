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

}