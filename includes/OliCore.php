<?php
/*\
|*|  ---------------------------------
|*|  --- [  Oli - PHP Framework  ] ---
|*|  --- [  Version BETA: 1.8.0  ] ---
|*|  ---------------------------------
|*|  
|*|  Oli is an open source PHP framework made to help web developers creating their website.
|*|  Created and developed by Mathieu Guérin – aka Matiboux.
|*|  
|*|  Oli Github repository: https://github.com/OliFramework/Oli/
|*|  
|*|  Creator & Developer: Matiboux (Mathieu Guérin)
|*|    → Email: matiboux@gmail.com
|*|  
|*|  For more info, please read the README.md file.
|*|  You can find it on the Oli repository (Github link above).
|*|  
|*|  --- --- ---
|*|  
|*|  Copyright (C) 2015-2017 Matiboux (Mathieu Guérin)
|*|  
|*|    This program is free software: you can redistribute it and/or modify
|*|    it under the terms of the GNU Affero General Public License as published
|*|    by the Free Software Foundation, either version 3 of the License, or
|*|    (at your option) any later version.
|*|    
|*|    This program is distributed in the hope that it will be useful,
|*|    but WITHOUT ANY WARRANTY; without even the implied warranty of
|*|    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
|*|    See the GNU Affero General Public License for more details.
|*|    
|*|    You should have received a copy of the GNU Affero General Public License
|*|    along with this program. If not, see <http://www.gnu.org/licenses/>.
|*|  
|*|  You'll find a copy of the GNU AGPL v3 license in the LICENSE file.
|*|  
|*|  --- --- ---
|*|  Project dedicated to Maliott..
|*|  --- --- ---
|*|  
|*|  Releases date:
|*|  - PRE-DEV: November 15, 2014
|*|  - ALPHA: February 6, 2015 (released on Github)
|*|  - BETA: July 2015
|*|    * No info on previous releases
|*|    * [version 1.5]:
|*|              (1.5.0): August 17, 2015
|*|              (1.5.1): August 21, 2015
|*|              (1.5.2): August 25, 2015
|*|              (1.5.3): August 26, 2015
|*|              (1.5.5): November 20, 2015
|*|    * [version 1.6]:
|*|              (1.6.0): December 6, 2015
|*|              (1.6.2): December 9, 2015
|*|              (1.6.3): January 10, 2016
|*|              (1.6.4): February 10, 2016
|*|              (1.6.6): June 2, 2016
|*|    * [version 1.7]: -- [ Github repository created! ] --
|*|              (1.7.0): August 11, 2016
|*|              (1.7.1): February 19, 2017
|*|              (1.7.2): February 22, 2017
|*|    * [version 1.8]:
|*|              (1.8.0): Currently in development
\*/

/*\
|*|  Table of Content:
|*|  
|*|  I. Variables
|*|  II. Magic Methods
|*|  III. Configuration
|*|    1. Config Loader
|*|    2. MySQL Config
|*|    3. General Config 
|*|    4. Addons
|*|      A. Management
|*|      B. Infos
|*|  IV. MySQL Functions
|*|    1. Status Functions
|*|    2. Read Functions
|*|    3. Write Functions
|*|    4. Database Functions
|*|  V. General Functions
|*|    1. Oli Informations
|*|    2. Website Content
|*|    3. Website Settings
|*|    4. Website Tools
|*|    5. Translations & Text
|*|      A. Read Functions
|*|      B. Write Functions
|*|      C. Print Functions
|*|    6. HTTP Tools
|*|      A. Content Type
|*|      B. Cookie Management
|*|        a. Read Functions
|*|        b. Write Functions
|*|      C. _POST vars
|*|        a. Read Functions
|*|        b. Write Functions
|*|    6. HTML Tools
|*|      A. File Loaders
|*|      B. File Minimizers
|*|    7. Url Functions
|*|    8. User Language
|*|    9. Utility Tools
|*|      A. Generators
|*|      B. Date & Time
|*|      C. Client Infos
|*|  [...]
\*/

namespace OliFramework {

class OliCore {

	/** -------------- */
	/**  I. Variables  */
	/** -------------- */
	
	/** Read-only variables */
	private $readOnlyVars = ['oliInfos', 'addonsInfos', 'db', 'config'];
	
	/** Components infos */
	private $oliInfos = []; // Oli Infos (PUBLIC READONLY)
	private $addonsInfos = []; // Addons Infos (PUBLIC READONLY)
	
	/** Config */
	private $db = null; // MySQL PDO Object (PUBLIC READONLY)
	private $config = null; // (PUBLIC READONLY)
	private $mysqlConfig = null;
	
	
	/** Content */
	private $fileNameParam = null;
	private $contentStatus = null;
	
	/** Page Settings */
	private $contentType = null;
	private $contentTypeBeenForced = false;
	private $currentCharset = null;
	private $htmlLoaderList = [];
	
	/** User Language */
	private $currentLanguage = '';
	
	/** Post Vars Cookie */
	private $postVarsProtection = false;
	
	
	/** User ID */
	private $userID = null;
	
	/** *** *** *** */
	
	/** ------------------- */
	/**  II. Magic Methods  */
	/** ------------------- */
	
	/** Class Construct function */
	public function __construct($initTimestamp = null) {
		/** Load Oli Infos & Default Config */
		if(file_exists(INCLUDEPATH . 'oli-infos.json')) $this->oliInfos = json_decode(file_get_contents(INCLUDEPATH . 'oli-infos.json'), true);
		if(file_exists(INCLUDEPATH . 'config.default.json')) $this->loadConfig(json_decode(file_get_contents(INCLUDEPATH . 'config.default.json'), true));
		
		$this->config['init_timestamp'] = $initTimestamp ?: microtime(true);
		$this->setContentType('DEFAULT', 'utf-8');
		$this->setCurrentLanguage('DEFAULT');
	}
	
	/** Class Destruct function */
	public function __destruct() {
		$this->loadEndHtmlFiles();
		if($this->config['user_management']) $this->updateUserSession();
	}
	
	/** Read-only variables */
	function __get($whatVar) {
		if(in_array($whatVar, $this->readOnlyVars)) return $this->$whatVar;
		else return null;
    }
	
	/** __toString() function:
		See the section V. 1. */
	
	/** *** *** *** */
	
	/** -------------------- */
	/**  III. Configuration  */
	/** -------------------- */
	
		/** ----------------------- */
		/**  III. 1. Config Loader  */
		/** ----------------------- */
		
		/** Load Config */
		public function loadConfig($config) {
			foreach($config as $eachConfig => $eachValue) {
				$eachValue = $this->decodeConfigValues($eachValue);
				
				if($eachConfig == 'mysql' AND !empty($eachValue)) $this->setupMySQL($eachValue['database'], $eachValue['username'], $eachValue['password'], $eachValue['hostname'], $eachValue['charset']);
				else if($eachConfig == 'settings_tables' AND isset($this->db)) $this->setSettingsTables($eachValue);
				else if($eachConfig == 'common_path') $this->setCommonPath($eachValue);
				else $this->config[$eachConfig] = $this->decodeConfigArray($eachValue, array_key_exists($eachConfig, $this->config ?: []) ? $this->config[$eachConfig] : null);
			}
		}
		
		/** Decode config arrays */
		public function decodeConfigArray($array, $currentConfig = []) {
			$output = [];
			foreach((!is_array($array) ? [$array] : $array) as $eachKey => $eachValue) {
				if(is_assoc($eachValue)) $output[$eachKey] = $this->decodeConfigArray($eachValue, $currentConfig[$eachKey]);
				else if(!empty($currentConfig) AND $eachValue === null) $output[$eachKey] = is_array($currentConfig) ? $currentConfig[$eachKey] : $currentConfig;
				else if($eachValue == 'NULL') $output[$eachKey] = null;
				else $output[$eachKey] = $eachValue;
			}
			return (!is_array($array) AND count($output) == 1) ? $output[0] : $output;
		}
		
		/** Decode config text codes */
		public function decodeConfigValues($values) {
			if(!empty($values)) {
				foreach((!is_array($values) ? [$values] : $values) as $eachKey => $eachValue) {
					$isArray = false;
					if(is_array($eachValue)) {
						$result = $this->decodeConfigValues($eachValue);
						$isArray = true;
					} else {
						$result = [];
						if($eachValue === null) $result = null;
						else {
							foreach(explode('|', $eachValue) as $eachPart) {
								$partResult = '';
								if(is_string($eachPart)) {
									if(preg_match('/^["\'](.*)["\']$/i', $eachPart, $matches)) $partResult = $eachPart;
									else if(preg_match('/^Setting:\s?(.*)$/i', $eachPart, $matches)) $partResult = $this->getSetting($matches[1]);
									else if(preg_match('/^UrlParam:\s?(.*)$/i', $eachPart, $matches)) $partResult = $this->getUrlParam($matches[1]);
									else if(preg_match('/^ShortcutLink:\s?(.*)$/i', $eachPart, $matches)) $partResult = $this->getShortcutLink($matches[1]);
									else if(preg_match('/^Const:\s?(.*)$/i', $eachPart, $matches)) $partResult = constant($matches[1]);
									else if(preg_match('/^Time:\s?(\d+)\s?(\S*)$/i', $eachPart, $matches)) {
										if($matches[2] == 'years' OR $matches[2] == 'year') $partResult = $matches[1] * 365.25 * 24 * 3600;
										else if($matches[2] == 'months' OR $matches[2] == 'month') $partResult = $matches[1] * 30.4375 * 24 * 3600;
										else if($matches[2] == 'weeks' OR $matches[2] == 'week') $partResult = $matches[1] * 7 * 24 * 3600;
										else if($matches[2] == 'days' OR $matches[2] == 'day') $partResult = $matches[1] * 24 * 3600;
										else if($matches[2] == 'hours' OR $matches[2] == 'hour') $partResult = $matches[1] * 3600;
										else if($matches[2] == 'minutes' OR $matches[2] == 'minute') $partResult = $matches[1] * 60;
										else $partResult = $matches[1];
									} else if(preg_match('/^Size:\s?(\d+)\s?(\S*)$/i', $eachPart, $matches)) {
										if($matches[2] == 'TiB' OR $matches[2] == 'Tio') $partResult = $matches[1] * (1024 ** 4);
										else if($matches[2] == 'TB' OR $matches[2] == 'To') $partResult = $matches[1] * (1000 ** 4);
										else if($matches[2] == 'GiB' OR $matches[2] == 'Gio') $partResult = $matches[1] * (1024 ** 3);
										else if($matches[2] == 'GB' OR $matches[2] == 'Go') $partResult = $matches[1] * (1000 ** 3);
										else if($matches[2] == 'MiB' OR $matches[2] == 'Mio') $partResult = $matches[1] * (1024 ** 2);
										else if($matches[2] == 'MB' OR $matches[2] == 'Mo') $partResult = $matches[1] * (1000 ** 2);
										else if($matches[2] == 'KiB' OR $matches[2] == 'Kio') $partResult = $matches[1] * 1024;
										else if($matches[2] == 'KB' OR $matches[2] == 'Ko') $partResult = $matches[1] * 1000;
										
										else if($matches[2] == 'Tib') $partResult = $matches[1] * 8 * (1024 ** 4);
										else if($matches[2] == 'Tb') $partResult = $matches[1] * 8 * (1000 ** 4);
										else if($matches[2] == 'Gib') $partResult = $matches[1] * 8 * (1024 ** 3);
										else if($matches[2] == 'Gb') $partResult = $matches[1] * 8 * (1000 ** 3);
										else if($matches[2] == 'Mib') $partResult = $matches[1] * 8 * (1024 ** 2);
										else if($matches[2] == 'Mb') $partResult = $matches[1] * 8 * (1000 ** 2);
										else if($matches[2] == 'Kib') $partResult = $matches[1] * 8 * 1024;
										else if($matches[2] == 'Kb') $partResult = $matches[1] * 8 * 1000;
										else if($matches[2] == 'b') $partResult = $matches[1] * 8;
										
										else $partResult = $matches[1];
									}
									else if(preg_match('/^MediaUrl$/i', $eachPart)) $partResult = $this->getMediaUrl();
									else if(preg_match('/^DataUrl$/i', $eachPart)) $partResult = $this->getAssetsUrl();
									else $partResult = $eachPart;
								}
								$result[] = $partResult;
							}
						}
					}
					$output[$eachKey] = $isArray ? (!is_array($result) ? [$result] : $result) : (count($result) > 1 ? implode($result) : $result[0]);
				}
				return (!is_array($values) AND count($output) == 1) ? $output[0] : $output;
			} else return $values;
		}
		
		/** ---------------------- */
		/**  III. 2. MySQL Config  */
		/** ---------------------- */
		
		/** MySQL Setup & Config */
		public function setupMySQL($database, $username = 'root', $password = '', $hostname = 'localhost', $charset = 'utf-8') {
			if(!empty($database)) {
				try {
					$this->db = new \PDO('mysql:dbname=' . $database . ';host=' . $hostname . ';charset=' . $charset, $username, $password);
					$this->mysqlConfig = array('database' => $database, 'username' => $username, 'password' => $password, 'hostname' => $hostname, 'charset' => $charset);
				} catch(PDOException $e) {
					trigger_error($e->getMessage(), E_USER_ERROR);
				}
			} else return false;
		}
		public function resetMySQL() {
			$this->db = null;
			$this->mysqlConfig = null;
		}
		
		/** ------------------------ */
		/**  III. 3. General Config  */
		/** ------------------------ */
		
		/** Set Settings Tables */
		public function setSettingsTables($tables) {
			$this->config['settings_tables'] = $tables = !is_array($tables) ? [$tables] : $tables;
			$hasArray = false;
			foreach($tables as $eachTableGroup) {
				if(is_array($eachTableGroup) OR $hasArray) {
					$hasArray = true;
					$this->config['settings_tables'] = $eachTableGroup;
					$this->getUrlParam('base', $hasUsedHttpHostBase);
					
					if(!$hasUsedHttpHostBase) break;
				}
			}
			
			$i = 1;
			while($i <= strlen($this->config['media_path']) AND $i <= strlen($this->config['theme_path']) AND substr($this->config['media_path'], 0, $i) == substr($this->config['theme_path'], 0, $i)) {
				$contentPath = substr($this->config['media_path'], 0, $i);
				$i++;
			}
			define('CONTENTPATH', ABSPATH . ($contentPath ?: 'content/'));
			define('MEDIAPATH', $this->config['media_path'] ? ABSPATH . $this->config['media_path'] : CONTENTPATH . 'media/');
			define('THEMEPATH', $this->config['theme_path'] ? ABSPATH . $this->config['theme_path'] : CONTENTPATH . 'theme/');
		}
		
		/** Set Common Files Path */
		public function setCommonPath($path) {
			if(!empty($path)) {
				$this->config['common_path'] = $path;
				if(!defined('COMMONPATH')) define('COMMONPATH', ABSPATH . $path);
			}
		}
		
		/** ---------------- */
		/**  III. 4. Addons  */
		/** ---------------- */
		
			/** ----------------------- */
			/**  III. 4. A. Management  */
			/** ----------------------- */
			
			/** Add Addon */
			public function addAddon($id, $varname) { $this->addonsInfos[$id]['varname'] = $varname; }
			
			/** Remove Addon */
			// public function removeAddons(...$id) {}
			public function removeAddon($id) { unset($this->addonsInfos[$id]); }
			
			/** Is exist Addon */
			public function isExistAddon($id) { return array_key_exists($id, $this->addonsInfos); }
			
			/** Rename Addon */
			public function renameAddon($id, $newId) {
				if($this->isExistAddon($id) AND !$this->isExistAddon($newId)) {
					$this->addonsInfos[$newId] = $this->addonsInfos[$id];
					$this->removeAddon($id);
					return true;
				} else return false;
			}
			
			/** ------------------ */
			/**  III. 4. B. Infos  */
			/** ------------------ */
			
			/** Add Addon Infos */
			public function addAddonInfos($id, $infos) {
				$this->addonsInfos[$id] = array_merge($this->addonsInfos[$id], !is_array($infos) ? [$infos] : $infos);
			}
			// public function addAddonInfo($id, $infoId, $infoValue) {}
			
			/** Remove Addon Infos */
			// public function removeAddonInfos($id, ...$infoIds) {}
			public function removeAddonInfo($id, $infoId) { unset($this->addonsInfos[$id][$infoId]); }
			
			/** Is exist Addon */
			public function isExistAddonInfo($id, $infoId) { return array_key_exists($infoId, $this->addonsInfos[$id]); }
			
			/** Get Addon Infos */
			public function getAddonInfos($id = null, $infoId = null) {
				if(!isset($id) OR $id == '*') return $this->addonsInfos;
				else if($this->isExistAddon($id)) {
					if($this->isExistAddonInfo($id, $infoId)) return $this->addonsInfos[$id][$infoId];
					else return $this->addonsInfos[$id];
				} else return false;
			}
			public function getAddonVar($id) {
				if($this->isExistAddon($id) AND $this->isExistAddonInfo($id, 'varname')) return $this->addonsInfos[$id]['varname'];
				else return false;
			}
			// public function getAddonName($varname) {}
	
	/** *** *** *** */
	
	/** --------------------- */
	/**  IV. MySQL Functions  */
	/** --------------------- */
	
		/** ------------------------ */
		/**  IV. 1. Status Function  */
		/** ------------------------ */
		
		/**
		 * Is setup MySQL connection
		 * 
		 * @uses OliCore::$db to check the MySQL connection status
		 * @return boolean|void Returns the MySQL connection status
		 */
		public function isSetupMySQL() {
			if($this->db) return true;
			else return false;
		}
		
		/**
		 * Get raw MySQL PDO Object
		 * 
		 * @uses OliCore::isSetupMySQL() to check the MySQL connection
		 * @uses OliCore::$db to return PDO object
		 * @deprecated OliCore::$db can be directly accessed
		 * @return object Returns current MySQL PDO object
		 */
		// public function getRawMySQL() {
			// $this->isSetupMySQL();
			// return $this->db;
		// }
	
		/** ----------------------- */
		/**  IV. 2. Read Functions  */
		/** ----------------------- */
		
