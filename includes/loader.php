<?php
/** ------------ */
/**  Oli loader  */
/** ------------ */

/** Define PHP_VERSION_ID if not defined */
if(!defined('PHP_VERSION_ID')) {
	$phpVersion = explode('.', PHP_VERSION);
	define('PHP_VERSION_ID', $phpVersion[0] * 10000 + $phpVersion[1] * 100 + $phpVersion[2]);
}

/** Include Oli files */
require_once INCLUDEPATH . 'OliCore.php'; // Oli core file
foreach(array_merge(glob(INCLUDEPATH . '*.php'), glob(INCLUDEPATH . '*/*.php')) as $filename) {
	if($filename != INCLUDEPATH . 'OliCore.php') require_once $filename;
}

/** Load Addons files */
foreach(array_merge(glob(ADDONSPATH . '*.php'), glob(ADDONSPATH . '*/*.php')) as $filename) {
	include_once $filename;
}
?>