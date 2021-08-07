<?php
/*\
|*|  ---------------------------------
|*|  --- [  Oli Config Registry  ] ---
|*|  ---------------------------------
|*|  
|*|  This is the static config registry for Oli.
|*|  
|*|  More information about Oli in the README.md file.
|*|  You can find it in the project repository: https://github.com/matiboux/Oli/
\*/

/*\
|*|  ╒════════════════════════╕
|*|  │ :: TABLE OF CONTENT :: │
|*|  ╞════════════════════════╛
|*|  │
|*|  ├ I. Variables
|*|  ├ II. Private constructor
|*|  ├ III. Magic Methods
|*|  ├ IV. Configuration
|*|  │ ├ 1. Get Config
|*|  │ ├ 2. Load Config
|*|  │ ├ 3. Parse Config
|*|  │ └ 4. Process Config
|*|  └ V. Getters
\*/

namespace Oli;

use Exception;

class Config
{
	// region I. Variables

	private static ?float $lastUpdateTimestamp = null;

	private static ?array $appConfig = null;

	private static ?array $defaultConfig = null;
	private static ?array $globalConfig = null;
	private static ?array $localConfig = null;

	public static ?array $rawConfig = null;
	public static ?array $config = null;

	public static array $errors = [];

	// endregion

	// region II. Private constructor

	private function __construct()
	{
	} // Non-instantiable

	// endregion

	// region III. Magic Methods

	/**
	 * Oli\Config Class to String function
	 *
	 * @return string Returns Oli Config Registry information.
	 * @version BETA-1.8.1
	 * @updated BETA-2.0.0
	 */
	public function __toString()
	{
		return 'Config Registry for Oli';
	}

	// endregion

	// region IV. Configuration

	// region IV. 1. Get config

	/**
	 * Get Default Config
	 *
	 * @param mixed $index
	 * @param bool $reload
	 *
	 * @return array|null Array or requested value.
	 * @version BETA-2.0.0
	 * @updated BETA-2.0.0
	 */
	public static function getDefaultConfig(mixed $index = null, bool $reload = false): ?array
	{
		if (is_bool($index))
		{
			$reload = $index;
			$index = null;
		}

		if ($reload || !isset(self::$defaultConfig))
		{
			$defaultConfig = @file_get_contents(INCLUDESPATH . 'config.default.json');
			if ($defaultConfig !== false)
				self::$defaultConfig = json_decode($defaultConfig, true);
			else
				trigger_error('Failed to read ' . INCLUDESPATH . 'config.default.json', E_USER_ERROR);
		}

		if (self::$defaultConfig === null) return null;
		if ($index !== null) return @self::$defaultConfig[$index];
		return self::$defaultConfig ?: [];
	}

	/**
	 * Get Global Config
	 *
	 * @param mixed $index
	 * @param bool $reload
	 *
	 * @return array|null Array or requested value.
	 * @version BETA-2.0.0
	 * @updated BETA-2.0.0
	 */
	public static function getGlobalConfig(mixed $index = null, bool $reload = false): ?array
	{
		if (is_bool($index))
		{
			$reload = $index;
			$index = null;
		}

		if ($reload || !isset(self::$globalConfig))
		{
			$globalConfig = @file_get_contents(OLIPATH . 'config.global.json');
			if ($globalConfig !== false)
				self::$globalConfig = json_decode($globalConfig, true);
			else
				self::$defaultConfig = null;
		}

		if (self::$globalConfig === null) return null;
		if ($index !== null) return @self::$globalConfig[$index];
		return self::$globalConfig ?: [];
	}

	/**
	 * Get Default Config
	 *
	 * @param mixed|null $index
	 * @param bool $reload
	 *
	 * @return array|null Array or requested value.
	 * @version BETA-2.0.0
	 * @updated BETA-2.0.0
	 */
	public static function getLocalConfig(mixed $index = null, bool $reload = false): ?array
	{
		if (is_bool($index))
		{
			$reload = $index;
			$index = null;
		}

		if ($reload || !isset(self::$localConfig))
		{
			$localConfig = @file_get_contents(ABSPATH . 'config.json');
			if ($localConfig !== false)
				self::$localConfig = json_decode($localConfig, true);
			else
			{
				// Create a blank config file
				$handle = fopen(ABSPATH . 'config.json', 'w');
				fwrite($handle, "{\n}\n");
				fclose($handle);

				self::$localConfig = [];
			}
		}

		if (self::$localConfig === null) return null;
		if ($index !== null) return @self::$localConfig[$index];
		return self::$localConfig ?: [];
	}

