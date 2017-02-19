<?php
/** ------------ */
/**  Oli loader  */
/** ------------ */

/** Define PHP_VERSION_ID if not defined */
if(!defined('PHP_VERSION_ID')) {
	$phpVersion = explode('.', PHP_VERSION);
	define('PHP_VERSION_ID', $version[0] * 10000 + $version[1] * 100 + $version[2]);
}

/** Include Oli files */
foreach(array_merge(glob(INCLUDEPATH . '*.php'), glob(INCLUDEPATH . '*.php')) as $filename) {
    require_once $filename;
}

/** Load Addons files */
foreach(array_merge(glob(ADDONSPATH . '*.php'), glob(ADDONSPATH . '*/*.php')) as $filename) {
    include_once $filename;
}
?>