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
|*|  ├ II. Magic Methods
|*|  ├ III. Configuration
|*|  │ ├ 1. Get Config
|*|  │ ├ 2. Load Config
|*|  │ ├ 3. Parse Config
|*|  │ └ 4. Process Config
|*|  │
|*|  └ IV. Tools
\*/

namespace Oli {

class Config {

	/** -------------- */
	/**  I. Variables  */
	/** -------------- */
	
	private static $lastUpdateTimestamp = null;
	
	private static $appConfig = null;
	
	private static $defaultConfig = null;
	private static $globalConfig = null;
	private static $localConfig = null;
	
	public static $rawConfig = null;
	public static $config = null;
	
	
	/** *** *** */
	
	/** ------------------- */
	/**  II. Magic Methods  */
	/** ------------------- */
	
	/**
	 * Oli\Config Class to String function
	 * 
	 * @version BETA-1.8.1
	 * @updated BETA-2.0.0
	 * @return string Returns Oli Config Registry information.
	 */
	public function __toString() {
		return 'Config Registry for Oli';
	}
	
	/** *** *** */
	
	/** ------------------- */
	/**  II. Configuration  */
	/** ------------------- */
		
		/** ------------------- */
		/**  IV. 1. Get Config  */
		/** ------------------- */
		
		/**
		 * Get Default Config
		 * 
		 * @version BETA-2.0.0
		 * @updated BETA-2.0.0
		 * @return mixed Array or requested value.
		 */
		public static function getDefaultConfig($index = null, $reload = false) {
			if(is_bool($index)) $reload = $index;
			if(($reload OR !isset(self::$defaultConfig)) AND file_exists(INCLUDESPATH . 'config.default.json'))
				self::$defaultConfig = json_decode(file_get_contents(INCLUDESPATH . 'config.default.json'), true);
			
			if(self::$defaultConfig !== null) {
				if(!empty($index)) return self::$defaultConfig[$index] ?: null;
				else return self::$defaultConfig ?: [];
			} else return null;
		}
		
		/**
		 * Get Global Config
		 * 
		 * @version BETA-2.0.0
		 * @updated BETA-2.0.0
		 * @return mixed Array or requested value.
		 */
		public static function getGlobalConfig($index = null, $reload = false) {
			if(is_bool($index)) $reload = $index;
			if(($reload OR !isset(self::$globalConfig)) AND file_exists(OLIPATH . 'config.global.json'))
				self::$globalConfig = json_decode(file_get_contents(OLIPATH . 'config.global.json'), true);
			
			if(self::$globalConfig !== null) {
				if(!empty($index)) return self::$globalConfig[$index] ?: null;
				else return self::$globalConfig ?: [];
			} else return null;
		}
		
		/**
		 * Get Default Config
		 * 
		 * @version BETA-2.0.0
		 * @updated BETA-2.0.0
		 * @return mixed Array or requested value.
		 */
		public static function getLocalConfig($index = null, $reload = false) {
			if(is_bool($index)) $reload = $index;
			if(($reload OR !isset(self::$localConfig)) AND file_exists(ABSPATH . 'config.json'))
				self::$localConfig = json_decode(file_get_contents(ABSPATH . 'config.json'), true);
			
			if(self::$localConfig !== null) {
				if(!empty($index)) return self::$localConfig[$index] ?: null;
				else return self::$localConfig ?: [];
			} else return null;
		}
		
		/**
		 * Get App Config
		 * 
		 * @version BETA-2.0.0
		 * @updated BETA-2.0.0
		 * @return mixed Array or requested value.
		 */
		public static function getAppConfig($index = null, $reload = false) {
			if(is_bool($index)) $reload = $index;
			
			if(($reload OR !isset(self::$appConfig)) AND file_exists(ABSPATH . 'app.json'))
				self::$appConfig = json_decode(file_get_contents(ABSPATH . 'app.json'), true);
			
			if(self::$appConfig !== null) {
				if(!empty($index)) return self::$appConfig[$index] ?: null;
				else return self::$appConfig ?: [];
			} else return null;
		}
		