	/**
	 * Get App Config
	 *
	 * @param mixed|null $index
	 * @param bool $reload
	 *
	 * @return array|string|null Array or requested value.
	 * @version BETA-2.0.0
	 * @updated BETA-2.0.0
	 */
	public static function getAppConfig(mixed $index = null, bool $reload = false): array|string|null
	{
		if (is_bool($index))
		{
			$reload = $index;
			$index = null;
		}

		if ($reload || !isset(self::$appConfig))
		{
			$appConfig = @file_get_contents(ABSPATH . 'app.json');
			if ($appConfig !== false)
				self::$appConfig = json_decode($appConfig, true);
		}

		if (self::$appConfig === null) return null;
		if ($index !== null) return @self::$appConfig[$index];
		return self::$appConfig ?: [];
	}

	// endregion

	// region IV. 2. Load config

	/**
	 * Load Oli Config
	 *
	 * @return bool Returns true if the config was successfully loaded, false otherwise.
	 * @version BETA-2.0.0
	 * @updated BETA-2.0.0
	 */
	public static function loadRawConfig(): bool
	{
		self::$rawConfig = [];

		// Load default config
		$defaultConfig = self::getDefaultConfig();
		if (!empty($defaultConfig) && is_array($defaultConfig))
			self::$rawConfig = array_merge(self::$rawConfig, $defaultConfig);

		// Load global config
		$globalConfig = self::getGlobalConfig();
		if (!empty($globalConfig) && is_array($globalConfig))
			self::$rawConfig = array_merge(self::$rawConfig, $globalConfig);

		// Load local config
		$localConfig = self::getLocalConfig();
		if (!empty($localConfig) && is_array($localConfig))
			self::$rawConfig = array_merge(self::$rawConfig, $localConfig);

		// Update the last update timestamp
		self::$lastUpdateTimestamp = microtime(true);

		return !empty(self::$rawConfig);
	}

	/**
	 * Load Oli Config
	 *
	 * @return bool Returns true if the config was successfully loaded, false otherwise.
	 * @version BETA-2.0.0
	 * @updated BETA-2.0.0
	 */
	public static function loadConfig($_Oli): bool
	{
		return (!empty(self::$rawConfig) || self::loadRawConfig())
		       && self::parseConfig($_Oli);
	}

	/**
	 * Reload Oli Config
	 *
	 * @return bool Returns true if the config was successfully loaded, false otherwise.
	 * @version BETA-2.0.0
	 * @updated BETA-2.0.0
	 */
	public static function reloadConfig($_Oli): bool
	{
		return self::loadRawConfig() && self::parseConfig($_Oli);
	}

	// endregion

	// region IV. 3. Save config

	/**
	 * Update Config
	 *
	 * @return bool Returns true if the config is updated.
	 * @version BETA-2.0.0
	 * @updated BETA-2.0.0
	 */
	public static function updateConfig($_Oli, $config, $target = null, $replace = false): bool
	{
		if ($target && !self::saveConfig($_Oli, $config, $target, $replace))
			return false;

		if ($target === 'app')
			self::$appConfig = array_merge(self::$appConfig ?: self::getAppConfig(), $config);
		else
			self::$rawConfig = array_merge(self::$rawConfig, $config);

		return self::reloadConfig($_Oli);
	}

	/**
	 * Save Config
	 *
	 * Targets for saving config:
	 * - 'app' for saving in app.json
	 * - 'global' for saving in config.global.json
	 * - anything else for saving in config.json
	 *
	 * @return bool Returns true if succeeded.
	 * @version BETA-2.0.0
	 * @updated BETA-2.0.0
	 */
	public static function saveConfig($_Oli, $config, $target = null, $replace = false): bool
	{
		$result = [];
		if ($target === 'global')
		{
			if (!$replace and is_array($globalConfig = self::getGlobalConfig()))
				$config = array_merge($globalConfig, $config);

			$handle = fopen(OLIPATH . 'config.global.json', 'w');

			if ($result[] = fwrite($handle, json_encode($config, JSON_FORCE_OBJECT | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)))
				self::$globalConfig = $config;

			fclose($handle);
		}
		else if ($target === 'app')
		{
			if ($_Oli->isExistTableMySQL(self::$config['settings_tables'][0]))
			{
				// Are Settings Managed via MySQL?
				foreach ($config as $name => $value)
					$result[] = $_Oli->updateInfosMySQL(self::$config['settings_tables'][0], ['value' => $value], ['name' => $name]);
			}
			else
			{
				/** Merging with existing config */
				if (!$replace and is_array($appConfig = self::getAppConfig()))
					$config = array_merge(['url' => null,
					                       'name' => null,
					                       'description' => null,
					                       'creation_date' => null,
					                       'owner' => null],
					                      $appConfig, $config);

				/** Saving new config */
				$handle = fopen(ABSPATH . 'app.json', 'w');
				if ($result[] = fwrite($handle, json_encode($config, JSON_FORCE_OBJECT | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)))
					self::$appConfig = $config;
				fclose($handle);
			}
		}
		else
		{
			if (!$replace and is_array($localConfig = self::getLocalConfig()))
				$config = array_merge($localConfig, $config);

			$handle = fopen(ABSPATH . 'config.json', 'w');
			if ($result[] = fwrite($handle, json_encode($config, JSON_FORCE_OBJECT | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)))
				self::$localConfig = $config;
			fclose($handle);
		}

		return !in_array(false, $result, true);
	}

