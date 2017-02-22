<?php
/** Define Global Paths */
if(!defined('ABSPATH')) define('ABSPATH', dirname(__FILE__) . '/');
if(!defined('CONTENTPATH')) define('CONTENTPATH', ABSPATH . 'content/');
if(!defined('MEDIAPATH')) define('MEDIAPATH', CONTENTPATH . 'media/');
if(!defined('THEMEPATH')) define('THEMEPATH', CONTENTPATH . 'theme/');

$config = json_decode(file_get_contents(ABSPATH . 'config.json'), true);
if(!defined('SCRIPTBASEPATH')) define('SCRIPTBASEPATH', $config['source_path'] ?: ABSPATH);
unset($config['source_path']);

if(!defined('INCLUDEPATH')) define('INCLUDEPATH', SCRIPTBASEPATH . 'includes/');
if(!defined('ADDONSPATH')) define('ADDONSPATH', SCRIPTBASEPATH . 'addons/');

/** Include OliCore & Addons */
require_once INCLUDEPATH . 'loader.php';

/** Load OliCore & Addons */
$_Oli = new \OliFramework\OliCore;
if(!empty($config['addons'])) {
	foreach($config['addons'] as $eachAddon) {
		if(!empty($eachAddon['name']) AND !empty($eachAddon['var']) AND !empty($eachAddon['class']) AND !isset(${$eachAddon['var']})) {
			$className = (!empty($eachAddon['namespace']) ? str_replace('/', '\\', $eachAddon['namespace']) . '\\' : '\\') . $eachAddon['class'];
			${$eachAddon['var']} = new $className;
			$_Oli->addAddon($eachAddon['name'], $eachAddon['var']);
		}
	}
}
unset($config['addons']);

/** Load Configs */
if(!empty($config)) {
	foreach($config as $eachKey => $eachConfig) {
		${($eachKey == 'Oli') ? '_Oli' : $_Oli->getAddonVar($eachKey)}->loadConfig($eachConfig);
	}
}
if(file_exists(ABSPATH . 'config.php')) include ABSPATH . 'config.php';
?>