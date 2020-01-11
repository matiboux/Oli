<?php
/** ------------ */
/**  Oli loader  */
/** ------------ */

/** Define PHP_VERSION_ID if not defined (PHP < 5.2.7) */
if(!defined('PHP_VERSION_ID')) {
	$phpVersion = explode('.', PHP_VERSION);
	define('PHP_VERSION_ID', $phpVersion[0] * 10000 + $phpVersion[1] * 100 + $phpVersion[2]);
}

/** Load librairies */
require_once INCLUDESPATH . 'PHP-Addons.php';
require_once INCLUDESPATH . 'ErrorManager.php';

/** Load Oli */
require_once INCLUDESPATH . 'OliCore.php'; // Oli Core
$_Oli = new \Oli\OliCore(INITTIME);

/** Load Addons files */
// foreach(array_merge(glob(ADDONSPATH . '*.php'), glob(ADDONSPATH . '*/*.php')) as $filename) {
	// include_once $filename;
// }
?>