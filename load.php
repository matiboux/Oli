<?php
/** Define Global Paths */
if(!defined('ABSPATH')) define('ABSPATH', dirname(__FILE__) . '/');

$config = json_decode(file_get_contents(ABSPATH . 'config.json'), true);
if(!defined('SCRIPTBASEPATH')) define('SCRIPTBASEPATH', (!empty($config['source_path'])) ? $config['source_path'] : ABSPATH);

if(!defined('INCLUDEPATH')) define('INCLUDEPATH', SCRIPTBASEPATH . 'includes/');
if(!defined('ADDONSPATH')) define('ADDONSPATH', SCRIPTBASEPATH . 'addons/');

/** Include OliCore & Addons */
require_once INCLUDEPATH . 'loader.php';

/** Load OliCore & Addons */
$_Oli = new \OliFramework\OliCore;
if(!empty($config['addons'])) {
	foreach($config['addons'] as $eachAddon) {
		if(!empty($eachAddon['name']) AND !empty($eachAddon['var']) AND !empty($eachAddon['class']) AND !isset(${$eachAddon['var']})) {
			$addonNamespace = !empty($eachAddon['namespace']) ? str_replace('/', '\\', $eachAddon['namespace']) . '\\' . $eachAddon['class'] : $eachAddon['class'];
			${$eachAddon['var']} = new $addonNamespace;
			$_Oli->addAddon($eachAddon['name'], $eachAddon['var']);
		}
	}
}

/** Load Config */
unset($config['source_path'], $config['addons']);
if(!empty($config)) {
	foreach($config as $eachKey => $eachConfig) {
		${($eachKey == 'Oli') ? '_Oli' : $_Oli->getAddonVar($eachKey)}->loadConfig($eachConfig);
	}
}
if(file_exists(ABSPATH . 'config.php')) include ABSPATH . 'config.php';

/** Define Content Paths */
$mediaPathAddon = $this->getSetting('media_path');
$themePathAddon = $this->getSetting('theme_path');

if(!defined('CONTENTPATH')) define('CONTENTPATH', ABSPATH . 'content/');
if(!defined('MEDIAPATH')) define('MEDIAPATH', $mediaPathAddon ? ABSPATH . $mediaPathAddon : CONTENTPATH . 'media/');
if(!defined('THEMEPATH')) define('THEMEPATH', $themePathAddon ? ABSPATH . $themePathAddon : CONTENTPATH . 'theme/');
?>