		/**
		 * Get all data from table
		 * 
		 * @param string $table Table to get data from
		 * @param string|array|void $params MySQL Parameters
		 * 
		 * @uses OliCore::isSetupMySQL() to check the MySQL connection
		 * @uses OliCore::$db to execute SQL requests
		 * @return array|boolean Returns data from specified table
		 */
		public function getDataMySQL($table, ...$params) {
			if(!$this->db) trigger_error('Sorry, the MySQL PDO Object hasn\'t been defined!', E_USER_ERROR);
			$select = (is_array($params[0])) ? implode(', ', $params[0]) : '*';
			foreach($params as $eachKey => $eachParam) {
				if(!empty($eachParam)) $queryParams .= ' ' . $eachParam;
			}

			$query = $this->db->prepare('SELECT ' . $select . ' FROM ' . $table . $queryParams);
			if($query->execute()) return $query->fetchAll(\PDO::FETCH_ASSOC);
			else return false;
		}
		
		/**
		 * Get first info from table
		 * 
		 * @param string $table Table to get data from
		 * @param string $whatVar Variable to get
		 * @param boolean|void $rawResult Return raw result or not
		 * 
		 * @uses OliCore::getDataMySQL() to get data from table
		 * @return array|boolean Returns first info from specified table
		 */
		public function getFirstInfoMySQL($table, $whatVar, $rawResult = false) {
			$dataMySQL = $this->getDataMySQL($table);
			if(!empty($dataMySQL)) return (!is_array($dataMySQL[0][$whatVar]) AND is_array(json_decode($dataMySQL[0][$whatVar], true)) AND !$rawResult) ? json_decode($dataMySQL[0][$whatVar], true) : $dataMySQL[0][$whatVar];
			else return false;
		}
		
		/**
		 * Get first line from table
		 * 
		 * @param string $table Table to get data from
		 * @param boolean|void $rawResult Return raw result or not
		 * 
		 * @uses OliCore::getDataMySQL() to get data from table
		 * @return array|boolean Returns first line from specified table
		 */
		public function getFirstLineMySQL($table, $rawResult = false) {
			$dataMySQL = $this->getDataMySQL($table);
			if(!empty($dataMySQL)) {
				foreach($dataMySQL[0] as $eachKey => $eachValue) {
					$dataMySQL[0][$eachKey] = (!is_array($eachValue) AND is_array(json_decode($eachValue, true)) AND !$rawResult) ? json_decode($eachValue, true) : $eachValue;
				}
				return $dataMySQL[0];
			}
			else return false;
		}
		
		/**
		 * Get last info from table
		 * 
		 * @param string $table Table to get data from
		 * @param string $whatVar Variable to get
		 * @param boolean|void $rawResult Return raw result or not
		 * 
		 * @uses OliCore::getDataMySQL() to get data from table
		 * @return array|boolean Returns last info from specified table
		 */
		public function getLastInfoMySQL($table, $whatVar, $rawResult = false) {
			$dataMySQL = $this->getDataMySQL($table, 'ORDER BY id DESC');
			if(!empty($dataMySQL)) return (!is_array($dataMySQL[0][$whatVar]) AND is_array(json_decode($dataMySQL[0][$whatVar], true)) AND !$rawResult) ? json_decode($dataMySQL[0][$whatVar], true) : $dataMySQL[0][$whatVar];
			else return false;
		}
		
		/**
		 * Get last line from table
		 * 
		 * @param string $table Table to get data from
		 * @param boolean|void $rawResult Return raw result or not
		 * 
		 * @uses OliCore::getDataMySQL() to get data from table
		 * @return array|boolean Returns last line from specified table
		 */
		public function getLastLineMySQL($table, $rawResult = false) {
			$dataMySQL = $this->getDataMySQL($table, 'ORDER BY id DESC');
			if(!empty($dataMySQL)) {
				foreach($dataMySQL[0] as $eachKey => $eachValue) {
					$dataMySQL[0][$eachKey] = (!is_array($eachValue) AND is_array(json_decode($eachValue, true)) AND !$rawResult) ? json_decode($eachValue, true) : $eachValue;
				}
				return $dataMySQL[0];
			}
			else return false;
		}
		
		/**
		 * Get lines from table
		 * 
		 * @param string $table Table to get data from
		 * @param array|void $where Where to get data from
		 * @param array|void $settings Data returning settings
		 * @param boolean|void $caseSensitive Where is case sensitive or not
		 * @param boolean|void $forceArray Return result in an array or not
		 * @param boolean|void $rawResult Return raw result or not
		 * 
		 * @uses OliCore::getDataMySQL() to get data from table
		 * @return array|boolean Returns lines from specified table
		 */
		public function getLinesMySQL($table, $where = null, $settings = null, $caseSensitive = null, $forceArray = null, $rawResult = null) {
			if(is_bool($settings)) {
				$rawResult = isset($rawResult) ? $rawResult : $forceArray;
				$forceArray = $caseSensitive;
				$caseSensitive = $settings;
				$settings = null;
			}
			if(!isset($caseSensitive)) $caseSensitive = true;
			if(!isset($forceArray)) $forceArray = false;
			if(!isset($rawResult)) $rawResult = false;
			
			$orderByParam = (isset($settings['order_by'])) ? 'ORDER BY ' . $settings['order_by'] : null;
			$startFrom = (isset($settings['from']) AND $settings['from'] > 0) ? $settings['from'] : 1;
			$startFromId = (isset($settings['fromId']) AND $settings['fromId'] > 0) ? $settings['fromId'] : 1;
			$rowLimit = (isset($settings['limit']) AND $settings['limit'] > 0) ? $settings['limit'] : null;
			
			$dataMySQL = $this->getDataMySQL($table, $orderByParam);
			$valueArray = [];
			$status = [];
			$countRows = 0;
			if(!empty($dataMySQL)) {
				foreach($dataMySQL as $eachLineKey => $eachLine) {
					if($eachLine['id'] < $startFromId) continue;
					
					$status[$eachLineKey] = [];
					if(!empty($where) AND is_array($where)) {
						$whereLineID = 0;
						foreach($where as $whereVar => $whereValue) {
							$whereLineID++;
							if($whereVar == '*') {
								foreach($eachLine as $eachKey => $eachValue) {
									if(is_array($whereValue)) {
										$eachValue = (!is_array($eachValue) AND is_array(json_decode($eachValue, true)) AND !$rawResult) ? json_decode($eachValue, true) : $eachValue;
										
										$status[$eachLineKey][$whereLineID] = $eachKey;
										foreach($whereValue as $eachWhereKey => $eachWhereValue) {
											$toCompare = (!$caseSensitive) ? strtolower($eachValue[$eachWhereKey]) : $eachValue[$eachWhereKey];
											$compareWith = (!$caseSensitive) ? strtolower($eachWhereValue) : $eachWhereValue;
											
											if($toCompare != $compareWith) {
												$status[$eachLineKey][$whereLineID] = false;
												break;
											}
										}
										
										if($status[$eachLineKey][$whereLineID] == $eachKey) break;
									}
									else {
										$toCompare = (!$caseSensitive) ? strtolower($eachValue) : $eachValue;
										$compareWith = (!$caseSensitive) ? strtolower($whereValue) : $whereValue;
										
										if($toCompare == $compareWith) {
											$status[$eachLineKey][$whereLineID] = $eachKey;
											break;
										}
										else $status[$eachLineKey][$whereLineID] = false;
									}
								}
							}
							else {
								if(is_array($whereValue)) {
									$eachLine[$whereVar] = (!is_array($eachLine[$whereVar]) AND is_array(json_decode($eachLine[$whereVar], true)) AND !$rawResult) ? json_decode($eachLine[$whereVar], true) : $eachLine[$whereVar];
									
									$status[$eachLineKey][$whereLineID] = $whereVar;
									foreach($whereValue as $eachWhereKey => $eachWhereValue) {
										$toCompare = (!$caseSensitive) ? strtolower($eachLine[$whereVar][$eachWhereKey]) : $eachLine[$whereVar][$eachWhereKey];
										$compareWith = (!$caseSensitive) ? strtolower($eachWhereValue) : $eachWhereValue;
										
										if($toCompare != $compareWith) {
											$status[$eachLineKey][$whereLineID] = false;
											break;
										}
									}
								}
								else {
									$toCompare = (!$caseSensitive) ? strtolower($eachLine[$whereVar]) : $eachLine[$whereVar];
									$compareWith = (!$caseSensitive) ? strtolower($whereValue) : $whereValue;
									
									if($toCompare == $compareWith) $status[$eachLineKey][$whereLineID] = $whereVar;
									else $status[$eachLineKey][$whereLineID] = false;
								}
							}
						}
					}
					
					if((!in_array(false, $status[$eachLineKey]) AND !empty($status[$eachLineKey])) OR empty($where) OR !is_array($where)) {
						$countRows++;
						if($countRows < $startFrom) continue;
						else if(isset($rowLimit) AND $countRows >= $startFrom + $rowLimit) break;
						
						foreach($eachLine as $eachKey => $eachValue) {
							$eachLine[$eachKey] = (!is_array($eachValue) AND is_array(json_decode($eachValue, true)) AND !$rawResult) ? json_decode($eachValue, true) : $eachValue;
						}
						
						$valueArray[] = $eachLine;
					}
				}
			}
			else return false;
			
			if($forceArray OR count($valueArray) > 1) return $valueArray;
			else if(count($valueArray) == 1) return $valueArray[0];
			else return false;
		}
		
		/**
		 * Get infos from table
		 * 
		 * @param string $table Table to get data from
		 * @param string|array $whatVar What var(s) to return
		 * @param array|void $where Where to get data from
		 * @param array|void $settings Data returning settings
		 * @param boolean|void $caseSensitive Where is case sensitive or not
		 * @param boolean|void $forceArray Return result in an array or not
		 * @param boolean|void $rawResult Return raw result or not
		 * 
		 * @uses OliCore::getDataMySQL() to get data from table
		 * @return mixed Returns infos from specified table
		 */
		public function getInfosMySQL($table, $whatVar, $where = null, $settings = null, $caseSensitive = null, $forceArray = null, $rawResult = null) {
			if(!is_array($whatVar)) $whatVar = [$whatVar];
			else $whatVarArray = true;
			
			if(is_bool($settings)) {
				$rawResult = isset($rawResult) ? $rawResult : $forceArray;
				$forceArray = $caseSensitive;
				$caseSensitive = $settings;
				$settings = null;
			}
			if(!isset($caseSensitive)) $caseSensitive = true;
			if(!isset($forceArray)) $forceArray = false;
			if(!isset($rawResult)) $rawResult = false;
			
			$orderByParam = (isset($settings['order_by'])) ? 'ORDER BY ' . $settings['order_by'] : null;
			$startFrom = (isset($settings['from']) AND $settings['from'] > 0) ? $settings['from'] : 1;
			$startFromId = (isset($settings['fromId']) AND $settings['fromId'] > 0) ? $settings['fromId'] : 1;
			$rowLimit = (isset($settings['limit']) AND $settings['limit'] > 0) ? $settings['limit'] : null;
			
			$dataMySQL = $this->getDataMySQL($table, $orderByParam);
			$valueArray = [];
			$status = [];
			$countRows = 0;
			if(!empty($dataMySQL)) {
				foreach($dataMySQL as $eachLineKey => $eachLine) {
					if($eachLine['id'] < $startFromId) continue;
					
					$status[$eachLineKey] = [];
					if(isset($where) AND is_array($where)) {
						$whereLineID = 0;
						foreach($where as $whereVar => $whereValue) {
							$whereLineID++;
							if($whereVar == '*') {
								foreach($eachLine as $eachKey => $eachValue) {
									if(is_array($whereValue)) {
										$eachValue = (!is_array($eachValue) AND is_array(json_decode($eachValue, true)) AND !$rawResult) ? json_decode($eachValue, true) : $eachValue;
										
										$status[$eachLineKey][$whereLineID] = $eachKey;
										foreach($whereValue as $eachWhereKey => $eachWhereValue) {
											$toCompare = (!$caseSensitive) ? strtolower($eachValue[$eachWhereKey]) : $eachValue[$eachWhereKey];
											$compareWith = (!$caseSensitive) ? strtolower($eachWhereValue) : $eachWhereValue;
											
											if($toCompare != $compareWith) {
												$status[$eachLineKey][$whereLineID] = false;
												break;
											}
										}
										
										if($status[$eachLineKey][$whereLineID] == $eachKey) break;
									}
									else {
										$toCompare = (!$caseSensitive) ? strtolower($eachValue) : $eachValue;
										$compareWith = (!$caseSensitive) ? strtolower($whereValue) : $whereValue;
										
										if($toCompare == $compareWith) {
											$status[$eachLineKey][$whereLineID] = $eachKey;
											break;
										}
										else $status[$eachLineKey][$whereLineID] = false;
									}
								}
							}
							else {
								if(is_array($whereValue)) {
									$eachLine[$whereVar] = (!is_array($eachLine[$whereVar]) AND is_array(json_decode($eachLine[$whereVar], true)) AND !$rawResult) ? json_decode($eachLine[$whereVar], true) : $eachLine[$whereVar];
									
									$status[$eachLineKey][$whereLineID] = $whereVar;
									foreach($whereValue as $eachWhereKey => $eachWhereValue) {
										$toCompare = (!$caseSensitive) ? strtolower($eachLine[$whereVar][$eachWhereKey]) : $eachLine[$whereVar][$eachWhereKey];
										$compareWith = (!$caseSensitive) ? strtolower($eachWhereValue) : $eachWhereValue;
										
										if($toCompare != $compareWith) {
											$status[$eachLineKey][$whereLineID] = false;
											break;
										}
									}
								} else {
									$toCompare = (!$caseSensitive) ? strtolower($eachLine[$whereVar]) : $eachLine[$whereVar];
									$compareWith = (!$caseSensitive) ? strtolower($whereValue) : $whereValue;
									
									if($toCompare == $compareWith) $status[$eachLineKey][$whereLineID] = $whereVar;
									else $status[$eachLineKey][$whereLineID] = false;
								}
							}
						}
					}
					
					if((!in_array(false, $status[$eachLineKey]) AND !empty($status[$eachLineKey])) OR empty($where) OR !is_array($where)) {
						$countRows++;
						if($countRows < $startFrom) continue;
						else if(isset($rowLimit) AND $countRows >= $startFrom + $rowLimit) break;
						
						$lineResult = null;
						foreach($whatVar as $eachVar) {
							if(isset($eachLine[$eachVar])) {
								$eachLine[$eachVar] = (!is_array($eachLine[$eachVar]) AND is_array(json_decode($eachLine[$eachVar], true)) AND !$rawResult) ? json_decode($eachLine[$eachVar], true) : $eachLine[$eachVar];
								$lineResult[$eachVar] = $eachLine[$eachVar];
							}
						}
						$valueArray[] = (!isset($lineResult) OR $whatVarArray OR count($lineResult) > 1) ? $lineResult : array_values($lineResult)[0];
					}
				}
			}
			else return false;
			
			if($forceArray OR count($valueArray) > 1) return $valueArray;
			else if(count($valueArray) == 1) return $valueArray[0];
			else return false;
		}
		
		/**
		 * Get summed infos from table
		 * 
		 * @param string $table Table to get data from
		 * @param string|array $whatVar What var(s) to return
		 * @param array|void $where Where to get data from
		 * @param array|void $settings Data returning settings
		 * @param boolean|void $caseSensitive Where is case sensitive or not
		 * @param boolean|void $rawResult Return raw result or not
		 * 
		 * @uses OliCore::getInfosMySQL() to get infos from table
		 * @return mixed Returns summed infos from specified table
		 */
		public function getSummedInfosMySQL($table, $whatVar, $where = null, $settings = null, $caseSensitive = null, $rawResult = null) {
			if(is_bool($settings)) {
				$rawResult = isset($rawResult) ? $rawResult : $caseSensitive;
				$caseSensitive = isset($rawResult) ? $caseSensitive : $settings;
				$settings = null;
			}
			if(!isset($caseSensitive)) $caseSensitive = true;
			if(!isset($rawResult)) $rawResult = false;
			
			$summedInfos = null;
			foreach($this->getInfosMySQL($table, $whatVar, $where, $settings, $caseSensitive, true) as $eachInfo) {
				$eachInfo = (!is_array($eachInfo) AND is_array(json_decode($eachInfo, true))) ? json_decode($eachInfo, true) : $eachInfo;
				$summedInfos += $eachInfo;
			}
			return (is_array($summedInfos) AND $rawResult) ? json_encode($summedInfos, JSON_FORCE_OBJECT) : $summedInfos;
		}
		
		/**
		 * Is empty infos in table
		 * 
		 * @param string $table Table to get data from
		 * @param string|array $whatVar What var(s) to return
		 * @param array|void $where Where to get data from
		 * @param array|void $settings Data returning settings
		 * @param boolean|void $caseSensitive Where is case sensitive or not
		 * 
		 * @uses OliCore::getInfosMySQL() to get infos from table
		 * @return boolean Returns true if infos are empty, false otherwise
		 */
		public function isEmptyInfosMySQL($table, $whatVar, $where = null, $settings = null, $caseSensitive = null) {
			return empty($this->getInfosMySQL($table, $whatVar, $where, $settings, $caseSensitive));
		}
		