		/** -------------------- */
		/**  IV. 2. Load Config  */
		/** -------------------- */
		
		/**
		 * Update Config
		 * 
		 * @version BETA-2.0.0
		 * @updated BETA-2.0.0
		 * @return boolean Returns true if the config is updated.
		 */
		public static function updateConfig($config, $target = false, $replace = false) {
			if(!$target OR self::saveConfig($config, $target, $replace)) {
				self::$rawConfig = array_merge(self::$rawConfig, $config);
				return self::reloadConfig();
			} else return false;
		}
		
		/**
		 * Save Config
		 * 
		 * Targets for saving config:
		 * - 'app' for saving in app.json
		 * - 'global' for saving in config.global.json
		 * - anything else for saving in config.json
		 * 
		 * @version BETA-2.0.0
		 * @updated BETA-2.0.0
		 * @return boolean Returns true if succeeded.
		 */
		public static function saveConfig($config, $target = null, $replace = false) {
			$result = [];
			if($target === 'global') {
				if(!$replace AND is_array($globalConfig = self::getGlobalConfig())) $config = array_merge($globalConfig, $config);
				$handle = fopen(OLIPATH . 'config.global.json', 'w');
				if($result[] = fwrite($handle, json_encode($config, JSON_FORCE_OBJECT | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT))) self::$globalConfig = $config;
				fclose($handle);
			} else if($target === 'app') {
				if(self::isExistTableMySQL(self::$config['settings_tables'][0])) { // Are Settings Managed via MySQL?
					foreach($config as $name => $value) {
						$result[] = self::updateInfosMySQL(self::$config['settings_tables'][0], array('name' => $name), array('value' => $value));
					}
					if(!in_array(false, $result, true)) self::$appConfig = $config;
				} else {
					/** Merging with existing config */
					if(!$replace AND is_array($appConfig = self::getAppConfig())) $config = array_merge(array(
						'url' => null,
						'name' => null,
						'description' => null,
						'creation_date' => null,
						'owner' => null
					), $appConfig, $config);
					
					/** Saving new config */
					$handle = fopen(ABSPATH . 'app.json', 'w');
					if($result[] = fwrite($handle, json_encode($config, JSON_FORCE_OBJECT | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT))) self::$appConfig = $config;
					fclose($handle);
				}
			} else {
				if(!$replace AND is_array($localConfig = self::getLocalConfig())) $config = array_merge($localConfig, $config);
				$handle = fopen(ABSPATH . 'config.json', 'w');
				if($result[] = fwrite($handle, json_encode($config, JSON_FORCE_OBJECT | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT))) self::$localConfig = $config;
				fclose($handle);
			}
			return !in_array(false, $result, true);
		}
		
		/**
		 * Load Oli Config
		 * 
		 * @version BETA-2.0.0
		 * @updated BETA-2.0.0
		 * @return boolean Returns true if the config was successfully loaded, false otherwise.
		 */
		public static function loadRawConfig() {
			/** Load Config */
			$defaultConfig = self::getDefaultConfig();
			$globalConfig = self::getGlobalConfig();
			$localConfig = self::getLocalConfig();
			
			// Merge Default, Global & Local configs together
			self::$rawConfig = [];
			if(!empty($defaultConfig) AND is_array($defaultConfig)) self::$rawConfig = array_merge(self::$rawConfig, $defaultConfig);
			if(!empty($globalConfig) AND is_array($globalConfig)) self::$rawConfig = array_merge(self::$rawConfig, $globalConfig);
			if(!empty($localConfig) AND is_array($localConfig)) self::$rawConfig = array_merge(self::$rawConfig, $localConfig);
			
			// Update the last update timestamp
			self::$lastUpdateTimestamp = microtime(true);
			
			return !empty(self::$rawConfig);
		}
		
		/**
		 * Load Oli Config
		 * 
		 * @version BETA-2.0.0
		 * @updated BETA-2.0.0
		 * @return boolean Returns true if the config was successfully loaded, false otherwise.
		 */
		public static function loadConfig($_Oli) {
			return (!empty(self::$rawConfig) OR self::loadRawConfig()) AND self::parseConfig($_Oli);
		}
		
