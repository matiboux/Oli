<?php
// *** Define constants
// Initial timestamp
if (!defined('INITTIME')) define('INITTIME', microtime(true));
// Absolute path
if (!defined('ABSPATH')) define('ABSPATH', __DIR__ . '/');

// *** Store merged form data (FILES, POST and GET arguments)
$_ = array_merge($_GET, $_POST, $_FILES);

// *** Get path to Oli source files
$oliPath = @file_get_contents(ABSPATH . '.olipath');
if ($oliPath === false || !file_exists($oliPath . 'includes/'))
{
	if (file_exists(ABSPATH . 'includes/')) $oliPath = ABSPATH;
	else $oliPath = null;
}

// If the path to Oli source files was not found, show a config wizard to the user
if ($oliPath == null)
{
	if (!empty($_['olipath']))
	{
		$_['olipath'] = substr($_['olipath'], -1) != '/'
			? $_['olipath'] . '/' : $_['olipath'];
		if (file_exists($_['olipath'] . 'includes/'))
		{
			$oliPath = $_['olipath'];

			// Save the path to Oli source files
			$handle = fopen(ABSPATH . '.olipath', 'w');
			fwrite($handle, $oliPath);
			fclose($handle);
		}
	}

	if ($oliPath == null)
	{
		?>

        <h1>Oli Basic Configuration —</h1>
        <p><b>Hey, looks like the framework core files could not found</b>. You need to either..</p>
        <ul>
            <li>Add the core folders and files in your website directory.</li>
            <li>Define manually the path leading to them in the form below.</li>
        </ul>

        <h2>Define OliPath —</h2>
        <form action="#" method="post">
			<?php if (!empty($_POST) and $_POST['olipath']) { ?><p>[!] Looks like an error occurred.. Are you sure the
                path you entered led to Oli core folders and files?</p><?php } ?>
            <p>Please type in the absolute path of the directory containing the Oli core folders (like <i>includes/</i>)
                and files.</p>
            <input type="text" name="olipath" placeholder="/var/www/OliSources/"/>
            <button type="submit">Submit the path</button>
        </form>

		<?php exit;
	}
}

// *** Define Oli constants
if (!defined('OLIPATH')) define('OLIPATH', $oliPath ?: ABSPATH);
unset($oliPath);
if (!defined('ADDONSPATH')) define('ADDONSPATH', OLIPATH . 'addons/');
if (!defined('INCLUDESPATH')) define('INCLUDESPATH', OLIPATH . 'includes/');
if (!defined('CONTENTPATH')) define('CONTENTPATH', ABSPATH . 'content/');

// *** Load Oli
if (file_exists(INCLUDESPATH . 'loader.php')) require INCLUDESPATH . 'loader.php';
else die('Error: The framework <b>loader.php</b> file countn\'t be found!' .
         '(in "' . INCLUDESPATH . 'loader.php")');

// *** Load Addons
if (!empty($_Oli->config['addons']) && is_array($_Oli->config['addons']))
{
	foreach ($_Oli->config['addons'] as $addonVar => $addonName)
	{
		if (file_exists(ADDONSPATH . basename(str_replace('\\', '/', $addonName)) . '.php'))
		{
			include ADDONSPATH . basename(str_replace('\\', '/', $addonName)) . '.php';
			// $addonName = '\\' . $addonName;
		}
		else if (file_exists(ADDONSPATH . basename(str_replace('\\', '/', $addonName)) . '/loader.php'))
		{
			include ADDONSPATH . basename(str_replace('\\', '/', $addonName)) . '/loader.php';
			// $addonName = '\\' . $addonName;
		}

		if (class_exists($addonName)) ${$addonVar} = new $addonName;

//		if (!empty($addonInfos) and is_array($addonInfos))
//		{
//			if (!empty($eachAddonInfosName) and !empty($eachAddonInfos['var']) and !empty($eachAddonInfos['class']) and !isset(${$eachAddonInfos['var']}))
//			{
//				$className = (!empty($eachAddonInfos['namespace']) ? str_replace('/', '\\', $eachAddonInfos['namespace']) . '\\' : '\\') . $eachAddonInfos['class'];
//				${$eachAddonInfos['var']} = new $className;
//				$_Oli->addAddon($eachAddonInfosName, $eachAddonInfos['var']);
//				$_Oli->addAddonInfos($eachAddonInfosName, $eachAddonInfos);
//
//				if (file_exists(CONTENTPATH . $eachAddonInfosName . '.json')) ${$eachAddonInfos['var']}->loadConfig(json_decode(file_get_contents(CONTENTPATH . $eachAddonInfosName . '.json'), true));
//				else
//				{
//					$handle = fopen(CONTENTPATH . $eachAddonInfosName . '.json', 'w');
//					fclose($handle);
//				}
//			}
//		}
	}
}

///** Load Configs */
//if (!empty($config['user-config']))
//{
//	foreach ($config['user-config'] as $eachKey => $eachConfig)
//	{
//		if ($eachKey == 'Oli')
//		{
//			if (file_exists(ABSPATH . 'mysql.json')) $_Oli->loadConfig(['mysql' => json_decode(file_get_contents(ABSPATH . 'mysql.json'), true)]);
//			$_Oli->loadConfig($eachConfig);
//		}
//		else ${$_Oli->getAddonVar($eachKey)}->loadConfig($eachConfig);
//	}
//}

// *** Load custom config
if (file_exists(ABSPATH . 'config.php')) include ABSPATH . 'config.php';
