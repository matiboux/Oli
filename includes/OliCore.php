<?php
/*\
|*|  ---------------------------------
|*|  --- [  Oli - PHP Framework  ] ---
|*|  --- [  Version BETA: 2.0.0  ] ---
|*|  ---------------------------------
|*|  
|*|  Oli is an open source PHP framework designed to help you create your website.
|*|  Oli Github repository: https://github.com/matiboux/Oli/
|*|  
|*|  Creator & Developer: Matiboux (Mathieu Guérin)
|*|   → Github: @matiboux – https://github.com/matiboux/
|*|   → Twitter: @Matiboux – https://twitter.com/Matiboux/
|*|   → Telegram: @Matiboux – https://t.me/Matiboux/
|*|   → Email: matiboux@gmail.com
|*|  
|*|  For more info, please read the README.md file.
|*|  You can find it in the project repository (see the Github link above).
|*|  
|*|  --- --- ---
|*|  
|*|  Copyright (C) 2015-2018 Matiboux (Mathieu Guérin)
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
|*|    along with this program.
|*|    If not, see <http://www.gnu.org/licenses/>.
|*|  
|*|  You'll find a copy of the GNU AGPL v3 license in the LICENSE file.
|*|  
|*|  --- --- ---
|*|  
|*|  Releases date:
|*|  - PRE-DEV: November 15, 2014
|*|  - ALPHA: February 6, 2015
|*|  - BETA: July 2015
|*|    * No info on previous releases
|*|    * BETA 1.5.0: August 17, 2015
|*|           1.5.1: August 21, 2015
|*|           1.5.2: August 25, 2015
|*|           1.5.3: August 26, 2015
|*|           1.5.5: November 20, 2015
|*|    * BETA 1.6.0: December 6, 2015
|*|           1.6.2: December 9, 2015
|*|           1.6.3: January 10, 2016
|*|           1.6.4: February 10, 2016
|*|           1.6.6: June 2, 2016
|*|    * BETA 1.7.0: August 11, 2016 – [ Github repository created! ]
|*|           1.7.1: February 19, 2017
|*|           1.7.2: February 22, 2017
|*|    * BETA 1.8.0: June 30, 2017
|*|           1.8.1: July 13, 2017
|*|           1.8.2: [Unfinished.]
|*|    * BETA 2.0.0: [WIP]
\*/

/*\
|*|  ╒════════════════════════╕
|*|  │ :: TABLE OF CONTENT :: │
|*|  ╞════════════════════════╛
|*|  │
|*|  ├ I. Variables
|*|  ├ II. Magic Methods
|*|  ├ III. Oli
|*|  │ ├ 1. Oli Infos
|*|  │ ├ 2. Oli Security Code
|*|  │ └ 3. Tools
|*|  │
|*|  ├ IV. Configuration
|*|  │ ├ 1. MySQL
|*|  │ ├ 2. General 
|*|  │ └ 3. Addons
|*|  │   ├ A. Management
|*|  │   └ B. Infos
|*|  │
|*|  ├ V. MySQL
|*|  │ ├ 1. Regular MySQL Functions
|*|  │ │ ├ A. Status
|*|  │ │ ├ B. Read
|*|  │ │ ├ C. Write
|*|  │ │ └ D. Database Edits
|*|  │ │   ├ a. Tables
|*|  │ │   └ b. Columns
|*|  │ └ 2. Legacy (Read) MySQL Functions
|*|  │
|*|  ├ VI. General
|*|  │ ├ 1. Load Website
|*|  │ ├ 2. Settings
|*|  │ ├ 3. Custom Content
|*|  │ ├ 4. Translations & Text
|*|  │ │ ├ A. Read
|*|  │ │ ├ B. Write
|*|  │ │ └ C. Print
|*|  │ ├ 5. HTTP Tools
|*|  │ │ ├ A. Content Type
|*|  │ │ ├ B. Cookie Management
|*|  │ │ │ ├ a. Read Functions
|*|  │ │ │ └ b. Write Functions
|*|  │ │ ├ C. _POST vars
|*|  │ │ │ ├ a. Read Functions
|*|  │ │ │ └ b. Write Functions
|*|  │ │ └ D. Mail Management
|*|  │ ├ 6. HTML Tools
|*|  │ │ ├ A. File Loaders
|*|  │ │ └ B. File Minimizers
|*|  │ ├ 7. Url Functions
|*|  │ └ 8. Utility Tools
|*|  │   ├ A. Templates
|*|  │   ├ B. Generators
|*|  │   │ ├ a. UUID
|*|  │   │ └ b. Misc
|*|  │   ├ C. Data Conversion
|*|  │   ├ D. Date & Time
|*|  │   └ E. Client Infos
|*|  │
|*|  └ VII. Accounts
|*|    ├ 1. Status
|*|    ├ 2. MySQL
|*|    │ ├ A. Read
|*|    │ ├ B. Read
|*|    │ ├ C. Write
|*|    │ └ D. Client Infos
|*|    ├ 3. User Rights & Permissions
|*|    │ ├ A. User Rights
|*|    │ └ B. User Permissions
|*|    │   ├ a. General
|*|    │   ├ b. Rights Permissions
|*|    │   ├ c. User Permissions
|*|    │   ├ a. General
|*|    │   └ b. Write Functions
|*|    ├ -. Auth Key Cookie //*!
|*|    │ ├ A. Create & Delete
|*|    │ └ B. Infos
|*|    ├ 5. User Sessions
|*|    │ ├ A. General
|*|    │ ├ B. Cookie
|*|    │ │ ├ a. Management
|*|    │ │ └ b. Infos
|*|    │ └ B. Infos
|*|    ├ 6. User Avatar
|*|    └ 7. Hash Password
\*/

namespace Oli {

abstract class OliCore {

	/** -------------- */
	/**  I. Variables  */
	/** -------------- */
	
	/** Read-only variables */
	private $readOnlyVars = [
		'initTimestamp',
		'oliInfos', 'addonsInfos',
		// 'db', 'dbError',
		'fileNameParam', 'contentStatus'];
	
	/** Components infos */
	private $initTimestamp = null; // (PUBLIC READONLY)
	private $oliInfos = []; // Oli Infos (SPECIAL PUBLIC READONLY)
	private $addonsInfos = []; // Addons Infos (PUBLIC READONLY)
	
	/** Databases Management */
	private $dbs = []; // SQL Wrappers
	private $defaultdb = null; // SQL Wrappers
	// private $db = null; // MySQL PDO Object (PUBLIC READONLY)
	// private $dbError = null; // MySQL PDO Error (PUBLIC READONLY)
	
	/** Content Management */
	private $fileNameParam = null; // Define Url Param #0 (PUBLIC READONLY)
	private $contentStatus = null; // Content Status (found, not found, forbidden...) (PUBLIC READONLY)
	
	/** Page Settings */
	private $contentType = null;
	private $charset = null;
	private $contentTypeBeenForced = false;
	private $htmlLoaderList = [];
	
	/** Post Vars Cookie */
	private $postVarsProtection = false;
	
	/** Data Cache */
	private $cache = [];
	
	
	/** *** *** */
	
	/** ------------------- */
	/**  II. Magic Methods  */
	/** ------------------- */
	
	/**
	 * OliCore Class Contruct function
	 * 
	 * @version BETA
	 * @updated BETA-2.0.0
	 * @return void
	 */
	public function __construct($initTimestamp = null) {
		/** Primary constants - Should have been defined in /load.php */
		if(!defined('ABSPATH')) die('Oli Error: ABSPATH is not defined.');
		if(!defined('ADDONSPATH')) die('Oli Error: ADDONSPATH is not defined.');
		if(!defined('INCLUDESPATH')) define('INCLUDESPATH', __DIR__ . '/');
		if(!defined('CONTENTPATH')) define('INCLUDESPATH', ABSPATH . 'content/');
		
		/** Secondary constants */
		if(!defined('OLIADMINPATH')) define('OLIADMINPATH', INCLUDESPATH . 'admin/');
		if(!defined('OLISETUPPATH')) define('OLISETUPPATH', INCLUDESPATH . 'setup/');
		if(!defined('OLILOGINPATH')) define('OLILOGINPATH', INCLUDESPATH . 'login/');
		if(!defined('OLISCRIPTPATH')) define('OLISCRIPTPATH', INCLUDESPATH . 'scripts/');
		
		/** Load Config */
		Config::loadConfig($this);
		if(file_exists(ABSPATH . '.oliurl')) {
			$oliUrl = file_get_contents(ABSPATH . '.oliurl');
			if(!empty($oliUrl) AND preg_match('/^(?:[a-z0-9-]+\.)+[a-z.]+(?:\/[^/]+)*\/$/i', $oliUrl) AND !empty(Config::$config['settings']) AND $oliUrl != Config::$config['settings']['url']) {
				$this->updateConfig(array('settings' => array_merge(Config::$config['settings'], array('url' => $oliUrl))), true);
				Config::$config['settings']['url'] = $oliUrl;
			}
		}
		
		/** User Content constants */
		if(!defined('ASSETSPATH')) define('ASSETSPATH', CONTENTPATH . 'assets/');
		if(!defined('MEDIAPATH')) define('MEDIAPATH', CONTENTPATH . 'media/');
		if(!defined('THEMEPATH')) define('THEMEPATH', CONTENTPATH . 'theme/');
		if(!defined('SCRIPTSPATH')) define('SCRIPTSPATH', CONTENTPATH . 'scripts/');
		if(!defined('TEMPLATESPATH')) define('TEMPLATESPATH', CONTENTPATH . 'templates/');
		
		// Framework Initialization
		$this->initTimestamp = $initTimestamp ?: microtime(true);
		$this->setContentType('DEFAULT', 'utf-8');
		
		// Check for debug stutus override
		if(@Config::$config['debug'] === false AND @$_GET['oli-debug'] == $this->getOliSecurityCode())
			Config::$config['debug'] = true;
		
		// Error reporting configuration
		error_reporting(@Config::$config['debug'] === true ? E_ALL : E_ALL & ~E_NOTICE);
	}
	
	/**
	 * OliCore Class Destruct function
	 * 
	 * @version BETA
	 * @updated BETA-2.0.0
	 * @return void
	 */
	public function __destruct() {
		$this->loadEndHtmlFiles();
	}
	
	/**
	 * OliCore Class Read-only variables management
	 * 
	 * @version BETA
	 * @updated BETA-2.0.0
	 * @return mixed Returns the requested variable value if is allowed to read, null otherwise.
	 */
	public function __get($whatVar) {
		if($whatVar == 'config') return Config::$config;
		else if(in_array($whatVar, $this->readOnlyVars)) {
			if($whatVar == 'oliInfos') return $this->getOliInfos();
			else return $this->$whatVar;
		} else return null;
    }
	
	/**
	 * OliCore Class Is Set variables management
	 * This fix the empty() false negative issue on inaccessible variables.
	 * 
	 * @version BETA
	 * @updated BETA-2.0.0
	 * @return mixed Returns true if the requested variable isn't empty and if is allowed to read, null otherwise.
	 */
    public function __isset($whatVar) {
        if(in_array($whatVar, $this->readOnlyVars)) return empty($this->$whatVar) === false;
        else return null;
    }
	
	/**
	 * OliCore Class to String function
	 * 
	 * @version BETA-1.8.1
	 * @updated BETA-2.0.0
	 * @return string Returns Oli Infos.
	 */
	public function __toString() {
		return 'Powered by ' . $this->getOliInfos('name') . ', ' . $this->getOliInfos('short_description') . ' (v. ' . $this->getOliInfos('version') . ')';
	}
	
	/** *** *** */
	
	/** ---------- */
	/**  III. Oli  */
	/** ---------- */
	
		/** ------------------- */
		/**  III. 1. Oli Infos  */
		/** ------------------- */
		
		/**
		 * Get Oli Infos
		 * 
		 * @version BETA
		 * @updated BETA-2.0.0
		 * @return string Returns a short description of Oli.
		 */
		public function getOliInfos($whatInfo = null) {
			if(empty($this->oliInfos)) $this->oliInfos = file_exists(INCLUDESPATH . 'oli-infos.json') ? json_decode(file_get_contents(INCLUDESPATH . 'oli-infos.json'), true) : null; // Load Oli Infos if not already
			return !empty($whatInfo) ? $this->oliInfos[$whatInfo] : $this->oliInfos;
		}
		
		/** Get Team Infos */
		public function getTeamInfos($who = null, $whatInfo = null) {
			if(!empty($who)) {
				foreach($this->oliInfos['team'] as $eachMember) {
					if($eachMember['name'] == $who OR in_array($who, !is_array($eachMember['nicknames']) ? [$eachMember['nicknames']] : $eachMember['nicknames'])) {
						if(!empty($whatInfo)) return $eachMember[$whatInfo];
						else return $eachMember;
					}
				}
			} else return $this->oliInfos['team'];
		}
	
		/** --------------------------- */
		/**  III. 2. Oli Security Code  */
		/** --------------------------- */
		
		/**
		 * Get Oli Security Code
		 * 
		 * @version BETA-2.0.0
		 * @updated BETA-2.0.0
		 * @return string Returns Oli Security Code.
		 */
		public function getOliSecurityCode() {
			return $this->refreshOliSecurityCode() ?: file_get_contents(ABSPATH . '.olisc');
		}
		/** * @alias OliCore::getOliSecurityCode() */
		public function getOliSC() { return $this->getOliSecurityCode(); }
		
		/**
		 * Refresh Oli Security Code
		 * 
		 * @version BETA-2.0.0
		 * @updated BETA-2.0.0
		 * @return string|boolean Returns Oli Security Code if it was updated, false otherwise.
		 */
		public function refreshOliSecurityCode() {
			if(!file_exists(ABSPATH . '.olisc') OR time() > filemtime(ABSPATH . '.olisc') + 3600*2 OR empty(file_get_contents(ABSPATH . '.olisc'))) {
				$handle = fopen(ABSPATH . '.olisc', 'w');
				fwrite($handle, $olisc = $this->keygen(6, true, false, true));
				fclose($handle);
				return $olisc;
			} else return false;
		}
		/** * @alias OliCore::refreshOliSecurityCode() */
		public function refreshOliSC() { return $this->refreshOliSecurityCode(); }
		
		/** -------------- */
		/**  III. 3. Tools  */
		/** -------------- */
		
		/** Get Execution Time */
		public function getExecutionTime($fromRequest = false) {
			if($fromRequest) return microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
			else return microtime(true) - Config::$config['init_timestamp'];
		}
		public function getExecutionDelay($fromRequest = false) { return $this->getExecutionTime($fromRequest); }
		public function getExecuteDelay($fromRequest = false) { return $this->getExecutionTime($fromRequest); }
	
	/** *** *** */
	
	/** ------------------- */
	/**  IV. Configuration  */
	/** ------------------- */
		
		/** -------------- */
		/**  IV. 1. MySQL  */
		/** -------------- */
		
		/**
		 * MySQL Connection Setup
		 * 
		 * @version BETA
		 * @updated BETA-2.0.0
		 * @return boolean Returns true if succeeded.
		 */
		public function setupSQL($args)
		{
			if (!Config::$config['allow_mysql'] || !is_array($args)) // OR empty($database))
				return false;
			
			$dbms = array_pull($args, 'dbms');
			$dbname = array_pull($args, 'dbname');
			if ($dbname === null)
				$dbname = array_pull($args, 'database');
			
			$this->dbs[$dbname] = new SQLWrapper($dbms, $dbname, $args);
			
			if ($this->defaultdb === null)
				$this->defaultdb = $dbname;
			
			return true;
			
			// if(Config::$config['allow_mysql'] AND !empty($database)) {
				// try {
					// $this->db = new \PDO('mysql:dbname=' . $database . ';host=' . ($hostname ?: 'localhost') . ';charset=' . ($charset ?: 'utf8'), $username ?: 'root', $password ?: '');
					// $this->db = new \PDO('pgsql:dbname=' . $database . ';host=' . ($hostname ?: 'localhost') . ';port=' . ($port ?: '5432'), $username ?: 'root', $password ?: '');
				// } catch(\PDOException $e) {
					// $this->dbError = $e->getMessage();
					// return false;
				// }
			// } else return false;
		}
		