	// endregion

	// region IV. 4. Parse config

	/**
	 * Parse Oli Config
	 *
	 * @return bool Returns true if the config was successfully loaded, false otherwise.
	 * @version BETA-2.0.0
	 * @updated BETA-2.0.0
	 */
	public static function parseConfig(OliCore $Oli): bool
	{
		if (empty(self::$rawConfig)) return false;

		self::$config = [];
		self::$errors = [];

		foreach (self::$rawConfig as $eachConfig => $eachValue)
		{
			$eachValue = self::decodeConfigValue($eachValue, $Oli);

			if ($eachConfig === 'constants')
			{
				if (empty($eachValue)) ;
				else if (!is_assoc($eachValue))
					self::$errors[] = "Ignoring 'constants', value must be an associative array.";
				else
					foreach ($eachValue as $eachConstantName => $eachConstantValue)
						if (!defined($eachConstantName))
							define($eachConstantName, $eachConstantValue);
			}
			else if ($eachConfig === 'dbs')
			{
				if (!self::$config['allow_dbs'] || empty($eachValue)) ;
				else if (!is_seq_array($eachValue)) // Empty or associative array
					self::$errors[] = "Ignoring 'dbs', value must be a sequential associative array.";
				else
					foreach ($eachValue as $dbConfig)
					{
						try
						{
							$dbms = ['mysql' => MySQL::class, // MySQL
							         'pgsql' => PostgreSQL::class, // PostgreSQL
							         'mssql' => SQLServer::class, // Microsoft SQL Server
							         'srvsql' => SQLServer::class,
							];
							$dbClass = @$dbms[$dbConfig['dbms']];
							if ($dbClass !== null)
							{
								$db = new $dbClass($dbConfig);
								$Oli->addDB($db);
							}
							else
								trigger_error("DBMS '" . $dbConfig['dbms'] . "' is not recognized or not supported", E_USER_ERROR);
						}
						catch (Exception $e)
						{
							trigger_error("Failed to initialize a DB connection: '" . $e->getMessage(), E_USER_ERROR);
						}
					}
			}
//			else if ($eachConfig == 'settings_tables' && isset(self::$sql)) self::setSettingsTables($eachValue);
			else if ($eachConfig == 'assets_path')
			{
				if (!defined('ASSETSPATH')) define('ASSETSPATH', $eachValue);
			}
//			else if ($eachConfig == 'assets_url') $_Oli->assetsUrl = $eachValue;
			else if ($eachConfig == 'media_path')
			{
				if (!defined('MEDIAPATH')) define('MEDIAPATH', $eachValue);
			}
//			else if ($eachConfig == 'media_url') $_Oli->mediaUrl = $eachValue;
			else if ($eachConfig == 'pages_path')
			{
				if (!defined('PAGESPATH')) define('PAGESPATH', $eachValue);
			}
			else if ($eachConfig == 'scripts_path')
			{
				if (!defined('SCRIPTSPATH')) define('SCRIPTSPATH', $eachValue);
			}
			else if ($eachConfig == 'templates_path')
			{
				if (!defined('TEMPLATESPATH')) define('TEMPLATESPATH', $eachValue);
			}
//			else if ($eachConfig == 'common_path') self::setCommonPath($eachValue);
//			else if ($eachConfig == 'accounts_tables' && !empty($eachValue) && is_assoc($eachValue))
//			{
//				if (!empty($eachValue['accounts'])) $_Oli->accountsTables['ACCOUNTS'] = $eachValue['accounts'];
//				if (!empty($eachValue['infos'])) $_Oli->accountsTables['INFOS'] = $eachValue['infos'];
//				if (!empty($eachValue['sessions'])) $_Oli->accountsTables['SESSIONS'] = $eachValue['sessions'];
//				if (!empty($eachValue['requests'])) $_Oli->accountsTables['REQUESTS'] = $eachValue['requests'];
//				if (!empty($eachValue['log_limits'])) $_Oli->accountsTables['LOG_LIMITS'] = $eachValue['log_limits'];
//				if (!empty($eachValue['rights'])) $_Oli->accountsTables['RIGHTS'] = $eachValue['rights'];
//				if (!empty($eachValue['permissions'])) $_Oli->accountsTables['PERMISSIONS'] = $eachValue['permissions'];
//			}

			self::$config[$eachConfig] =
				self::decodeConfigArray($eachValue,
				                        array_key_exists($eachConfig, self::$config ?: [])
					                        ? self::$config[$eachConfig] : null);
		}

		return !empty(self::$config);
	}

