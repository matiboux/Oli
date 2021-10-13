<?php
/*\
|*|  Oli Source files loader
\*/

use Oli\Config;
use Oli\Oli;
use Oli\OliFramework;
use Oli\OliLite;

// *** First step is defining all path constants

// Primary constants - These should have been defined in /load.php
if (!defined('ABSPATH'))
	die('Oli Error: ABSPATH is not defined.');
if (!defined('ADDONSPATH'))
	die('Oli Error: ADDONSPATH is not defined.');
if (!defined('INCLUDESPATH'))
	define('INCLUDESPATH', __DIR__ . '/');
if (!defined('CONTENTPATH'))
	define('CONTENTPATH', ABSPATH . 'content/');

// Secondary constants
if (!defined('OLIADMINPATH'))
	define('OLIADMINPATH', INCLUDESPATH . 'admin/');
if (!defined('OLILOGINPATH'))
	define('OLILOGINPATH', INCLUDESPATH . 'login/');
if (!defined('OLIPAGESPATH'))
	define('OLIPAGESPATH', INCLUDESPATH . 'pages/');
if (!defined('OLISCRIPTPATH'))
	define('OLISCRIPTPATH', INCLUDESPATH . 'scripts/');
if (!defined('OLISETUPPATH'))
	define('OLISETUPPATH', INCLUDESPATH . 'setup/');
if (!defined('OLISRCPATH'))
	define('OLISRCPATH', INCLUDESPATH . 'src/');
if (!defined('OLITEMPLATESPATH'))
	define('OLITEMPLATESPATH', INCLUDESPATH . 'templates/');

// User Content constants
if (!defined('ASSETSPATH'))
	define('ASSETSPATH', CONTENTPATH . 'assets/');
if (!defined('MEDIAPATH'))
	define('MEDIAPATH', CONTENTPATH . 'media/');
if (!defined('THEMEPATH'))
	define('THEMEPATH', CONTENTPATH . 'theme/'); // Legacy
if (!defined('PAGESPATH'))
	define('PAGESPATH', CONTENTPATH . 'pages/');
if (!defined('SCRIPTSPATH'))
	define('SCRIPTSPATH', CONTENTPATH . 'scripts/');
if (!defined('TEMPLATESPATH'))
	define('TEMPLATESPATH', CONTENTPATH . 'templates/');

// *** Second step is including Oli source files

// Load libraries
require OLISRCPATH . 'PHP-Addons.php';
require OLISRCPATH . 'ErrorManager.php';

require OLISRCPATH . 'accounts/AccountPermissionsTrait.php';
require OLISRCPATH . 'accounts/AccountsManager.php';

require OLISRCPATH . 'db/DBMS.php';
require OLISRCPATH . 'db/DBWrapper.php';
require OLISRCPATH . 'db/MySQL.php';
require OLISRCPATH . 'db/PostgreSQL.php';
require OLISRCPATH . 'db/SQLServer.php';

// *** Final step is initializing Oli

// Load Config
require OLISRCPATH . 'config/Config.php'; // Oli Config Registry
Config::loadRawConfig();
$_OliConfig = &Config::$config; // Config array alias

// Load Oli
require OLISRCPATH . 'OliCore.php';
require OLISRCPATH . 'Oli.php';
require OLISRCPATH . 'Script.php';
if (Config::$rawConfig['oli_mode'] === 'lite')
{
	require OLISRCPATH . 'OliLite.php'; // Oli Lite
	$_Oli = Oli::getInstance(OliLite::class, INITTIME);
}
else
{
	require OLISRCPATH . 'OliFramework.php'; // Oli Framework
	$_Oli = Oli::getInstance(OliFramework::class, INITTIME);
}

// Check for error
if ($_OliConfig === null)
	die('Oli Error: Failed initializing Oli or loading the configuration.');

// Load Addons
// print_r($_OliConfig['addons']);
foreach ($_OliConfig['addons'] as $var => $infos)
{
	if (isset(${$var}))
		die('Addon Error: Variable $' . $var . ' is already set.');
	if (!is_file(ADDONSPATH . $infos['include']))
		die('Addon Error: File "' . ADDONSPATH . $infos['include'] . '" does not exist.');

	require_once ADDONSPATH . $infos['include'];

	${$var} = @$infos['args'] !== null ? new $infos['class'](...$infos['args']) : new $infos['class']();
}