		/**
		 * MySQL Connection Reset
		 * 
		 * @version BETA-1.8.0
		 * @updated BETA-2.0.0
		 * @return boolean Returns true if succeeded.
		 */
		public function resetMySQL() {
			$this->dbs = [];
			$this->defaultdb = null;
			
			// $this->db = null;
			// $this->dbError = null;
		}
	
		/** ---------------- */
		/**  IV. 2. General  */
		/** ---------------- */
	
		/** Set Settings Tables */
		public function setSettingsTables($tables) {
			Config::$config['settings_tables'] = $tables = !is_array($tables) ? [$tables] : $tables;
			$hasArray = false;
			foreach($tables as $eachTableGroup) {
				if(is_array($eachTableGroup) OR $hasArray) {
					$hasArray = true;
					Config::$config['settings_tables'] = $eachTableGroup;
					$this->getUrlParam('base', $hasUsedHttpHostBase);
					
					if(!$hasUsedHttpHostBase) break;
				}
			}
			
			$i = 1;
			while($i <= strlen(Config::$config['media_path']) AND $i <= strlen(Config::$config['theme_path']) AND substr(Config::$config['media_path'], 0, $i) == substr(Config::$config['theme_path'], 0, $i)) {
				$contentPath = substr(Config::$config['media_path'], 0, $i);
				$i++;
			}
			define('CONTENTPATH', ABSPATH . ($contentPath ?: 'content/'));
			define('MEDIAPATH', Config::$config['media_path'] ? ABSPATH . Config::$config['media_path'] : CONTENTPATH . 'media/');
			define('THEMEPATH', Config::$config['theme_path'] ? ABSPATH . Config::$config['theme_path'] : CONTENTPATH . 'theme/');
		}
		
		/** Set Common Files Path */
		public function setCommonPath($path) {
			if(!empty($path)) {
				Config::$config['common_path'] = $path;
				if(!defined('COMMONPATH')) define('COMMONPATH', ABSPATH . $path);
			}
		}
		
		/** --------------- */
		/**  IV. 3. Addons  */
		/** --------------- */
		
			/** ---------------------- */
			/**  IV. 3. A. Management  */
			/** ---------------------- */
			
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
			
			/** ----------------- */
			/**  IV. 5. B. Infos  */
			/** ----------------- */
			
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
	
	/** *** *** */
	
	/** ---------- */
	/**  V. MySQL  */
	/** ---------- */
	
		/** ------------------------------- */
		/**  V. 1. Regular MySQL Functions  */
		/** ------------------------------- */
	
			/** ----------------- */
			/**  V. 1. A. Status  */
			/** ----------------- */
			
			/**
			 * Is setup MySQL connection
			 * 
			 * @version BETA
			 * @updated BETA-2.0.0
			 * @return boolean Returns the MySQL connection status
			 */
			public function isSetupMySQL() {
				return $this->getDB()->isSetupSQL();
				// return isset($this->db);
			}
			
			/**
			 * Get raw MySQL PDO Object
			 * 
			 * @version BETA
			 * @deprecated BETA OliCore::$db can be directly accessed
			 * @updated BETA-1.8.0
			 * @removed BETA-1.8.0
			 * @return object Returns current MySQL PDO object
			 */ // public function getRawMySQL() {}
			
			/**
			 * Get raw MySQL PDO Object
			 * 
			 * @version BETA
			 * @deprecated BETA OliCore::$db can be directly accessed
			 * @updated BETA-1.8.0
			 * @removed BETA-1.8.0
			 * @return object Returns current MySQL PDO object
			 */
			 public function getDB($dbname = null)
			 {
				 return @$this->dbs[!empty($dbname) ? $dbname : $this->defaultdb];
			 }
		
			/** --------------- */
			/**  V. 2. B. Read  */
			/** --------------- */
			
			/**
			 * Run a raw MySQL Query
			 * 
			 * @version BETA-1.8.0
			 * @updated BETA-2.0.0
			 * @return array|boolean Returns the query result content or true if succeeded.
			 */
			// public function runQueryMySQL($query, $fetchStyle = true) {
			public function runQueryMySQL(...$args) {
				return $this->getDB()->runQuerySQL(...$args);
				/*
				if(!$this->isSetupMySQL()) return null;
				else {
					$query = $this->db->prepare($query);
					if($query->execute()) return $query->fetchAll(!is_bool($fetchStyle) ? $fetchStyle : ($fetchStyle ? \PDO::FETCH_ASSOC : null));
					else {
						$this->dbError = $query->errorInfo();
						return false;
					}
				}
				*/
			}
			
			/**
			 * Get data from MySQL
			 * 
			 * @version BETA
			 * @updated BETA-2.0.0
			 * @return array|boolean|void Returns data from the requested table if succeeded.
			 */
			// public function getDataMySQL($table, ...$params) {
			public function getDataMySQL(...$args) {
				return $this->getDB()->getDataSQL(...$args);
				/*
				if(!$this->isSetupMySQL()) return null;
				else {
					// Select rows
					if(!empty($params[0])) {
						if(is_array($params[0]) AND preg_grep("/^\S+$/", $params[0]) == $params[0]) $select = implode(', ', array_shift($params));
						else if(strpos($params[0], ' ') === false) $select = array_shift($params);
					}
					if(empty($select)) $select = '*';
					
					// Fetch Style
					if(!empty($params[count($params) - 1]) AND is_integer($params[count($params) - 1])) $fetchStyle = implode(', ', array_pop($params));
					else $fetchStyle = true;
					
					// Custom parameters
					$queryParams = null;
					if(!empty($params)) {
						foreach($params as $eachKey => $eachParam) {
							if(!empty($eachParam)) $queryParams .= ' ' . $eachParam;
						}
					}
					
					return $this->runQueryMySQL('SELECT ' . $select . ' FROM ' . $table . $queryParams, $fetchStyle);
				}
				*/
			}
			
			/**
			 * Get first info from table
			 * 
			 * @version BETA
			 * @updated BETA-2.0.0
			 * @return array|null Returns first info from specified table.
			 */
			// public function getFirstInfoMySQL($table, $whatVar = null, $sortBy = null, $rawResult = false) {
			public function getFirstInfoMySQL(...$args) {
				return $this->getDB()->getFirstInfoSQL(...$args);
				/*
				if(!$this->isSetupMySQL()) return null;
				else {
					// $dataMySQL = $this->getDataMySQL($table, $whatVar, !empty($sortBy) ? 'ORDER BY `' . $sortBy . '` ASC' : null, 'LIMIT 1')[0];
					$dataMySQL = $this->getDataMySQL($table, $whatVar, !empty($sortBy) ? 'ORDER BY "' . $sortBy . '" ASC' : null, 'LIMIT 1')[0];
					if(!empty($dataMySQL)) {
						if(!$rawResult) $where = array_map(function($value) {
							return (!is_array($value) AND is_array($decodedValue = json_decode($value, true))) ? $decodedValue : $value;
						}, $dataMySQL);
						return $dataMySQL;
					} else return null;
				}
				*/
			}
			/**
			 * Get first line from table
			 * 
			 * @version BETA
			 * @updated BETA-2.0.0
			 * @related OliCore::getFirstInfoMySQL()
			 * @return array|null Returns first line from specified table.
			 */
			// public function getFirstLineMySQL($table, $sortBy = null, $rawResult = false) {
			public function getFirstLineMySQL(...$args) {
				return $this->getDB()->getFirstLineSQL(...$args);
				// return $this->getFirstInfoMySQL($table, null, $sortBy, $rawResult);
			}
			
			/**
			 * Get first info from table
			 * 
			 * @version BETA
			 * @updated BETA-2.0.0
			 * @return array|null Returns last info from specified table.
			 */
			// public function getLastInfoMySQL($table, $whatVar, $rawResult = false) {
			public function getLastInfoMySQL(...$args) {
				return $this->getDB()->getLastInfoSQL(...$args);
				/*
				if(!$this->isSetupMySQL()) return false;
				else {
					// $dataMySQL = $this->getDataMySQL($table, $whatVar, !empty($sortBy) ? 'ORDER BY `' . $sortBy . '` DESC' : null, !empty($sortBy) ? 'LIMIT 1' : null);
					$dataMySQL = $this->getDataMySQL($table, $whatVar, !empty($sortBy) ? 'ORDER BY "' . $sortBy . '" DESC' : null, !empty($sortBy) ? 'LIMIT 1' : null);
					if($dataMySQL !== false) {
						$dataMySQL = array_reverse($dataMySQL)[0];
						if(!empty($dataMySQL)) {
							if(!$rawResult) $where = array_map(function($value) {
								return (!is_array($value) AND is_array($decodedValue = json_decode($value, true))) ? $decodedValue : $value;
							}, $dataMySQL);
							return $whatVar ? $dataMySQL[$whatVar] : $dataMySQL;
						} else return null;
					} else return false;
				}
				*/
			}
			/**
			 * Get last line from table
			 * 
			 * @version BETA
			 * @updated BETA-2.0.0
			 * @related OliCore::getLastInfoMySQL()
			 * @return array|null Returns last line from specified table.
			 */
			// public function getLastLineMySQL($table, $rawResult = false) {
			public function getLastLineMySQL(...$args) {
				return $this->getDB()->getLastLineSQL(...$args);
				// return $this->getLastInfoMySQL($table, null, $sortBy, $rawResult);
			}
			
			/**
			 * Get infos from table
			 * 
			 * @version BETA
			 * @updated BETA-2.0.0
			 * @return array|null Returns infos from specified table.
			 */
			// public function getInfosMySQL($table, $whatVar = null, $where = null, $settings = null, $caseSensitive = null, $forceArray = null, $rawResult = null) {
			public function getInfosMySQL(...$args) {
				return $this->getDB()->getInfosSQL(...$args);
				/*
				if(!$this->isSetupMySQL()) return null;
				else {
					// Parameters Management
					if(is_bool($settings)) {
						$rawResult = $forceArray;
						$forceArray = $caseSensitive;
						$caseSensitive = $settings;
						$settings = null;
					}
					if(!isset($caseSensitive)) $caseSensitive = true;
					if(!isset($forceArray)) $forceArray = false;
					if(!isset($rawResult)) $rawResult = false;
					
					// Where Condition
					// if(in_array($where, [null, 'all', '*'], true)) $where = '1';
					if(in_array($where, [null, 'all', '*'], true)) $where = 'TRUE';
					else if(is_assoc($where)) $where = array_map(function($key, $value) use ($caseSensitive) {
						// if(!$caseSensitive) return 'LOWER(`' . $key . '`) = \'' . strtolower(is_array($value) ? json_encode($value) : $value) . '\'';
						if(!$caseSensitive) return 'LOWER("' . $key . '") = \'' . strtolower(is_array($value) ? json_encode($value) : $value) . '\'';
						// else return '`' . $key . '` = \'' . (is_array($value) ? json_encode($value) : $value) . '\'';
						else return '"' . $key . '" = \'' . (is_array($value) ? json_encode($value) : $value) . '\'';
					}, array_keys($where), array_values($where));
					
					if(!empty($where)) {
						// Additional Settings
						$whereGlue = [];
						if(!empty($settings)) {
							if(is_assoc($settings)) {
								$settings = array_filter($settings);
								if(isset($settings['order_by'])) $settings[] = 'ORDER BY ' . array_pull($settings, 'order_by');
								if(isset($settings['limit'])) {
									if(isset($settings['from'])) $settings[] = 'LIMIT ' . array_pull($settings, 'limit') . ' OFFSET ' . array_pull($settings, 'from');
									else if(isset($settings['offset'])) $settings[] = 'LIMIT ' . array_pull($settings, 'limit') . ' OFFSET ' . array_pull($settings, 'offset');
									else $settings[] = 'LIMIT ' . array_pull($settings, 'limit');
								}
								// $startFromId = (isset($settings['fromId']) AND $settings['fromId'] > 0) ? $settings['fromId'] : 1;
								
								if(isset($settings['where_and'])) $whereGlue = array_pull($settings, 'where_and') ? ' AND ' : ' OR ';
								else if(isset($settings['where_or'])) $whereGlue = array_pull($settings, 'where_or') ? ' OR ' : ' AND ';
							} else if(!is_array($settings)) $settings = [$settings];
						}
						
						// Data Processing
						$dataMySQL = $this->getDataMySQL($table, $whatVar, 'WHERE ' . (is_array($where) ? implode($whereGlue ?: ' AND ', $where) : $where), !empty($settings) ? implode(' ', $settings) : null);
						if(!empty($dataMySQL) AND is_array($dataMySQL)) {
							// if(!count($dataMySQL) == 1) $dataMySQL = $dataMySQL[0];
							if(!$rawResult) $dataMySQL = array_map(function($value) {
								if(is_array($value) AND count($value) == 1) $value = array_values($value)[0];
								if(is_array($value)) return array_map(function($value) {
										if(!is_array($value) AND is_array($decodedValue = json_decode($value, true))) return $decodedValue;
										else return $value;
									}, $value);
								else if(is_array($decodedValue = json_decode($value, true))) return $decodedValue;
								else return $value;
							}, $dataMySQL);
							return ($forceArray OR count($dataMySQL) > 1) ? $dataMySQL : array_values($dataMySQL)[0];
						} else return null;
					} else return null;
				}
				*/
			}
			
			/**
			 * Get lines from table
			 * 
			 * @version BETA
			 * @updated BETA-2.0.0
			 * @related OliCore::getInfosMySQL()
			 * @return array|null Returns lines from specified table.
			 */
			// public function getLinesMySQL($table, $where = null, $settings = null, $caseSensitive = null, $forceArray = null, $rawResult = null) {
			public function getLinesMySQL(...$args) {
				return $this->getDB()->getLinesSQL(...$args);
				// return $this->getInfosMySQL($table, null, $where, $settings, $caseSensitive, $forceArray, $rawResult);
			}
			
			/**
			 * Get summed infos from table
			 * 
			 * @version BETA
			 * @updated BETA-2.0.0
			 * @related OliCore::getInfosMySQL()
			 * @return numeric|boolean|null Returns summed infos if numeric values are found, false otherwise. Returns null if no MySQL infos is found.
			 */
			// public function getSummedInfosMySQL($table, $whatVar = null, $where = null, $settings = null, $caseSensitive = null) {
			public function getSummedInfosMySQL(...$args) {
				return $this->getDB()->getSummedInfosSQL(...$args);
				/*
				if(!$this->isSetupMySQL()) return null;
				else {
					$infosMySQL = $this->getInfosMySQL($table, $whatVar, $where, $settings, $caseSensitive, true);
					if(!empty($infosMySQL)) {
						$summedInfos = null;
						foreach($infosMySQL as $eachValue) {
							if(is_numeric($eachValue)) $summedInfos += $eachInfo;
						}
					} else $summedInfos = false;
					return $summedInfos;
				}
				*/
			}
			
			/**
			 * Is exist infos in table
			 * 
			 * @version BETA
			 * @updated BETA-2.0.0
			 * @related OliCore::getInfosMySQL()
			 * @return integer|boolean Returns the number of infos found, false if none found.
			 */
			// public function isExistInfosMySQL($table, $where = null, $settings = null, $caseSensitive = null) {
			public function isExistInfosMySQL(...$args) {
				return $this->getDB()->isExistInfosSQL(...$args);
				// $result = $this->getInfosMySQL($table, 'COUNT(1)', $where, $settings, $caseSensitive);
				// return $result !== null ? ((int) $result ?: false) : null;
			}
			
			/**
			 * Is empty infos in table
			 * 
			 * @version BETA
			 * @updated BETA-2.0.0
			 * @related OliCore::getInfosMySQL()
			 * @return array|null Returns true if infos are empty, false otherwise.
			 */
			// public function isEmptyInfosMySQL($table, $whatVar = null, $where = null, $settings = null, $caseSensitive = null) {
			public function isEmptyInfosMySQL(...$args) {
				return $this->getDB()->isEmptyInfosSQL(...$args);
				// return empty($this->getInfosMySQL($table, $whatVar, $where, $settings, $caseSensitive));
			}
			
