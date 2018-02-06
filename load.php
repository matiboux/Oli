<?php
/** Define Initial Timestamp */
if(!defined('INITTIME')) define('INITTIME', $initTimestamp = microtime(true));

/** Define Global Paths */
if(!defined('ABSPATH')) define('ABSPATH', dirname(__FILE__) . '/');
if(!defined('CONTENTPATH')) define('CONTENTPATH', ABSPATH . 'content/');
if(!defined('MEDIAPATH')) define('MEDIAPATH', CONTENTPATH . 'media/');
if(!defined('THEMEPATH')) define('THEMEPATH', CONTENTPATH . 'theme/');
if(!defined('TEMPLATESPATH')) define('TEMPLATESPATH', CONTENTPATH . 'templates/');


/** Get Oli Source Files Path */
if(!file_exists(ABSPATH . '.olipath')) {
	$userConfig = json_decode(file_get_contents(ABSPATH . 'config.json'), true);
	
	$handle = fopen(ABSPATH . '.olipath', 'w');
	fwrite($handle, $oliPath = $userConfig['source_path']);
	fclose($handle);
} else $oliPath = file_get_contents(ABSPATH . '.olipath');

/** Define Oli Paths */
if(!defined('OLIPATH')) define('OLIPATH', $oliPath ?: ABSPATH);

unset($oliPath);

if(!defined('INCLUDESPATH')) define('INCLUDESPATH', OLIPATH . 'includes/');
if(!defined('SCRIPTSPATH')) define('SCRIPTSPATH', INCLUDESPATH . 'scripts/');
if(!defined('ADDONSPATH')) define('ADDONSPATH', OLIPATH . 'addons/');

/** Get Website Config */
if(!file_exists(INCLUDESPATH . 'config/config.oli') OR filemtime(ABSPATH . 'config.json') > filemtime(INCLUDESPATH . 'config/config.oli')) {
	if(!isset($userConfig)) $userConfig = json_decode(file_get_contents(ABSPATH . 'config.json'), true);
	$config = $userConfig;
	
	if(!file_exists(INCLUDESPATH . 'config/')) mkdir(INCLUDESPATH . 'config/');
	$handle = fopen(INCLUDESPATH . 'config/config.oli', 'w');
	fwrite($handle, json_encode($config, JSON_FORCE_OBJECT));
	fclose($handle);
} else $config = json_decode(file_get_contents(ABSPATH . 'config.json'), true);

unset($config['source_path']);

/** Define Additional Constants */
if(!empty($config['constants']) AND is_array($config['constants'])) {
	foreach($config['constants'] as $eachName => $eachValue) {
		if(!defined($eachName)) define($eachName, $eachValue);
	}
}
unset($config['constants']);

/** Include OliCore & Addons */
if(file_exists(INCLUDESPATH . 'loader.php')) require_once INCLUDESPATH . 'loader.php';
else trigger_error('The framework <b>loader.php</b> file countn\'t be found! (used path: "' . INCLUDESPATH . 'loader.php")', E_USER_ERROR);

/** Load OliCore & Addons */
$_Oli = new \OliFramework\OliCore($initTimestamp);
if(!empty($config['addons'])) {
	foreach($config['addons'] as $eachAddon) {
		if(!empty($eachAddon['name']) AND !empty($eachAddon['var']) AND !empty($eachAddon['class']) AND !isset(${$eachAddon['var']})) {
			$className = (!empty($eachAddon['namespace']) ? str_replace('/', '\\', $eachAddon['namespace']) . '\\' : '\\') . $eachAddon['class'];
			${$eachAddon['var']} = new $className;
			$_Oli->addAddon($eachAddon['name'], $eachAddon['var']);
			$_Oli->addAddonInfos($eachAddon['name'], $eachAddon);
		}
	}
}
unset($config['addons']);

/** Load Configs */
if(!empty($config)) {
	foreach($config as $eachKey => $eachConfig) {
		if($eachKey == 'Oli') {
			if(file_exists(ABSPATH . 'mysql.json')) $_Oli->loadConfig(array('mysql' => json_decode(file_get_contents(ABSPATH . 'mysql.json'), true)));
			$_Oli->loadConfig($eachConfig);
		}
		else ${$_Oli->getAddonVar($eachKey)}->loadConfig($eachConfig);
	}
}
if(file_exists(ABSPATH . 'config.php')) include ABSPATH . 'config.php';
?>