		/**
		 * Reload Oli Config
		 * 
		 * @version BETA-2.0.0
		 * @updated BETA-2.0.0
		 * @return boolean Returns true if the config was successfully loaded, false otherwise.
		 */
		public static function reloadConfig($_Oli) {
			return self::loadRawConfig() AND self::parseConfig($_Oli);
		}
		
		/** --------------------- */
		/**  IV. 3. Parse Config  */
		/** --------------------- */
		
		/**
		 * Parse Oli Config
		 * 
		 * @version BETA-2.0.0
		 * @updated BETA-2.0.0
		 * @return boolean Returns true if the config was successfully loaded, false otherwise.
		 */
		public static function parseConfig($_Oli) {
			if(empty(self::$rawConfig)) return false;
			
			self::$config = [];
			foreach(self::$rawConfig as $eachConfig => $eachValue) {
				$eachValue = self::decodeConfigValues($eachValue, $_Oli);
				
				if($eachConfig == 'constants' AND !empty($eachValue) AND is_assoc($eachValue)) {
					foreach($eachValue as $eachConstantName => $eachConstantValue) {
						if(!defined($eachConstantName)) define($eachConstantName, $eachConstantValue);
					}
				} else if($eachConfig == 'mysql' AND self::$config['allow_mysql'] AND !empty($eachValue))
					$_Oli->setupMySQL($eachValue['database'], $eachValue['username'], $eachValue['password'], $eachValue['hostname'], $eachValue['charset']);
				// else if($eachConfig == 'settings_tables' AND isset(self::$db)) self::setSettingsTables($eachValue);
				else if($eachConfig == 'assets_path' AND !defined('ASSETSPATH')) define('ASSETSPATH', $eachValue);
				// else if($eachConfig == 'assets_url') $_Oli->assetsUrl = $eachValue;
				else if($eachConfig == 'media_path' AND !defined('MEDIAPATH')) define('MEDIAPATH', $eachValue);
				// else if($eachConfig == 'media_url') $_Oli->mediaUrl = $eachValue;
				else if($eachConfig == 'theme_path' AND !defined('THEMEPATH')) define('THEMEPATH', $eachValue);
				else if($eachConfig == 'scripts_path' AND !defined('SCRIPTSPATH')) define('SCRIPTSPATH', $eachValue);
				else if($eachConfig == 'templates_path' AND !defined('TEMPLATESPATH')) define('TEMPLATESPATH', $eachValue);
				// else if($eachConfig == 'common_path') self::setCommonPath($eachValue);
				// else if($eachConfig == 'accounts_tables' AND !empty($eachValue) AND is_assoc($eachValue)) {
					// if(!empty($eachValue['accounts'])) $_Oli->accountsTables['ACCOUNTS'] = $eachValue['accounts'];
					// if(!empty($eachValue['infos'])) $_Oli->accountsTables['INFOS'] = $eachValue['infos'];
					// if(!empty($eachValue['sessions'])) $_Oli->accountsTables['SESSIONS'] = $eachValue['sessions'];
					// if(!empty($eachValue['requests'])) $_Oli->accountsTables['REQUESTS'] = $eachValue['requests'];
					// if(!empty($eachValue['log_limits'])) $_Oli->accountsTables['LOG_LIMITS'] = $eachValue['log_limits'];
					// if(!empty($eachValue['rights'])) $_Oli->accountsTables['RIGHTS'] = $eachValue['rights'];
					// if(!empty($eachValue['permissions'])) $_Oli->accountsTables['PERMISSIONS'] = $eachValue['permissions'];
				// }
				
				self::$config[$eachConfig] = self::decodeConfigArray($eachValue, array_key_exists($eachConfig, self::$config ?: []) ? self::$config[$eachConfig] : null);
			}
			
			return !empty(self::$config);
		}
		
		/** ----------------------- */
		/**  IV. 4. Process Config  */
		/** ----------------------- */
		