	/**
	 * Decode config text codes
	 *
	 * @param mixed $values
	 * @param OliCore $Oli
	 *
	 * @return mixed
	 */
	public static function decodeConfigValue(mixed $values, OliCore $Oli): mixed
	{
		if (empty($values)) return $values;

		$isArray = is_array($values);
		if (!$isArray) $values = [$values];

		$output = [];
		foreach ($values as $eachKey => $eachValue)
		{
			if (is_array($eachValue))
			{
				$result = self::decodeConfigValue($eachValue, $Oli);
				$output[$eachKey] = !is_array($result) ? [$result] : $result;
			}
			else
			{
				if ($eachValue === null) $output[$eachKey] = null;
				else
				{
					$result = [];
					foreach (explode('|', $eachValue) as $eachPart)
					{
						$partResult = '';
						if (is_string($eachPart))
						{
							if (preg_match('/^["\'](.*)["\']$/i', $eachPart, $matches)) $partResult = $eachPart;
							else if (preg_match('/^Setting:\s?(.*)$/i', $eachPart, $matches)) $partResult = $Oli->getSetting($matches[1]);
							else if (preg_match('/^UrlParam:\s?(.*)$/i', $eachPart, $matches)) $partResult = $Oli->getUrlParam($matches[1]);
							else if (preg_match('/^ShortcutLink:\s?(.*)$/i', $eachPart, $matches)) $partResult = $Oli->getShortcutLink($matches[1]);
							else if (preg_match('/^Const:\s?(.*)$/i', $eachPart, $matches)) $partResult = constant($matches[1]);
							else if (preg_match('/^Time:\s?(\d+)\s?(\S*)$/i', $eachPart, $matches))
							{
								if ($matches[2] == 'years' || $matches[2] == 'year') $partResult = $matches[1] * 365.25 * 24 * 3600;
								else if ($matches[2] == 'months' || $matches[2] == 'month') $partResult = $matches[1] * 30.4375 * 24 * 3600;
								else if ($matches[2] == 'weeks' || $matches[2] == 'week') $partResult = $matches[1] * 7 * 24 * 3600;
								else if ($matches[2] == 'days' || $matches[2] == 'day') $partResult = $matches[1] * 24 * 3600;
								else if ($matches[2] == 'hours' || $matches[2] == 'hour') $partResult = $matches[1] * 3600;
								else if ($matches[2] == 'minutes' || $matches[2] == 'minute') $partResult = $matches[1] * 60;
								else $partResult = $matches[1];
							}
							else if (preg_match('/^Size:\s?(\d+)\s?(\S*)$/i', $eachPart, $matches)) $partResult = $Oli->convertFileSize($matches[1] . ' ' . $matches[2]);
							else if (preg_match('/^MediaUrl$/i', $eachPart)) $partResult = $Oli->getMediaUrl();
							else if (preg_match('/^DataUrl$/i', $eachPart)) $partResult = $Oli->getAssetsUrl();
							else $partResult = $eachPart;
						}
						$result[] = $partResult;
					}

					$output[$eachKey] = (count($result) > 1 ? implode($result) : $result[0]);
				}
			}
		}

		if (!$isArray && count($output) === 1) return $output[0];
		return $output;
	}

	/**
	 * Decode config arrays
	 *
	 * @param mixed $array
	 * @param array|null $currentConfig
	 *
	 * @return mixed
	 */
	public static function decodeConfigArray(mixed $array, ?array $currentConfig = null): mixed
	{
		$isArray = is_array($array);
		if (!$isArray) $array = [$array];

		$output = [];
		foreach ($array as $eachKey => $eachValue)
		{
			if (is_assoc($eachValue))
				$output[$eachKey] = self::decodeConfigArray($eachValue, @$currentConfig[$eachKey]);
			else if ($eachValue === null && $currentConfig !== null)
				$output[$eachKey] = ($isArray && is_array($currentConfig)) ? @$currentConfig[$eachKey] : $currentConfig;
			else if ($eachValue === 'NULL')
				$output[$eachKey] = null;
			else
				$output[$eachKey] = $eachValue;
		}

		if (!$isArray && count($output) === 1) return $output[0];
		return $output;
	}

	// endregion

	// endregion

	// region V. Getters

	/**
	 * Get the last update timestamp
	 *
	 * @return float|null Returns the last update timestamp.
	 * @version BETA-2.0.0
	 * @updated BETA-2.0.0
	 */
	public static function getLastUpdateTime(): ?float
	{
		return self::$lastUpdateTimestamp;
	}

	// endregion
}