		/**
		 * Is exist infos in table
		 * 
		 * @param string $table Table to get data from
		 * @param array|void $where Where to get data from
		 * @param boolean|void $caseSensitive Where is case sensitive or not
		 * 
		 * @uses OliCore::getInfosMySQL() to get infos from table
		 * @return boolean Returns true if infos exists, false otherwise
		 */
		public function isExistInfosMySQL($table, $where = null, $caseSensitive = true) {
			$dataMySQL = $this->getDataMySQL($table);
			$valueArray = [];
			$status = [];
			if(!empty($dataMySQL)) {
				foreach($dataMySQL as $eachLineKey => $eachLine) {
					$status[$eachLineKey] = [];
					if(!empty($where)) {
						$whereLineID = 0;
						foreach($where as $whereVar => $whereValue) {
							$whereLineID++;
							if($whereVar == '*') {
								foreach($eachLine as $eachKey => $eachValue) {
									if(is_array($whereValue)) {
										$eachValue = (!is_array($eachValue) AND is_array(json_decode($eachValue, true)) AND !$rawResult) ? json_decode($eachValue, true) : $eachValue;
										
										$status[$eachLineKey][$whereLineID] = $eachKey;
										foreach($whereValue as $eachWhereKey => $eachWhereValue) {
											$toCompare = (!$caseSensitive) ? strtolower($eachValue[$eachWhereKey]) : $eachValue[$eachWhereKey];
											$compareWith = (!$caseSensitive) ? strtolower($eachWhereValue) : $eachWhereValue;
											
											if($toCompare != $compareWith) {
												$status[$eachLineKey][$whereLineID] = false;
												break;
											}
										}
										
										if($status[$eachLineKey][$whereLineID] == $eachKey) break;
									}
									else {
										$toCompare = (!$caseSensitive) ? strtolower($eachValue) : $eachValue;
										$compareWith = (!$caseSensitive) ? strtolower($whereValue) : $whereValue;
										
										if($toCompare == $compareWith) {
											$status[$eachLineKey][$whereLineID] = $eachKey;
											break;
										}
										else $status[$eachLineKey][$whereLineID] = false;
									}
								}
							}
							else {
								if(is_array($whereValue)) {
									$eachLine[$whereVar] = (!is_array($eachLine[$whereVar]) AND is_array(json_decode($eachLine[$whereVar], true)) AND !$rawResult) ? json_decode($eachLine[$whereVar], true) : $eachLine[$whereVar];
									
									$status[$eachLineKey][$whereLineID] = $whereVar;
									foreach($whereValue as $eachWhereKey => $eachWhereValue) {
										$toCompare = (!$caseSensitive) ? strtolower($eachLine[$whereVar][$eachWhereKey]) : $eachLine[$whereVar][$eachWhereKey];
										$compareWith = (!$caseSensitive) ? strtolower($eachWhereValue) : $eachWhereValue;
										
										if($toCompare != $compareWith) {
											$status[$eachLineKey][$whereLineID] = false;
											break;
										}
									}
								} else {
									$toCompare = (!$caseSensitive) ? strtolower($eachLine[$whereVar]) : $eachLine[$whereVar];
									$compareWith = (!$caseSensitive) ? strtolower($whereValue) : $whereValue;
									
									if($toCompare == $compareWith) $status[$eachLineKey][$whereLineID] = $whereVar;
									else $status[$eachLineKey][$whereLineID] = false;
								}
							}
						}
					}
					
					if((!in_array(false, $status[$eachLineKey]) AND !empty($status[$eachLineKey])) OR empty($where))
						$valueArray[] = true;
				}
			}
			else
				return false;
			
			if(count($valueArray) >= 1)
				return count($valueArray);
			else
				return false;
		}
	
		/** ------------------------ */
		/**  IV. 3. Write Functions  */
		/** ------------------------ */
		
		/**
		 * Insert line in table
		 * 
		 * @param string $table Table to insert line into
		 * @param array $matches Data to insert into the table
		 * 
		 * @uses OliCore::isSetupMySQL() to check the MySQL connection
		 * @uses OliCore::$db to execute SQL requests
		 * @return boolean Return true if the request succeeded, false otherwise
		 */
		public function insertLineMySQL($table, $matches) {
			if(!$this->db) trigger_error('Sorry, the MySQL PDO Object hasn\'t been defined!', E_USER_ERROR);
			foreach($matches as $matchKey => $matchValue) {
				$queryVars[] = $matchKey;
				$queryValues[] = ':' . $matchKey;
				
				$matchValue = (is_array($matchValue)) ? json_encode($matchValue, JSON_FORCE_OBJECT) : $matchValue;
				$matches[$matchKey] = $matchValue;
			}
			$query = $this->db->prepare('INSERT INTO ' . $table . '(' . implode(', ', $queryVars) . ') VALUES(' . implode(', ', $queryValues) . ')');
			return $query->execute($matches);
		}
		
		/**
		 * Update infos from table
		 * 
		 * @param string $table Table to update infos from
		 * @param array $what What to replace data with
		 * @param string|array $where Where to update data
		 * 
		 * @uses OliCore::isSetupMySQL() to check the MySQL connection
		 * @uses OliCore::$db to execute SQL requests
		 * @return boolean Return true if the request succeeded, false otherwise
		 */
		public function updateInfosMySQL($table, $what, $where) {
			if(!$this->db) trigger_error('Sorry, the MySQL PDO Object hasn\'t been defined!', E_USER_ERROR);
			$matches = [];
			foreach($what as $whatVar => $whatValue) {
				$queryWhat[] = $whatVar . ' = :what_' . $whatVar;
				
				$whatValue = (is_array($whatValue)) ? json_encode($whatValue, JSON_FORCE_OBJECT) : $whatValue;
				$matches['what_' . $whatVar] = $whatValue;
			}
			if($where != 'all') {
				foreach($where as $whereVar => $whereValue) {
					$queryWhere[] = $whereVar . ' = :where_' . $whereVar;
					
					$whereValue = (is_array($whereValue)) ? json_encode($whereValue, JSON_FORCE_OBJECT) : $whereValue;
					$matches['where_' . $whereVar] = $whereValue;
				}
			}
			$query = $this->db->prepare('UPDATE ' . $table . ' SET '  . implode(', ', $queryWhat) . ($where != 'all' ? ' WHERE ' . implode(' AND ', $queryWhere) : ''));
			return $query->execute($matches);
		}
		
		/**
		 * Delete lines from table
		 * 
		 * @param string $table Table to delete data from
		 * @param string|array $where Where to delete data
		 * 
		 * @uses OliCore::isSetupMySQL() to check the MySQL connection
		 * @uses OliCore::$db to execute SQL requests
		 * @return boolean Return true if the request succeeded, false otherwise
		 */
		public function deleteLinesMySQL($table, $where) {
			if(!$this->db) trigger_error('Sorry, the MySQL PDO Object hasn\'t been defined!', E_USER_ERROR);
			if($where != 'all') {
				$matches = [];
				foreach($where as $whereVar => $whereValue) {
					$queryWhere[] = $whereVar . ' = :' . $whereVar;
					
					$whereValue = (is_array($whereValue)) ? json_encode($whereValue, JSON_FORCE_OBJECT) : $whereValue;
					$matches[$whereVar] = $whereValue;
				}
			}
			$query = $this->db->prepare('DELETE FROM ' . $table . (($where != 'all') ? ' WHERE ' . implode(' AND ', $queryWhere) : ''));
			return $query->execute($matches);
		}
	
		/** --------------------------- */
		/**  IV. 4. Database Functions  */
		/** --------------------------- */
		
		/**
		 * Create new table
		 * 
		 * @param string $table Table to insert data into
		 * @param array $columns Columns to insert into the table
		 * 
		 * @uses OliCore::isSetupMySQL() to check the MySQL connection
		 * @uses OliCore::$db to execute SQL requests
		 * @return boolean Return true if the request succeeded, false otherwise
		 */
		public function createTableMySQL($table, $columns) {
			if(!$this->db) trigger_error('Sorry, the MySQL PDO Object hasn\'t been defined!', E_USER_ERROR);
			foreach($columns as $matchName => $matchOption) {
				$queryData[] = $matchName . ' ' . $matchOption;
			}
			$query = $this->db->prepare('CREATE TABLE ' . $table . '(' . implode(', ', $queryData) . ')');
			return $query->execute();
		}
		
		/**
		 * Clear table data
		 * 
		 * Delete everything in the table but not the table itself
		 * 
		 * @param string $table Table to delete data from
		 * 
		 * @uses OliCore::isSetupMySQL() to check the MySQL connection
		 * @uses OliCore::$db to execute SQL requests
		 * @return boolean Return true if the request succeeded, false otherwise
		 */
		public function clearTableMySQL($table) {
			if(!$this->db) trigger_error('Sorry, the MySQL PDO Object hasn\'t been defined!', E_USER_ERROR);
			$query = $this->db->prepare('TRUNCATE TABLE ' . $table);
			return $query->execute();
		}
		
		/**
		 * Delete table
		 * 
		 * @param string $table Table to delete
		 * 
		 * @uses OliCore::isSetupMySQL() to check the MySQL connection
		 * @uses OliCore::$db to execute SQL requests
		 * @return boolean Return true if the request succeeded, false otherwise
		 */
		public function deleteTableMySQL($table) {
			if(!$this->db) trigger_error('Sorry, the MySQL PDO Object hasn\'t been defined!', E_USER_ERROR);
			$query = $this->db->prepare('DROP TABLE ' . $table);
			return $query->execute();
		}
		
		/**
		 * Add column to table
		 * 
		 * @param string $table Table to insert column into
		 * @param string $column Column to insert into the table
		 * @param string $type Type to set for the column
		 * 
		 * @uses OliCore::isSetupMySQL() to check the MySQL connection
		 * @uses OliCore::$db to execute SQL requests
		 * @return boolean Return true if the request succeeded, false otherwise
		 */
		public function addColumnTableMySQL($table, $column, $type) {
			if(!$this->db) trigger_error('Sorry, the MySQL PDO Object hasn\'t been defined!', E_USER_ERROR);
			$query = $this->db->prepare('ALTER TABLE ' . $table . ' ADD ' . $column . ' ' . $type);
			return $query->execute();
		}
		
		/**
		 * Update column from table
		 * 
		 * @param string $table Table to update column from
		 * @param string $column Column to update from the table
		 * @param string $type Type to set for the column
		 * 
		 * @uses OliCore::isSetupMySQL() to check the MySQL connection
		 * @uses OliCore::$db to execute SQL requests
		 * @todo Add PostgreSQL support
		 * @return boolean Return true if the request succeeded, false otherwise
		 */
		public function updateColumnTableMySQL($table, $column, $type) {
			if(!$this->db) trigger_error('Sorry, the MySQL PDO Object hasn\'t been defined!', E_USER_ERROR);
			$query = $this->db->prepare('ALTER TABLE ' . $table . ' MODIFY ' . $column . ' ' . $type);
			return $query->execute();
		}
		
		/**
		 * Rename column from table
		 * 
		 * @param string $table Table to rename column from
		 * @param array $oldColumn Row to rename from the table
		 * @param string $newColumn New column name
		 * @param string|void $type Type to set for the column
		 * 
		 * @uses OliCore::isSetupMySQL() to check the MySQL connection
		 * @uses OliCore::$db to execute SQL requests
		 * @return boolean Return true if the request succeeded, false otherwise
		 */
		public function renameColumnTableMySQL($table, $oldColumn, $newColumn, $type = null) {
			if(!$this->db) trigger_error('Sorry, the MySQL PDO Object hasn\'t been defined!', E_USER_ERROR);
			$query = $this->db->prepare('ALTER TABLE ' . $table . (isset($type) ? ' CHANGE ' : ' RENAME COLUMN ') . $oldColumn . (isset($type) ? ' ' : ' TO ') . $newColumn . (isset($type) ? ' ' . $type : ''));
			return $query->execute();
		}
		
		/**
		 * Delete column from table
		 * 
		 * @param string $table Table to delete column from
		 * @param array $column Column to delete from the table
		 * 
		 * @uses OliCore::isSetupMySQL() to check the MySQL connection
		 * @uses OliCore::$db to execute SQL requests
		 * @todo Add PostgreSQL support
		 * @return boolean Return true if the request succeeded, false otherwise
		 */
		public function deleteColumnTableMySQL($table, $column) {
			if(!$this->db) trigger_error('Sorry, the MySQL PDO Object hasn\'t been defined!', E_USER_ERROR);
			$query = $this->db->prepare('ALTER TABLE ' . $table . ' DROP ' . $column . ')');
			return $query->execute();
		}
	
	/** *** *** *** */
	
	/** ---------------------- */
	/**  V. General Functions  */
	/** ---------------------- */
	
		/** ------------------------ */
		/**  V. 1. Oli Informations  */
		/** ------------------------ */
		
		/** Magic __toString function */
		public function __toString() {
			return 'Powered by <a href="' . $this->oliInfos['website'] . '">' . $this->oliInfos['name'] . '</a> (v. ' . $this->getOliInfos('version') . ')';
		}
		
		public function getOliInfos($whatInfo = null) {
			if(!empty($whatInfo)) return $this->oliInfos[$whatInfo];
			else return $this->oliInfos;
		}
		
		public function getTeamInfos($who = null, $whatInfo = null) {
			if(!empty($who)) {
				foreach($this->oliInfos['team'] as $eachMember) {
					if($eachMember['name'] == $who OR in_array($who, !is_array($eachMember['nicknames']) ? [$eachMember['nicknames']] : $eachMember['nicknames'])) {
						if(!empty($whatInfo)) return $eachMember[$whatInfo];
						else return $eachMember;
					}
				}
			}
			else return $this->oliInfos['team'];
		}
		
		/** ----------------------- */
		/**  V. 2. Website Content  */
		/** ----------------------- */
		
		/** Load page content */
		public function loadContent() {
			if($this->config['user_management'] AND !empty($this->getUserLanguage())) $this->setCurrentLanguage($this->getUserLanguage());
			
			$params = $this->getUrlParam('params');
			$contentStatus = null;
			$found = null;
			
			$contentRulesFile = file_exists(THEMEPATH . '.olicontent') ? file_get_contents(THEMEPATH . '.olicontent') : [];
			$contentRules = array_merge(array('access' => array('*' => array('ALLOW' => '*'))), $this->decodeContentRules($contentRulesFile) ?: []);
			
			if(!empty($params)) {
				foreach($params as $eachParam) {
					$fileName[] = $eachParam;
					$pathTo = implode('/', array_slice($fileName, 0, -1));
					$accessAllowed = null;
					
					if(!empty($contentRules) AND !empty($pathTo)) $contentRules = array_merge($contentRules, $this->decodeContentRules($contentRulesFile, $pathTo));
					
					if(file_exists(THEMEPATH . implode('/', $fileName) . '.php') AND $accessAllowed = $this->fileAccessAllowed($contentRules['access'], implode('/', $fileName) . '.php')) {
						$found = THEMEPATH . implode('/', $fileName) . '.php';
						$this->fileNameParam = implode('/', $fileName);
					}
					else if($fileName[0] == 'data') break;
					else {
						if(!empty($this->config['index_files'])) $indexFiles = !is_array($this->config['index_files']) ? [$this->config['index_files']] : $this->config['index_files'];
						
						if(!empty($indexFiles)) {
							foreach(array_slice($indexFiles, 1) as $eachValue) {
								$eachValue = explode('/', $eachValue);
								$indexFilePath = implode('/', array_slice($eachValue, 0, -1));
								$indexFileName = implode('/', array_slice($eachValue, -1));
								
								if(implode('/', $fileName) == $indexFilePath AND file_exists(THEMEPATH . $indexFilePath . '/' . $indexFileName)  AND $accessAllowed = $this->fileAccessAllowed($contentRules['access'], $indexFilePath . '/' . $indexFileName)) {
									$found = THEMEPATH . $indexFilePath . '/' . $indexFileName;
									$this->fileNameParam = $indexFilePath;
								}
								/** Sub-directory  Content Rules Indexes */
								else if(file_exists(THEMEPATH . implode('/', $fileName) . '/' . $indexFiles[0]) AND $accessAllowed = $this->fileAccessAllowed($contentRules['access'], implode('/', $fileName) . '/' . $indexFiles[0])) {
									$found = THEMEPATH . implode('/', $fileName) . '/' . $indexFiles[0];
									$this->fileNameParam = implode('/', $fileName);
								}
								else if(file_exists(THEMEPATH . implode('/', $fileName) . '/index.php') AND $accessAllowed = $this->fileAccessAllowed($contentRules['access'], implode('/', $fileName) . '/index.php')) {
									$found = THEMEPATH . implode('/', $fileName) . '/index.php';
									$this->fileNameParam = implode('/', $fileName);
								}
							}
						}
						
						if(empty($found) AND $fileName[0] == 'home' AND file_exists(THEMEPATH .  ($contentRules['index'] ?: $indexFiles[0] ?: 'index.php')) AND $this->fileAccessAllowed($contentRules['access'], $contentRules['index'] ?: $indexFiles[0] ?: 'index.php')) {
							$found = THEMEPATH . ($contentRules['index'] ?: $indexFiles[0] ?: 'index.php');
							$contentStatus = 'index';
						}
					}
				}
			}
			
			if(!empty($found)) {
				http_response_code(302); // 302 Found
				$this->contentStatus = $contentStatus ?: 'found';
				return $found;
			} else if(isset($accessAllowed) AND !$accessAllowed) {
				http_response_code(403); // 403 Forbidden
				$this->contentStatus = '403';
				
				if(file_exists(THEMEPATH . ($contentRules['error']['403'] ?: $this->config['error_files']['403'] ?: '403.php'))) return THEMEPATH . ($contentRules['error']['403'] ?: $this->config['error_files']['403'] ?: '403.php');
				else die('Error 403: Access forbidden');
			} else {
				http_response_code(404); // 404 Not Found
				$this->contentStatus = '404';
				
				if(file_exists(THEMEPATH .  ($contentRules['error']['404'] ?: $this->config['error_files']['404'] ?: '404.php')) AND $this->fileAccessAllowed($contentRules['access'], $contentRules['error']['404'] ?: $this->config['error_files']['404'] ?: '404.php')) return THEMEPATH . ($contentRules['error']['404'] ?: $this->config['error_files']['404'] ?: '404.php');
				else die('Error 404: File not found');
			}
		}
		
		/** Get content status */
		public function getContentStatus() { return $this->contentStatus; }
		