		/** Decode config arrays */
		public static function decodeConfigArray($array, $currentConfig = null) {
			$output = [];
			foreach((!is_array($array) ? [$array] : $array) as $eachKey => $eachValue) {
				if(is_assoc($eachValue)) $output[$eachKey] = self::decodeConfigArray($eachValue, $currentConfig[$eachKey]);
				else if(isset($currentConfig) AND $eachValue === null) $output[$eachKey] = (is_array($array) AND is_array($currentConfig)) ? $currentConfig[$eachKey] : $currentConfig;
				else if($eachValue == 'NULL') $output[$eachKey] = null;
				else $output[$eachKey] = $eachValue;
			}
			return (!is_array($array) AND count($output) == 1) ? $output[0] : $output;
		}
		
		/** Decode config text codes */
		public static function decodeConfigValues($values, $_Oli) {
			if(empty($values)) return $values;
			
			foreach((!is_array($values) ? [$values] : $values) as $eachKey => $eachValue) {
				$isArray = false;
				if(is_array($eachValue)) {
					$result = self::decodeConfigValues($eachValue, $_Oli);
					$isArray = true;
				} else {
					$result = [];
					if($eachValue === null) $result[] = null;
					else {
						foreach(explode('|', $eachValue) as $eachPart) {
							$partResult = '';
							if(is_string($eachPart)) {
								if(preg_match('/^["\'](.*)["\']$/i', $eachPart, $matches)) $partResult = $eachPart;
								else if(preg_match('/^Setting:\s?(.*)$/i', $eachPart, $matches)) $partResult = $_Oli->getSetting($matches[1]);
								else if(preg_match('/^UrlParam:\s?(.*)$/i', $eachPart, $matches)) $partResult = $_Oli->getUrlParam($matches[1]);
								else if(preg_match('/^ShortcutLink:\s?(.*)$/i', $eachPart, $matches)) $partResult = $_Oli->getShortcutLink($matches[1]);
								else if(preg_match('/^Const:\s?(.*)$/i', $eachPart, $matches)) $partResult = constant($matches[1]);
								else if(preg_match('/^Time:\s?(\d+)\s?(\S*)$/i', $eachPart, $matches)) {
									if($matches[2] == 'years' OR $matches[2] == 'year') $partResult = $matches[1] * 365.25 * 24 * 3600;
									else if($matches[2] == 'months' OR $matches[2] == 'month') $partResult = $matches[1] * 30.4375 * 24 * 3600;
									else if($matches[2] == 'weeks' OR $matches[2] == 'week') $partResult = $matches[1] * 7 * 24 * 3600;
									else if($matches[2] == 'days' OR $matches[2] == 'day') $partResult = $matches[1] * 24 * 3600;
									else if($matches[2] == 'hours' OR $matches[2] == 'hour') $partResult = $matches[1] * 3600;
									else if($matches[2] == 'minutes' OR $matches[2] == 'minute') $partResult = $matches[1] * 60;
									else $partResult = $matches[1];
								} else if(preg_match('/^Size:\s?(\d+)\s?(\S*)$/i', $eachPart, $matches)) $partResult = $_Oli->convertFileSize($matches[1] . ' ' . $matches[2]);
								else if(preg_match('/^MediaUrl$/i', $eachPart)) $partResult = $_Oli->getMediaUrl();
								else if(preg_match('/^DataUrl$/i', $eachPart)) $partResult = $_Oli->getAssetsUrl();
								else $partResult = $eachPart;
							}
							$result[] = $partResult;
						}
					}
				}
				$output[$eachKey] = $isArray ? (!is_array($result) ? [$result] : $result) : (count($result) > 1 ? implode($result) : $result[0]);
			}
			return (!is_array($values) AND count($output) == 1) ? $output[0] : $output;
		}
	
	/** *** *** */
		
	/** ----------- */
	/**  IV. Tools  */
	/** ----------- */
	
	/**
	 * Get the last update timestamp
	 * 
	 * @version BETA-2.0.0
	 * @updated BETA-2.0.0
	 * @return float Returns the last update timestamp.
	 */
	public static function getLastUpdateTime() {
		return self::$lastUpdateTimestamp;
	}

}

}
?>