			/** ---------------- */
			/**  V. 1. C. Write  */
			/** ---------------- */
			
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
			// public function insertLineMySQL($table, $matches, &$errorInfo = null) {
			public function insertLineMySQL(...$args) {
				return $this->getDB()->insertLineSQL(...$args);
				/*
				if(!$this->isSetupMySQL()) return null;
				else {
					foreach($matches as $matchKey => $matchValue) {
						$queryVars[] = $matchKey;
						$queryValues[] = ':' . $matchKey;
						
						$matchValue = (is_array($matchValue)) ? json_encode($matchValue) : $matchValue;
						$matches[$matchKey] = $matchValue;
					}
					$query = $this->db->prepare('INSERT INTO ' . $table . '(' . implode(', ', $queryVars) . ') VALUES(' . implode(', ', $queryValues) . ')');
					$errorInfo = $query->errorInfo();
					return $query->execute($matches);
				}
				*/
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
			// public function updateInfosMySQL($table, $what, $where, &$errorInfo = null) {
			public function updateInfosMySQL(...$args) {
				return $this->getDB()->updateInfosSQL(...$args);
				/*
				if(!$this->isSetupMySQL()) return null;
				else {
					$matches = [];
					foreach($what as $whatVar => $whatValue) {
						$queryWhat[] = $whatVar . ' = :what_' . $whatVar;
						
						$whatValue = (is_array($whatValue)) ? json_encode($whatValue) : $whatValue;
						$matches['what_' . $whatVar] = $whatValue;
					}
					if($where != 'all') {
						foreach($where as $whereVar => $whereValue) {
							$queryWhere[] = $whereVar . ' = :where_' . $whereVar;
							
							$whereValue = (is_array($whereValue)) ? json_encode($whereValue) : $whereValue;
							$matches['where_' . $whereVar] = $whereValue;
						}
					}
					$query = $this->db->prepare('UPDATE ' . $table . ' SET '  . implode(', ', $queryWhat) . ($where != 'all' ? ' WHERE ' . implode(' AND ', $queryWhere) : ''));
					$errorInfo = $query->errorInfo();
					return $query->execute($matches);
				}
				*/
			}
			
			/**
			 * Delete lines from a table
			 * 
			 * @version BETA
			 * @updated BETA-2.0.0
			 * @return boolean Returns true if the request succeeded, false otherwise.
			 */
			// public function deleteLinesMySQL($table, $where, &$errorInfo = null) {
			public function deleteLinesMySQL(...$args) {
				return $this->getDB()->deleteLinesSQL(...$args);
				/*
				if(!$this->isSetupMySQL()) return null;
				else {
					if(is_array($where)) {
						$matches = [];
						foreach($where as $whereVar => $whereValue) {
							$queryWhere[] = $whereVar . ' = :' . $whereVar;
							
							$whereValue = (is_array($whereValue)) ? json_encode($whereValue) : $whereValue;
							$matches[$whereVar] = $whereValue;
						}
					}
					$query = $this->db->prepare('DELETE FROM ' . $table . ' WHERE ' .
					(is_array($where) ? implode(' AND ', $queryWhere) : ($where !== 'all' ? $where : '*')));
					$errorInfo = $query->errorInfo();
					return $query->execute($matches);
				}
				*/
			}
			
			/** ------------------------- */
			/**  V. 1. D. Database Edits  */
			/** ------------------------- */
			
				/** -------------------- */
				/**  V. 1. D. a. Tables  */
				/** -------------------- */
			
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
				// public function createTableMySQL($table, $columns, &$errorInfo = null) {
				public function createTableMySQL(...$args) {
					return $this->getDB()->createTableSQL(...$args);
					/*
					if(!$this->isSetupMySQL()) return null;
					else {
						foreach($columns as $matchName => $matchOption) {
							$queryData[] = $matchName . ' ' . $matchOption;
						}
						$query = $this->db->prepare('CREATE TABLE ' . $table . '(' . implode(', ', $queryData) . ')');
						$errorInfo = $query->errorInfo();
						return $query->execute();
					}
					*/
				}
				
				/**
				 * Is Exist MySQL Table
				 * 
				 * @version BETA-2.0.0
				 * @updated BETA-2.0.0
				 * @return boolean Returns true if it exists.
				 */
				// public function isExistTableMySQL($table, &$errorInfo = null) {
				public function isExistTableMySQL(...$args) {
					return $this->getDB()->isExistTableSQL(...$args);
					/*
					if(!$this->isSetupMySQL()) return null;
					else {
						$query = $this->db->prepare('SELECT 1 FROM ' . $table);
						$errorInfo = $query->errorInfo();
						return $query->execute();
					}
					*/
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
				// public function clearTableMySQL($table, &$errorInfo = null) {
				public function clearTableMySQL(...$args) {
					return $this->getDB()->clearTableSQL(...$args);
					/*
					if(!$this->isSetupMySQL()) return null;
					else {
						$query = $this->db->prepare('TRUNCATE TABLE ' . $table);
						$errorInfo = $query->errorInfo();
						return $query->execute();
					}
					*/
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
				// public function deleteTableMySQL($table, &$errorInfo = null) {
				public function deleteTableMySQL(...$args) {
					return $this->getDB()->deleteTableSQL(...$args);
					/*
					if(!$this->isSetupMySQL()) return null;
					else {
						$query = $this->db->prepare('DROP TABLE ' . $table);
						$errorInfo = $query->errorInfo();
						return $query->execute();
					}
					*/
				}
				
				/** --------------------- */
				/**  V. 1. D. b. Columns  */
				/** --------------------- */
				
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
				// public function addColumnTableMySQL($table, $column, $type, &$errorInfo = null) {
				public function addColumnTableMySQL(...$args) {
					return $this->getDB()->addColumnTableSQL(...$args);
					/*
					if(!$this->isSetupMySQL()) return null;
					else {
						$query = $this->db->prepare('ALTER TABLE ' . $table . ' ADD ' . $column . ' ' . $type);
						$errorInfo = $query->errorInfo();
						return $query->execute();
					}
					*/
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
				// public function updateColumnTableMySQL($table, $column, $type, &$errorInfo = null) {
				public function updateColumnTableMySQL(...$args) {
					return $this->getDB()->updateColumnTableSQL(...$args);
					/*
					if(!$this->isSetupMySQL()) return null;
					else {
						$query = $this->db->prepare('ALTER TABLE ' . $table . ' MODIFY ' . $column . ' ' . $type);
						$errorInfo = $query->errorInfo();
						return $query->execute();
					}
					*/
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
				// public function renameColumnTableMySQL($table, $oldColumn, $newColumn, $type = null, &$errorInfo = null) {
				public function renameColumnTableMySQL(...$args) {
					return $this->getDB()->renameColumnTableSQL(...$args);
					/*
					if(!$this->isSetupMySQL()) return null;
					else {
						$query = $this->db->prepare('ALTER TABLE ' . $table . (isset($type) ? ' CHANGE ' : ' RENAME COLUMN ') . $oldColumn . (isset($type) ? ' ' : ' TO ') . $newColumn . (isset($type) ? ' ' . $type : ''));
						$errorInfo = $query->errorInfo();
						return $query->execute();
					}
					*/
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
				// public function deleteColumnTableMySQL($table, $column, &$errorInfo = null) {
				public function deleteColumnTableMySQL(...$args) {
					return $this->getDB()->deleteColumnTableSQL(...$args);
					/*
					if(!$this->isSetupMySQL()) return null;
					else {
						$query = $this->db->prepare('ALTER TABLE ' . $table . ' DROP ' . $column . ')');
						$errorInfo = $query->errorInfo();
						return $query->execute();
					}
					*/
				}
	
		/** ------------------------------------- */
		/**  V. 2. Legacy (Read) MySQL Functions  */
		/** ------------------------------------- */
			
		/**
		 * Run a raw MySQL Query
		 * 
		 * @version BETA-1.8.0
		 * @updated BETA-2.0.0
		 * @return array|boolean Returns the query result content or true if succeeded.
		 */
		public function runQueryLegacyMySQL($query, $fetchStyle = true) {
			if(!$this->isSetupMySQL()) return null;
			else {
				$query = $this->db->prepare($query);
				if($query->execute()) return $query->fetchAll(!is_bool($fetchStyle) ? $fetchStyle : ($fetchStyle ? \PDO::FETCH_ASSOC : null)) ?: true;
				else {
					$this->dbError = $query->errorInfo();
					return false;
				}
			}
		}
		
		/**
		 * Get data from MySQL
		 * 
		 * @version BETA
		 * @updated BETA-2.0.0
		 * @return array|boolean Returns data from the requested table if succeeded.
		 */
		public function getDataLegacyMySQL($table, ...$params) {
			if(!$this->isSetupMySQL()) return null;
			else {
				$select = (!empty($params) AND is_array($params[0])) ? implode(', ', array_shift($params)) : '*';
				$fetchStyle = (!empty($params) AND is_integer(array_reverse($params)[0])) ? implode(', ', array_pop($params)) : true;
				
				$queryParams = null;
				if(!empty($params)) {
					foreach($params as $eachKey => $eachParam) {
						if(!empty($eachParam)) $queryParams .= ' ' . $eachParam;
					}
				}
				
				return $this->runQueryMySQL('SELECT ' . $select . ' FROM ' . $table . $queryParams, $fetchStyle);
			}
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
		public function getFirstInfoLegacyMySQL($table, $whatVar, $rawResult = false) {
			$dataMySQL = $this->getDataMySQL($table);
			if(!empty($dataMySQL) AND is_array($dataMySQL)) return (!is_array($dataMySQL[0][$whatVar]) AND is_array(json_decode($dataMySQL[0][$whatVar], true)) AND !$rawResult) ? json_decode($dataMySQL[0][$whatVar], true) : $dataMySQL[0][$whatVar];
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
		public function getFirstLineLegacyMySQL($table, $rawResult = false) {
			$dataMySQL = $this->getDataMySQL($table);
			if(!empty($dataMySQL) AND is_array($dataMySQL)) {
				foreach($dataMySQL[0] as $eachKey => $eachValue) {
					$dataMySQL[0][$eachKey] = (!is_array($eachValue) AND is_array(json_decode($eachValue, true)) AND !$rawResult) ? json_decode($eachValue, true) : $eachValue;
				}
				return $dataMySQL[0];
			} else return false;
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
		public function getLastInfoLegacyMySQL($table, $whatVar, $rawResult = false) {
			$dataMySQL = $this->getDataMySQL($table, 'ORDER BY id DESC');
			if(!empty($dataMySQL) AND is_array($dataMySQL)) return (!is_array($dataMySQL[0][$whatVar]) AND is_array(json_decode($dataMySQL[0][$whatVar], true)) AND !$rawResult) ? json_decode($dataMySQL[0][$whatVar], true) : $dataMySQL[0][$whatVar];
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
		public function getLastLineLegacyMySQL($table, $rawResult = false) {
			$dataMySQL = $this->getDataMySQL($table, 'ORDER BY id DESC');
			if(!empty($dataMySQL) AND is_array($dataMySQL)) {
				foreach($dataMySQL[0] as $eachKey => $eachValue) {
					$dataMySQL[0][$eachKey] = (!is_array($eachValue) AND is_array(json_decode($eachValue, true)) AND !$rawResult) ? json_decode($eachValue, true) : $eachValue;
				}
				return $dataMySQL[0];
			} else return false;
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
		public function getLinesLegacyMySQL($table, $where = null, $settings = null, $caseSensitive = null, $forceArray = null, $rawResult = null) {
			if(!is_array($settings)) {
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
			if(!empty($dataMySQL) AND is_array($dataMySQL)) {
				$id = 0;
				foreach($dataMySQL as $eachLineKey => $eachLine) {
					if((!empty($eachLine['id']) ? $id = $eachLine['id'] : ++$id) < $startFromId) continue;
					
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
									} else {
										$toCompare = (!$caseSensitive) ? strtolower($eachValue) : $eachValue;
										$compareWith = (!$caseSensitive) ? strtolower($whereValue) : $whereValue;
										
										if($toCompare == $compareWith) {
											$status[$eachLineKey][$whereLineID] = $eachKey;
											break;
										} else $status[$eachLineKey][$whereLineID] = false;
									}
								}
							} else {
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
						
						foreach($eachLine as $eachKey => $eachValue) {
							$eachLine[$eachKey] = (!is_array($eachValue) AND is_array(json_decode($eachValue, true)) AND !$rawResult) ? json_decode($eachValue, true) : $eachValue;
						}
						
						$valueArray[] = $eachLine;
					}
				}
			} else return false;
			
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
		public function getInfosLegacyMySQL($table, $whatVar, $where = null, $settings = null, $caseSensitive = null, $forceArray = null, $rawResult = null) {
			if(!is_array($whatVar)) {
				$whatVar = [$whatVar];
				$whatVarArray = false;
			} else $whatVarArray = true;
			
			if(!is_array($settings)) {
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
			if(!empty($dataMySQL) AND is_array($dataMySQL)) {
				$id = 0;
				foreach($dataMySQL as $eachLineKey => $eachLine) {
					if((!empty($eachLine['id']) ? $id = $eachLine['id'] : ++$id) < $startFromId) continue;
					
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
									} else {
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
			} else return false;
			
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
		public function getSummedInfosLegacyMySQL($table, $whatVar, $where = null, $settings = null, $caseSensitive = null, $rawResult = null) {
			if(!is_array($settings)) {
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
			return (is_array($summedInfos) AND $rawResult) ? json_encode($summedInfos) : $summedInfos;
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
		public function isEmptyInfosLegacyMySQL($table, $whatVar, $where = null, $settings = null, $caseSensitive = null) {
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
		public function isExistInfosLegacyMySQL($table, $where = null, $caseSensitive = true) {
			$dataMySQL = $this->getDataMySQL($table);
			$valueArray = [];
			$status = [];
			if(!empty($dataMySQL) AND is_array($dataMySQL)) {
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
									} else {
										$toCompare = (!$caseSensitive) ? strtolower($eachValue) : $eachValue;
										$compareWith = (!$caseSensitive) ? strtolower($whereValue) : $whereValue;
										
										if($toCompare == $compareWith) {
											$status[$eachLineKey][$whereLineID] = $eachKey;
											break;
										} else $status[$eachLineKey][$whereLineID] = false;
									}
								}
							} else {
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
					
					if((!in_array(false, $status[$eachLineKey]) AND !empty($status[$eachLineKey])) OR empty($where)) $valueArray[] = true;
				}
			} else return false;
			
			if(count($valueArray) >= 1) return count($valueArray);
			else return false;
		}
	
	/** *** *** */
	
	/** ------------- */
	/**  VI. General  */
	/** ------------- */
		
		/** --------------------- */
		/**  VI. 1. Load Website  */
		/** --------------------- */
		
		/**
		 * Load page content
		 * 
		 * @version BETA
		 * @updated BETA-2.0.0
		 * @return string|void Returns the path to the file to include.
		 */
		public function loadContent(array $params = null) {
			$params = !empty($params) ? $params : $this->getUrlParam('params');
			
			$accessAllowed = null;
			$contentStatus = null;
			$found = null;
			
			// Default content rules
			$contentRules = array(
				'index' => 'index.php',
				'error' => array('403' => '403.php', '404' => '404.php'),
				'access' => array('*' => array('allow' => '*'))
			);
			
			// Update with custom content rules
			$this->updateContentRules($contentRules, THEMEPATH . '.olicontent');
			
			if(!empty($params)) {
				$fileName = [];
				$countFileName = 0;
				
				foreach($params as $eachParam) {
					if(empty($eachParam)) break; // Filename can't be empty.
					
					$fileName[] = $eachParam;
					$countFileName++;
					$fileNameParam = implode('/', $fileName);
					$slicedFileNameParam = implode('/', array_slice($fileName, 1));
					
					// Oli Setup
					if($fileName[0] == Config::$config['setup_alias'] OR (Config::$config['setup_wizard'] AND !Config::$config['debug'])) {
						// Assets
						if($countFileName > 1 AND is_file(OLISETUPPATH . $slicedFileNameParam)) {
							$found = OLISETUPPATH . $slicedFileNameParam;
							$this->fileNameParam = $fileNameParam;
							$this->setAutoContentType($fileNameParam);
							break; // Sub-level pages not allowed.
						
						// Setup Page
						} else if(is_file(OLISETUPPATH . 'setup.php')) {
							$found = OLISETUPPATH . 'setup.php';
							$this->fileNameParam = Config::$config['setup_alias'];
							continue; // There may be a requested page.
						}
					
					// Oli Login
					} else if($fileName[0] == Config::$config['login_alias']) {
						// Assets
						if($countFileName > 1 AND is_file(OLILOGINPATH . $slicedFileNameParam)) {
							$found = OLILOGINPATH . $slicedFileNameParam;
							$this->fileNameParam = $fileNameParam;
							$this->setAutoContentType($fileNameParam);
							break; // Sub-level pages not allowed.
						
						// Login Page
						} else if(is_file(OLILOGINPATH . 'login.php')) {
							$found = OLILOGINPATH . 'login.php';
							$this->fileNameParam = $fileName[0];
							continue; // There may be a requested page.
						}
					
					// Oli Admin
					} else if($fileName[0] == Config::$config['admin_alias']) {
						// Assets
						if($countFileName > 1 AND is_file(OLIADMINPATH . $slicedFileNameParam)) {
							$found = OLIADMINPATH . $slicedFileNameParam;
							$this->fileNameParam = $fileNameParam;
							$this->setAutoContentType($fileNameParam);
							break; // Sub-level pages not allowed.
						
						// Custom Pages
						} else if($countFileName > 1 AND is_file(OLIADMINPATH . $slicedFileNameParam . '.php')) {
							$found = OLIADMINPATH . $slicedFileNameParam . '.php';
							$this->fileNameParam = $fileNameParam;
							break; // Sub-level pages not allowed.
						
						// Home Page
						} else if(is_file(OLIADMINPATH . 'index.php')) {
							$found = OLIADMINPATH . 'index.php';
							$this->fileNameParam = $fileNameParam;
							continue; // There may be a requested page.
						}
					
					// Scripts
					} else if($fileName[0] == Config::$config['scripts_alias']) {
						// User Scripts
						if(is_file(SCRIPTSPATH . $fileNameParam)) {
							$found = SCRIPTSPATH . $fileNameParam;
							$this->fileNameParam = $fileNameParam;
							$this->setContentType('JSON');
							break;
						
						// Oli Scripts
						} else if(is_file(OLISCRIPTPATH . $fileNameParam)) {
							$found = OLISCRIPTPATH . $fileNameParam;
							$this->fileNameParam = $fileNameParam;
							$this->setContentType('JSON');
							break;
						}
					
					// User Assets
					} else if($fileName[0] == Config::$config['assets_alias']) {
						if(is_file(ASSETSPATH . $slicedFileNameParam)) {
							$found = ASSETSPATH . $slicedFileNameParam;
							$this->fileNameParam = $fileNameParam;
							$this->setAutoContentType($slicedFileNameParam);
							break;
						}
					
					// User Media
					} else if($fileName[0] == Config::$config['media_alias']) {
						if(is_file(MEDIAPATH . $slicedFileNameParam)) {
							$found = MEDIAPATH . $slicedFileNameParam;
							$this->fileNameParam = $fileNameParam;
							$this->setAutoContentType($slicedFileNameParam);
							break;
						}
					
					// User Pages
					} else {
						// Custom Page (supports sub-directory)
						if(is_file(THEMEPATH . $fileNameParam . '.php')) {
							if($accessAllowed = $this->isAccessAllowed($contentRules['access'], $fileNameParam . '.php')) {
								$found = THEMEPATH . $fileNameParam . '.php';
								$this->fileNameParam = $fileNameParam;
							}
						
						// Sub-directory Home Page 
						} else if(is_file(THEMEPATH . $fileNameParam . '/' . $contentRules['index'])) {
							if($accessAllowed = $this->isAccessAllowed($contentRules['access'], $fileNameParam . '/' . $contentRules['index'])) {
								$found = THEMEPATH . $fileNameParam . '/' . $contentRules['index'];
								$contentStatus = 'index';
							}
						
						// Home Page 
						} else if($fileName[0] == 'home' AND is_file(THEMEPATH . $contentRules['index'])) {
							if($accessAllowed = $this->isAccessAllowed($contentRules['access'], $contentRules['index'])) {
								$found = THEMEPATH . $contentRules['index'];
								$contentStatus = 'index';
							}
						}
					}
				}
			}
			
			// if($this->contentType == 'text/html') echo '<!-- ' . $this . ' -->' . "\n\n";
			
			// Forbidden
			if($accessAllowed === false) {
				http_response_code(403); // 403 Forbidden
				$this->contentStatus = '403';
				
				if(file_exists(THEMEPATH . $contentRules['error']['403']))
					return THEMEPATH . $contentRules['error']['403'];
				
				die('Error 403: Access forbidden');
			
			// Found
			} else if(!empty($found)) {
				http_response_code(200); // 200 OK
				$this->contentStatus = $contentStatus ?: 'found';
				return $found;
			
			// Not Found
			} else {
				http_response_code(404); // 404 Not Found
				$this->contentStatus = '404';
				
				if(file_exists(THEMEPATH . $contentRules['error']['404']) AND $this->isAccessAllowed($contentRules['access'], $contentRules['error']['404']))
					return THEMEPATH . $contentRules['error']['404'];
				
				die('Error 404: File not found');
			}
		}
		
		/**
		 * Update content rules
		 * 
		 * @version BETA
		 * @updated BETA-2.0.0
		 * @return boolean Returns whether or not access to the file is allowed
		 */
		public function updateContentRules(&$contentRules, $contentRulesFilename) {
			if(($rawContentRules = @file_get_contents($contentRulesFilename)) === false) return;
			
			$rawContentRules = explode("\n", $rawContentRules);
			foreach($rawContentRules as $eachRule) {
				if(empty($eachRule)) continue;
				if(!preg_match('/^([^\s]+):\s*(.+)$/', $eachRule, $matches)) continue;
				
				$ruleType = strtolower($matches[1]);
				$ruleValue = trim($matches[2]);
				
				// Error
				if($ruleType == 'error') {
					if(!preg_match('/^(\d{3})\s+(.*)$/', $ruleValue, $matches)) continue;
					
					$contentRules['error'][$matches[1]] = $matches[2];
				}
				
				// Access
				else if($ruleType == 'access') {
					if(!preg_match('/^(?:"?((?:(?<=")[^"]*(?="))|\S*)"?\s+)?(\w+)(?:\s+(.*))?$/', $ruleValue, $matches)) continue;
					
					$file = $matches[1] ?: '*';
					$access = strtolower($matches[2]);
					$scope = $matches[3] ?: '*';
					
					// Initialize the access rules array for the file
					if(@$contentRules['access'][$file] === null) $contentRules['access'][$file] = [];
					
					// Global rule
					if($scope == '*') $contentRules['access'][$file][$access] = '*';
					
					// Complex rule
					else if(preg_match('/^(?:from\s+(\w+))?\s*(?:to\s+(\w+))?$/', $scope, $rights)) {
						if(!is_array(@$contentRules['access'][$file][$access]))
							$contentRules['access'][$file][$access] = [];
						
						$contentRules['access'][$file][$access]['from'] = $this->translateUserRight($rights[1]);
						$contentRules['access'][$file][$access]['to'] = $this->translateUserRight($rights[2]);
					
					// Simple rule
					} else $contentRules['access'][$file][$access] = $this->translateUserRight($scope);
				
				// Index & Unknown
				} else $contentRules[$ruleType] = $ruleValue;
			}
		}
		
		/**
		 * Access rules check for a file
		 * 
		 * @version BETA
		 * @updated BETA-2.0.0
		 * @return boolean Returns whether or not access to the file is allowed
		 */
		public function isAccessAllowed($accessRules, $filename) {
			if(empty($accessRules)) return false;
			
			// File access rules
			if(!empty($accessRules[$filename])) {
				$access = $this->isAccessAllowedExplicit($accessRules, $filename);
				if($access !== null) return $access;
			}
			
			// Folder access rules
			while(!empty($accessRules[$filename = substr($filename, 0, strrpos($filename, '/'))])) {
				$access = $this->isAccessAllowedExplicit($accessRules, $filename . '/*');
				if($access !== null) return $access;
			}
			
			// Global access rules
			if(!empty($accessRules['*'])) {
				$access = $this->isAccessAllowedExplicit($accessRules, '*');
				if($access !== null) return $access;
			}
			
			// Default access: denied
			return false;
		}
		
		/**
		 * Explicit access rules check for a file
		 * 
		 * @version BETA-2.0.0
		 * @updated BETA-2.0.0
		 * @return boolean|null Returns whether or not access to the file is explicitly allowed, null if implicit
		 */
		private function isAccessAllowedExplicit($accessRules, $identifier) {
			if(@$accessRules[$identifier] === null) return null; // Implicit access rule
			
			if(@$accessRules[$identifier]['deny'] === '*') return false;
			if(@$accessRules[$identifier]['allow'] === '*') return true;
			if($this->isAccountsManagementReady() AND ($userRightLevel = $this->getUserRightLevel())) {
				// Deny checks
				if(($denyfrom = @$accessRules[$identifier]['deny']['from']) !== null AND $denyfrom <= $userRightLevel) return false;
				if(($denyto = @$accessRules[$identifier]['deny']['to']) !== null AND $denyto >= $userRightLevel) return false;
				
				// Allow checks
				if(($allowfrom = @$accessRules[$identifier]['allow']['from']) !== null AND $allowfrom <= $userRightLevel) return true;
				if(($allowto = @$accessRules[$identifier]['allow']['to']) !== null AND $allowto >= $userRightLevel) return true;
			}
			
			// Implicit access rule
			return null;
		}
		
		/** ----------------- */
		/**  VI. 2. Settings  */
		/** ----------------- */
		
		/**
		 * Get Settings Tables
		 * 
		 * @version BETA
		 * @updated BETA-2.0.0
		 * @deprecated Directly accessible with OliCore::$config
		 * @return array Returns the settings tables.
		 */
		public function getSettingsTables() { return Config::$config['settings_tables']; }
		
		/**
		 * Get Setting
		 * 
		 * @version BETA
		 * @updated BETA-2.0.0
		 * @return string|boolean Returns the requested setting if succeeded.
		 */
		public function getSetting($setting, $depth = 0) {
			$isExist = [];
			if($this->isSetupMySQL() AND !empty(Config::$config['settings_tables'])) {
				
				foreach(($depth > 0 AND count(Config::$config['settings_tables']) > $depth) ? array_slice(Config::$config['settings_tables'], $depth) : Config::$config['settings_tables'] as $eachTable) {
					if($this->isExistTableMySQL($eachTable)) {
						$isExist[] = true;
						if(isset($setting)) {
							$optionResult = $this->getInfosMySQL($eachTable, 'value', array('name' => $setting));
							if(!empty($optionResult)) {
								if($optionResult == 'null') return '';
								else return $optionResult;
							}
						} else return false; //$this->getInfosMySQL($eachTable, ['name', 'value']);
					} else $isExist[] = false;
				}
			}
			if(!in_array(true, $isExist, true)) return Config::getAppConfig($setting);
		}
		/** * @alias OliCore::getSetting() */
		public function getOption($setting, $depth = 0) { return $this->getSetting($setting, $depth); }
		
		/** [WIP] Get All Settings */
		// public function getAllSettings() { return $this->getSetting(null); }
		
		/** ----------------------- */
		/**  VI. 3. Custom Content  */
		/** ----------------------- */
		
		/**
		 * Get Shortcut Link
		 * 
		 * @version BETA
		 * @updated BETA-2.0.0
		 * @return boolean Returns true if succeeded.
		 */
		public function getShortcutLink($shortcut, $caseSensitive = false) {
			if(!empty(Config::$config['shortcut_links_table']) AND $this->isExistTableMySQL(Config::$config['shortcut_links_table'])) return $this->getInfosMySQL(Config::$config['shortcut_links_table'], 'url', array('name' => $shortcut), $caseSensitive);
			else return false;
		}
		
		/** ------------------- */
		/**  VI. 5. HTTP Tools  */
		/** ------------------- */
		
			/** ------------------------ */
			/**  VI. 5. A. Content Type  */
			/** ------------------------ */
			
			/** Set Content Type */
			public function setContentType($contentType = null, $charset = null, $force = false) {
				if(!$this->contentTypeBeenForced OR $force) {
					if($force) $this->contentTypeBeenForced = true;
					
					if(isset($contentType)) $contentType = strtolower($contentType);
					if($contentType == 'default') $contentType = strtolower(Config::$config['default_content_type'] ?: 'plain');
					
					if($contentType == 'html') $newContentType = 'text/html';
					else if($contentType == 'css') $newContentType = 'text/css';
					else if(in_array($contentType, ['js', 'javascript'])) $newContentType = 'text/javascript';
					else if($contentType == 'json') $newContentType = 'application/json';
					else if($contentType == 'pdf') $newContentType = 'application/pdf';
					else if($contentType == 'rss') $newContentType = 'application/rss+xml';
					else if($contentType == 'xml') $newContentType = 'text/xml';
					else if(in_array($contentType, ['debug', 'plain'])) $newContentType = 'text/plain';
					else $newContentType = $contentType;
					
					if(isset($charset)) $charset = strtolower($charset);
					if(!isset($charset) OR $charset == 'default') $charset = Config::$config['default_charset'];
					
					// error_reporting($contentType == 'debug' || Config::$config['debug'] ? E_ALL : E_ALL & ~E_NOTICE);
					header('Content-Type: ' . $newContentType . ';charset=' . $charset);
					
					$this->contentType = $newContentType;
					$this->charset = $charset;
					return $newContentType;
				} else return false;
			}
			
			/** Set Content Type Automatically */
			public function setAutoContentType($filename = null, $charset = null, $force = false) {
				$contentType = null;
				if(!empty($filename) AND preg_match('/\.([^.]+)$/', $filename, $matches))
					$contentType = $matches[1];
				
				return $this->setContentType($contentType, $charset, $force);
			}
			
			/** Reset Content Type */
			public function resetContentType() { return $this->setContentType(); }
			
			/** Get Content Type */
			public function getContentType() { return $this->contentType; }
			
			/** Get Charset */
			public function getCharset() { return $this->charset; }
			
			/** ----------------------------- */
			/**  VI. 5. B. Cookie Management  */
			/** ----------------------------- */
			
				/** ----------------------------- */
				/**  VI. 5. B. a. Read Functions  */
				/** ----------------------------- */
				
				/** Get cookie content */
				public function getCookie($name, $rawResult = false) {
					return (!$rawResult AND ($arr = json_decode($_COOKIE[$name], true)) !== null) ? $arr : $_COOKIE[$name];
				}
				public function getCookieContent($name, $rawResult = false) { $this->getCookie($name, $rawResult); }
				
				/** Is exist cookie */
				public function isExistCookie($name) { return isset($_COOKIE[$name]); }
				
				/** Is empty cookie */
				public function isEmptyCookie($name) { return empty($_COOKIE[$name]); }
				
				/** ------------------------------ */
				/**  VI. 5. B. b. Write Functions  */
				/** ------------------------------ */
				
				/** Set cookie */
				public function setCookie($name, $value, $expireDelay, $path, $domains, $secure = false, $httpOnly = false) {
					$value = (is_array($value)) ? json_encode($value) : $value;
					$domains = (!is_array($domains)) ? [$domains] : $domains;
					foreach($domains as $eachDomain) {
						if(!setcookie($name, $value, $expireDelay ? time() + $expireDelay : 0, '/', $eachDomain, $secure, $httpOnly)) {
							$cookieError = true;
							break;
						}
					}
					return !$cookieError ? true : false;
				}
				
				/** Delete cookie */
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
			
			/** ---------------------- */
			/**  VI. 5. C. _POST vars  */
			/** ---------------------- */
			
				/** ----------------------------- */
				/**  VI. 5. C. a. Read Functions  */
				/** ----------------------------- */
				
				/** Get post vars cookie name */
				public function getPostVarsCookieName() { return Config::$config['post_vars_cookie']['name']; }
				
				/** Get post vars */
				public function getPostVars($whatVar = null, $rawResult = false) {
					$postVars = $this->getCookie(Config::$config['post_vars_cookie']['name'], $rawResult);
					return isset($whatVar) ? $postVars[$whatVar] : $postVars;
				}
				
				/** Is empty post vars */
				public function isEmptyPostVars($whatVar = null) {
					return isset($whatVar) ? empty($this->getPostVars($whatVar)) : $this->isEmptyCookie(Config::$config['post_vars_cookie']['name']);
				}
				
				/** Is set post vars */
				public function issetPostVars($whatVar = null) {
					return isset($whatVar) ? $this->getPostVars($whatVar) !== null : $this->isExistCookie(Config::$config['post_vars_cookie']['name']);
				}
				
				/** Is protected post vars */
				public function isProtectedPostVarsCookie() { return $this->postVarsProtection; }
				
				/** ------------------------------ */
				/**  VI. 5. C. b. Write Functions  */
				/** ------------------------------ */
				
				/** Set post vars cookie */
				public function setPostVarsCookie($postVars) {
					$this->postVarsProtection = true;
					return $this->setCookie(Config::$config['post_vars_cookie']['name'], $postVars, 1, '/', Config::$config['post_vars_cookie']['domain'], Config::$config['post_vars_cookie']['secure'], Config::$config['post_vars_cookie']['http_only']);
				} 
				
				/** Delete post vars cookie */
				public function deletePostVarsCookie() {
					if(!$this->postVarsProtection) return $this->deleteCookie(Config::$config['post_vars_cookie']['name'], '/', Config::$config['post_vars_cookie']['domain'], Config::$config['post_vars_cookie']['secure'], Config::$config['post_vars_cookie']['http_only']);
					else return false;
				} 
				
				/** Protect post vars cookie */
				public function protectPostVarsCookie() {
					$this->postVarsProtection = true;
					return $this->setCookie(Config::$config['post_vars_cookie']['name'], $this->getRawPostVars(), 1, '/', Config::$config['post_vars_cookie']['domain'], Config::$config['post_vars_cookie']['secure'], Config::$config['post_vars_cookie']['http_only']);
				}
			
			/** --------------------------- */
			/**  VI. 5. D. Mail Management  */
			/** --------------------------- */
			
			/**
			 * Get default mail headers
			 * 
			 * @version BETA-2.0.0
			 * @updated BETA-2.0.0
			 * @return array Returns the default mail headers.
			 */
			public function getDefaultMailHeaders($toString = false) {
				$mailHeaders = [
					'Reply-To: ' . $this->getUrlParam('name') . ' <contact@' . $this->getUrlParam('fulldomain') . '>',
					'From: ' . $this->getUrlParam('name') . ' <noreply@' . $this->getUrlParam('fulldomain') . '>',
					'MIME-Version: 1.0',
					'Content-type: text/html; charset=utf-8',
					'X-Mailer: PHP/' . phpversion()
				];
				if($toString) return implode("\r\n", $mailHeaders);
				else return $mailHeaders;
			}
		
		/** ------------------- */
		/**  VI. 6. HTML Tools  */
		/** ------------------- */
		
			/** ------------------------ */
			/**  VI. 6. A. File Loaders  */
			/** ------------------------ */
			
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
				$this->loadStyle(Config::$config['cdn_url'] . $url, $tags, $loadNow, $minimize);
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
				$this->loadScript(Config::$config['cdn_url'] . $url, $tags, $loadNow, $minimize);
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
		
			/** --------------------------- */
			/**  VI. 6. B. File Minimizers  */
			/** --------------------------- */
			
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
		
		/** ---------------------- */
		/**  VI. 7. Url Functions  */
		/** ---------------------- */
		
		/**
		 * Get Url Parameter
		 * 
		 * $param supported values:
		 * - null 'full' => Full Url (e.g. 'http://hello.example.com/page/param')
		 * - 'protocol' => Get url protocol (e.g. 'https')
		 * - 'base' => Get base url (e.g. 'http://hello.example.com/')
		 * - 'allbases' => Get all bases urls (e.g. ['http://hello.example.com/', 'http://example.com/'])
		 * - 'alldomains' => Get all domains (e.g. ['hello.example.com', 'example.com'])
		 * - 'fulldomain' => Get domain (e.g. 'hello.example.com')
		 * - 'domain' => Get main domain (e.g. 'example.com')
		 * - 'subdomain' => Get subdomains (e.g. 'hello')
		 * - 'all' => All url fragments
		 * - 'params' => All parameters fragments
		 * - 0 => Url without any parameters (same as base url)
		 * - 1 => First parameter: file name parameter (e.g. 'page')
		 * - # => Other parameters (e.g. 2 => 'param')
		 * - 'last' => Get the last parameters fragment
		 * - 'get' => Get $_GET
		 * - 'getvars' => Get raw GET vars
		 * 
		 * @version BETA
		 * @updated BETA-2.0.0
		 * @return string|void Returns requested url param if succeeded.
		 */
		public function getUrlParam($param = null, &$hasUsedHttpHostBase = false) {
			if($param === 'get') return $_GET;
			
			$protocol = (!empty($_SERVER['HTTPS']) OR (!empty(Config::$config['force_https']) AND Config::$config['force_https'])) ? 'https' : 'http';
			$urlPrefix = $protocol . '://';
			
			if(!isset($param) OR $param < 0 OR $param === 'full') return $urlPrefix . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
			else if($param === 'protocol') return $protocol;
			
			$urlSetting = $this->getSetting('url');
			$urlSetting = !empty($urlSetting) ? (!is_array($urlSetting) ? [$urlSetting] : $urlSetting) : null;
			
			if(in_array($param, ['allbases', 'alldomains'], true)) {
				$allBases = $allDomains = [];
				foreach($urlSetting as $eachUrl) {
					preg_match('/^(https?:\/\/)?(((?:[w]{3}\.)?(?:[\da-z\.-]+\.)*(?:[\da-z-]+\.(?:[a-z\.]{2,6})))\/?(?:.)*)/', $eachUrl, $matches);
					$allBases[] = ($matches[1] ?: $urlPrefix) . $matches[2];
					$allDomains[] = $matches[3];
				}
				
				if($param === 'allbases') return $allBases;
				else if($param === 'alldomains') return $allDomains;
			} else {
				$httpParams = explode('?', $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], 2);
				if($param === 'getvars' AND !empty($httpParams[1])) return explode('&', $httpParams[1]);
				else {
					$fractionedUrl = explode('/', $httpParams[0]);
					$lenFractionedUrl = count($fractionedUrl);
					unset($httpParams);
					
					// Parse escaped slashes
					for($i = 0; $i < $lenFractionedUrl - 2; )
						if(empty($fractionedUrl[$i])) {
							$replacement = $fractionedUrl[$i] . '/' . $fractionedUrl[$i + 1];
							array_splice($fractionedUrl, $i - 1, 3, $replacement);
							$lenFractionedUrl -= 2;
						} else $i++;
					
					$baseUrlMatch = false;
					$baseUrl = $urlPrefix;
					$shortBaseUrl = ''; // IS THIS USEFULL? - It seems.
					$countLoop = 0;
					
					if(isset($urlSetting)) {
						foreach($fractionedUrl as $eachPart) {
							if(in_array($baseUrl, $urlSetting) OR in_array($shortBaseUrl, $urlSetting)) {
								$baseUrlMatch = true;
								break;
							} else {
								$baseUrlMatch = false;
								$baseUrl .= urldecode($eachPart) . '/';
								$shortBaseUrl .= urldecode($eachPart) . '/';
								$countLoop++;
							}
						}
					}
					
					$hasUsedHttpHostBase = false;
					if(!isset($urlSetting) OR !$baseUrlMatch) {
						$baseUrl = $urlPrefix . $_SERVER['HTTP_HOST'] . '/';
						$hasUsedHttpHostBase = true;
						$countLoop = 1; // Fix $countLoop value
					}
					
					if(in_array($param, [0, 'base'], true)) return $baseUrl;
					else if(in_array($param, ['fulldomain', 'subdomain', 'domain'], true)) {
						if(preg_match('/^https?:\/\/(?:[w]{3}\.)?((?:([\da-z\.-]+)\.)*([\da-z-]+\.(?:[a-z\.]{2,6})))\/?/', $baseUrl, $matches)) {
							if($param === 'fulldomain') return $matches[1];
							if($param === 'subdomain') return $matches[2];
							if($param === 'domain') return $matches[3];
						}
					} else {
						$newFractionedUrl[] = $baseUrl;
						if(!empty($this->fileNameParam)) {
							$i = $countLoop;
							
							$fileName = [];
							while($i < $lenFractionedUrl) {
								if(!empty($fileName) AND implode('/', $fileName) == $this->fileNameParam)
									break;
								else {
									$fileName[] = urldecode($fractionedUrl[$countLoop]);
									$i++;
								}
							}
							
							// fileNameParam found!
							if($i < $lenFractionedUrl) {
								$newFragment = explode('?', implode('/', $fileName), 2)[0];
								if(!empty($newFragment)) $newFractionedUrl[] = $newFragment;
								
								$countLoop = $i;
							
							// Not found
							} else {
								// Override the first element to match fileNameParam
								$newFractionedUrl[] = $this->fileNameParam;
								
								// Next fragments will be left as-is
								$countLoop++;
							}
						}
						
						while($countLoop < $lenFractionedUrl) {
							$nextFractionedUrl = urldecode($fractionedUrl[$countLoop]);
							
							$newFragment = explode('?', $nextFractionedUrl, 2)[0];
							if(!empty($newFragment)) $newFractionedUrl[] = $newFragment;
							
							$countLoop++;
						}
						
						if(empty($newFractionedUrl[1])) $newFractionedUrl[1] = 'home';
						
						if($param === 'all') return $newFractionedUrl;
						else if($param === 'params') return array_slice($newFractionedUrl, 1);
						else if($param === 'last') return $newFractionedUrl[count($newFractionedUrl) - 1];
						else if(preg_match('/^(.+)\+$/', $param, $matches)) {
							$slice = array_slice($newFractionedUrl, $matches[1]);
							return implode('/', $slice);
						}
						else if(isset($newFractionedUrl[$param])) return $newFractionedUrl[$param];
					}
				}
			}
			
			return null;
		}
		
		/** Get Full Url */
		public function getFullUrl() { return $this->getUrlParam('full'); }
		
		/**
		 * Get Assets Url
		 * 
		 * @version BETA-2.0.0
		 * @updated BETA-2.0.0
		 * @return string|void Returns the assets url.
		 */
		public function getAssetsUrl() {
			return $this->getUrlParam(0) . Config::$config['assets_alias'] . '/';
		}
		
		/**
		 * Get Media Url
		 * 
		 * @version BETA-2.0.0
		 * @updated BETA-2.0.0
		 * @return string|void Returns the media url.
		 */
		public function getMediaUrl() {
			return $this->getUrlParam(0) . Config::$config['media_alias'] . '/';
		}
		
		/**
		 * Get Login Url
		 * 
		 * @version BETA-2.0.0
		 * @updated BETA-2.0.0
		 * @return string|void Returns the login url.
		 */
		public function getLoginUrl() { return $this->isExternalLogin() ? Config::$config['external_login_url'] : $this->getUrlParam(0) . (Config::$config['login_alias'] ?: 'oli-login') . '/'; }
		
		/**
		 * Get Oli Admin Url
		 * 
		 * @version BETA-2.0.0
		 * @updated BETA-2.0.0
		 * @return string|void Returns the admin url.
		 */
		public function getOliAdminUrl() { return $this->getUrlParam(0) . Config::$config['admin_alias'] . '/'; }
		/** * @alias OliCore::getOliAdminUrl() */
		public function getAdminUrl() { return $this->getOliAdminUrl(); }
		
		/**
		 * Get Common Assets Url
		 * 
		 * @version BETA-2.0.0
		 * @updated BETA-2.0.0
		 * @return string|void Returns the common assets url.
		 */
		public function getCommonAssetsUrl() { return $this->getUrlParam(0) . Config::$config['common_path'] . Config::$config['common_assets_folder'] . '/'; }
		/** * @alias OliCore::getCommonAssetsUrl() */
		public function getCommonFilesUrl() { return $this->getCommonAssetsUrl(); }
		
		/**
		 * Get CDN Url
		 * 
		 * @version BETA-2.0.0
		 * @updated BETA-2.0.0
		 * @return string|void Returns the CDN url.
		 */
		public function getCdnUrl() { return Config::$config['cdn_url']; }
		
		/** ---------------------- */
		/**  VI. 8. Utility Tools  */
		/** ---------------------- */
		
			/** --------------------- */
			/**  VI. 8. A. Templates  */
			/** --------------------- */
			
			/**
			 * Get Template
			 * 
			 * @version BETA-1.8.0
			 * @updated BETA-2.0.0
			 * @return string|void Returns template content if found, null otherwise.
			 */
			public function getTemplate($template, $filter = null, $regex = false) {
				if(!empty($template)) {
					if(file_exists(TEMPLATESPATH . strtolower($template) . '.html')) $templateContent = file_get_contents(TEMPLATESPATH . strtolower($template) . '.html');
					else if(file_exists(INCLUDESPATH . 'templates/' . strtolower($template) . '.html')) $templateContent = file_get_contents(INCLUDESPATH . 'templates/' . strtolower($template) . '.html');
					
					if(!empty($templateContent)) {
						if(!empty($filter)) {
							foreach(!is_array($filter) ? [$filter] : $filter as $eachPattern => $eachReplacement) {
								if($regex) $templateContent = preg_replace($eachPattern, $eachReplacement, $templateContent);
								else $templateContent = str_replace($eachPattern, $eachReplacement, $templateContent);
							}
						}
						return $templateContent ?: null;
					} else return null;
				} else return null;
			}
		
			/** ---------------------- */
			/**  VI. 8. B. Generators  */
			/** ---------------------- */
		
				/** ------------------- */
				/**  VI. 8. B. a. UUID  */
				/** ------------------- */
				
				/**
				 * UUID Generator Gateway
				 * 
				 * @version BETA-2.0.0
				 * @updated BETA-2.0.0
				 * @return string Returns the requested UUID.
				 */
				public function uuid($type, ...$args) {
					if(in_array($type, ['v4', '4', 4], true)) call_user_func_array(array($this, 'uuid4'), $args);
					else if($type == 'alt') call_user_func_array(array($this, 'uuidAlt'), $args);
					else return false;
				}
				
				/**
				 * UUID v4 Generator Script
				 * 
				 * From https://stackoverflow.com/a/15875555/5255556
				 * 
				 * @version BETA-2.0.0
				 * @updated BETA-2.0.0
				 * @return string Returns the generated UUID.
				 */
				public function uuid4() {
					if(function_exists('random_bytes')) $data = random_bytes(16);
					else $data = openssl_random_pseudo_bytes(16);
					
					$data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
					$data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
					return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
				}
				
				/**
				 * Alternative UUID Generation Script
				 * 
				 * This is an alternative version of a randomly generated UUID, based on both timestamp and pseudo-random bytes.
				 * 
				 * This generated UUID format is {oooooooo-oooo-Mooo-Nxxx-xxxxxxxxxxxx}, it concatenates:
				 * - o: The current timestamp (60 bits) (time_low, time_mid, time_high)
				 * - M: The version (4 bits)
				 * - N: The variant (2 bits)
				 * - x: Pseudo-random values (62 bits)
				 * 
				 * Based on:
				 * - Code from an UUID v1 Generation script. https://github.com/fredriklindberg/class.uuid.php/blob/c1de11110970c6df4f5d7743a11727851c7e5b5a/class.uuid.php#L220
				 * - Code from an UUID v4 Generation script. https://stackoverflow.com/a/15875555/5255556
				 * 
				 * @author Matiboux <matiboux@gmail.com>
				 * @link https://github.com/matiboux/Time-Based-Random-UUID
				 * @version 1.0
				 * @return string Returns the generated UUID.
				 */
				function uuidAlt($tp = null) {
					if(!empty($tp)) {
						if(is_array($tp)) $time = ($tp['sec'] * 10000000) + ($tp['usec'] * 10);
						else if(is_numeric($tp)) $time = (int) ($tp * 10000000);
						else return false;
					} else $time = (int) (gettimeofday(true) * 10000000);
					$time += 0x01B21DD213814000;
					
					$arr = str_split(dechex($time & 0xffffffff), 4); // time_low (32 bits)
					$high = intval($time / 0xffffffff);
					array_push($arr, dechex($high & 0xffff)); // time_mid (16 bits)
					array_push($arr, dechex(0x4000 | (($high >> 16) & 0x0fff))); // Version (4 bits) + time_high (12 bits)
					
					// Variant (2 bits) + Cryptographically Secure Pseudo-Random Bytes (62 bits)
					if(function_exists('random_bytes')) $random = random_bytes(8);
					else $random = openssl_random_pseudo_bytes(8);
					$random[0] = chr(ord($random[0]) & 0x3f | 0x80); // Apply variant: Set the two first bits of the random set to 10.
					
					$uuid = vsprintf('%s%s-%s-%s-%s-%s%s%s', array_merge($arr, str_split(bin2hex($random), 4)));
					return strlen($uuid) == 36 ? $uuid : false;
				}
		
				/** ------------------- */
				/**  VI. 8. B. b. Misc  */
				/** ------------------- */
				
				/** Random Number generator */
				public function rand($min = 1, $max = 100) {
					if(is_numeric($min) AND is_numeric($max)) {
						if($min > $max) $min = [$max, $max = $min][0];
						return mt_rand($min, $max);
					} else return false;
				}
				public function randomNumber($min = null, $max = null) { $this->rand($min, $max); }
				
				/** KeyGen built-in script */
				// See https://github.com/matiboux/KeyGen-Lib for the full PHP library.
				public function keygen($length = 12, $numeric = true, $lowercase = true, $uppercase = true, $special = false, $redundancy = true) {
					$charactersSet = '';
					if($numeric) $charactersSet .= '1234567890';
					if($lowercase) $charactersSet .= 'abcdefghijklmnopqrstuvwxyz';
					if($uppercase) $charactersSet .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
					if($special) $charactersSet .= '!#$%&\()+-;?@[]^_{|}';
					
					if(empty($charactersSet) OR empty($length) OR $length <= 0) return false;
					else {
						if($length > strlen($redundancy) AND !$redundancy) $redundancy = true;
						
						$keygen = '';
						while(strlen($keygen) < $length) {
							$randomCharacter = substr($charactersSet, mt_rand(0, strlen($charactersSet) - 1), 1);
							if($redundancy OR !strstr($keygen, $randomCharacter)) $keygen .= $randomCharacter;
						}
						
						return $keygen;
					}
				}
			
			/** --------------------------- */
			/**  VI. 8. C. Data Conversion  */
			/** --------------------------- */
		
			/** Convert Number */
			public function convertNumber($value, $toUnit = null, $precision = null) {
				if(preg_match('/^([\d.]+)\s?(\S*)$/i', $value, $matches)) {
					list($result, $unit) = [floatval($matches[1]), $matches[2]];
					if($unit != $toUnit) {
						$unitsTable = array(
							'Yi' => 1024 ** 8,
							'Zi' => 1024 ** 7,
							'Ei' => 1024 ** 6,
							'Pi' => 1024 ** 5,
							'Ti' => 1024 ** 4,
							'Gi' => 1024 ** 3,
							'Mi' => 1024 ** 2,
							'Ki' => 1024,
							'Y' => 1000 ** 8,
							'Z' => 1000 ** 7,
							'E' => 1000 ** 6,
							'P' => 1000 ** 5,
							'T' => 1000 ** 4,
							'G' => 1000 ** 3,
							'M' => 1000 ** 2,
							'K' => 1000
						);
						
						if(!empty($unit) AND !empty($unitsTable[$unit])) $result *= $unitsTable[$unit];
						if(!empty($toUnit) AND !empty($unitsTable[$toUnit])) $result /= $unitsTable[$toUnit];
					}
					return isset($precision) ? round($result, $precision) : $result;
				} else return $value;
			}
			
			/** Convert File Size */
			public function convertFileSize($size, $toUnit = null, $precision = null) {
				if(preg_match('/^([\d.]+)\s?(\S*)$/i', $size, $matches)) {
					list($result, $unit) = [floatval($matches[1]), $matches[2]];
					if($unit != $toUnit) {
						$unitsTable = array(
							'YiB' => 1024 ** 8,
							'ZiB' => 1024 ** 7,
							'EiB' => 1024 ** 6,
							'PiB' => 1024 ** 5,
							'TiB' => 1024 ** 4,
							'GiB' => 1024 ** 3,
							'MiB' => 1024 ** 2,
							'KiB' => 1024,
							'YB' => 1000 ** 8,
							'ZB' => 1000 ** 7,
							'EB' => 1000 ** 6,
							'PB' => 1000 ** 5,
							'TB' => 1000 ** 4,
							'GB' => 1000 ** 3,
							'MB' => 1000 ** 2,
							'KB' => 1000,
							
							'Yio' => 1024 ** 8,
							'Zio' => 1024 ** 7,
							'Eio' => 1024 ** 6,
							'Pio' => 1024 ** 5,
							'Tio' => 1024 ** 4,
							'Gio' => 1024 ** 3,
							'Mio' => 1024 ** 2,
							'Kio' => 1024,
							'Yo' => 1000 ** 8,
							'Zo' => 1000 ** 7,
							'Eo' => 1000 ** 6,
							'Po' => 1000 ** 5,
							'To' => 1000 ** 4,
							'Go' => 1000 ** 3,
							'Mo' => 1000 ** 2,
							'Ko' => 1000,
							
							'Yib' => 1024 ** 8 / 8,
							'Zib' => 1024 ** 7 / 8,
							'Eib' => 1024 ** 6 / 8,
							'Pib' => 1024 ** 5 / 8,
							'Tib' => 1024 ** 4 / 8,
							'Gib' => 1024 ** 3 / 8,
							'Mib' => 1024 ** 2 / 8,
							'Kib' => 1024 / 8,
							'Yb' => 1000 ** 8 / 8,
							'Zb' => 1000 ** 7 / 8,
							'Eb' => 1000 ** 6 / 8,
							'Pb' => 1000 ** 5 / 8,
							'Tb' => 1000 ** 4 / 8,
							'Gb' => 1000 ** 3 / 8,
							'Mb' => 1000 ** 2 / 8,
							'Kb' => 1000 / 8,
							'b' => 1 / 8,
						);
						
						if(!empty($unit) AND !empty($unitsTable[$unit])) $result *= $unitsTable[$unit];
						if(!empty($toUnit) AND !empty($unitsTable[$toUnit])) $result /= $unitsTable[$toUnit];
					}
					return isset($precision) ? round($result, $precision) : $result;
				} else return $size;
			}
			
			/** ----------------------- */
			/**  VI. 8. D. Date & Time  */
			/** ----------------------- */
			
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
			
			/** ------------------------ */
			/**  VI. 8. E. Client Infos  */
			/** ------------------------ */
			
			/** Get User IP address */
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
	
	/** *** *** */
	
	/** --------------- */
	/**  VII. Accounts  */
	/** --------------- */
	
		/** ---------------- */
		/**  VII. 1. Status  */
		/** ---------------- */
		
		/**
		 * Enable accounts management
		 * 
		 * Allow to log, register and logout users
		 * Enable full login management
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
			return $this->isAccountsManagementReady();
		}
			
		/**
		 * Check if the database is ready for user management
		 * 
		 * @version BETA-2.0.0
		 * @updated BETA-2.0.0
		 * @return boolean Returns true if local.
		 */
		public function isAccountsManagementReady() {
			if($this->isSetupMySQL()) {
				$status = [];
				foreach(Config::$config['accounts_tables'] as $eachTable) {
					if(!$status[] = $this->isExistTableMySQL($eachTable)) break;
				}
				return !in_array(false, $status, true);
			} else return false;
		}
		
		/** --------------- */
		/**  VII. 2. MySQL  */
		/** --------------- */
	
			/** ------------------------ */
			/**  VII. 2. A. Table Codes  */
			/** ------------------------ */
			
			/**
			 * Translate Accounts Table Codes
			 * 
			 * - ACCOUNTS - Accounts list and main informations (password, email...)
			 * - INFOS - Accounts other informations
			 * - SESSIONS - Accounts login sessions
			 * - REQUESTS - Accounts requests
			 * - LOG_LIMITS - ///
			 * - RIGHTS - Accounts rights list (permissions groups) 
			 * - PERMISSIONS - Accounts personnal permissions
			 * 
			 * @param string $tableCode Table code to translate
			 * @uses OliCore::$accountsTables To get account tables names
			 *
			 * @version BETA
			 * @updated BETA-2.0.0
			 * @return string|void Returns account table name if succeeded, null otherwise.
			 */
			public function translateAccountsTableCode($tableCode) {
				$tableCode = strtolower($tableCode);
				return !empty(Config::$config['accounts_tables'][$tableCode]) ? Config::$config['accounts_tables'][$tableCode] : null;
			}
		
			/** ----------------- */
			/**  VII. 2. B. Read  */
			/** ----------------- */
		
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
			 *
			 * @version BETA
			 * @updated BETA-2.0.0
			 * @return array|boolean Returns lines from specified table
			 */
			public function getAccountLines($tableCode, $where = null, $settings = null, $caseSensitive = null, $forceArray = null, $rawResult = null) {
				if(!isset($where)) {
					if($this->isLoggedIn()) $where = array('uid' => $this->getLoggedUser());
					else return false;
				} else if(!is_array($where) AND $where != 'all') $where = array('uid' => $where);
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
			 *
			 * @version BETA
			 * @updated BETA-2.0.0
			 * @return mixed Returns infos from specified table
			 */
			public function getAccountInfos($tableCode, $whatVar, $where = null, $settings = null, $caseSensitive = null, $forceArray = null, $rawResult = null) {
				if(!isset($where)) {
					if($this->isLoggedIn()) $where = array('uid' => $this->getLoggedUser());
					else return null;
				} else if(!is_array($where) AND $where != 'all') $where = array('uid' => $where);	
				
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
			 *
			 * @version BETA
			 * @updated BETA-2.0.0
			 * @return mixed Returns summed infos from specified table
			 */
			public function getSummedAccountInfos($tableCode, $whatVar, $where = null, $caseSensitive = true) {
				if(!isset($where)) {
					if($this->isLoggedIn()) $where = array('uid' => $this->getLoggedUser());
					else return false;
				} else if(!is_array($where) AND $where != 'all') $where = array('uid' => $where);
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
			 *
			 * @version BETA
			 * @updated BETA-2.0.0
			 * @return boolean Returns true if infos are empty, false otherwise
			 */
			public function isEmptyAccountInfos($tableCode, $whatVar, $where = null, $settings = null, $caseSensitive = null) {
				if(!isset($where)) {
					if($this->isLoggedIn()) $where = array('uid' => $this->getLoggedUser());
					else return false;
				} else if(!is_array($where) AND $where != 'all') $where = array('uid' => $where);
				
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
			 *
			 * @version BETA
			 * @updated BETA-2.0.0
			 * @return boolean Returns true if infos exists, false otherwise
			 */
			public function isExistAccountInfos($tableCode, $where = null, $caseSensitive = true) {
				if(!isset($where)) {
					if($this->isLoggedIn()) $where = array('uid' => $this->getLoggedUser());
					else return false;
				} else if(!is_array($where) AND $where != 'all') $where = array('uid' => $where);
				
				return $this->isExistInfosMySQL($this->translateAccountsTableCode($tableCode) ?: $tableCode, $where, $caseSensitive);
			}
			
			/** ------------------ */
			/**  VII. 2. C. Write  */
			/** ------------------ */
			
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
			public function insertAccountLine($tableCode, $what, &$errorInfo = null) {
				return $this->insertLineMySQL($this->translateAccountsTableCode($tableCode) ?: $tableCode, $what, $errorInfo);
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
			 *
			 * @version BETA
			 * @updated BETA-2.0.0
			 * @return boolean Return true if the request succeeded, false otherwise
			 */
			public function updateAccountInfos($tableCode, $what, $where = null, &$errorInfo = null) {
				if(!isset($where)) {
					if($this->isLoggedIn()) $where = array('uid' => $this->getLoggedUser());
					else return false;
				}
				else if(!is_array($where) AND $where != 'all') $where = array('uid' => $where);
				
				return $this->updateInfosMySQL($this->translateAccountsTableCode($tableCode) ?: $tableCode, $what, $where, $errorInfo);
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
				return ($this->updateAccountInfos('ACCOUNTS', array('username' => $newUsername), $oldUsername)
					AND $this->updateAccountInfos('INFOS', array('username' => $newUsername), $oldUsername)
					AND $this->updateAccountInfos('SESSIONS', array('username' => $newUsername), $oldUsername)
					AND $this->updateAccountInfos('REQUESTS', array('username' => $newUsername), $oldUsername));
			}
			
			/**
			 * Delete lines from an account table
			 * 
			 * @version BETA
			 * @updated BETA-2.0.0
			 * @return boolean Returns true if the request succeeded, false otherwise.
			 */
			public function deleteAccountLines($tableCode, $where, &$errorInfo = null) {
				if(!is_array($where) AND $where !== 'all' AND strpos($where, ' ') === false) $where = array('uid' => $where);
				return $this->deleteLinesMySQL($this->translateAccountsTableCode($tableCode) ?: $tableCode, $where, $errorInfo);
			}
			
			/**
			 * Delete full account
			 * 
			 * @param string|array $where Where to delete user
			 * 
			 * @uses OliCore::deleteLinesMySQL() to delete lines from table
			 * @uses OliCore::translateAccountsTableCode() to translate account table code
			 *
			 * @version BETA
			 * @updated BETA-2.0.0
			 * @return boolean Returns true if the requests succeeded, false otherwise
			 */
			public function deleteFullAccount($where) {
				return ($this->deleteAccountLines('ACCOUNTS', $where)
					AND $this->deleteAccountLines('INFOS', $where)
					AND $this->deleteAccountLines('SESSIONS', $where)
					AND $this->deleteAccountLines('REQUESTS', $where)
					AND $this->deleteAccountLines('PERMISSIONS', $where));
			}
			
		/** ----------------------------------- */
		/**  VII. 3. User Rights & Permissions  */
		/** ----------------------------------- */
			
			/** ------------------------ */
			/**  VII. 3. A. User Rights  */
			/** ------------------------ */
			
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
				return !empty($userRight) AND $this->isExistAccountInfos('RIGHTS', array('user_right' => $userRight), $caseSensitive);
			}
			
			/**
			 * Translate User Right
			 * 
			 * @version BETA
			 * @updated BETA-2.0.0
			 * @return string|null Returns translated user right if succeeded.
			 */
			public function translateUserRight($userRight, $caseSensitive = true) {
				// Local user rights management
				if($this->isLocalLogin()) {
					// Check for Level -> User Right translation
					$userRightsTable = ['VISITOR', 'ROOT']; // Index starts at 0
					if(@$userRightsTable[$userRight] !== null) return $userRightsTable[$userRight];
					
					if(!$caseSensitive) $caseSensitive = strtoupper($caseSensitive);
					
					// Check for User Right -> Level translation
					array_flip($userRightsTable);
					if(@$userRightsTable[$userRight] !== null) return $userRightsTable[$userRight];
				
				// Database user rights management
				} else if($this->isAccountsManagementReady() AND !empty($userRight)) {
					// Check for Level -> User Right translation
					if($returnValue = $this->getAccountInfos('RIGHTS', 'user_right', array('level' => $userRight), $caseSensitive)) return $returnValue;
					
					// Check for User Right -> Level translation
					if($returnValue = $this->getAccountInfos('RIGHTS', 'level', array('user_right' => $userRight), $caseSensitive)) return (int) $returnValue;
				}
				
				return null; // Failure
			}
			
			/**
			 * Get right permissions
			 * 
			 * @param string $userRight User right to get permissions of
			 * @param boolean|void $caseSensitive Translate is case sensitive or not
			 * 
			 * @uses OliCore::getAccountInfos() to get infos from account table
			 * @return array Returns user right permissions
			 */
			public function getRightPermissions($userRight, $caseSensitive = true) {
				return $this->getAccountInfos('RIGHTS', 'permissions', array('user_right' => $userRight), $caseSensitive) ?: null;
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
				if(!is_array($where)) $where = array('uid' => $where);
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
			 * Get User Right
			 * 
			 * @version BETA
			 * @updated BETA-2.0.0
			 * @return string Returns the user right.
			 */
			public function getUserRight($where = null, $caseSensitive = true) {
				if($this->isLocalLogin() AND !empty($this->getLocalRootInfos())) return $this->isLoggedIn() ? 'ROOT' : 'VISITOR';
				
				if(empty($where)) {
					if($this->isLoggedIn()) $where = array('uid' => $this->getLoggedUser());
					else return $this->translateUserRight(0); // Default user right (visitor)
				}
				
				return $this->getAccountInfos('ACCOUNTS', 'user_right', $where, $caseSensitive);
			}
			
			/**
			 * Get user right level
			 * 
			 * @param string|array|void $where Where to get data from
			 * @param boolean|void $caseSensitive Translate is case sensitive or not
			 * 
			 * @uses OliCore::getUserRight() to get user right
			 * @return string Returns user right level (integer as string)
			 */
			public function getUserRightLevel($where = null, $caseSensitive = true) {
				if($userRight = $this->getUserRight($where, $caseSensitive)) return (int) $this->translateUserRight($userRight);
				return 0; // Default user right level (visitor)
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
		
			/** ----------------------------- */
			/**  VII. 3. B. User Permissions  */
			/** ----------------------------- */
		
				/*\
				|*|      -[ WORK IN PROGRESS ]-
				|*|  USER PERMISSIONS WILL BE ADDED
				|*|        IN A FUTURE UPDATE
				|*|    (RESCHEDULED FOR BETA 2.1)
				\*/
				
				/** ----------------------- */
				/**  VII. 3. B. a. General  */
				/** ----------------------- */
				
				/** Get user own permissions */
				public function getUserOwnPermissions($permission) {
					
				}
				
				/** Get user permissions */
				public function getUserPermissions($permission) {
					
				}
				
				/** Is User Permitted */
				public function isUserPermitted($permission) {
					
				}
		
				/** ---------------------------------- */
				/**  VII. 3. B. b. Rights Permissions  */
				/** ---------------------------------- */
				
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
		
				/** -------------------------------- */
				/**  VII. 3. B. c. User Permissions  */
				/** -------------------------------- */
				
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
		
		/** ------------------------- */
		/**  VII. -. Auth Key Cookie  */
		/** ------------------------- */
		
			/** ---------------------------- */
			/**  VII. -. A. Create & Delete  */
			/** ---------------------------- */
			
			/** Set Auth Key cookie */
			public function setAuthKeyCookie($authKey, $expireDelay) {
				return $this->setCookie(Config::$config['auth_key_cookie']['name'], $authKey, $expireDelay, '/', Config::$config['auth_key_cookie']['domain'], Config::$config['auth_key_cookie']['secure'], Config::$config['auth_key_cookie']['http_only']);
			}
			
			/** Delete Auth Key cookie */
			public function deleteAuthKeyCookie() {
				return $this->deleteCookie(Config::$config['auth_key_cookie']['name'], '/', Config::$config['auth_key_cookie']['domain'], Config::$config['auth_key_cookie']['secure'], Config::$config['auth_key_cookie']['http_only']);
			}
			
			/** ------------------------------- */
			/**  VII. -. B. Get Auth Key Infos  */
			/** ------------------------------- */
			
			/** Get Auth Key cookie name */
			public function getAuthKeyCookieName() { return Config::$config['auth_key_cookie']['name']; }
			
			/** Auth Key cookie content */
			// public function getAuthKey() { return $this->cache['authKey'] ?: $this->cache['authKey'] = $this->getCookie(Config::$config['auth_key_cookie']['name']); }
			public function isExistAuthKey() { return $this->isExistCookie(Config::$config['auth_key_cookie']['name']); }
			public function isEmptyAuthKey() { return $this->isEmptyCookie(Config::$config['auth_key_cookie']['name']); }
			
			// Get Auth Key
			// MOVED
			
			/**
			 * Is User Logged In?
			 * 
			 * @version BETA-2.0.0
			 * @updated BETA-2.0.0
			 * @return boolean Returns true if logged out successfully, false otherwise.
			 */
			public function isLoggedIn($authKey = null) {
				if(!isset($authKey)) $authKey = $this->getAuthKey();
				
				if(empty($authKey)) return false;
				
				$sessionInfos = ($this->isLocalLogin() AND !$this->isExternalLogin()) ? $this->getLocalRootInfos() : $this->getAccountLines('SESSIONS', array('auth_key' => hash('sha512', $authKey)));
				return strtotime($sessionInfos['expire_date']) >= time();
			}
			/** @alias OliCore::isLoggedIn() */
			public function verifyAuthKey($authKey = null) { return $this->isLoggedIn($authKey); }
			
			/**
			 * Get Logged User
			 * 
			 * @version BETA-2.0.0
			 * @updated BETA-2.0.0
			 * @return string|boolean Returns the uid if logged in, false otherwise.
			 */
			public function getLoggedUser($authKey = null) {
				if(empty($authKey)) $authKey = $this->getAuthKey();
				
				if(!$this->isLoggedIn($authKey)) return null;
				if($this->isLocalLogin() AND !$this->isExternalLogin()) return $this->getLocalRootInfos()['username'];
				return $this->getAccountInfos('SESSIONS', 'uid', array('auth_key' => hash('sha512', $authKey)));
			}
			
			/**
			 * Get User Name
			 * 
			 * @version BETA-2.0.0
			 * @updated BETA-2.0.0
			 * @return string|boolean Returns the username of user, false otherwise.
			 */
			public function getName($uid, &$type = null) {
				if($this->isExistAccountInfos('ACCOUNTS', array('uid' => $uid))) {
					if($name = $this->getAccountInfos('ACCOUNTS', 'username', $uid)) {
						$type = 'username';
						return $name;
					}
					if($name = $this->getAccountInfos('ACCOUNTS', 'email', $uid)) {
						$type = 'email';
						return substr($name, 0, strpos($name, '@'));
					}
					
					$type = 'uid';
					return $uid;
				}
				return null;
			}
			
			/**
			 * Get Logged Name
			 * 
			 * @version BETA-2.0.0
			 * @updated BETA-2.0.0
			 * @return string|boolean Returns the user name if logged in, false otherwise.
			 */
			public function getLoggedName($authKey = null, &$type = null) {
				if($this->isLoggedIn($authKey)) {
					if($this->isLocalLogin()) {
						$type = 'local';
						return 'root';
					}
					if($uid = $this->getLoggedUser($authKey))
						return $this->getName($uid, $type);
				}
				return null;
			}
			
			/**
			 * Get User Username
			 * 
			 * @version BETA-2.0.0
			 * @updated BETA-2.0.0
			 * @return string|boolean Returns the username of user, false otherwise.
			 */
			public function getUsername($uid) {
				return $this->getAccountInfos('ACCOUNTS', 'username', array('uid' => $uid)) ?: false;
			}
			
			/**
			 * Get Logged Username
			 * 
			 * @version BETA-2.0.0
			 * @updated BETA-2.0.0
			 * @return string|boolean Returns the username if logged in, false otherwise.
			 */
			public function getLoggedUsername($authKey = null) {
				if($this->isLoggedIn($authKey)) {
					if($this->isLocalLogin()) return 'root';
					if($uid = $this->getLoggedUser($authKey)
						AND $name = $this->getAccountInfos('ACCOUNTS', 'username', $uid)) return $name;
				}
				return null;
			}
			/**
			 * Get Auth Key Owner
			 * 
			 * @version BETA
			 * @updated BETA-2.0.0
			 * @alias OliCore::getLoggedUsername()
			 */
			public function getAuthKeyOwner($authKey = null) { return $this->getLoggedUsername($authKey); }
		
		/** ----------------------- */
		/**  VII. 5. User Sessions  */
		/** ----------------------- */
		
			/** -------------------- */
			/**  VII. 5. A. General  */
			/** -------------------- */
			
			/** ------------------------ */
			/**  VII. 5. B. Auth Cookie  */
			/** ------------------------ */
			
				/** -------------------------- */
				/**  VII. 5. B. a. Management  */
				/** -------------------------- */
				
				/**
				 * Set Auth Cookie
				 * 
				 * @version BETA-1.8.0
				 * @updated BETA-2.0.0
				 * @return boolean Returns true if succeeded, false otherwise.
				 */
				public function setAuthCookie($authKey, $expireDelay = null) {
					return $this->setCookie(Config::$config['auth_key_cookie']['name'], $authKey, $expireDelay, '/', Config::$config['auth_key_cookie']['domain'], Config::$config['auth_key_cookie']['secure'], Config::$config['auth_key_cookie']['http_only']);
				}
				
				/**
				 * Delete Auth Cookie
				 * 
				 * @version BETA-1.8.0
				 * @updated BETA-2.0.0
				 * @return boolean Returns true if succeeded, false otherwise.
				 */
				public function deleteAuthCookie() {
					return $this->deleteCookie(Config::$config['auth_key_cookie']['name'], '/', Config::$config['auth_key_cookie']['domain'], Config::$config['auth_key_cookie']['secure'], Config::$config['auth_key_cookie']['http_only']);
				}
				
				/** --------------------- */
				/**  VII. 5. B. a. Infos  */
				/** --------------------- */
				
				/** Get Auth Cookie name */
				public function getAuthIDCookieName() {
					return Config::$config['auth_key_cookie']['name'];
				}
				
				/** Is exist Auth Cookie */
				public function isExistAuthID() {
					return $this->isExistCookie(Config::$config['auth_key_cookie']['name']);
				}
				
				/** Is empty Auth Cookie */
				public function isEmptyAuthID() {
					return $this->isEmptyCookie(Config::$config['auth_key_cookie']['name']);
				}
				
				/**
				 * Get Auth Key
				 * 
				 * @version BETA-1.8.0
				 * @updated BETA-2.0.0
				 * @return string Returns the Auth Key.
				 */
				public function getAuthKey() {
					if(empty($this->cache['authKey'])) $this->cache['authKey'] = $this->getCookie(Config::$config['auth_key_cookie']['name']);
					return $this->cache['authKey'];
				}
		
		/** ----------------------- */
		/**  VII. 6. User Accounts  */
		/** ----------------------- */
		
			/** --------------------- */
			/**  VII. 5. A. Requests  */
			/** --------------------- */
			
			/** Get the requests expire delay */
			// -- Deprecated --
			public function getRequestsExpireDelay() { return Config::$config['request_expire_delay']; }
			
			/**
			 * Create a new request
			 * 
			 * @version BETA
			 * @updated BETA-2.0.0
			 * @return string|boolean Returns the request activate key.
			 */
			public function createRequest($uid, $action, &$requestTime = null) {
				if(!$this->isAccountsManagementReady()) trigger_error('Sorry, the user management has been disabled.', E_USER_ERROR);
				else {
					$requestsMatches['activate_key'] = hash('sha512', $activateKey = $this->keygen(6, true, false, true));
					$requestsMatches['uid'] = $uid;
					$requestsMatches['action'] = $action;
					$requestsMatches['request_date'] = date('Y-m-d H:i:s', $requestTime = time());
					$requestsMatches['expire_date'] = date('Y-m-d H:i:s', $requestTime + Config::$config['request_expire_delay']);
					
					if($this->insertAccountLine('REQUESTS', $requestsMatches)) return $activateKey;
					else return false;
				}
			}
			
			/** --------------------- */
			/**  VII. 5. B. Register  */
			/** --------------------- */
			
			/** Is register verification enabled */
			// -- Deprecated --
			public function isRegisterVerificationEnabled() { return Config::$config['account_activation']; }
			public function getRegisterVerificationStatus() { return Config::$config['account_activation']; }
			
			/**
			 * Register a new Account
			 * 
			 * $mailInfos syntax: (array) [ subject, message, headers ]
			 * 
			 * @version BETA
			 * @updated BETA-2.0.0
			 * @return string|boolean Returns the activate key or true if succeeded, false otherwise.
			 */
			public function registerAccount($email, $password, $oliSC = null, $mailInfos = []) {
				// if(!empty($password)) {
					if(is_array($oliSC) OR is_bool($oliSC)) $mailInfos = [$oliSC, $oliSC = null][0];
					
					if(!empty($oliSC) AND $oliSC == $this->getOliSecurityCode()) $isRootRegister = true;
					else if($this->isAccountsManagementReady() AND Config::$config['allow_register']) $isRootRegister = false;
					else $isRootRegister = null;
					
					if($isRootRegister !== null) {
						if($this->isLocalLogin()) {
							if($isRootRegister AND !empty($hashedPassword = $this->hashPassword($password))) {
								$handle = fopen(OLIPATH . '.oliauth', 'w');
								$result = fwrite($handle, json_encode(array('password' => $hashedPassword), JSON_FORCE_OBJECT | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
								fclose($handle);
								return $result ? true : false;
							} else return false;
						} else if(!empty($email) AND $this->isAccountsManagementReady() AND (Config::$config['allow_register'] OR $isRootRegister)) {
							/** Account Clean-up Process */
							if($uid = $this->getAccountInfos('ACCOUNTS', 'uid', array('email' => $_['email']), false) AND $this->getUserRightLevel(array('email' => $email)) == $this->translateUserRight('NEW-USER') AND (!$expireDate = $this->getAccountInfos('REQUESTS', 'expire_date', array('uid' => $uid, 'action' => 'activate'), false) OR strtotime($expireDate) < time())) $this->deleteFullAccount($uid);
							unset($uid);
							
							if(!$this->isExistAccountInfos('ACCOUNTS', array('email' => $email), false) AND (!$isRootRegister OR !$this->isExistAccountInfos('ACCOUNTS', array('user_right' => 'ROOT'), false))) {
								/** Hash the password (may be empty) */
								$hashedPassword = $this->hashPassword($password);
								
								/** Generate a new uid */
								do { $uid = $this->uuidAlt();
								} while($this->isExistAccountInfos('ACCOUNTS', $uid, false));
								
								/** Set other account parameters */
								$userRight = $isRootRegister ? 'ROOT' : (!Config::$config['account_activation'] ? 'USER' : 'NEW-USER');
								
								/** Register Account */
								$this->insertAccountLine('ACCOUNTS', array('uid' => $uid, 'password' => $hashedPassword, 'email' => $email, 'register_date' => date('Y-m-d H:i:s'), 'user_right' => $userRight));
								$this->insertAccountLine('INFOS', array('uid' => $uid));
								$this->insertAccountLine('PERMISSIONS', array('uid' => $uid));
								
								/** Allow to force-disabled account mail activation */
								if($mailInfos !== false) {
									/** Generate Activate Key (if activation needed) */
									if(Config::$config['account_activation']) $activateKey = $this->createRequest($uid, 'activate');
									
									$subject = (!empty($mailInfos) AND is_assoc($mailInfos)) ? $mailInfos['subject'] : 'Your account has been created!';
									$message = (!empty($mailInfos) AND is_assoc($mailInfos)) ? $mailInfos['message'] : null;
									if(!isset($message)) {
										$message .= '<p><b>Welcome</b>, your account has been successfully created! ♫</p>';
										if(!empty($activateKey)) {
											$message .= '<p>One last step! Before you can log into your account, you need to <a href="' . $this->getUrlParam(0) . 'login/activate/' . $activateKey . '">activate your account</a> by clicking on this previous link, or by copying this url into your browser: ' . $this->getUrlParam(0) . 'login/activate/' . $activateKey . '.</p>';
											$message .= '<p>Once your account is activated, this activation link will be deleted. If you choose not to use it, it will automaticaly expire in ' . ($days = floor(Config::$config['request_expire_delay'] /3600 /24)) . ($days > 1 ? ' days' : ' day') . ', then you won\'t be able to use it anymore and anyone will be able to register using the same email you used.</p>';
										} else $message .= '<p>No further action is needed: your account is already activated. You can easily log into your account from <a href="' . $this->getUrlParam(0) . 'login/">our login page</a>, using your email, and – of course – your password.</p>';
										if(!empty(Config::$config['allow_recover'])) $message .= '<p>If you ever lose your password, you can <a href="' . $this->getUrlParam(0) . 'login/recover">recover your account</a> using your email: a confirmation mail will be sent to you on your demand.</p> <hr />';
										
										$message .= '<p>Your user ID: <i>' . $uid . '</i> <br />';
										$message .= 'Your hashed password (what we keep stored): <i>' . $hashedPassword . '</i> <br />';
										$message .= 'Your email: <i>' . $email . '</i> <br />';
										$message .= 'Your rights level: <i>' . $userRight . '</i></p>';
										$message .= '<p>Your password is kept secret and stored hashed in our database. <b>Do not give your password to anyone</b>, including our staff.</p> <hr />';
										
										$message .= '<p>Go on our website – <a href="' . $this->getUrlParam(0) . '">' . $this->getUrlParam(0) . '</a> <br />';
										$message .= 'Login – <a href="' . $this->getUrlParam(0) . 'login/">' . $this->getUrlParam(0) . 'login/</a> <br />';
										if(!empty(Config::$config['allow_recover'])) $message .= 'Recover your account – <a href="' . $this->getUrlParam(0) . 'login/recover">' . $this->getUrlParam(0) . 'login/recover</a></p>';
									}
									$headers = (!empty($mailInfos) AND is_assoc($mailInfos)) ? $mailInfos['headers'] : $this->getDefaultMailHeaders();
									if(is_array($headers)) $headers = implode("\r\n", $headers);
									
									$mailResult = mail($email, $subject, $this->getTemplate('mail', array('__URL__' => $this->getUrlParam(0), '__NAME__' => $this->getSetting('name') ?: 'Oli Mailling Service', '__SUBJECT__' => $subject, '__CONTENT__' => $message)), $headers);
								}
								
								if(!$activateKey OR $mailResult) return $uid;
								else {
									$this->deleteFullAccount($uid);
									return false;
								}
							} else return false; 
						} else return false;
					} else return false;
				// } else return false;
			}
			
			/** ------------------ */
			/**  VII. 5. C. Login  */
			/** ------------------ */
			
			/**
			 * Check if the login process is considered to be local
			 * 
			 * @version BETA-2.0.0
			 * @updated BETA-2.0.0
			 * @return boolean Returns true if local.
			 */
			public function isLocalLogin() {
				return !$this->isAccountsManagementReady() OR !Config::$config['allow_login'];
			}
			
			/**
			 * Check if the login process is handled by an external login page
			 * 
			 * @version BETA-2.0.0
			 * @updated BETA-2.0.0
			 * @return boolean Returns true if external.
			 */
			public function isExternalLogin() {
				return preg_match('/^https?:\/\/(?:[w]{3}\.)?((?:([\da-z\.-]+)\.)*([\da-z-]+\.(?:[a-z\.]{2,6})))\/?(\S+)$/i', Config::$config['external_login_url']);
			}
			
			/**
			 * Get Local Root User informations
			 * 
			 * @version BETA-2.0.0
			 * @updated BETA-2.0.0
			 * @return array|boolean Returns Local Root User informations if they exist, false otherwise.
			 */
			public function getLocalRootInfos($whatVar = null) {
				if(file_exists(OLIPATH . '.oliauth')) {
					$localRootInfos = json_decode(file_get_contents(OLIPATH . '.oliauth'), true);
					return !empty($whatVar) ? $localRootInfos[$whatVar] : $localRootInfos;
				} else return false;
			}
			
			/**
			 * Verify login informations
			 * 
			 * @version BETA
			 * @updated BETA-2.0.0
			 * @return boolean Returns true if valid login infos.
			 */
			public function verifyLogin($logid, $password = null) {
				if(empty($password)) $password = [$logid, $logid = null][0];
				
				if(empty($password)) return false;
				else if($this->isLocalLogin()) return !empty($rootUserInfos = $this->getLocalRootInfos()) AND password_verify($password, $rootUserInfos['password']);
				else if(!empty($logid)) {
					$uid = $this->getAccountInfos('ACCOUNTS', 'uid', array('uid' => $logid, 'username' => $logid, 'email' => $logid), array('where_or' => true), false);
					if($userPassword = $this->getAccountInfos('ACCOUNTS', 'password', $uid, false)) return password_verify($password, $userPassword);
					else return false;
				} else return false;
			}
			
			/**
			 * Handle the login process
			 * 
			 * @version BETA
			 * @updated BETA-2.0.0
			 * @return string|boolean Returns the auth key if logged in successfully, false otherwise.
			 */
			public function loginAccount($logid, $password, $expireDelay = null, $setCookie = true) {
				if($this->isExternalLogin()) return null;
				else if($this->verifyLogin($logid, $password)) {
					if($this->isLocalLogin()) $uid = $logid;
					else {
						$uid = $this->getAccountInfos('ACCOUNTS', 'uid', array('uid' => $logid, 'username' => $logid, 'email' => $logid), array('where_or' => true), false);
						if($this->needsRehashPassword($this->getAccountInfos('ACCOUNTS', 'password', $uid))) $this->updateAccountInfos('ACCOUNTS', array('password' => $this->hashPassword($password)), $uid);
					}
					
					if($this->isLocalLogin() OR $this->getUserRightLevel($uid) >= $this->translateUserRight('USER')) {
						$now = time();
						if(empty($expireDelay) OR $expireDelay <= 0) $expireDelay = Config::$config['default_session_duration'] ?: 2*3600;
						
						$authKey = $this->keygen(Config::$config['auth_key_length'] ?: 32);
						if(!empty($authKey)) {
							$result = null;
							if(!$this->isLocalLogin()) { //!?
							// if(!$this->isLocalLogin() OR $this->isExternalLogin()) { //!?
							// if(!$this->isLocalLogin() AND !$this->isExternalLogin()) { //!?
								/** Cleanup Process */
								// $this->deleteAccountLines('SESSIONS', '`update_date` < NOW() - INTERVAL 2 DAY');
								$this->deleteAccountLines('SESSIONS', '"update_date" < NOW() - INTERVAL \'2 DAY\'');
								
								if($this->isExistAccountInfos('SESSIONS', array('auth_key' => hash('sha512', $authKey)))) $this->deleteAccountLines('SESSIONS', array('auth_key' => hash('sha512', $authKey)));
								
								$now = time();
								$result = $this->insertAccountLine('SESSIONS', array(
									'uid' => $uid,
									'auth_key' => hash('sha512', $authKey),
									'creation_date' => date('Y-m-d H:i:s', $now),
									'ip_address' => $this->getUserIP(),
									'user_agent' => $_SERVER['HTTP_USER_AGENT'],
									'login_date' => date('Y-m-d H:i:s', $now),
									'expire_date' => date('Y-m-d H:i:s', $now + $expireDelay),
									'update_date' => date('Y-m-d H:i:s', $now),
									'last_seen_page' => $this->getUrlParam(0) . implode('/', $this->getUrlParam('params'))
								));
							
							} else {
								$rootUserInfos = $this->getLocalRootInfos();
								$handle = fopen(OLIPATH . '.oliauth', 'w');
								$result = fwrite($handle, json_encode(array_merge($rootUserInfos, array(
									'auth_key' => hash('sha512', $authKey),
									'ip_address' => $this->getUserIP(),
									'login_date' => date('Y-m-d H:i:s', $now),
									'expire_date' => date('Y-m-d H:i:s', $now + $expireDelay)
								)), JSON_FORCE_OBJECT | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
								fclose($handle);
							}
							
							if($setCookie) $this->setAuthCookie($authKey, Config::$config['auth_key_cookie']['expire_delay'] ?: 3600*24*7);
							$this->cache['authKey'] = $authKey;
							
							return $result ? $authKey : false;
						} else return false;
					} else return false;
				} else return false;
			}
			
			/** ------------------- */
			/**  VII. 5. D. Logout  */
			/** ------------------- */
			
			/**
			 * Log out from a session
			 * 
			 * @version BETA
			 * @updated BETA-2.0.0
			 * @return boolean Returns true if logged out successfully, false otherwise.
			 */
			public function logoutAccount($authKey = null, $deleteCookie = true) {
				if($this->isLoggedIn($authKey)) {
					if($this->isLocalLogin()) {
						$rootUserInfos = $this->getLocalRootInfos();
						$handle = fopen(OLIPATH . '.oliauth', 'w');
						$result = fwrite($handle, json_encode(array_merge($rootUserInfos, array('login_date' => null, 'expire_date' => null)), JSON_FORCE_OBJECT | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
						fclose($handle);
					} else $result = $this->deleteAccountLines('SESSIONS', array('auth_key' => hash('sha512', $authKey ? : $this->getAuthKey())));
					
					if($deleteCookie) $this->deleteAuthCookie();
					return $result ? true : false;
				} else return false;
			}
			
			/**
			 * Log out an account on all sessions
			 * 
			 * @version BETA
			 * @updated BETA-2.0.0
			 * @return boolean Returns true if logged out successfully, false otherwise.
			 */
			public function logoutAllAccount($uid = null, $deleteCookie = false) {
				if($this->isLocalLogin()) {
					$rootUserInfos = $this->getLocalRootInfos();
					$handle = fopen(OLIPATH . '.oliauth', 'w');
					$result = fwrite($handle, json_encode(array_merge($rootUserInfos, array('login_date' => null, 'expire_date' => null)), JSON_FORCE_OBJECT | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
					fclose($handle);
				} else {
					if(empty($uid)) $uid = $this->getLoggedUser();
					$result = !empty($uid) ? $this->deleteAccountLines('SESSIONS', array('uid' => $uid)) : false;
				}
				
				if($deleteCookie) $this->deleteAuthCookie();
				return $result ? true : false;
			}
			
			/** ---------------------------------- */
			/**  VII. 5. E. Accounts Restrictions  */
			/** ---------------------------------- */
			
			/** Get prohibited usernames */
			public function getProhibitedUsernames() {
				return Config::$config['prohibited_usernames'];
			}
			
			/** Is prohibited username? */
			public function isProhibitedUsername($username) {
				if(empty($usernae)) return null;
				else if(in_array($username, Config::$config['prohibited_usernames'])) return true;
				else {
					$found = false;
					foreach(Config::$config['prohibited_usernames'] as $eachProhibitedUsername) {
						if(stristr($username, $eachProhibitedUsername)) {
							$found = true;
							break;
						}
					}
					return $found ? true : false;
				}
			}
		
		/** --------------------- */
		/**  VII. 6. User Avatar  */
		/** --------------------- */
		
		/**
		 * Get User Avatar Method
		 * 
		 * @version BETA-2.0.0
		 * @updated BETA-2.0.0
		 * @return string Returns method.
		 */
		public function getUserAvatarMethod($uid = null) {
			return $this->getAccountInfos('ACCOUNTS', 'avatar_method', $uid) ?: 'default';
		}
		
		/**
		 * Get Logged Avatar Method
		 * 
		 * @version BETA-2.0.0
		 * @updated BETA-2.0.0
		 * @return string Returns method.
		 */
		public function getLoggedAvatarMethod() {
			return $this->getUserAvatarMethod();
		}
		
		/**
		 * Get User Avatar
		 * 
		 * @version BETA-2.0.0
		 * @updated BETA-2.0.0
		 * @return string Returns url.
		 */
		public function getUserAvatar($uid = null, $selector = null, $size = null) {
			if(empty($uid)) $uid = $this->getLoggedUser();
			if(empty($selector)) $selector = $this->getUserAvatarMethod($uid);
			
			if($selector == 'gravatar') return 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($this->getAccountInfos('ACCOUNTS', 'email', $uid)))) . (!empty($size) ? '?s=' . $size : null); // File Extension not necessary here.
			else if($selector == 'custom' AND !empty($filetype = $this->getAccountInfos('ACCOUNTS', 'avatar_filetype', $uid)) AND file_exists(MEDIAPATH . 'avatars/' . $uid . '.' . $filetype)) return $this->getMediaUrl() . 'avatars/' . $uid . '.' . $filetype;
			else return $this->getMediaUrl() . 'default-avatar.png';
		}
		
		/**
		 * Get Logged User Avatar
		 * 
		 * @version BETA-2.0.0
		 * @updated BETA-2.0.0
		 * @return string Returns url.
		 */
		public function getLoggedAvatar($selector = null, $size = null) {
			return $this->getUserAvatar(null, $selector, $size);
		}
		
		/**
		 * Save User Avatar
		 * 
		 * @version BETA-2.0.0
		 * @updated BETA-2.0.0
		 * @return string Returns url.
		 */
		public function saveUserAvatar($filename, $filetype, $uid = null) {
			if(empty($uid)) $uid = $this->getLoggedUser();
			if(is_uploaded_file($filename)) {
				// Check if the avatars/ folder exists
				if(!file_exists(MEDIAPATH . 'avatars/')) mkdir(MEDIAPATH . 'avatars/');
				else $this->deleteUserAvatar($uid); // Delete the current custom user avatar (if it exists)
				
				// Save the new custom user avatar
				return move_uploaded_file($filename, MEDIAPATH . 'avatars/' . $uid . '.' . $filetype) AND $this->updateAccountInfos('ACCOUNTS', array('avatar_filetype' => $filetype), $uid);
			} else return false;
		}
		
		/**
		 * Delete User Avatar
		 * 
		 * Return values:
		 * - true: Successfully deleted the user avatar file (see PHP unlink() docs)
		 * - false: Failed to delete the file (see PHP unlink() docs)
		 * - null: Actually did nothing; the file probably does not exists.
		 * 
		 * @version BETA-2.0.0
		 * @updated BETA-2.0.0
		 * @return bool Returns true on success.
		 */
		public function deleteUserAvatar($uid = null) {
			if(file_exists(MEDIAPATH . 'avatars/')) {
				$currentFiletype = $this->getAccountInfos('ACCOUNTS', 'avatar_filetype', $uid);
				if(!empty($currentFiletype) AND file_exists(MEDIAPATH . 'avatars/' . $uid . '.' . $currentFiletype)) return unlink(MEDIAPATH . 'avatars/' . $uid . '.' . $currentFiletype);
				else return null;
			}
			else return null;
		}
		
		/** ----------------------- */
		/**  VII. 7. Hash Password  */
		/** ----------------------- */
		
		/** Hash Password */
		public function hashPassword($password) {
			if(!empty($password)) {
				if(!empty(Config::$config['password_hash']['salt'])) $hashOptions['salt'] = Config::$config['password_hash']['salt'];
				if(!empty(Config::$config['password_hash']['cost'])) $hashOptions['cost'] = Config::$config['password_hash']['cost'];
				return password_hash($password, Config::$config['password_hash']['algorithm'], $hashOptions ?: []);
			} else return null;
		}
		
		public function needsRehashPassword($password) {
			if(!empty(Config::$config['password_hash']['salt'])) $hashOptions['salt'] = Config::$config['password_hash']['salt'];
			if(!empty(Config::$config['password_hash']['cost'])) $hashOptions['cost'] = Config::$config['password_hash']['cost'];
			return password_needs_rehash($password, Config::$config['password_hash']['algorithm'], $hashOptions);
		}

}

}
?>