		/** Decode content rules */
		public function decodeContentRules($rules, $pathTo = null) {
			if(!empty($rules)) {
				$results = [];
				$rules = explode("\n", $rules);
				foreach((!is_array($rules) ? [$rules] : $rules) as $eachRule) {
					if(!empty($eachRule)) {
						list($ruleType, $ruleValue) = explode(': ', $eachRule);
						$ruleType = strtolower($ruleType);
						
						if($ruleType == 'index' AND preg_match('/^["\'](.*)["\']$/', $ruleValue, $matches)) $results['index'] = $matches[1];
						else if($ruleType == 'error' AND preg_match('/^(\d{3})\s["\'](.*)["\']$/', $ruleValue, $matches)) $results['error'][$matches[1]] = $matches[2];
						else if($ruleType == 'access' AND preg_match('/^(?:((?:\[.+\])|(?:\*))\s)?([a-zA-Z]{4,5})\s(.*)$/', $ruleValue, $matches)) {
							$files = $matches[1] == '*' ? '*' : json_decode($matches[1], true);
							foreach((!is_array($files) ? [$files] : $files) as $eachFile) {
								if(is_string($eachFile)) {
									if(preg_match('/^(?:\*|all|(?:from\s([a-zA-Z]+))?\s?(?:to\s([a-zA-Z]+))?)$/', $matches[3], $rights)) {
										if($rights[0] == 'all' OR $rights[0] == '*') $results['access'][$pathTo . $eachFile][$matches[2]] = '*';
										else {
											$results['access'][$pathTo . $eachFile][$matches[2]]['from'] = $this->translateUserRight($rights[1]);
											$results['access'][$pathTo . $eachFile][$matches[2]]['to'] = $this->translateUserRight($rights[2]);
										}
									}
								}
							}
						} else $results[$ruleType] = $ruleValue;
					}
				}
				return $results;
			} else return false;
		}
		
		/** File Access Allowed */
		public function fileAccessAllowed($accessRules, $fileName) {
			$result = null;
			$defaultResult = false;
			
			if(empty($accessRules)) return $defaultResult;
			else {
				if(!empty($fileName) AND !empty($accessRules[$fileName])) {
					if($accessRules[$fileName]['DENY'] == '*') $result = false;
					else if($accessRules[$fileName]['ALLOW'] == '*') $result = true;
					else if($this->config['user_management'] AND $userRight = $this->getUserRightLevel()) {
						if(!empty($accessRules[$fileName]['DENY']) AND ((empty($accessRules[$fileName]['DENY']['from']) OR (!empty($accessRules[$fileName]['DENY']['from']) AND $accessRules[$fileName]['DENY']['from'] <= $userRight)) XOR (!empty($accessRules[$fileName]['DENY']['to']) OR (!empty($accessRules[$fileName]['DENY']['to']) AND $accessRules[$fileName]['DENY']['to'] >= $userRight)))) $result = false;
						else if(!empty($accessRules[$fileName]['ALLOW']) AND ((empty($accessRules[$fileName]['ALLOW']['from']) OR (!empty($accessRules[$fileName]['ALLOW']['from']) AND $accessRules[$fileName]['ALLOW']['from'] <= $userRight)) XOR (!empty($accessRules[$fileName]['ALLOW']['to']) OR (!empty($accessRules[$fileName]['ALLOW']['to']) AND $accessRules[$fileName]['ALLOW']['to'] >= $userRight)))) $result = true;
					}
				}
				
				if(!isset($result) AND !empty($accessRules['*'])) {
					if($accessRules['*']['DENY'] == '*') $result = false;
					else if($accessRules['*']['ALLOW'] == '*') $result = true;
					else if($this->config['user_management'] AND $userRight = $this->getUserRightLevel()) {
						if(!empty($accessRules['*']['DENY']) AND ((empty($accessRules['*']['DENY']['from']) OR (!empty($accessRules['*']['DENY']['from']) AND $accessRules['*']['DENY']['from'] <= $userRight)) XOR (!empty($accessRules['*']['DENY']['to']) OR (!empty($accessRules['*']['DENY']['to']) AND $accessRules['*']['DENY']['to'] >= $userRight)))) $result = false;
						else if(!empty($accessRules['*']['ALLOW']) AND ((empty($accessRules['*']['ALLOW']['from']) OR (!empty($accessRules['*']['ALLOW']['from']) AND $accessRules['*']['ALLOW']['from'] <= $userRight)) XOR (!empty($accessRules['*']['ALLOW']['to']) OR (!empty($accessRules['*']['ALLOW']['to']) AND $accessRules['*']['ALLOW']['to'] >= $userRight)))) $result = true;
						else $result = $defaultResult;
					} else $result = $defaultResult;
				}
				return $result;
			}
		}
		
		/** ------------------------ */
		/**  V. 3. Website Settings  */
		/** ------------------------ */
		
		/**
		 * Get settings tables
		 * @return string|array Settings tables
		 */
		public function getSettingsTables() { return $this->config['settings_tables']; }
		
		/**
		 * Get setting
		 * 
		 * @param string $setting Setting to get
		 * 
		 * @uses OliCore::$settingsTables to get the settings tables
		 * @uses OliCore::getInfosMySQL() to get settings infos
		 * @return mixed|void Setting value
		 */
		public function getSetting($setting /*= null*/) {
			if(!empty($this->config['settings_tables'])) {
				foreach($this->config['settings_tables'] as $eachTable) {
					// if(isset($setting)) {
						$optionResult = $this->getInfosMySQL($eachTable, 'value', array('name' => $setting));
						if(!empty($optionResult)) {
							if($optionResult == 'null') return '';
							else return $optionResult;
						}
					// }
					// else false; //$this->getInfosMySQL($eachTable, ['name', 'value']);
				}
			}
			else return false;
		}
		
		/**
		 * Get option
		 * 
		 * @param string $setting Setting to get
		 * 
		 * @uses OliCore::getSetting() to get the settings
		 * @see OliCore::getSetting() Alternative to this function
		 * @deprecated Old function, alternative to another function
		 * @return mixed Setting value
		 */
		public function getOption($setting /*= null*/) {
			return $this->getSetting($setting);
		}
		
		/**
		 * Get shortcut link
		 * 
		 * @param string $setting Setting to get
		 * 
		 * @uses OliCore::$shortcutLinksTable to get the shortcut links table name
		 * @uses OliCore::getInfosMySQL() to get shortcut links infos
		 * @return mixed Shortcut link
		 */
		public function getShortcutLink($shortcut) {
			if(isset($this->config['shortcut_links_table'])) return $this->getInfosMySQL($this->config['shortcut_links_table'], 'url', array('name' => $shortcut));
			else return false;
		}
		
		/** --------------------- */
		/**  V. 4. Website Tools  */
		/** --------------------- */
		
		/**
		 * Get execution delay
		 * 
		 * @param boolean|void $fromRequest Get delay from request time or not
		 * 
		 * @uses OliCore::$config to get Oli setup timestamp
		 * @return integer Execution delay
		 */
		public function getExecutionDelay($fromRequest = false) {
			if($fromRequest) return microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
			else return microtime(true) - $this->config['init_timestamp'];
		}
		
		/**
		 * Get execute delay
		 * 
		 * @param boolean|void $fromRequest Get delay from request time or not
		 * 
		 * @uses OliCore::getExecutionDelay() to get the execution delay
		 * @see OliCore::getExecutionDelay() Alternative to this function
		 * @deprecated Old function, alternative to another function
		 * @return integer Execution delay
		 */
		public function getExecuteDelay($fromRequest = false) {
			return $this->getExecutionDelay($fromRequest);
		}
		
		/** --------------------------- */
		/**  V. 5. Translations & Text  */
		/** --------------------------- */
		
			/** ------------------------- */
			/**  V. 5. A. Read Functions  */
			/** ------------------------- */
			
			/**
			 * Get translations lines
			 * 
			 * @param array $where Where to get translations from
			 * @param array|void $settings Data returning settings
			 * @param boolean|void $caseSensitive Where is case sensitive or not
			 * @param boolean|void $forceArray Always return an array or not
			 * @param boolean|void $rawResult Return raw result or not
			 * 
			 * @uses OliCore::getLinesMySQL() to get translations lines
			 * @uses OliCore::$translationsTable to get Oli setup timestamp
			 * @return array|boolean Translations lines
			 */
			public function getTranslationLines($where, $settings = null, $caseSensitive = null, $forceArray = null, $rawResult = null) {
				return $this->getLinesMySQL($this->config['translations_table'], $where, $settings, $caseSensitive, $forceArray, $rawResult);
			}
			
			/**
			 * Get translation
			 * 
			 * @param string|array $whatLanguage What language to return
			 * @param array $where Where to get translations from
			 * @param array|void $settings Data returning settings
			 * @param boolean|void $caseSensitive Where is case sensitive or not
			 * @param boolean|void $forceArray Always return an array or not
			 * @param boolean|void $rawResult Return raw result or not
			 * 
			 * @uses OliCore::getInfosMySQL() to get translations infos
			 * @uses OliCore::$translationsTable to get Oli setup timestamp
			 * @return array|boolean Translation
			 */
			public function getTranslation($whatLanguage, $where, $settings = null, $caseSensitive = null, $forceArray = null, $rawResult = null) {
				return $this->getInfosMySQL($this->config['translations_table'], $whatLanguage, $where, $settings, $caseSensitive, $forceArray, $rawResult);
			}
			
			/**
			 * Is exist translation
			 * 
			 * @param array $where Where to get translations from
			 * @param boolean|void $caseSensitive Where is case sensitive or not
			 * 
			 * @uses OliCore::isExistInfosMySQL() to get if infos exists or not
			 * @uses OliCore::$translationsTable to get Oli setup timestamp
			 * @return array|boolean Translation
			 */
			public function isExistTranslation($where, $caseSensitive = true) {
				return $this->isExistInfosMySQL($this->config['translations_table'], $where, $caseSensitive);
			}
			
			/** -------------------------- */
			/**  V. 5. B. Write Functions  */
			/** -------------------------- */
			
			/**
			 * Add translations
			 * 
			 * @param array $translations Translations to add
			 * 
			 * @uses OliCore::insertLineMySQL() to add translations
			 * @uses OliCore::$translationsTable to get the translations table name
			 * @return boolean Return true if the request succeeded, false otherwise
			 */
			public function addTranslations($translations) {
				return $this->insertLineMySQL($this->config['translations_table'], $translations);
			}
			
			/**
			 * Update translations
			 * 
			 * @param array $what What to replace translations with
			 * @param array $where Where to update translations
			 * 
			 * @uses OliCore::updateInfosMySQL() to update translations
			 * @uses OliCore::$translationsTable to get the translations table name
			 * @return boolean Return true if the request succeeded, false otherwise
			 */
			public function updateTranslations($what, $where) {
				return $this->updateInfosMySQL($this->config['translations_table'], $what, $where);
			}
			
			/**
			 * Delete translations
			 * 
			 * @param array $where Where to delete translations
			 * 
			 * @uses OliCore::deleteLinesMySQL() to delete translations
			 * @uses OliCore::$translationsTable to get the translations table name
			 * @return boolean Return true if the request succeeded, false otherwise
			 */
			public function deleteTranslations($where) {
				return $this->deleteLinesMySQL($this->config['translations_table'], $where);
			}
			
			/** -------------------------- */
			/**  V. 5. C. Print Functions  */
			/** -------------------------- */
			
			/**
			 * Echo translated text
			 * 
			 * @param string $text Text to echo or translate
			 * @param string|void $text_plural Text in plural form
			 * @param integer|void $count Will define which form to use
			 * 
			 * @uses OliCore::$currentLanguage to get the current language
			 * @uses OliCore::$defaultLanguage to get the default language
			 * @uses OliCore::getTranslation() to get translation
			 * @uses OliCore::isExistTranslation() to check if a translation exists or not
			 * @uses OliCore::addTranslations() to add a new translation
			 * @return void
			 */
			public function __($text, $text_plural = '', $count = 0) {
				$text = ($count > 1) ? $text_plural : $text;
				if($this->currentLanguage != $this->config['default_user_language'] AND $translatedText = $this->getTranslation($this->currentLanguage, array($this->config['default_user_language'] => $text)))
					echo $translatedText;
				else {
					if(!$this->isExistTranslation(array($this->config['default_user_language'] => $text))) $this->addTranslations(array($this->config['default_user_language'] => $text));
					echo $text;
				}
			}
		
		/** ------------------ */
		/**  V. 6. HTTP Tools  */
		/** ------------------ */
		
			/** ----------------------- */
			/**  V. 6. A. Content Type  */
			/** ----------------------- */
			
			/**
			 * Set content type
			 * 
			 * @param string|void $contentType Content type to set
			 * @param string|void $charset Charset to use
			 * @param boolean|void $force Force the new content type
			 * 
			 * @uses OliCore::$contentTypeBeenForced to lock the new content type, if forced
			 * @uses OliCore::$defaultContentType to get the default content type
			 * @uses OliCore::$contentType to get the current content type
			 * @uses OliCore::$defaultCharset to get the default charset
			 * @uses OliCore::$currentCharset to get the current charset
			 * @return boolean|void
			 */
			public function setContentType($contentType = null, $charset = null, $force = false) {
				if(!$this->contentTypeBeenForced OR $force) {
					if($force) $this->contentTypeBeenForced = true;
					
					if(!isset($contentType) OR $contentType == 'DEFAULT') $contentType = $this->config['default_content_type'];
					if(!isset($charset) OR $charset == 'DEFAULT') $charset = $this->config['default_charset'];
					error_reporting($contentType == 'DEBUG_MODE' ? E_ALL : E_ALL & ~E_NOTICE);
					
					if($contentType == 'HTML') $newContentType = 'text/html';
					else if($contentType == 'CSS') $newContentType = 'text/css';
					else if($contentType == 'JAVASCRIPT') $newContentType = 'text/javascript';
					else if($contentType == 'JSON') $newContentType = 'application/json';
					else if($contentType == 'PDF') $newContentType = 'application/pdf';
					else if($contentType == 'RSS') $newContentType = 'application/rss+xml';
					else if($contentType == 'XML') $newContentType = 'text/xml';
					else if($contentType == 'DEBUG_MODE' OR $contentType == 'PLAIN') $newContentType = 'text/plain';
					else $newContentType = $contentType;
					
					header('Content-Type: ' . $newContentType . ';charset=' . $charset);
					$this->currentContentType = $newContentType;
					$this->currentCharset = $charset;
					
					return $newContentType;
				}
				else return false;
			}
			
			/**
			 * Get current content type
			 * 
			 * @uses OliCore::$contentType to get the current content type
			 * @return string
			 */
			public function getContentType() {
				return $this->currentContentType;
			}
			
			/**
			 * Get current charset
			 * 
			 * @uses OliCore::$contentType to get the current content type
			 * @return string
			 */
			public function getCharset() {
				return $this->currentCharset;
			}
			
			/** ---------------------------- */
			/**  V. 6. B. Cookie Management  */
			/** ---------------------------- */
			
				/** ---------------------------- */
				/**  V. 6. B. a. Read Functions  */
				/** ---------------------------- */
				
				/**
				 * Get cookie content
				 * 
				 * @param string $name Cookie name
				 * @param boolean|void $rawResult Return raw result or not
				 * 
				 * @return mixed Returns cookie content
				 */
				public function getCookieContent($name, $rawResult = false) {
					return (!is_array($_COOKIE[$name]) AND is_array(json_decode($_COOKIE[$name], true)) AND !$rawResult) ? json_decode($_COOKIE[$name], true) : $_COOKIE[$name];
				}
				
				/**
				 * Is exist cookie
				 * 
				 * @param string $name Cookie name
				 * 
				 * @return boolean Returns true if the cookie exists, false otherwise
				 */
				public function isExistCookie($name) {
					return isset($_COOKIE[$name]);
				}
				
				/**
				 * Is empty cookie
				 * 
				 * @param string $name Cookie name
				 * 
				 * @return boolean Returns true if the cookie is empty, false otherwise
				 */
				public function isEmptyCookie($name) {
					return empty($_COOKIE[$name]);
				}
				
				/** ----------------------------- */
				/**  V. 6. B. b. Write Functions  */
				/** ----------------------------- */
				
				/**
				 * Set cookie
				 * 
				 * @param string $name Cookie name
				 * @param string $value Cookie value
				 * @param integer $expireDelay Cookie expire delay
				 * @param string $path Cookie path
				 * @param string|array $domains Cookie domains
				 * @param boolean|void $secure Cookie secure parameter
				 * @param boolean|void $httpOnly Cookie http only parameter
				 * 
				 * @return boolean Returns true if the cookies have been created, false otherwise
				 */
				public function setCookie($name, $value, $expireDelay, $path, $domains, $secure = false, $httpOnly = false) {
					$value = (is_array($value)) ? json_encode($value, JSON_FORCE_OBJECT) : $value;
					$domains = (!is_array($domains)) ? [$domains] : $domains;
					foreach($domains as $eachDomain) {
						if(!setcookie($name, $value, $expireDelay ? time() + $expireDelay : 0, '/', $eachDomain, $secure, $httpOnly)) {
							$cookieError = true;
							break;
						}
					}
					return !$cookieError ? true : false;
				}
				
				/**
				 * Delete cookie
				 * 
				 * @param string $name Cookie name
				 * @param string $path Cookie path
				 * @param string|array $domains Cookie domains
				 * @param boolean|void $secure Cookie secure parameter
				 * @param boolean|void $httpOnly Cookie http only parameter
				 * 
				 * @return boolean Returns true if the cookies have been deleted, false otherwise
				 */
				public function deleteCookie($name, $path, $domains, $secure = false, $httpOnly = false) {
					$domains = (!is_array($domains)) ? [$domains] : $domains;
					foreach($domains as $eachDomain) {
						setcookie($name, null, -1, '/', $eachDomain, $secure, $httpOnly);
						if(!setcookie($name, null, -1, '/', $eachDomain, $secure, $httpOnly)) {
							$cookieError = true;
							break;
						}
					}
					return !$cookieError ? true : false;
				}
			
			/** --------------------- */
			/**  V. 6. C. _POST vars  */
			/** --------------------- */
			
				/** ---------------------------- */
				/**  V. 6. C. a. Read Functions  */
				/** ---------------------------- */
				
				/**
				 * Get post vars cookie name
				 * 
				 * @uses OliCore::$postVarsCookieName to get the post vars cookie name
				 * @return string Returns the post vars cookie name
				 */
				public function getPostVarsCookieName() {
					return $this->config['post_vars_cookie']['name'];
				}
				
