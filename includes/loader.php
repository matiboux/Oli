<?php
/** ------------ */
/**  Oli loader  */
/** ------------ */

/** Define PHP_VERSION_ID if not defined (PHP < 5.2.7) */
if(!defined('PHP_VERSION_ID')) {
	$phpVersion = explode('.', PHP_VERSION);
	define('PHP_VERSION_ID', $phpVersion[0] * 10000 + $phpVersion[1] * 100 + $phpVersion[2]);
}

/** Load Oli files */
require_once INCLUDESPATH . 'OliCore.php'; // Oli core file
foreach(array_merge(glob(INCLUDESPATH . '*.php'), glob(INCLUDESPATH . '*/*.php')) as $filename) {
	if($filename != INCLUDESPATH . 'OliCore.php') require_once $filename;
}

/** Load Addons files */
foreach(array_merge(glob(ADDONSPATH . '*.php'), glob(ADDONSPATH . '*/*.php')) as $filename) {
	include_once $filename;
}
?>