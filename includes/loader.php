<?php
// ------------ //
//  Oli loader  //
// ------------ //

// Load librairies
require_once INCLUDESPATH . 'PHP-Addons.php';
require_once INCLUDESPATH . 'ErrorManager.php';

require_once INCLUDESPATH . 'SQLWrapper.php';

// Load Config
require_once INCLUDESPATH . 'Config.php'; // Oli Config Registry
\Oli\Config::loadRawConfig();
$_OliConfig = &\Oli\Config::$config; // Config array alias

// Load Oli
require_once INCLUDESPATH . 'OliCore.php'; // Oli Core
if(\Oli\Config::$rawConfig['oli_mode'] == 'lite') {
	require_once INCLUDESPATH . 'OliLite.php'; // Oli Lite
	$_Oli = new \Oli\OliLite(INITTIME);
} else {
	require_once INCLUDESPATH . 'OliFramework.php'; // Oli Framework
	$_Oli = new \Oli\OliFramework(INITTIME);
}

// Check for error
if($_OliConfig === null)
	die('Oli Error: Failed initializing Oli or loading the configuration.');

// Load Addons
// print_r($_OliConfig['addons']);
foreach($_OliConfig['addons'] as $var => $infos) {
	if(isset(${$var}))
		die('Addon Error: Variable $' . $var . ' is already set.');
	if(!is_file(ADDONSPATH . $infos['include']))
		die('Addon Error: File "' . ADDONSPATH . $infos['include'] . '" does not exist.');
	
	require_once ADDONSPATH . $infos['include'];
	
	${$var} = @$infos['args'] !== null ? new $infos['class'](...$infos['args']) : new $infos['class']();
}
?>