				/**
				 * Get post vars
				 * 
				 * @param string|void $whatVar Post vars to get
				 * @param boolean|void $rawResult Return raw result or not
				 * 
				 * @uses OliCore::getCookieContent() to get the post vars cookie content
				 * @uses OliCore::$postVarsCookieName to get the post vars cookie name
				 * @return string Returns the post vars cookie content
				 */
				public function getPostVars($whatVar = null, $rawResult = false) {
					$postVars = $this->getCookieContent($this->config['post_vars_cookie']['name'], $rawResult);
					return isset($whatVar) ? $postVars[$whatVar] : $postVars;
				}
				
				/**
				 * Is empty post vars
				 * 
				 * @param string|void $whatVar Post vars to check
				 * 
				 * @uses OliCore::getPostVars() to get the post vars
				 * @uses OliCore::isEmptyCookie() to check if the post vars cookie is empty or not
				 * @uses OliCore::$postVarsCookieName to get the post vars cookie name
				 * @return boolean Returns true if the post vars is empty, false otherwise
				 */
				public function isEmptyPostVars($whatVar = null) {
					return isset($whatVar) ? empty($this->getPostVars($whatVar)) : $this->isEmptyCookie($this->config['post_vars_cookie']['name']);
				}
				
				/**
				 * Is set post vars
				 * 
				 * @param string|void $whatVar Post vars to check
				 * 
				 * @uses OliCore::getPostVars() to get the post vars
				 * @uses OliCore::isExistCookie() to check if the post vars cookie exists or not
				 * @uses OliCore::$postVarsCookieName to get the post vars cookie name
				 * @return boolean Returns true if the post vars is set, false otherwise
				 */
				public function issetPostVars($whatVar = null) {
					return isset($whatVar) ? $this->getPostVars($whatVar) !== null : $this->isExistCookie($this->config['post_vars_cookie']['name']);
				}
				
				/**
				 * Is protected post vars
				 * 
				 * @uses OliCore::$postVarsProtection to get post vars protection status
				 * @return boolean Returns true if the post vars is protected, false otherwise
				 */
				public function isProtectedPostVarsCookie() {
					return $this->postVarsProtection;
				}
				
				/** ----------------------------- */
				/**  V. 6. C. b. Write Functions  */
				/** ----------------------------- */
				
				/**
				 * Set post vars cookie
				 * 
				 * @param string $postVars Post vars values
				 * 
				 * @uses OliCore::setCookie() to set the post vars cookie
				 * @uses OliCore::$postVarsCookieName to get the post vars cookie name
				 * @uses OliCore::$postVarsCookieExpireDelay to get the post vars cookie expire delay
				 * @uses OliCore::$postVarsCookieDomain to get the post vars cookie domain
				 * @uses OliCore::$postVarsCookieSecure to get the post vars cookie secure parameter
				 * @uses OliCore::$postVarsCookieHttpOnly to get the post vars cookie http only parameter
				 * @return boolean Returns true if the cookie have been created, false otherwise
				 */
				public function setPostVarsCookie($postVars) {
					$this->postVarsProtection = true;
					return $this->setCookie($this->config['post_vars_cookie']['name'], $postVars, 1, '/', $this->config['post_vars_cookie']['domain'], $this->config['post_vars_cookie']['secure'], $this->config['post_vars_cookie']['http_only']);
				} 
				
				/**
				 * Delete post vars cookie
				 * 
				 * @uses OliCore::$postVarsProtection to get post vars protection status
				 * @uses OliCore::deleteCookie() to delete the post vars cookie
				 * @uses OliCore::$postVarsCookieName to get the post vars cookie name
				 * @uses OliCore::$postVarsCookieExpireDelay to get the post vars cookie expire delay
				 * @uses OliCore::$postVarsCookieDomain to get the post vars cookie domain
				 * @uses OliCore::$postVarsCookieSecure to get the post vars cookie secure parameter
				 * @uses OliCore::$postVarsCookieHttpOnly to get the post vars cookie http only parameter
				 * @deprecated Post vars cookie shouldn't be deleted by the user
				 * @return boolean Returns true if the cookie have been deleted, false otherwise
				 */
				public function deletePostVarsCookie() {
					if(!$this->postVarsProtection) return $this->deleteCookie($this->config['post_vars_cookie']['name'], '/', $this->config['post_vars_cookie']['domain'], $this->config['post_vars_cookie']['secure'], $this->config['post_vars_cookie']['http_only']);
					else return false;
				} 
				
				/**
				 * Protect post vars cookie
				 * 
				 * @uses OliCore::$postVarsProtection to set post vars protection
				 * @uses OliCore::setCookie() to reset the post vars cookie
				 * @uses OliCore::$postVarsCookieName to get the post vars cookie name
				 * @uses OliCore::$postVarsCookieExpireDelay to get the post vars cookie expire delay
				 * @uses OliCore::$postVarsCookieDomain to get the post vars cookie domain
				 * @uses OliCore::$postVarsCookieSecure to get the post vars cookie secure parameter
				 * @uses OliCore::$postVarsCookieHttpOnly to get the post vars cookie http only parameter
				 * @return boolean Returns true if the cookie have been created, false otherwise
				 */
				public function protectPostVarsCookie() {
					$this->postVarsProtection = true;
					return $this->setCookie($this->config['post_vars_cookie']['name'], $this->getRawPostVars(), 1, '/', $this->config['post_vars_cookie']['domain'], $this->config['post_vars_cookie']['secure'], $this->config['post_vars_cookie']['http_only']);
				}
		
		/** ------------------ */
		/**  V. 7. HTML Tools  */
		/** ------------------ */
		
			/** ----------------------- */
			/**  V. 7. A. File Loaders  */
			/** ----------------------- */
			
			/**
			 * Load CSS stylesheet
			 * 
			 * @param string $url Custom full url to the stylesheet
			 * @param boolean|void $loadNow Post vars to check
			 * @param boolean|void $minimize Post vars to check
			 * 
			 * @uses OliCore::minimizeStyle() to minimize stylesheet file
			 * @uses OliCore::$htmlLoaderList to store file into the loader list
			 * @return void
			 */
			public function loadStyle($url, $tags = null, $loadNow = null, $minimize = null) {
				if(is_bool($tags)) {
					$minimize = $loadNow;
					$loadNow = $tags;
					$tags = null;
				}
				if(!isset($loadNow)) $loadNow = true;
				if(!isset($minimize)) $minimize = false;
				
				if($minimize AND empty($tags)) $codeLine = '<style type="text/css">' . $this->minimizeStyle(file_get_contents($url)) . '</style>';
				else $codeLine = '<link rel="stylesheet" type="text/css" href="' . $url . '" ' . ($tags ?: '') . '>';
				
				if($loadNow) echo $codeLine . PHP_EOL;
				else $this->htmlLoaderList[] = $codeLine;
			}
			
			/**
			 * Load local CSS stylesheet
			 * 
			 * @param string $url Data url to the stylesheet
			 * @param boolean|void $loadNow Post vars to check
			 * @param boolean|void $minimize Post vars to check
			 * 
			 * @uses OliCore::loadStyle() to load stylesheet file
			 * @uses OliCore::getAssetsUrl() to get data url
			 * @return void
			 */
			public function loadLocalStyle($url, $tags = null, $loadNow = null, $minimize = null) {
				$this->loadStyle($this->getAssetsUrl() . $url, $tags, $loadNow, $minimize);
			}
			
			/** Load common CSS stylesheet */
			public function loadCommonStyle($url, $tags = null, $loadNow = null, $minimize = null) {
				$this->loadStyle($this->getCommonAssetsUrl() . $url, $tags, $loadNow, $minimize);
			}
			
			/**
			 * Load cdn CSS stylesheet
			 * 
			 * @param string $url Cdn url to the stylesheet
			 * @param boolean|void $loadNow Post vars to check
			 * @param boolean|void $minimize Post vars to check
			 * 
			 * @uses OliCore::loadStyle() to load stylesheet file
			 * @uses OliCore::getCdnUrl() to get cdn url
			 * @return void
			 */
			public function loadCdnStyle($url, $tags = null, $loadNow = null, $minimize = null) {
				$this->loadStyle($this->config['cdn_url'] . $url, $tags, $loadNow, $minimize);
			}
			
			/**
			 * Load JS script
			 * 
			 * @param string $url Custom full url to the script
			 * @param boolean|void $loadNow Post vars to check
			 * @param boolean|void $minimize Post vars to check
			 * 
			 * @uses OliCore::minimizeScript() to minimize script file
			 * @uses OliCore::$htmlLoaderList to store file into the loader list
			 * @return void
			 */
			public function loadScript($url, $tags = null, $loadNow = null, $minimize = null) {
				if(is_bool($tags)) {
					$minimize = $loadNow;
					$loadNow = $tags;
					$tags = null;
				}
				if(!isset($loadNow)) $loadNow = true;
				if(!isset($minimize)) $minimize = false;
				
				if($minimize AND empty($tags)) $codeLine = '<script type="text/javascript">' . $this->minimizeScript(file_get_contents($url)) . '</script>';
				else $codeLine = '<script type="text/javascript" src="' . $url . '" ' . ($tags ?: '') . '></script>';
				
				if($loadNow) echo $codeLine . PHP_EOL;
				else $this->htmlLoaderList[] = $codeLine;
			}
			
			/**
			 * Load local JS script
			 * 
			 * @param string $url Data url to the script
			 * @param boolean|void $loadNow Post vars to check
			 * @param boolean|void $minimize Post vars to check
			 * 
			 * @uses OliCore::loadScript() to load script file
			 * @uses OliCore::getAssetsUrl() to get data url
			 * @return void
			 */
			public function loadLocalScript($url, $tags = null, $loadNow = null, $minimize = null) {
				$this->loadScript($this->getAssetsUrl() . $url, $tags, $loadNow, $minimize);
			}
			
			/** Load common JS script */
			public function loadCommonScript($url, $tags = null, $loadNow = null, $minimize = null) {
				$this->loadScript($this->getCommonAssetsUrl() . $url, $tags, $loadNow, $minimize);
			}
			
			/**
			 * Load cdn JS script
			 * 
			 * @param string $url Cdn url to the script
			 * @param boolean|void $loadNow Post vars to check
			 * @param boolean|void $minimize Post vars to check
			 * 
			 * @uses OliCore::loadScript() to load script file
			 * @uses OliCore::getCdnUrl() to get cdn url
			 * @return void
			 */
			public function loadCdnScript($url, $tags = null, $loadNow = null, $minimize = null) {
				$this->loadScript($this->config['cdn_url'] . $url, $tags, $loadNow, $minimize);
			}
			
			/**
			 * Load end html files
			 * 
			 * Force the loader list files to load
			 * 
			 * @uses OliCore::$htmlLoaderList to get files from the loader list
			 * @return void
			 */
			public function loadEndHtmlFiles() {
				if(!empty($this->htmlLoaderList)) {
					echo PHP_EOL;
					foreach($this->htmlLoaderList as $eachCodeLine) {
						echo array_shift($this->htmlLoaderList) . PHP_EOL;
					}
				}
			}
		
			/** -------------------------- */
			/**  V. 7. B. File Minimizers  */
			/** -------------------------- */
			
			/**
			 * Minimize stylesheet
			 * 
			 * @param string $styleCode Stylesheet code to minimize
			 * 
			 * @return string Stylesheet code minimized
			 */
			public function minimizeStyle($styleCode) {
				$styleCode = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $styleCode);
				$styleCode = preg_replace('!\s+!', ' ', $styleCode);
				$styleCode = str_replace(': ', ':', $styleCode);
				$styleCode = str_replace(["\r\n", "\r", "\n", "\t"], '', $styleCode);
				$styleCode = str_replace(';}', '}', $styleCode);
				return $styleCode;
			}
			
			/**
			 * Minimize script
			 * 
			 * @param string $scriptCode Script code to minimize
			 * 
			 * @return string Script code minimized
			 */
			public function minimizeScript($scriptCode) {
				$scriptCode = preg_replace('!^[ \t]*/\*.*?\*/[ \t]*[\r\n]!s', '', $scriptCode);
				$scriptCode = preg_replace('![ \t]*[^:]//.*[ \t]*[\r\n]?!', '', $scriptCode);
				$scriptCode = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $scriptCode);
				$scriptCode = preg_replace('!\s+!', ' ', $scriptCode);
				$scriptCode = str_replace([' {', ' }', '{ ', '; '], ['{', '}', '{', ';'], $scriptCode);
				$scriptCode = str_replace(["\r\n", "\r", "\n", "\t"], '', $scriptCode);
				return $scriptCode;
			}
		
		/** --------------------- */
		/**  V. 7. Url Functions  */
		/** --------------------- */
		
		/** Get Url Parameter */
		// $param supported values:
		// - null 'full' => Full Url (e.g. 'http://hello.example.com/page/param')
		// - 'protocol' => Get url protocol (e.g. 'https')
		// - 'base' => Get base url (e.g. 'http://hello.example.com/')
		// - 'allbases' => Get all bases urls (e.g. ['http://hello.example.com/', 'http://example.com/'])
		// - 'alldomains' => Get all domains (e.g. ['hello.example.com', 'example.com'])
		// - 'fulldomain' => Get domain (e.g. 'hello.example.com')
		// - 'domain' => Get main domain (e.g. 'example.com')
		// - 'subdomain' => Get subdomains (e.g. 'hello')
		// - 'all' => All url fragments
		// - 'params' => All parameters fragments
		// - 0 => Url without any parameters (same as base url)
		// - 1 => First parameter: file name parameter (e.g. 'page')
		// - # => Other parameters (e.g. 2 => 'param')
		public function getUrlParam($param = null, &$hasUsedHttpHostBase = false) {
			$protocol = (!empty($_SERVER['HTTPS']) OR $this->config['force_https']) ? 'https' : 'http';
			$urlPrefix = $protocol . '://';
			
			if(!isset($param) OR $param < 0 OR $param === 'full') return $urlPrefix . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
			else if($param === 'protocol') return $protocol;
			else {
				$urlSetting = $this->getSetting('url');
				$urlSetting = !empty($urlSetting) ? (!is_array($urlSetting) ? [$urlSetting] : $urlSetting) : null;
				
				if(in_array($param, ['allbases', 'alldomains'], true)) {
					$allBases = $allDomains = [];
					foreach($urlSetting as $eachUrl) {
 						preg_match('/^(https?:\/\/)?(((?:[w]{3}\.)?(?:[\da-z\.-]+\.)*(?:[\da-z-]+\.(?:[a-z\.]{2,6})))\/?(?:.)*)/', $eachUrl, $matches);
						$allBases[] = ($matches[1] ?: $urlPrefix) . $matches[2];
						$allDomains[] = $matches[3];
					}
					
					if($param == 'allbases') return $allBases;
					else if($param == 'alldomains') return $allDomains;
				}
				else {
					$frationnedUrl = explode('/', $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
					$hasUsedHttpHostBase = false;
					$baseUrlMatch = false;
					$baseUrl = $urlPrefix;
					$shortBaseUrl = '';
					$countLoop = 0;
					
					if(isset($urlSetting)) {
						foreach($frationnedUrl as $eachPart) {
							if(in_array($baseUrl, $urlSetting) OR in_array($shortBaseUrl, $urlSetting)) {
								$baseUrlMatch = true;
								break;
							}
							else {
								$baseUrlMatch = false;
								$baseUrl .= urldecode($eachPart) . '/';
								$shortBaseUrl .= urldecode($eachPart) . '/';
								$countLoop++;
							}
						}
					}
					
					if(!isset($urlSetting) OR !$baseUrlMatch) {
						$baseUrl = $urlPrefix . $_SERVER['HTTP_HOST'] . '/';
						$hasUsedHttpHostBase = true;
					}
					
					if(in_array($param, [0, 'base'], true)) return $baseUrl;
					else if(in_array($param, ['fulldomain', 'subdomain', 'domain'], true)) {
						preg_match('/^https?:\/\/(?:[w]{3}\.)?((?:([\da-z\.-]+)\.)*([\da-z-]+\.(?:[a-z\.]{2,6})))\/?/', $baseUrl, $matches);
						if($param == 'fulldomain') return $matches[1];
						if($param == 'subdomain') return $matches[2];
						if($param == 'domain') return $matches[3];
					}
					else {
						$newFrationnedUrl[] = $baseUrl;
						if(!empty($this->fileNameParam)) {
							while(isset($frationnedUrl[$countLoop])) {
								if(!empty($fileName) AND implode('/', $fileName) == $this->fileNameParam) break;
								else {
									$fileName[] = urldecode($frationnedUrl[$countLoop]);
									$countLoop++;
								}
							}
							$newFrationnedUrl[] = implode('/', $fileName);
						}
						
						while(isset($frationnedUrl[$countLoop])) {
							if(!empty($frationnedUrl[$countLoop]) OR isset($frationnedUrl[$countLoop + 1])) {
								$nextFrationnedUrl = urldecode($frationnedUrl[$countLoop]);
								if(isset($frationnedUrl[$countLoop + 1]) AND empty($frationnedUrl[$countLoop + 1]) AND isset($frationnedUrl[$countLoop + 2])) {
									$nextFrationnedUrl .= '/' . urldecode($frationnedUrl[$countLoop + 2]);
									$countLoop += 2;
								}
								
								str_replace('\/', '/', $nextFrationnedUrl);
								$newFrationnedUrl[] = $nextFrationnedUrl;
							}
							$countLoop++;
						}
						$newFrationnedUrl[1] = $newFrationnedUrl[1] ?: 'home';
						
						if($param == 'all') return $newFrationnedUrl;
						else if($param == 'params') return array_slice($newFrationnedUrl, 1);
						else if(isset($newFrationnedUrl[$param])) return $newFrationnedUrl[$param];
						else return false;
					}
				}
			}
		}
		
		/** Get Full Url */
		public function getFullUrl() { return $this->getUrlParam('full'); }
		
		/** Get Data Url */
		public function getAssetsUrl() { return $this->getUrlParam(0) . ($this->config['theme_path'] ?: 'content/theme/') . $this->config['assets_folder']; }
		public function getDataUrl() { return $this->getAssetsUrl(); }
		
		/** Get Media Url */
		public function getMediaUrl() { return $this->getUrlParam(0) . $this->config['media_path']; }
		
		/** Get Common Files Url */
		public function getCommonAssetsUrl() { return $this->getUrlParam(0) . $this->config['common_path'] . $this->config['common_assets_folder']; }
		public function getCommonFilesUrl() { return $this->getCommonAssetsUrl(); }
		
		/** Get CDN Url */
		public function getCdnUrl() { return $this->config['cdn_url']; }
		
		/** --------------------- */
		/**  V. 8. User Language  */
		/** --------------------- */
		
		/**
		 * Get default language
		 * 
		 * @uses OliCore::$defaultLanguage to get default language
		 * @return string Returns default language
		 */
		public function getDefaultLanguage() { return $this->config['default_user_language']; }
		
		/**
		 * Set current language
		 * 
		 * @param string|void $language Language to set
		 * 
		 * @uses OliCore::$currentLanguage to get current language
		 * @uses OliCore::$defaultLanguage to get default language
		 * @return void
		 */
		public function setCurrentLanguage($language = null) {
			$this->currentLanguage = (!empty($language) AND $language != 'DEFAULT') ? strtolower($language) : $this->config['default_user_language'];
		}
		
		/**
		 * Get current language
		 * 
		 * @uses OliCore::$currentLanguage to get current language
		 * @return string Returns current language
		 */
		public function getCurrentLanguage() { return $this->currentLanguage; }
		
		/**
		 * Set user language
		 * 
		 * @param string|void $language Language to set
		 * @param string|array|void $where Where to change language
		 * 
		 * @uses OliCore::$currentLanguage to get current language
		 * @uses OliCore::$defaultLanguage to get default language
		 * @return boo
		 */
		public function setUserLanguage($language = null, $where = null) {
			$language = (!empty($language) AND $language != 'DEFAULT') ? strtolower($language) : $this->config['default_user_language'];
			
			if(!isset($where)) {
				if($this->verifyAuthKey()) $where = array('username' => $this->getAuthKeyOwner());
				else return false;
			}
			else if(!is_array($where)) $where = array('username' => $where);
			
			if($this->updateAccountInfos('ACCOUNTS', array('language' => $language), $where)) {
				$this->currentLanguage = $language;
				return true;
			}
			else return false;
		}
		
		/**
		 * Get user language
		 * 
		 * @param string|array|void $where Where to get language
		 * @param boolean|void $caseSensitive Where is case sensitive or not
		 * 
		 * @uses OliCore::$currentLanguage to get current language
		 * @return string Returns current language
		 */
		public function getUserLanguage($where = null, $caseSensitive = true) {
			if(!isset($where)) {
				if($this->verifyAuthKey()) $where = array('username' => $this->getAuthKeyOwner());
				else return false;
			}
			else if(!is_array($where)) $where = array('username' => $where);
			
			return $this->getAccountInfos('ACCOUNTS', 'language', $where, $caseSensitive);
		}
		
		/** --------------------- */
		/**  V. 9. Utility Tools  */
		/** --------------------- */
		
			/** --------------------- */
			/**  V. 9. A. Generators  */
			/** --------------------- */
			
			/**
			 * Generate random number
			 * 
			 * @param integer|void $minimal Minimal value (default: 1)
			 * @param integer|void $maximal Maximal value (default: 100)
			 * 
			 * @return integer Returns random number between minimal and maximal value
			 */
			public function randomNumber($minimal = 1, $maximal = 100) {
				return mt_rand($minimal, $maximal);
			}
			
			/**
			 * Generate random secure key
			 * 
			 * @param integer|void $length Keygen length (default: 12)
			 * @param boolean|void $numeric Numeric characters (default: true)
			 * @param boolean|void $lowercase Lowercase characters (default: true)
			 * @param boolean|void $uppercase Uppercase characters (default: true)
			 * @param boolean|void $special Special characters (default: false)
			 * @param boolean|void $characterRedundancy Force characters redundancy (default: false)
			 * 
			 * @uses OliCore::randomNumber() to get a random number
			 * @return string|boolean Returns generated keygen
			 */
			public function keygen($length = 12, $numeric = true, $lowercase = true, $uppercase = true, $special = false, $characterRedundancy = false) {
				$charactersAllowed = '';
				if($numeric) $charactersAllowed .= '1234567890';
				if($lowercase) $charactersAllowed .= 'abcdefghijklmnopqrstuvwxyz';
				if($uppercase) $charactersAllowed .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
				if($special) $charactersAllowed .= '!#$%&\()+-;?@[]^_{|}';
				
				if(empty($charactersAllowed) OR empty($length) OR $length <= 0) return false;
				else {
					if($length > strlen($charactersAllowed) AND !$characterRedundancy) $characterRedundancy = true;
					
					$keygen = '';
					while(strlen($keygen) < $length) {
						$randomCharacter = substr($charactersAllowed, $this->randomNumber(0, strlen($charactersAllowed) - 1), 1);
						if($characterRedundancy OR !strstr($keygen, $randomCharacter)) $keygen .= $randomCharacter;
					}
					
					return $keygen;
				}
			}
			
			/** ---------------------- */
			/**  V. 9. B. Date & Time  */
			/** ---------------------- */
			
			/**
			 * Get difference between two dates
			 * 
			 * @param string $startDate Start date
			 * @param string $endDate End date
			 * @param boolean $precise Precise parameter
			 * @param boolean|void $details Details units parameter (default: true)
			 * 
			 * @return integer|array Returns date difference
			 */
			public function dateDifference($startDate, $endDate, $precise, $details = true) {
				if(is_string($startDate))
					$startDate = strtotime($startDate);
				if(is_string($endDate))
					$endDate = strtotime($endDate);
				
				$difference = abs($startDate - $endDate);
				$buffer = $difference;
				
				$results['total_seconds'] = $buffer;
				$results['seconds'] = $buffer % 60;
				
				$buffer = floor(($buffer - $results['seconds']) / 60);
				$results['total_minutes'] = $buffer;
				$results['minutes'] = $buffer % 60;
				
				$buffer = floor(($buffer - $results['minutes']) / 60);
				$results['total_hours'] = $buffer;
				$results['total_hours'] = $buffer;
				$results['hours'] = $buffer % 24;
				
				$buffer = floor(($buffer - $results['hours']) / 24);
				$results['total_days'] = $buffer;
				$results['days'] = $buffer % 365.25;
				
				$buffer = floor(($buffer - $results['months']) / 365.25);
				$results['years'] = $buffer;
				
				if($precise) {
					if(!empty($results['years']))
						return array('years' => $results['years'], 'days' => $results['days'], 'hours' => $results['hours'], 'minutes' => $results['minutes'], 'seconds' => $results['seconds']);
					else if(!empty($results['days']))
						return array('days' => $results['total_days'], 'hours' => $results['hours'], 'minutes' => $results['minutes'], 'seconds' => $results['seconds']);
					else if(!empty($results['hours']))
						return array('hours' => $results['total_hours'], 'minutes' => $results['minutes'], 'seconds' => $results['seconds']);
					else if(!empty($results['minutes']))
						return array('minutes' => $results['total_minutes'], 'seconds' => $results['seconds']);
					else
						return array('seconds' => $results['total_seconds']);
				}
				else {
					if($details) {
						if(!empty($results['years']))
							return array('years' => $results['years']);
						else if(!empty($results['total_days']))
							return array('days' => $results['total_days']);
						else if(!empty($results['total_hours']))
							return array('hours' => $results['total_hours']);
						else if(!empty($results['total_minutes']))
							return array('minutes' => $results['total_minutes']);
						else
							return array('seconds' => $results['total_seconds']);
					}
					else {
						if(!empty($results['years']))
							return $results['years'];
						else if(!empty($results['total_days']))
							return $results['total_days'];
						else if(!empty($results['total_hours']))
							return $results['total_hours'];
						else if(!empty($results['total_minutes']))
							return $results['total_minutes'];
						else
							return $results['total_seconds'];
					}
				}
			}
			
			/** ----------------------- */
			/**  V. 9. C. Client Infos  */
			/** ----------------------- */
			
			/**
			 * Get user IP address
			 * 
			 * @return string Returns user IP address
			 */
			public function getUserIP() {
				if(!empty($_SERVER['REMOTE_ADDR'])) $client_ip = $_SERVER['REMOTE_ADDR'];
				else if(!empty($_ENV['REMOTE_ADDR'])) $client_ip = $_ENV['REMOTE_ADDR'];
				else $client_ip = 'unknown';
				
				if(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
					$entries = preg_split('[, ]', $_SERVER['HTTP_X_FORWARDED_FOR']);
					
					reset($entries);
					while(list(, $entry) = each($entries)) {
						$entry = trim($entry);
						if(preg_match('/^([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)/', $entry, $ip_list)){
							$private_ip = [
								'/^0\./',
								'/^127\.0\.0\.1/',
								'/^192\.168\..*/',
								'/^172\.((1[6-9])|(2[0-9])|(3[0-1]))\..*/',
								'/^10\..*/'];
							
							$found_ip = preg_replace($private_ip, $client_ip, $ip_list[1]);

							if($client_ip != $found_ip) {
								$client_ip = $found_ip;
								break;
							}
						}
					}
				}
				
				return $client_ip;
			}
	
	/** -------------------- */
	/**  Accounts Functions  */
	/** -------------------- */
	
		/** -------------------------------------- */
		/**  Enable / Disable Accounts Management  */
		/** -------------------------------------- */
		
		/**
		 * Enable accounts management
		 * 
		 * Enable full login management
		 * Allow to log, register and logout users
		 * 
		 * @uses OliCore::$accountsManagementStatus to set accounts management status
		 * @return void
		 */
		// public function enableAccountsManagement() {
			// $this->accountsManagementStatus = true;
		// }
		
		/**
		 * Is accounts management enabled
		 * 
		 * @uses OliCore::$accountsManagementStatus to get accounts management status
		 * @return boolean Accounts management status
		 */
		public function getAccountsManagementStatus() {
			return $this->config['user_management'];
		}
		
		/** ----------------- */
		/**  MySQL Functions  */
		/** ----------------- */
	
			/** ----------------------- */
			/**  Translate Table Codes  */
			/** ----------------------- */
			
			/**
			 * Translate accounts table codes
			 * 
			 * - ACCOUNTS: Accounts list and main informations (password, email...)
			 * - INFOS - Accounts other informations
			 * - PERMISSIONS - Accounts personnal permissions
			 * - RIGHTS - Accounts rights list (permissions groups) 
			 * - SESSIONS - Accounts login sessions
			 * - REQUESTS - Accounts requests
			 * 
			 * @param string $tableCode Table code to translate
			 * 
			 * @uses OliCore::$accountsTable to get main account table
			 * @uses OliCore::$accountsInfosTable to get account infos table
			 * @uses OliCore::$accountsSessionsTable to get account sessions table
			 * @uses OliCore::$accountsRequestsTable to get account requests table
			 * @uses OliCore::$accountsPermissionsTable to get account permissions table
			 * @uses OliCore::$accountsRightsTable to get account rights table
			 * @return boolean Returns translated table name
			 */
			public function translateAccountsTableCode($tableCode) {
				if($tableCode == 'ACCOUNTS') return $this->config['accounts_tables']['accounts'];
				else if($tableCode == 'INFOS') return $this->config['accounts_tables']['infos'];
				else if($tableCode == 'SESSIONS') return $this->config['accounts_tables']['sessions'];
				else if($tableCode == 'LOGIN_LIMITS') return $this->config['accounts_tables']['login_limits'];
				else if($tableCode == 'REQUESTS') return $this->config['accounts_tables']['requests'];
				else if($tableCode == 'PERMISSIONS') return $this->config['accounts_tables']['permissions'];
				else if($tableCode == 'RIGHTS') return $this->config['accounts_tables']['rights'];
				else return false;
			}
		
			/** ------------------------------- */
			/**  Read Accounts Infos Functions  */
			/** ------------------------------- */
		
			/**
			 * Get first info from account table
			 * 
			 * @param string $tableCode Table code of the table to get data from
			 * @param string $whatVar Variable to get
			 * @param boolean|void $rawResult Return raw result or not
			 * 
			 * @uses OliCore::getFirstInfoMySQL() to get first info from table
			 * @uses OliCore::translateAccountsTableCode() to translate account table code
			 * @return array|boolean Returns first info from specified table
			 */
			public function getFirstAccountInfo($tableCode, $whatVar, $rawResult = false) {
				return $this->getFirstInfoMySQL($this->translateAccountsTableCode($tableCode) ?: $tableCode, $whatVar, $rawResult);
			}
			
			/**
			 * Get first line from account table
			 * 
			 * @param string $tableCode Table code of the table to get data from
			 * @param boolean|void $rawResult Return raw result or not
			 * 
			 * @uses OliCore::getFirstLineMySQL() to get first line from table
			 * @uses OliCore::translateAccountsTableCode() to translate account table code
			 * @return array|boolean Returns first line from specified table
			 */
			public function getFirstAccountLine($tableCode, $rawResult = false) {
				return $this->getFirstLineMySQL($this->translateAccountsTableCode($tableCode) ?: $tableCode, $rawResult);
			}
			
			/**
			 * Get last info from table
			 * 
			 * @param string $tableCode Table code of the table to get data from
			 * @param string $whatVar Variable to get
			 * @param boolean|void $rawResult Return raw result or not
			 * 
			 * @uses OliCore::getLastInfoMySQL() to get last info from table
			 * @uses OliCore::translateAccountsTableCode() to translate account table code
			 * @return array|boolean Returns last info from specified table
			 */
			public function getLastAccountInfo($tableCode, $whatVar, $rawResult = false) {
				return $this->getLastInfoMySQL($this->translateAccountsTableCode($tableCode) ?: $tableCode, $whatVar, $rawResult);
			}
			
			/**
			 * Get last line from account table
			 * 
			 * @param string $tableCode Table code of the tableTable to get data from
			 * @param boolean|void $rawResult Return raw result or not
			 * 
			 * @uses OliCore::getLastLineMySQL() to get last line from table
			 * @uses OliCore::translateAccountsTableCode() to translate account table code
			 * @return array|boolean Returns last line from specified table
			 */
			public function getLastAccountLine($tableCode, $rawResult = false) {
				return $this->getLastLineMySQL($this->translateAccountsTableCode($tableCode) ?: $tableCode, $whatVar, $rawResult);
			}
			
			/**
			 * Get lines from account table
			 * 
			 * @param string $tableCode Table code of the table to get data from
			 * @param string|array|void $where Where to get data from
			 * @param array|void $settings Data returning settings
			 * @param boolean|void $caseSensitive Where is case sensitive or not
			 * @param boolean|void $forceArray Return result in an array or not
			 * @param boolean|void $rawResult Return raw result or not
			 * 
			 * @uses OliCore::getLinesMySQL() to get lines from table
			 * @uses OliCore::translateAccountsTableCode() to translate account table codes
			 * @return array|boolean Returns lines from specified table
			 */
			public function getAccountLines($tableCode, $where = null, $settings = null, $caseSensitive = null, $forceArray = null, $rawResult = null) {
				if(!isset($where)) {
					if($this->verifyAuthKey()) $where = array('username' => $this->getAuthKeyOwner());
					else return false;
				}
				else if(!is_array($where) AND $where != 'all') $where = array('username' => $where);
				return $this->getLinesMySQL($this->translateAccountsTableCode($tableCode) ?: $tableCode, $where, $settings, $caseSensitive, $forceArray, $rawResult);
			}
			
			/**
			 * Get infos from account table
			 * 
			 * @param string $tableCode Table code of the table to get data from
			 * @param string|array $whatVar What var(s) to return
			 * @param string|array|void $where Where to get data from
			 * @param array|void $settings Data returning settings
			 * @param boolean|void $caseSensitive Where is case sensitive or not
			 * @param boolean|void $forceArray Return result in an array or not
			 * @param boolean|void $rawResult Return raw result or not
			 * 
			 * @uses OliCore::getInfosMySQL() to get infos from table
			 * @uses OliCore::translateAccountsTableCode() to translate account table code
			 * @return mixed Returns infos from specified table
			 */
			public function getAccountInfos($tableCode, $whatVar, $where = null, $settings = null, $caseSensitive = null, $forceArray = null, $rawResult = null) {
				if(!isset($where)) {
					if($this->verifyAuthKey()) $where = array('username' => $this->getAuthKeyOwner());
					else return false;
				}
				else if(!is_array($where) AND $where != 'all') $where = array('username' => $where);
				return $this->getInfosMySQL($this->translateAccountsTableCode($tableCode) ?: $tableCode, $whatVar, $where, $settings, $caseSensitive, $forceArray, $rawResult);
			}
			
			/**
			 * Get summed infos from account table
			 * 
			 * @param string $tableCode Table code of the table to get data from
			 * @param string|array $whatVar What var(s) to return
			 * @param string|array|void $where Where to get data from
			 * @param array|void $settings Data returning settings
			 * @param boolean|void $caseSensitive Where is case sensitive or not
			 * @param boolean|void $rawResult Return raw result or not
			 * 
			 * @uses OliCore::getSummedInfosMySQL() to get summed infos from table
			 * @uses OliCore::translateAccountsTableCode() to translate account table code
			 * @return mixed Returns summed infos from specified table
			 */
			public function getSummedAccountInfos($tableCode, $whatVar, $where = null, $caseSensitive = true) {
				if(!isset($where)) {
					if($this->verifyAuthKey()) $where = array('username' => $this->getAuthKeyOwner());
					else return false;
				}
				else if(!is_array($where) AND $where != 'all') $where = array('username' => $where);
				return $this->getSummedInfosMySQL($this->translateAccountsTableCode($tableCode) ?: $tableCode, $whatVar, $where, $caseSensitive, $forceArray, $rawResult);
			}
			
			/**
			 * Is empty infos in account table
			 * 
			 * @param string $tableCode Table code of the table to get data from
			 * @param string|array $whatVar What var(s) to return
			 * @param string|array|void $where Where to get data from
			 * @param array|void $settings Data returning settings
			 * @param boolean|void $caseSensitive Where is case sensitive or not
			 * 
			 * @uses OliCore::isEmptyInfosMySQL() to get if infos are empty in table
			 * @uses OliCore::translateAccountsTableCode() to translate account table code
			 * @return boolean Returns true if infos are empty, false otherwise
			 */
			public function isEmptyAccountInfos($tableCode, $whatVar, $where = null, $settings = null, $caseSensitive = null) {
				if(!isset($where)) {
					if($this->verifyAuthKey()) $where = array('username' => $this->getAuthKeyOwner());
					else return false;
				}
				else if(!is_array($where) AND $where != 'all') $where = array('username' => $where);
				return $this->isEmptyInfosMySQL($this->translateAccountsTableCode($tableCode) ?: $tableCode, $whatVar, $where, $settings, $caseSensitive);
			}
			
			/**
			 * Is exist infos in account table
			 * 
			 * @param string $tableCode Table code of the table to get data from
			 * @param string|array|void $where Where to get data from
			 * @param boolean|void $caseSensitive Where is case sensitive or not
			 * 
			 * @uses OliCore::isExistInfosMySQL() to get if infos exists in table
			 * @uses OliCore::translateAccountsTableCode() to translate account table code
			 * @return boolean Returns true if infos exists, false otherwise
			 */
			public function isExistAccountInfos($tableCode, $where = null, $caseSensitive = true) {
				if(!isset($where)) {
					if($this->verifyAuthKey()) $where = array('username' => $this->getAuthKeyOwner());
					else return false;
				}
				else if(!is_array($where) AND $where != 'all') $where = array('username' => $where);
				return $this->isExistInfosMySQL($this->translateAccountsTableCode($tableCode) ?: $tableCode, $where, $caseSensitive);
			}
		
			/** -------------------------------- */
			/**  Write Accounts Infos Functions  */
			/** -------------------------------- */
			
			/**
			 * Insert line in account table
			 * 
			 * @param string $tableCode Table code of the table to insert lines into
			 * @param array $matches Data to insert into the table
			 * 
			 * @uses OliCore::insertLineMySQL() to insert lines in table
			 * @uses OliCore::translateAccountsTableCode() to translate account table code
			 * @return boolean Returns true if the request succeeded, false otherwise
			 */
			public function insertAccountLine($tableCode, $what) {
				return $this->insertLineMySQL($this->translateAccountsTableCode($tableCode) ?: $tableCode, $what);
			}
			
			/**
			 * Update infos from account table
			 * 
			 * @param string $tableCode Table code of the table to update infos from
			 * @param array $what What to replace data with
			 * @param string|array|void $where Where to update data
			 * 
			 * @uses OliCore::updateInfosMySQL() to update infos in table
			 * @uses OliCore::translateAccountsTableCode() to translate account table code
			 * @return boolean Return true if the request succeeded, false otherwise
			 */
			public function updateAccountInfos($tableCode, $what, $where = null) {
				if(!isset($where)) {
					if($this->verifyAuthKey()) $where = array('username' => $this->getAuthKeyOwner());
					else return false;
				}
				else if(!is_array($where) AND $where != 'all') $where = array('username' => $where);
				
				return $this->updateInfosMySQL($this->translateAccountsTableCode($tableCode) ?: $tableCode, $what, $where);
			}
			
			/**
			 * Update account username
			 * 
			 * @param string $newUsername New username for the user
			 * @param string $oldUsername Current username of the user
			 * 
			 * @uses OliCore::updateAccountInfos() to update infos from account table
			 * @return boolean Return true if the requests succeeded, false otherwise
			 */
			public function updateAccountUsername($newUsername, $oldUsername) {
				if($this->updateAccountInfos('ACCOUNTS', array('username' => $newUsername), $oldUsername) AND $this->updateAccountInfos('INFOS', array('username' => $newUsername), $oldUsername) AND $this->updateAccountInfos('SESSIONS', array('username' => $newUsername), $oldUsername) AND $this->updateAccountInfos('REQUESTS', array('username' => $newUsername), $oldUsername))
					return true;
				else return false;
			}
			
			/**
			 * Delete lines from account table
			 * 
			 * @param string $tableCode Table code of the table to delete lines from
			 * @param string|array $where Where to delete data
			 * 
			 * @uses OliCore::deleteLinesMySQL() to delete lines from table
			 * @uses OliCore::translateAccountsTableCode() to translate account table code
			 * @return boolean Returns true if the request succeeded, false otherwise
			 */
			public function deleteAccountLines($tableCode, $where) {
				if(!is_array($where) AND $where != 'all') $where = array('username' => $where);
				return $this->deleteLinesMySQL($this->translateAccountsTableCode($tableCode) ?: $tableCode, $where);
			}
			
			/**
			 * Delete full account
			 * 
			 * @param string|array $where Where to delete user
			 * 
			 * @uses OliCore::deleteLinesMySQL() to delete lines from table
			 * @uses OliCore::translateAccountsTableCode() to translate account table code
			 * @return boolean Returns true if the requests succeeded, false otherwise
			 */
			public function deleteFullAccount($where) {
				if($this->deleteAccountLines('ACCOUNTS', $where) AND $this->deleteAccountLines('INFOS', $where) AND $this->deleteAccountLines('SESSIONS', $where) AND $this->deleteAccountLines('REQUESTS', $where))
					return true;
				else return false;
			}
			
			/** ---------------------- */
			/**  User Right Functions  */
			/** ---------------------- */
			
			/**
			 * Verify user right syntax
			 * 
			 * @param string $userRight User right to check
			 * @param boolean|void $caseSensitive Check is case sensitive or not
			 * 
			 * @uses OliCore::isExistAccountInfos() to get if infos exists in account table
			 * @return boolean Returns true if the requests succeeded, false otherwise
			 */
			public function verifyUserRight($userRight, $caseSensitive = true) {
				if(!empty($userRight)) return $this->isExistAccountInfos('RIGHTS', array('user_right' => $userRight), $caseSensitive);
				else return false;
			}
			
			/**
			 * Translate user right
			 * 
			 * @param string $userRight User right to translate
			 * @param boolean|void $caseSensitive Translate is case sensitive or not
			 * 
			 * @uses OliCore::getAccountInfos() to get infos from account table
			 * @return string|boolean Returns translated user right
			 */
			public function translateUserRight($userRight, $caseSensitive = true) {
				if(!empty($userRight)) {
					if($returnValue = $this->getAccountInfos('RIGHTS', 'user_right', array('id' => $userRight), $caseSensitive)) return $returnValue;
					else if($returnValue = $this->getAccountInfos('RIGHTS', 'id', array('user_right' => $userRight), $caseSensitive)) return $returnValue;
					else if($returnValue = $this->getAccountInfos('RIGHTS', 'id', array('acronym' => $userRight), $caseSensitive)) return $returnValue;
					else return false;
				}
				else return false;
			}
			
			/** DEPRECATED Get Right Level */
			public function getRightLevel($userRight, $caseSensitive = true) {
				return $this->translateUserRight($userRight, $caseSensitive);
			}
			
			/**
			 * Get right permissions
			 * 
			 * @param string $userRight User right to get permissions of
			 * @param boolean|void $caseSensitive Translate is case sensitive or not
			 * 
			 * @uses OliCore::getAccountInfos() to get infos from account table
			 * @return integer Returns user right permissions
			 */
			public function getRightPermissions($userRight, $caseSensitive = true) {
				if($returnValue = $this->getAccountInfos('RIGHTS', 'permissions', array('user_right' => $userRight), $caseSensitive)) return $returnValue;
				else if($returnValue = $this->getAccountInfos('RIGHTS', 'permissions', array('acronym' => $userRight), $caseSensitive)) return $returnValue;
			}
			
			/**
			 * Get rights lines
			 * 
			 * @param string|array|void $where Where to get data from
			 * @param array|void $settings Data returning settings
			 * @param boolean|void $caseSensitive Where is case sensitive or not
			 * @param boolean|void $forceArray Return result in an array or not
			 * @param boolean|void $rawResult Return raw result or not
			 * 
			 * @uses OliCore::getAccountLines() to get lines from account table
			 * @return array|boolean Returns lines from specified table
			 */
			public function getRightsLines($where = [], $settings = null, $caseSensitive = null, $forceArray = null, $rawResult = null) {
				if(!is_array($where)) $where = array('username' => $where);
				return $this->getAccountLines('RIGHTS', $where, $settings, $caseSensitive, $forceArray, $rawResult);
			}
			
			/**
			 * Get rights infos
			 * 
			 * @param string|array|void $whatVar What var(s) to return
			 * @param string|array|void $where Where to get data from
			 * @param array|void $settings Data returning settings
			 * @param boolean|void $caseSensitive Where is case sensitive or not
			 * @param boolean|void $forceArray Return result in an array or not
			 * @param boolean|void $rawResult Return raw result or not
			 * 
			 * @uses OliCore::getAccountInfos() to get infos from account table
			 * @return mixed Returns infos from specified table
			 */
			public function getRightsInfos($whatVar = null, $where = [], $settings = null, $caseSensitive = null, $forceArray = null, $rawResult = null) {
				if(empty($whatVar)) $whatVar = 'user_right';
				return $this->getAccountInfos('RIGHTS', $whatVar, $where, $settings, $caseSensitive, $forceArray, $rawResult);
			}
			
			/**
			 * Get user right
			 * 
			 * @param string|array|void $whatVar What var(s) to return
			 * @param string|array|void $where Where to get data from
			 * @param array|void $settings Data returning settings
			 * @param boolean|void $caseSensitive Where is case sensitive or not
			 * @param boolean|void $forceArray Return result in an array or not
			 * @param boolean|void $rawResult Return raw result or not
			 * 
			 * @uses OliCore::getAccountInfos() to get infos from account table
			 * @return mixed Returns user right
			 */
			public function getUserRight($where = null, $caseSensitive = true) {
				if(empty($where)) {
					if($this->verifyAuthKey()) $where = array('username' => $this->getAuthKeyOwner());
					else return false;
				}
				else if(!is_array($where)) $where = array('username' => $where);
				
				return $this->getAccountInfos('ACCOUNTS', 'user_right', $where, $caseSensitive);
			}
			
			/**
			 * Get user right level
			 * 
			 * @param string|array|void $where Where to get data from
			 * @param boolean|void $caseSensitive Translate is case sensitive or not
			 * 
			 * @uses OliCore::getUserRight() to get user right
			 * @return integer Returns user right level
			 */
			public function getUserRightLevel($where = null, $caseSensitive = true) {
				return $this->translateUserRight($this->getUserRight($where, $caseSensitive));
			}
			
			/**
			 * Get user right permissions
			 * 
			 * @param string|array|void $where Where to get data from
			 * @param boolean|void $caseSensitive Translate is case sensitive or not
			 * 
			 * @uses OliCore::getRightPermissions() to get right permissions
			 * @uses OliCore::getUserRight() to get user right
			 * @return integer Returns user right permissions
			 */
			public function getUserRightPermissions($where = null, $caseSensitive = true) {
				return $this->getRightPermissions($this->getUserRight($where, $caseSensitive));
			}
			
			/**
			 * Update user right
			 * 
			 * @param string $userRight New right to set to the user
			 * @param array $what What to replace data with
			 * @param string|array $where Where to update data
			 * 
			 * @uses OliCore::verifyUserRight() to verify user right syntax
			 * @uses OliCore::updateAccountInfos() to update infos in account table
			 * @return boolean Returns true if the request succeeded, false otherwise
			 */
			public function updateUserRight($userRight, $where = null) {
				$userRight = strtoupper($userRight);
				if($this->verifyUserRight($userRight)) return $this->updateAccountInfos('ACCOUNTS', array('user_right' => $userRight), $where);
				else return false;
			}
		
			/** ---------------------------- */
			/**  User Permissions Functions  */
			/** ---------------------------- */
		
				/*\
				|*|      -[ WORK IN PROGRESS ]-
				|*|  USER PERMISSIONS WILL BE ADDED
				|*|        IN A FUTURE UPDATE
				|*|     (SCHEDULED FOR BETA 1.8)
				\*/
				
				/** --------- */
				/**  General  */
				/** --------- */
				
				/** Get user own permissions */
				public function getUserOwnPermissions($permission) {
					
				}
				
				/** Get user permissions */
				public function getUserPermissions($permission) {
					
				}
				
				/** Is User Permitted */
				public function isUserPermitted($permission) {
					
				}
		
				/** -------------------- */
				/**  Rights Permissions  */
				/** -------------------- */
				
				/** Set Right Permissions */
				public function setRightPermissions($permissions, $userRight) {
					
				}
				
				/** Add Right Permissions */
				public function addRightPermissions($permissions, $userRight) {
					
				}
				
				/** Remove Right Permissions */
				public function removeRightPermissions($permissions, $userRight) {
					
				}
				
				/** Delete Right Permissions */
				public function deleteRightPermissions($userRight) {
					
				}
				
				/** Is Right Permitted */
				public function isRightPermitted($permission) {
					
				}
		
				/** ------------------ */
				/**  User Permissions  */
				/** ------------------ */
				
				/** Set User Permissions */
				public function setUserPermissions($permissions, $userRight) {
					
				}
				
				/** Add User Permissions */
				public function addUserPermissions($permissions, $userRight) {
					
				}
				
				/** Remove User Permissions */
				public function removeUserPermissions($permissions, $userRight) {
					
				}
				
				/** Delete User Permissions */
				public function deleteUserPermissions($userRight) {
					
				}
		
		/** ---------------------------- */
		/**  Auth Key Cookie Management  */
		/** ---------------------------- */
		
			/** ------------------- */
			/**  Create and Delete  */
			/** ------------------- */
			
			/**
			 * Set auth key cookie
			 * 
			 * @param string $authKey Auth key
			 * @param integer $expireDelay Cookie expire delay
			 * 
			 * @uses OliCore::setCookie() to set the auth key cookie
			 * @uses OliCore::$authKeyCookieName to get the auth key cookie name
			 * @uses OliCore::$authKeyCookieDomain to get the auth key cookie domain
			 * @uses OliCore::$authKeyCookieSecure to get the auth key cookie secure parameter
			 * @uses OliCore::$authKeyCookieHttpOnly to get the auth key cookie http only parameter
			 * @return boolean Returns true if the cookies have been created, false otherwise
			 */
			public function setAuthKeyCookie($authKey, $expireDelay) {
				if(!$this->config['user_management']) trigger_error('Sorry, the user management has been disabled.', E_USER_ERROR);
				return $this->setCookie($this->config['auth_key_cookie']['name'], $authKey, $expireDelay, '/', $this->config['auth_key_cookie']['domain'], $this->config['auth_key_cookie']['secure'], $this->config['auth_key_cookie']['http_only']);
			}
			
			/**
			 * Delete cookie
			 * 
			 * @uses OliCore::deleteCookie() to delete the auth key cookie
			 * @uses OliCore::$authKeyCookieName to get the auth key cookie name
			 * @uses OliCore::$authKeyCookieDomain to get the auth key cookie domain
			 * @uses OliCore::$authKeyCookieSecure to get the auth key cookie secure parameter
			 * @uses OliCore::$authKeyCookieHttpOnly to get the auth key cookie http only parameter
			 * @return boolean Returns true if the cookies have been deleted, false otherwise
			 */
			public function deleteAuthKeyCookie() {
				if(!$this->config['user_management']) trigger_error('Sorry, the user management has been disabled.', E_USER_ERROR);
				return $this->deleteCookie($this->config['auth_key_cookie']['name'], '/', $this->config['auth_key_cookie']['domain'], $this->config['auth_key_cookie']['secure'], $this->config['auth_key_cookie']['http_only']);
			}
			
			/** -------------------- */
			/**  Get Auth Key Infos  */
			/** -------------------- */
			
			/**
			 * Get the auth key cookie name
			 * 
			 * @uses OliCore::$authKeyCookieName to get the auth key cookie name
			 * @return string Returns the auth key cookie name
			 */
			public function getAuthKeyCookieName() { return $this->config['auth_key_cookie']['name']; }
			
			/**
			 * Get auth key
			 * 
			 * Get the auth key cookie content
			 * 
			 * @uses OliCore::getCookieContent() to get the auth key cookie content
			 * @uses OliCore::$authKeyCookieName to get the auth key cookie name
			 * @return string Returns the auth key
			 */
			public function getAuthKey() {
				return $this->getCookieContent($this->config['auth_key_cookie']['name']);
			}
			
			/**
			 * Is exist auth key
			 * 
			 * @uses OliCore::isExistCookie()
			 * @return boolean Returns true if the cookie exists, false otherwise
			 */
			public function isExistAuthKey() {
				return $this->isExistCookie($this->config['auth_key_cookie']['name']);
			}
			
			/**
			 * Is empty auth key
			 * 
			 * @uses OliCore::isEmptyCookie()
			 * @return boolean Returns true if the cookie is empty, false otherwise
			 */
			public function isEmptyAuthKey() {
				return $this->isEmptyCookie($this->config['auth_key_cookie']['name']);
			}
			
			/** Verify Auth Key validity */
			public function verifyAuthKey($authKey = null) {
				if(!$this->config['user_management']) trigger_error('Sorry, the user management has been disabled.', E_USER_ERROR);
				
				if(empty($authKey)) $authKey = $this->getAuthKey();
				if(!empty($authKey = hash('sha512', $authKey)) AND $this->isExistAccountInfos('SESSIONS', array('auth_key' => $authKey)) AND $expireDate = $this->getAccountInfos('SESSIONS', 'expire_date', array('auth_key' => $authKey)) AND strtotime($expireDate) >= time()) return true;
				else return false;
			}
			
			/**
			 * Get auth key owner
			 * 
			 * @param string|void $authKey Auth key to get owner of
			 * 
			 * @uses OliCore::getCookieContent() to get the auth key cookie content
			 * @uses OliCore::$authKeyCookieName to get the auth key cookie name
			 * @api
			 * @return string Returns the auth key owner
			 */
			public function getAuthKeyOwner($authKey = null) {
				if(!$this->config['user_management']) trigger_error('Sorry, the user management has been disabled.', E_USER_ERROR);
				if(empty($authKey)) $authKey = $this->getAuthKey();
				
				if($this->verifyAuthKey($authKey)) return $this->getAccountInfos('SESSIONS', 'username', array('auth_key' => hash('sha512', $authKey)));
				else return false;
			}
		
		/** --------------- */
		/**  User Sessions  */
		/** --------------- */
		
			/** --------- */
			/**  General  */
			/** --------- */
			
			/** Init User Session */
			/* public function initUserSession() {
				if(!$this->config['user_management']) trigger_error('Sorry, the user management has been disabled.', E_USER_ERROR);
				else if(!$this->isExistUserID() OR strlen($userID = $this->getUserID()) != $this->config['user_id_length']) {
					// if(!empty($userID)) // Delete invalid session
					
					if($this->insertAccountLine('SESSIONS', array('id' => $this->getLastAccountInfo('SESSIONS', 'id') + 1, 'user_id' => $newUserID = $this->keygen($this->config['user_id_length']), 'ip_address' => $this->getUserIP(), 'update_date' => date('Y-m-d H:i:s')))) {
						// if($setUserIDCookie)
							$this->setUserIDCookie($newUserID, null);
						return $newAuthKey;
					} else return false;
				} else return false;
			} */
			
			/** Update User Session */
			public function updateUserSession($authKey = null) {
				if(!$this->config['user_management']) trigger_error('Sorry, the user management has been disabled.', E_USER_ERROR);
				
				if(empty($authKey)) $authKey = $this->getAuthKey();
				if($this->verifyAuthKey($authKey)) return $this->updateAccountInfos('SESSIONS', array('update_date' => date('Y-m-d H:i:s'), 'last_seen_page' => $this->getUrlParam(0) . implode('/', $this->getUrlParam('params'))), array('auth_key' => hash('sha512', $authKey)));
				else return false;
			}
			
			/** ------------------- */
			/**  Cookie Management  */
			/** ------------------- */
			
			/** Set User ID cookie */
			// public function setUserIDCookie($authKey, $expireDelay = null) {
				// if(!$this->config['user_management']) trigger_error('Sorry, the user management has been disabled.', E_USER_ERROR);
				
				// return $this->setCookie($this->config['user_id_cookie']['name'], $authKey, $expireDelay, '/', $this->config['user_id_cookie']['domain'], $this->config['user_id_cookie']['secure'], $this->config['user_id_cookie']['http_only']);
			// }
			
			/** Delete User ID cookie */
			// public function deleteUserIDCookie() {
				// if(!$this->config['user_management']) trigger_error('Sorry, the user management has been disabled.', E_USER_ERROR);
				
				// return $this->deleteCookie($this->config['user_id_cookie']['name'], '/', $this->config['user_id_cookie']['domain'], $this->config['user_id_cookie']['secure'], $this->config['user_id_cookie']['http_only']);
			// }
			
			/** -------------- */
			/**  Cookie Infos  */
			/** -------------- */
			
			/** Get User ID cookie name */
			// public function getUserIDCookieName() { return $this->config['user_id_cookie']['name']; }
			
			/** User ID cookie content */
			// public function getUserID() { return $this->getCookieContent($this->config['user_id_cookie']['name']); }
			// public function isExistUserID() { return $this->isExistCookie($this->config['user_id_cookie']['name']); }
			// public function isEmptyUserID() { return $this->isEmptyCookie($this->config['user_id_cookie']['name']); }
		
		/** ---------------- */
		/**  Login Requests  */
		/** ---------------- */
		
			/** ------------------------------- */
			/**  Requests Management Functions  */
			/** ------------------------------- */
			
			/**
			 * Get the requests expire delay
			 * 
			 * @uses OliCore::$requestsExpireDelay to get the the requests expire delay
			 * @return string Returns the the requests expire delay
			 */
			public function getRequestsExpireDelay() { return $this->config['request_expire_delay']; }
			
			/**
			 * Create a new request
			 * 
			 * @param string $username User to link the request to
			 * @param string $action Request action to set to
			 * 
			 * @uses OliCore::getLastAccountInfo() to get last info from account table
			 * @uses OliCore::keygen() to generate a keygen
			 * @uses OliCore::$requestsExpireDelay to get the requests expire delay
			 * @uses OliCore::insertAccountLine() to insert line in account table
			 * @return string Returns the activation key if the request succeed, false otherwise
			 */
			public function createRequest($username, $action, &$requestTime = null) {
				if(!$this->config['user_management']) trigger_error('Sorry, the user management has been disabled.', E_USER_ERROR);
				$requestsMatches['id'] = $this->getLastAccountInfo('REQUESTS', 'id') + 1;
				$requestsMatches['username'] = $username;
				$requestsMatches['activate_key'] = hash('sha512', $activateKey = $this->keygen(6, false, true, true));
				$requestsMatches['action'] = $action;
				$requestsMatches['request_date'] = date('Y-m-d H:i:s', $requestTime = time());
				$requestsMatches['expire_date'] = date('Y-m-d H:i:s', $requestTime + $this->config['request_expire_delay']);
				$this->insertAccountLine('REQUESTS', $requestsMatches);
				
				return $activateKey;
			}
			
			/** -------------------- */
			/**  Register Functions  */
			/** -------------------- */
			
			/**
			 * Is register verification enabled
			 * 
			 * @uses OliCore::$registerVerification to get the register verification status
			 * @return string Returns true if the register verification is enabled, false otherwise
			 */
			public function getRegisterVerificationStatus() { return $this->config['register_verification']; }
			
			/**
			 * Register a new account
			 * 
			 * @param string $username Username to use
			 * @param string $password Password to set to
			 * @param string $email Request action to set to
			 * 
			 * @uses OliCore::$accountsManagementStatus to get the requests expire delay
			 * @uses OliCore::isExistAccountInfos() to get if infos exists info from account table
			 * @uses OliCore::getUserRightLevel() to get user right level
			 * @uses OliCore::translateUserRight() to translate user right
			 * @uses OliCore::getAccountInfos() to get infos from account table
			 * @uses OliCore::deleteFullAccount() to delete full account
			 * @uses OliCore::deleteAccountLines() to delete lines from account table
			 * @uses OliCore::getLastAccountInfo() to get last info from account table
			 * @uses OliCore::hashPassword() to get hashed password
			 * @uses OliCore::$defaultUserRight to get default user right
			 * @uses OliCore::insertAccountLine() to insert line in account table
			 * @uses OliCore::$registerVerification to get the requests expire delay
			 * @uses OliCore::createRequest() to create a new request
			 * @uses OliCore::getUrlParam() to get url parameters
			 * @uses OliCore::$requestsExpireDelay to get the requests expire delay
			 * @uses OliCore::getSetting() to get setting
			 * @return string Returns true if the account is created, false otherwise
			 */
			public function registerAccount($username, $password, $email, $subject = null, $message = null, $headers = null) {
				if(!$this->config['user_management']) trigger_error('Sorry, the user management has been disabled.', E_USER_ERROR);
				else if(!$this->config['allow_register']) trigger_error('Sorry, the registering has been disabled.', E_USER_ERROR);
				else if(empty($username)) return false;
				else if(empty($password)) return false;
				else if(empty($email)) return false;
				else {
					if($this->isExistAccountInfos('ACCOUNTS', array('username' => $username), false) AND $this->getUserRightLevel($username) == $this->translateUserRight('NEW-USER') AND (($this->isExistAccountInfos('REQUESTS', array('username' => $username), false) AND strtotime($this->getAccountInfos('REQUESTS', 'expire_date', array('username' => $username))) < time()) OR !$this->isExistAccountInfos('REQUESTS', array('username' => $username), false)))
						$this->deleteFullAccount(array('username' => $username));
					else if($this->isExistAccountInfos('ACCOUNTS', array('email' => $email), false) AND $this->getUserRightLevel(array('email' => $email)) == $this->translateUserRight('NEW-USER') AND (($this->isExistAccountInfos('REQUESTS', array('username' => $this->getAccountInfos('ACCOUNTS', 'username', array('email' => $email))), false) AND strtotime($this->getAccountInfos('REQUESTS', 'expire_date', array('username' => $this->getAccountInfos('ACCOUNTS', 'username', array('email' => $email))))) < time()) OR !$this->isExistAccountInfos('REQUESTS', array('username' => $this->getAccountInfos('ACCOUNTS', 'username', array('email' => $email))), false)))
						$this->deleteFullAccount(array('email' => $email));
					
					if(!$this->isExistAccountInfos('ACCOUNTS', array('username' => $username), false)
					AND !$this->isExistAccountInfos('ACCOUNTS', array('email' => $email), false)) {
						if($this->isExistAccountInfos('REQUESTS', $username, false) OR $this->isExistAccountInfos('REQUESTS', $username, false) OR $this->isExistAccountInfos('REQUESTS', $username, false))
							$this->deleteAccountLines('REQUESTS', array('username' => $this->getAccountInfos('INFOS', $username, false)));
						
						$accountsMatches['id'] = $this->getLastAccountInfo('ACCOUNTS', 'id') + 1;
						$accountsMatches['username'] = $username;
						$accountsMatches['password'] = $this->hashPassword($password);
						$accountsMatches['email'] = $email;
						$accountsMatches['register_date'] = date('Y-m-d H:i:s');
						$accountsMatches['user_right'] = $this->config['default_user_right'];
						$this->insertAccountLine('ACCOUNTS', $accountsMatches);
						
						$infosMatches['id'] = $this->getLastAccountInfo('INFOS', 'id') + 1;
						$infosMatches['username'] = $username;
						$this->insertAccountLine('INFOS', $infosMatches);
					
						if(empty($headers)) {
							$headers = 'From: noreply@' . $this->getUrlParam('domain') . "\r\n";
							$headers .= 'MIME-Version: 1.0' . "\r\n";
							$headers .= 'Content-type: text/html; charset=iso-8859-1';
						}
						
						if($this->config['register_verification']) {
							$activateKey = $this->createRequest($username, 'activate');
							
							if(empty($subject)) $subject = 'Activate your account';
							if(empty($message)) {
								$message = '<b>Hey ' . $username . '</b>, <br /> <br />';
								$message .= '<b>One more step!</b> <br />';
								$message .= 'You just need to activate your account! Visit <a href="' . $this->getUrlParam(0) . 'login/activate/' . $activateKey . '">this page to activate it</a> (' . $this->getUrlParam(0) . 'login/activate/' . $activateKey . ') <br />';
								$message .= 'You have ' . ($this->config['request_expire_delay'] /3600 /24) . ' ' . ($this->config['request_expire_delay'] > 1 ? 'days' : 'day') . ' to confirm the request <br /> <br />';
								$message .= 'If you don\'t activate your account, it will be suspended after this delay (then deleted if someone register with the same username) <br /> <br />';
								$message .= 'You got this mail from <a href="' . $this->getUrlParam(0) . '">' . $this->getSetting('name') . '</a> <br />';
								$message .= '<a href="' . $this->getOliInfos('website_url') . '">Powered by Oli</a>';
							}
							
							$mailResult = mail($email, $subject ?: 'Activate your account', utf8_decode($message), $headers);
						}
						else {
							if(empty($subject)) $subject = 'Your account have been created';
							if(empty($message)) {
								$message = '<b>Hey ' . $username . '</b>, <br /> <br />';
								$message .= '<b>Yay! Your account have been successfully created</b> <br />';
								$message .= 'You can <a href="' . $this->getUrlParam(0) . 'login/' . $activateKey . '">connect to it on this page</a> (' . $this->getUrlParam(0) . 'login/' . $activateKey . ') <br />';
								$message .= 'You have ' . ($this->config['request_expire_delay'] /3600 /24) . ' ' . ($this->config['request_expire_delay'] > 1 ? 'days' : 'day') . ' to confirm the request <br /> <br />';
								$message .= 'If you don\'t activate your account, it will be suspended after this delay (then deleted if someone register with the same username) <br /> <br />';
								$message .= 'You got this mail from <a href="' . $this->getUrlParam(0) . '">' . $this->getSetting('name') . '</a> <br />';
								$message .= '<a href="' . $this->getOliInfos('website_url') . '">Powered by Oli</a>';
							}
							
							$mailResult = mail($email, $subject, utf8_decode($message), $headers);
						}
						
						if($mailResult) return true;
						else {
							$this->deleteAccountLines('REQUESTS', array('activate_key' => hash('sha512', $activateKey)));
							$this->deleteFullAccount($username);
							return false;
						}
					}
					else return false; 
				}
			}
			
			/** ----------------- */
			/**  Login Functions  */
			/** ----------------- */
			
			/**
			 * Verify login informations
			 * 
			 * @param string $username Username to check
			 * @param string $password Password to check
			 * 
			 * @uses OliCore::isExistAccountInfos() to get if infos exists in account table
			 * @uses OliCore::getAccountInfos() to get infos from account table
			 * @api
			 * @return boolean Returns true if login informations are valid, false otherwise
			 */
			public function verifyLogin($username, $password) {
				if(!$this->config['user_management']) trigger_error('Sorry, the user management has been disabled.', E_USER_ERROR);
				if($userPassword = $this->getAccountInfos('ACCOUNTS', 'password', array('username' => $username), false) OR $userPassword = $this->getAccountInfos('ACCOUNTS', 'password', array('email' => $username), false))
					return password_verify($password, $userPassword);
				else return false;
			}
			
			/**
			 * Login account
			 * 
			 * @param string $username Username of the user to log
			 * @param string $password Password to use
			 * @param integer|void $expireDelay Session expire delay in seconds (default: 1 day)
			 * @param boolean|void $setAuthKeyCookie Set the auth key cookie or not (default: true)
			 * 
			 * @uses OliCore::isExistAccountInfos() to get if infos exists in account table
			 * @uses OliCore::getAccountInfos() to get infos from account table
			 * @api
			 * @return boolean Returns true if login succeed, false otherwise
			 */
			public function loginAccount($username, $password, $expireDelay = null, $setAuthKeyCookie = true) {
				if(!$this->config['user_management']) trigger_error('Sorry, the user management has been disabled.', E_USER_ERROR);
				else if(!$this->config['allow_login']) trigger_error('Sorry, the logging in has been disabled.', E_USER_ERROR);
				else if($this->verifyLogin($username, $password)) {
					$username = $this->getAccountInfos('ACCOUNTS', 'username', array('email' => $username), false) ?: $this->getAccountInfos('ACCOUNTS', 'username', $username, false);
					
					if($this->needsRehashPassword($this->getAccountInfos('ACCOUNTS', 'password', $username)))
						$this->updateAccountInfos('ACCOUNTS', array('password' => $this->hashPassword($password)), $username);
					
					if($this->getUserRightLevel($username) >= $this->translateUserRight('USER')) {
						$newAuthKey = $this->keygen($this->config['auth_key_length']);
						if(empty($expireDelay) OR $expireDelay <= 0) $expireDelay = 24*3600;
						
						$matches['id'] = $this->getLastAccountInfo('SESSIONS', 'id') + 1;
						$matches['username'] = $username;
						$matches['auth_key'] = hash('sha512', $newAuthKey);
						$matches['ip_address'] = $this->getUserIP();
						$matches['login_date'] = date('Y-m-d H:i:s');
						$matches['expire_date'] = date('Y-m-d H:i:s', time() + $expireDelay);
						$matches['update_date'] = date('Y-m-d H:i:s');
						
						if($this->insertAccountLine('SESSIONS', $matches)) {
							if($setAuthKeyCookie) $this->setAuthKeyCookie($newAuthKey, $expireDelay);
							return $newAuthKey;
						}
						else return false;
					}
					else return false;
				}
				else return false;
			}
			
			/** ------------------ */
			/**  Logout Functions  */
			/** ------------------ */
			
			/**
			 * Logout account
			 * 
			 * This also delete auth key cookie
			 * 
			 * @uses OliCore::isExistAuthKey()
			 * @uses OliCore::deleteLinesMySQL() to delete lines from account table
			 * @uses OliCore::deleteAuthKeyCookie() to delete the auth key cookie
			 * @return boolean
			 */
			public function logoutAccount() {
				if(!$this->config['user_management']) trigger_error('Sorry, the user management has been disabled.', E_USER_ERROR);
				else if($this->isExistAuthKey()) {
					if($deleteResult = $this->deleteLinesMySQL($this->config['accounts_tables']['sessions'], array('auth_key' => hash('sha512', $this->getAuthKey())))) $deleteResult = $this->deleteAuthKeyCookie();
					return $deleteResult ? true : false;
				}
				else return false;
			}
			
			/** -------------------- */
			/**  Users Restrictions  */
			/** -------------------- */
			
			/**
			 * Get prohibited usernames
			 * 
			 * @uses OliCore::$prohibitedUsernames to get prohibited usernames
			 * @return array Returns prohibited usernames
			 */
			public function getProhibitedUsernames() {
				return $this->config['prohibited_usernames'];
			}
			
			/**
			 * Is this a prohibited username?
			 * 
			 * @param string $username Username to check
			 * 
			 * @uses OliCore::$prohibitedUsernames to get prohibited usernames
			 * @return boolean Returns true if the username is prohibited, false otherwise
			 */
			public function isProhibitedUsername($username) {
				return in_array($username, $this->config['prohibited_usernames']);
			}
		
		/** --------------- */
		/**  Hash Password  */
		/** --------------- */
		
		/**
		 * Hash a password
		 * 
		 * @param string $username Username to check
		 * 
		 * @uses OliCore::$hashSalt to get the hash salt parameter
		 * @uses OliCore::$hashCost to get the hash cost parameter
		 * @uses OliCore::$hashAlgorithm to get the hash algorithm parameter
		 * @return string Returns the password hash
		 */
		public function hashPassword($password) {
			if(!empty($this->config['hash']['salt'])) $hashOptions['salt'] = $this->config['hash']['salt'];
			if(!empty($this->config['hash']['cost'])) $hashOptions['cost'] = $this->config['hash']['cost'];
			return password_hash($password, $this->config['hash']['algorithm'], $hashOptions);
		}
		
		public function needsRehashPassword($password) {
			if(!empty($this->config['hash']['salt'])) $hashOptions['salt'] = $this->config['hash']['salt'];
			if(!empty($this->config['hash']['cost'])) $hashOptions['cost'] = $this->config['hash']['cost'];
			return password_needs_rehash($password, $this->config['hash']['algorithm'], $hashOptions);
		}

}

}
?>