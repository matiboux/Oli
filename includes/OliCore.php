<?php
/*\
|*|  ---------------------------
|*|  --- [  Oli Framework  ] ---
|*|  --- [   BETA: 1.7.0   ] ---
|*|  ---------------------------
|*|  
|*|  Oli is an open source PHP framework made to help web developers creating their website
|*|  Copyright (C) 2015 Mathieu Guérin ("Matiboux")
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
|*|  Please see the README file for more infos!
|*|  
|*|  --- --- ---
|*|  
|*|  Releases date:
|*|    PRE-DEV: 16 November 2014
|*|    ALPHA: 6 February 2015
|*|    BETA: July 2015
|*|    * Nothing about previous releases
|*|    * [version 1.5]:
|*|              (1.5.0): 17 August 2015
|*|              (1.5.1): 21 August 2015
|*|              (1.5.2): 25 August 2015
|*|              (1.5.3): 26 August 2015
|*|              (1.5.5): 20 November 2015
|*|    * [version 1.6]:
|*|              (1.6.0): 6 December 2015
|*|              (1.6.2): 9 December 2015
|*|              (1.6.3): 10 January 2016
|*|              (1.6.4): 10 February 2016
|*|              (1.6.6): 2 June 2016
|*|    * [version 1.7]:
|*|              (1.7.0): 11 August 2016
\*/

/**
 * Oli Core file
 * 
 * @author Matiboux <matiboux@gmail.com>
 * @license http://www.gnu.org/licenses/agpl.html GNU Affero General Public License, version 3
 * @copyright 2015 Matiboux
 */

/**
 * Oli private namespace
 * 
 * @package \OliFramework
 */
namespace OliFramework {

/**
 * Oli Core Class
 * 
 * @package \OliFramework\OliCore
 */
class OliCore {
	
	/** ------------------ */
	/**  Oli Version Info  */
	/** ------------------ */
	
	/** --------------- */
	/**  Oli Variables  */
	/** --------------- */
	
	/** Addons List */
	private $addonsList = [];
	
	/** Setup Class Timestamp */
	private $setupClassTimestamp = null;
	
	/** Externals Class */
	public $db = null; // Database PDO Object
	public $ErrorHandler = null; // Oli Error Handler
	public $ExceptionHandler = null; // Oli Error Handler
	
	/** Tables Configuration */
	private $settingsTables = [];
	private $shortcutLinksTable = null;
	
	/** Url Params Used for Content load */
	private $fileNameParam = '';
	
	/** Content Type */
	private $currentContentType = '';
	private $defaultContentType = 'HTML';
	private $contentTypeHasBeenForced = false;
	private $currentCharset = '';
	private $defaultCharset = 'utf-8';
	
	/** Html Files Buffer List */
	private $htmlLoaderList = [];
	
	/** CDN Url */
	private $cdnUrl = '';
	
	/** Translations & User Language */
	private $defaultLanguage = 'en';
	private $currentLanguage = '';
	private $translationsTable = null;
	
	/** Post Vars Cookie */
	private $postVarsProtection = false;
	private $postVarsCookieName = '';
	private $postVarsCookieExpireDelay = 1;
	private $postVarsCookieDomain = '';
	private $postVarsCookieSecure = false;
	private $postVarsCookieHttpOnly = false;
	
	/** -------------------- */
	/**  Accounts Variables  */
	/** -------------------- */
	
	/** Enable / Disable Accounts Management */
	private $accountsManagementStatus = false;
	
	/** Tables Configuration */
	public $accountsTable = null;
	private $accountsInfosTable = null;
	private $accountsSessionsTable = null;
	private $accountsRequestsTable = null;
	private $accountsPermissionsTable = null;
	private $accountsRightsTable = null;
	
	/** Hash Preferences */
	private $hashAlgorithm = PASSWORD_DEFAULT;
	private $hashSalt = '';
	private $hashCost = 0;
	
	/** Auth Key Cookie */
	private $authKeyCookieName = null;
	private $authKeyCookieDomain = null;
	private $authKeyCookieSecure = null;
	private $authKeyCookieHttpOnly = null;
	
	/** Register Verification Mode */
	private $registerVerification = false;
	private $requestsExpireDelay = 172800;
	private $defaultUserRight = 'USER';
	private $prohibitedUsernames = [];
	
	/** *** *** *** */
	
	/** --------------- */
	/**  Magic Methods  */
	/** --------------- */
	
	/**
	 * Class Construct function
	 * 
	 * @uses \OliFramework\ErrorManager\ExceptionHandler to handle exceptions
	 * @uses \OliFramework\ErrorManager\ErrorHandler to handle errors
	 * @return self
	 */
	public function __construct() {
		$this->setupClassTimestamp = microtime(true);
		$this->ExceptionHandler = new \OliFramework\ErrorManager\ExceptionHandler;
		$this->ErrorHandler = new \OliFramework\ErrorManager\ErrorHandler;
		
		$this->setContentType('DEFAULT', 'utf-8');
		$this->setCurrentLanguage('DEFAULT');
	}
	
	/**
	 * Class Destruct function
	 * 
	 * Load Html files (CSS styles & JS scripts)
	 * Update user session
	 * 
	 * @return void
	 */
	public function __destruct() {
		$this->loadEndHtmlFiles();
		$this->verifyAuthKey();
	}
	
	/**
	 * To string function
	 * 
	 * @todo Shows oli versionn, copyright, license and other stuff
	 * @return string Show infos about this framework
	 */
	public function __toString() {
		return 'Powered by Oli, a PHP Framework';
	}
	
	/** --------------- */
	/**  Config Loader  */
	/** --------------- */
	
	/**
	 * Decode config text codes
	 * 
	 * May be used in other cases
	 * 
	 * @param string|array $values Values to decode
	 * 
	 * @see OliCore::loadConfig() to see how it's used
	 * @return string|array Decoded value(s)
	 */
	public function decodeConfigValues($values) {
		foreach(((!is_array($values)) ? [$values] : $values) as $eachKey => $eachValue) {
			if(is_array($eachValue)) $result = $this->decodeConfigValues($eachValue);
			else {
				$result = [];
				foreach(explode('|', $eachValue) as $eachPart) {
					$partResult = '';
					if(is_string($eachPart)) {
						if(preg_match('/^"(.*)"$/i', $eachPart, $matches)) $partResult = $eachPart;
						else if(preg_match('/^Setting:(.*)$/i', $eachPart, $matches)) $partResult = $this->getSetting($matches[1]);
						else if(preg_match('/^UrlParam:(.*)$/i', $eachPart, $matches)) $partResult = $this->getUrlParam($matches[1]);
						else if(preg_match('/^ShortcutLink:(.*)$/i', $eachPart, $matches)) $partResult = $this->getShortcutLink($matches[1]);
						else if(preg_match('/^Const:(.*)$/i', $eachPart, $matches)) $partResult = constant($matches[1]);
						else if(preg_match('/^Time:(.*)$/i', $eachPart, $matches) AND preg_match('/^(\d*)\s?(\S*)$/i', $matches[1], $data)) {
							if($data[2] == 'years' OR $data[2] == 'year') $partResult = $data[1] * 365.25 * 24 * 3600;
							else if($data[2] == 'months' OR $data[2] == 'month') $partResult = $data[1] * 30.4375 * 24 * 3600;
							else if($data[2] == 'weeks' OR $data[2] == 'week') $partResult = $data[1] * 7 * 24 * 3600;
							else if($data[2] == 'days' OR $data[2] == 'day') $partResult = $data[1] * 24 * 3600;
							else if($data[2] == 'hours' OR $data[2] == 'hour') $partResult = $data[1] * 3600;
							else if($data[2] == 'minutes' OR $data[2] == 'minute') $partResult = $data[1] * 60;
							else $partResult = $data[1];
						}
						else if(preg_match('/^Size:(.*)$/i', $eachPart, $matches) AND preg_match('/^(\d*)\s?(\S*)$/i', $matches[1], $data)) {
							if($data[2] == 'TB' OR $data[2] == 'To') $partResult = $data[1] * (1000 ** 4);
							else if($data[2] == 'GB' OR $data[2] == 'Go') $partResult = $data[1] * (1000 ** 3);
							else if($data[2] == 'MB' OR $data[2] == 'Mo') $partResult = $data[1] * (1000 ** 2);
							else if($data[2] == 'KB' OR $data[2] == 'Ko') $partResult = $data[1] * 1000;
							
							else if($data[2] == 'TiB' OR $data[2] == 'Tio') $partResult = $data[1] * (1024 ** 4);
							else if($data[2] == 'GiB' OR $data[2] == 'Gio') $partResult = $data[1] * (1024 ** 3);
							else if($data[2] == 'MiB' OR $data[2] == 'Mio') $partResult = $data[1] * (1024 ** 2);
							else if($data[2] == 'KiB' OR $data[2] == 'Kio') $partResult = $data[1] * 1024;
							
							else $partResult = $data[1];
						}
						else if(preg_match('/^MediaUrl$/i', $eachPart)) $partResult = $this->getMediaUrl();
						else if(preg_match('/^DataUrl$/i', $eachPart)) $partResult = $this->getDataUrl();
						else $partResult = $eachPart;
					}
					$result[] = $partResult;
				}
			}
			$output[$eachKey] = count($result) > 1 ? implode($result) : $result[0];
		}
		return (!is_array($value) AND count($output) == 1) ? $output[0] : $output;
	}
	
	/**
	 * Load config from an array
	 * 
	 * @param array $config Config to load
	 * 
	 * @uses OliCore::decodeConfigValues() to decode values
	 * @return void
	 */
	public function loadConfig($config) {
		foreach($config as $eachConfig => $eachValue) {
			$eachValue = $this->decodeConfigValues($eachValue);
			
			if($eachConfig == 'mysql') $this->setupMySQL($eachValue['database'], $eachValue['username'], $eachValue['password'], $eachValue['hostname']);
			else if($eachConfig == 'settings_tables') $this->setSettingsTables($eachValue);
			else if($eachConfig == 'shotcut_links_table') $this->setShortcutLinksTable($eachValue);
			else if($eachConfig == 'default_content_type') $this->setDefaultContentType($eachValue);
			else if($eachConfig == 'default_charset') $this->setDefaultCharset($eachValue);
			else if($eachConfig == 'cdn_url') $this->setCdnUrl($eachValue);
			else if($eachConfig == 'default_user_language') $this->setDefaultLanguage($eachValue);
			else if($eachConfig == 'translations_table') $this->setTranslationsTable($eachValue);
			else if($eachConfig == 'time_zone') $this->setTimeZone($eachValue);
			else if($eachConfig == 'post_vars_cookie') {
				foreach($eachValue as $eachKey => $eachParam) {
					if($eachKey == 'name') $this->setPostVarsCookieName($eachParam);
					else if($eachKey == 'domain') $this->setPostVarsCookieDomain($eachParam);
					else if($eachKey == 'secure') $this->setPostVarsCookieSecure($eachParam);
					else if($eachKey == 'http_only') $this->setPostVarsCookieHttpOnly($eachParam);
				}
			}
			else if($eachConfig == 'login_management' AND $eachValue) $this->enableAccountsManagement();
			else if($eachConfig == 'accounts_tables') {
				foreach($eachValue as $eachKey => $eachTable) {
					if($eachKey == 'accounts') $this->setAccountsTable($eachTable);
					else if($eachKey == 'infos') $this->setAccountsInfosTable($eachTable);
					else if($eachKey == 'sessions') $this->setAccountsSessionsTable($eachTable);
					else if($eachKey == 'requests') $this->setAccountsRequestsTable($eachTable);
					else if($eachKey == 'permissions') $this->setAccountsPermissionsTable($eachTable);
					else if($eachKey == 'rights') $this->setAccountsRightsTable($eachTable);
				}
			}
			else if($eachConfig == 'prohibited_usernames') $this->setProhibitedUsernames($eachValue);
			else if($eachConfig == 'register') {
				foreach($eachValue as $eachKey => $eachParam) {
					if($eachKey == 'verification') {
						if($eachParam) $this->enableRegisterVerification();
						else $this->disableRegisterVerification();
					}
					else if($eachKey == 'request_expire_delay') $this->setRequestsExpireDelay($eachParam);
				}
			}
			else if($eachConfig == 'hash') {
				foreach($eachValue as $eachKey => $eachParam) {
					if($eachKey == 'algorithm') $this->setHashAlgorithm($eachParam);
					else if($eachKey == 'salt') $this->setHashSalt($eachParam);
					else if($eachKey == 'cost') $this->setHashCost($eachParam);
				}
			}
			else if($eachConfig == 'auth_key_cookie') {
				foreach($eachValue as $eachKey => $eachParam) {
					if($eachKey == 'name') $this->setAuthKeyCookieName($eachParam);
					else if($eachKey == 'domain') $this->setAuthKeyCookieDomain($eachParam);
					else if($eachKey == 'secure') $this->setAuthKeyCookieSecure($eachParam);
					else if($eachKey == 'http_only') $this->setAuthKeyCookieHttpOnly($eachParam);
				}
			}
		}
	}
	
	/** *** *** *** */
		
	/** ------------------- */
	/**  Addons Management  */
	/** ------------------- */
	
	/**
	 * Add addon
	 * 
	 * @param string $name Addon name
	 * @param string $var Addon variable
	 * 
	 * @return void
	 */
	public function addAddon($name, $var) {
		$this->addonsList[$name] = $var;
	}
	
	/**
	 * Get addon name
	 * 
	 * @param string $var Addon variable
	 * 
	 * @return string Addon name
	 */
	public function getAddonName($var) {
		return array_search($var, $this->addonsList);
	}
	
	/**
	 * Get addon var
	 * 
	 * @param string $name Addon name
	 * 
	 * @return string Addon variable
	 */
	public function getAddonVar($name) {
		return $this->addonsList[$name];
	}
	
	/**
	 * Is exist addon
	 * 
	 * @param string $info Addon name or variable
	 * 
	 * @return boolean
	 */
	public function isExistAddon($info) {
		return (array_key_exists($info, $this->addonsList) OR array_search($info, $this->addonsList)) ? true : false;
	}
	
	/** --------------- */
	/**  Configuration  */
	/** --------------- */
	
		/** ------------------ */
		/**  MySQL PDO Object  */
		/** ------------------ */
		
		/**
		 * Setup MySQL & Config
		 * 
		 * @param string $database MySQL database name
		 * @param string|void $username MySQL username
		 * @param string|void $password MySQL password
		 * @param string|void $hostname MySQL hostname
		 * 
		 * @uses \PDO to create link to the MySQL database
		 * @throws PDOException if an error occurred (while linking the MySQL database)
		 * @return void
		 */
		public function setupMySQL($database, $username = 'root', $password = '', $hostname = 'localhost') {
			try {
				$this->db = new \PDO('mysql:host=' . $hostname . ';dbname=' . $database . ';charset=utf8', $username, $password);
			}
			catch(PDOException $e) {
				trigger_error($e->getMessage(), E_USER_ERROR);
			}
		}
		
		/** ------------------- */
		/**  Oli Configuration  */
		/** ------------------- */
		
			/** -------------- */
			/**  MySQL Tables  */
			/** -------------- */
			
			/**
			 * Set Oli settings tables
			 * 
			 * @param string|array $tables Oli settings tables
			 * 
			 * @uses OliCore::$settingsTables to set the settings tables
			 * @return void
			 */
			public function setSettingsTables($tables) {
				$this->settingsTables = (!is_array($tables)) ? [$tables] : $tables;
			}
			
			/**
			 * Set shortcut links table
			 * 
			 * @param string $table Shortcut links table
			 * 
			 * @uses OliCore::$shortcutLinksTable to set the shortcut links table
			 * @return void
			 */
			public function setShortcutLinksTable($table) {
				$this->shortcutLinksTable = $table;
			}
			
			/**
			 * Set translations table
			 * 
			 * @param string $table Translations table
			 * 
			 * @uses OliCore::$translationsTable to set the translations table
			 * @return void
			 */
			public function setTranslationsTable($table) {
				$this->translationsTable = $table;
			}
			
			/** -------------- */
			/**  Content Type  */
			/** -------------- */
			
			/**
			 * Set default content type
			 * 
			 * @param string $defaultContentType Default content type
			 * 
			 * @uses OliCore::$defaultContentType to set the default content type
			 * @return void
			 */
			public function setDefaultContentType($defaultContentType) {
				$this->defaultContentType = $defaultContentType;
			}
			
			/**
			 * Set default charset
			 * 
			 * @param string $defaultCharset Default charset
			 * 
			 * @uses OliCore::$defaultCharset to set the default charset
			 * @return void
			 */
			public function setDefaultCharset($defaultCharset) {
				$this->defaultCharset = $defaultCharset;
			}
			
			/** -------------------- */
			/**  CDN (common files)  */
			/** -------------------- */
			
			/**
			 * Set CDN url (common files)
			 * 
			 * @param string $url CDN url
			 * 
			 * @uses OliCore::$cdnUrl to set the CDN url
			 * @return void
			 */
			public function setCdnUrl($url) {
				$this->cdnUrl = $url;
			}
			
			/** ------------------------- */
			/**  Translations & Language  */
			/** ------------------------- */
			
			/**
			 * Set default user language
			 * 
			 * @param string $language Default language
			 * 
			 * @uses OliCore::$defaultLanguageto to set the default language
			 * @return void
			 */
			public function setDefaultLanguage($language = 'en') {
				$this->defaultLanguage = $language;
			}
			
			/** ------------------ */
			/**  Post Vars Cookie  */
			/** ------------------ */
			
			/**
			 * Set post vars cookie name
			 * 
			 * @param string $name Cookie name
			 * 
			 * @uses OliCore::$postVarsCookieName to set the cookie name
			 * @return void
			 */
			public function setPostVarsCookieName($name) {
				$this->postVarsCookieName = $name;
			}
			
			/**
			 * Set post vars cookie expire delay
			 * 
			 * @param integer $delay Cookie expire delay
			 * 
			 * @uses OliCore::$postVarsCookieExpireDelay to set the cookie expire delay
			 * @deprecated Default delay is 1 second and should not be anything else
			 * @return void
			 */
			public function setPostVarsCookieExpireDelay($delay) { 
				$this->postVarsCookieExpireDelay = $delay;
			}
			
			/**
			 * Set post vars cookie domain
			 * 
			 * @param string $domain Cookie domain
			 * 
			 * @uses OliCore::$postVarsCookieDomain to set the cookie domain
			 * @return void
			 */
			public function setPostVarsCookieDomain($domain) {
				$this->postVarsCookieDomain = $domain;
			}
			
			/**
			 * Set post vars cookie secure parameter
			 * 
			 * @param boolean $secure Cookie secure parameter
			 * 
			 * @uses OliCore::$postVarsCookieSecure to set the cookie secure parameter
			 * @return void
			 */
			public function setPostVarsCookieSecure($secure) {
				$this->postVarsCookieSecure = $secure;
			}
			
			/**
			 * Set post vars cookie http only parameter
			 * 
			 * @param boolean $httponly Cookie http only parameter
			 * 
			 * @uses OliCore::$postVarsCookieHttpOnly to set the cookie http only parameter
			 * @return void
			 */
			public function setPostVarsCookieHttpOnly($httponly) {
				$this->postVarsCookieHttpOnly = $httponly;
			}
			
			/** ------------------ */
			/**  Time Zone & Date  */
			/** ------------------ */
			
			/**
			 * Set time zone
			 * 
			 * @param string $timezone Time Zone Code
			 * 
			 * @uses date_default_timezone_set() to set time zone
			 * @return boolean
			 */
			public function setTimeZone($timezone) {
				return date_default_timezone_set($timezone);
			}
		
		/** ------------------------ */
		/**  Accounts Configuration  */
		/** ------------------------ */
		
			/** -------------- */
			/**  MySQL Tables  */
			/** -------------- */
			
			/**
			 * Set main accounts table
			 * 
			 * @param string $table Main accounts table
			 * 
			 * @uses OliCore::$accountsTable to set the main accounts table
			 * @return void
			 */
			public function setAccountsTable($table) {
				$this->accountsTable = $table;
			}
			
			/**
			 * Set accounts infos table
			 * 
			 * @param string $table Accounts infos table
			 * 
			 * @uses OliCore::$accountsInfosTable to set the accounts infos table
			 * @return void
			 */
			public function setAccountsInfosTable($table) {
				$this->accountsInfosTable = $table;
			}
			
			/**
			 * Set accounts sessions table
			 * 
			 * @param string $table Accounts sessions table
			 * 
			 * @uses OliCore::$accountsSessionsTable to set the accounts sessions table
			 * @return void
			 */
			public function setAccountsSessionsTable($table) {
				$this->accountsSessionsTable = $table;
			}
			
			/**
			 * Set accounts requests table
			 * 
			 * @param string $table Accounts requests table
			 * 
			 * @uses OliCore::$accountsRequestsTable to set the accounts requests table
			 * @return void
			 */
			public function setAccountsRequestsTable($table) {
				$this->accountsRequestsTable = $table;
			}
			
			/**
			 * Set accounts permissions table
			 * 
			 * @param string $table Accounts permissions table
			 * 
			 * @uses OliCore::$accountsPermissionsTable to set the accounts permissions table
			 * @return void
			 */
			public function setAccountsPermissionsTable($table) {
				$this->accountsPermissionsTable = $table;
			}
			
			/**
			 * Set accounts rights table
			 * 
			 * @param string $table Accounts rights table
			 * 
			 * @uses OliCore::$accountsRightsTable to set the accounts rights table
			 * @return void
			 */
			public function setAccountsRightsTable($table) {
				$this->accountsRightsTable = $table;
			}
			
			/** -------------------- */
			/**  Hash Configuration  */
			/** -------------------- */
			
			/**
			 * Set hash algorithm
			 * 
			 * @param string $algorithm Hash algorithm
			 * 
			 * @uses OliCore::$hashAlgorithm to set the hash algorithm
			 * @return void
			 */
			public function setHashAlgorithm($algorithm) {
				$this->hashAlgorithm = $algorithm;
			}
			
			/**
			 * Set hash salt
			 * 
			 * @param string $salt Hash salt
			 * 
			 * @uses OliCore::$hashSalt to set the hash salt
			 * @return void
			 */
			public function setHashSalt($salt) {
				$this->hashSalt = $salt;
			}
			
			/**
			 * Set hash cost
			 * 
			 * @param integer $cost Hash cost
			 * 
			 * @uses OliCore::$hashCost to set the hash cost
			 * @return void
			 */
			public function setHashCost($cost) {
				$this->hashCost = $cost;
			}
			
			/** ------------------------------- */
			/**  Auth Key Cookie Configuration  */
			/** ------------------------------- */
			
			/**
			 * Set auth key cookie name
			 * 
			 * @param string $name Cookie name
			 * 
			 * @uses OliCore::$authKeyCookieName to set the cookie name
			 * @return void
			 */
			public function setAuthKeyCookieName($name) {
				$this->authKeyCookieName = $name;
			}
			
			/**
			 * Set auth key cookie domain
			 * 
			 * @param string $domain Cookie domain
			 * 
			 * @uses OliCore::$authKeyCookieDomain to set the cookie domain
			 * @return void
			 */
			public function setAuthKeyCookieDomain($domain) {
				$this->authKeyCookieDomain = $domain;
			}
			
			/**
			 * Set auth key cookie secure parameter
			 * 
			 * @param boolean $secure Cookie secure parameter
			 * 
			 * @uses OliCore::$authKeyCookieSecure to set the cookie secure parameter
			 * @return void
			 */
			public function setAuthKeyCookieSecure($secure) {
				$this->authKeyCookieSecure = $secure;
			}
			
			/**
			 * Set auth key cookie http only parameter
			 * 
			 * @param boolean $httponly Cookie http only parameter
			 * 
			 * @uses OliCore::$authKeyCookieHttpOnly to set the cookie http only parameter
			 * @return void
			 */
			public function setAuthKeyCookieHttpOnly($httponly) {
				$this->authKeyCookieHttpOnly = $httponly;
			}
			
			/** ----------------------- */
			/**  Register Verification  */
			/** ----------------------- */
			
			/**
			 * Enable mail register verification
			 * 
			 * @uses OliCore::$registerVerification to enable the register verification
			 * @uses OliCore::$defaultUserRight to set the default user right
			 * @return void
			 */
			public function enableRegisterVerification() {
				$this->registerVerification = true;
				$this->defaultUserRight = 'NEW-USER';
			}
			
			/**
			 * Disable mail register verification
			 * 
			 * @uses OliCore::$registerVerification to disable the register verification
			 * @uses OliCore::$defaultUserRight to set the default user right
			 * @return void
			 */
			public function disableRegisterVerification() {
				$this->registerVerification = false;
				$this->defaultUserRight = 'USER';
			}
			
			/**
			 * Set requests expire delay
			 * 
			 * @param integer $delay Requests expire delay
			 * 
			 * @uses OliCore::$requestsExpireDelay to set the requests expire delay
			 * @return void
			 */
			public function setRequestsExpireDelay($delay) {
				$this->requestsExpireDelay = $delay;
			}
			
			/**
			 * Set prohibited usernames
			 * 
			 * @param string|array $usernames Prohibited usernames
			 * 
			 * @uses OliCore::$prohibitedUsernames to set the prohibited usernames
			 * @return void
			 */
			public function setProhibitedUsernames($usernames) {
				$this->prohibitedUsernames = (!is_array($usernames)) ? [$usernames] : $usernames;
			}
	
	/** *** *** *** */
	
	/** ----------------- */
	/**  MySQL Functions  */
	/** ----------------- */
	
		/** ----------------- */
		/**  Check Functions  */
		/** ----------------- */
		
		/**
		 * Is setup MySQL connection
		 * 
		 * @uses OliCore::$db to check the MySQL connection status
		 * @return boolean|void Returns the MySQL connection status
		 */
		public function isSetupMySQL() {
			if(!empty($this->db)) return true;
			else trigger_error('No MySQL connection available', E_USER_ERROR);
		}
		
		/**
		 * Get raw MySQL PDO Object
		 * 
		 * @uses OliCore::isSetupMySQL() to check the MySQL connection
		 * @uses OliCore::$db to return PDO object
		 * @deprecated OliCore::$db can be directly accessed
		 * @return object Returns current MySQL PDO object
		 */
		public function getRawMySQL() {
			$this->isSetupMySQL();
			return $this->db;
		}
	
		/** ---------------- */
		/**  Read Functions  */
		/** ---------------- */
		
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
			$this->isSetupMySQL();
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
		public function getLinesMySQL($table, $where = [], $settings = null, $caseSensitive = null, $forceArray = null, $rawResult = null) {
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
		public function getInfosMySQL($table, $whatVar, $where = [], $settings = null, $caseSensitive = null, $forceArray = null, $rawResult = null) {
			$whatVar = (!is_array($whatVar)) ? [$whatVar] : $whatVar;
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
						$valueArray[] = (!isset($lineResult) OR count($lineResult) > 1) ? $lineResult : array_values($lineResult)[0];
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
		public function getSummedInfosMySQL($table, $whatVar, $where = [], $settings = null, $caseSensitive = null, $rawResult = null) {
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
		public function isEmptyInfosMySQL($table, $whatVar, $where = [], $settings = null, $caseSensitive = null) {
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
		public function isExistInfosMySQL($table, $where = [], $caseSensitive = true) {
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
		
		/** ----------------- */
		/**  Write Functions  */
		/** ----------------- */
		
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
			$this->isSetupMySQL();
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
			$this->isSetupMySQL();
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
			$this->isSetupMySQL();
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
		
		/** -------------------- */
		/**  Database Functions  */
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
		public function createTableMySQL($table, $columns) {
			$this->isSetupMySQL();
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
			$this->isSetupMySQL();
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
			$this->isSetupMySQL();
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
			$this->isSetupMySQL();
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
			$this->isSetupMySQL();
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
			$this->isSetupMySQL();
			
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
			$this->isSetupMySQL();
			$query = $this->db->prepare('ALTER TABLE ' . $table . ' DROP ' . $column . ')');
			return $query->execute();
		}
	
	/** *** *** *** */
	
	/** --------------- */
	/**  Oli Functions  */
	/** --------------- */
	
		/** ------------------- */
		/**  Load Page Content  */
		/** ------------------- */
		
		/**
		 * Load page content
		 * 
		 * @uses OliCore::getUserLanguage() to get the current language
		 * @uses OliCore::setCurrentLanguage() to set the current language
		 * @uses OliCore::getUrlParam() to get url params
		 * @uses OliCore::$fileNameParam to set the file name param
		 * @return string|void Path to the page
		 */
		public function loadContent() {
			if(!empty($this->getUserLanguage())) $this->setCurrentLanguage($this->getUserLanguage());
			
			$params = $this->getUrlParam('params');
			if(!empty($params)) {
				foreach($params as $eachParam) {
					$fileName[] = $eachParam;
					if(count($fileName) > 1 AND $fileName[0] == 'data') break;
					else if(file_exists(THEMEPATH . implode('/', $fileName) . '.php')) {
						$found = THEMEPATH . implode('/', $fileName) . '.php';
						$this->fileNameParam = implode('/', $fileName);
					}
					else if(file_exists(THEMEPATH . implode('/', $fileName) . '/index.php')) {
						$found = THEMEPATH . implode('/', $fileName) . '/index.php';
						$this->fileNameParam = implode('/', $fileName);
					}
					else if(empty($found) AND $fileName[0] == 'home' AND file_exists(THEMEPATH . 'index.php')) $found = THEMEPATH . 'index.php';
				}
			}
			
			if(!empty($found)) return $found;
			else if(file_exists(THEMEPATH . '404.php')) return THEMEPATH . '404.php';
			else die('Erreur 404');
		}
	
		/** --------- */
		/**  General  */
		/** --------- */
		
		/**
		 * Get settings tables
		 * 
		 * @uses OliCore::$settingsTables to get the settings tables
		 * @return string|array Settings tables
		 */
		public function getSettingsTables() {
			return $this->settingsTables;
		}
		
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
			foreach($this->settingsTables as $eachTable) {
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
			if(isset($this->shortcutLinksTable)) return $this->getInfosMySQL($this->shortcutLinksTable, 'url', array('name' => $shortcut));
			else return false;
		}
		
		/**
		 * Get execution delay
		 * 
		 * @param boolean|void $fromRequest Get delay from request time or not
		 * 
		 * @uses OliCore::$setupClassTimestamp to get Oli setup timestamp
		 * @return integer Execution delay
		 */
		public function getExecutionDelay($fromRequest = false) {
			if($fromRequest) return microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
			else return microtime(true) - $this->setupClassTimestamp;
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
		
		/** --------------------- */
		/**  Translations & Text  */
		/** --------------------- */
		
			/** ---------------- */
			/**  Read Functions  */
			/** ---------------- */
			
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
				return $this->getLinesMySQL($this->translationsTable, $where, $settings, $caseSensitive, $forceArray, $rawResult);
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
				return $this->getInfosMySQL($this->translationsTable, $whatLanguage, $where, $settings, $caseSensitive, $forceArray, $rawResult);
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
				return $this->isExistInfosMySQL($this->translationsTable, $where, $caseSensitive);
			}
			
			/** ----------------- */
			/**  Write Functions  */
			/** ----------------- */
			
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
				return $this->insertLineMySQL($this->translationsTable, $translations);
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
				return $this->updateInfosMySQL($this->translationsTable, $what, $where);
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
				return $this->deleteLinesMySQL($this->translationsTable, $where);
			}
			
			/** ------------------- */
			/**  Translation Tools  */
			/** ------------------- */
			
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
				if($this->currentLanguage != $this->defaultLanguage AND $translatedText = $this->getTranslation($this->currentLanguage, array($this->defaultLanguage => $text)))
					echo $translatedText;
				else {
					if(!$this->isExistTranslation(array($this->defaultLanguage => $text))) $this->addTranslations(array($this->defaultLanguage => $text));
					echo $text;
				}
			}
		
		/** ---------------- */
		/**  HTTP Functions  */
		/** ---------------- */
		
			/** -------------- */
			/**  Content Type  */
			/** -------------- */
			
			/**
			 * Set content type
			 * 
			 * @param string|void $contentType Content type to set
			 * @param string|void $charset Charset to use
			 * @param boolean|void $force Force the new content type
			 * 
			 * @uses OliCore::$contentTypeHasBeenForced to lock the new content type, if forced
			 * @uses OliCore::$defaultContentType to get the default content type
			 * @uses OliCore::$currentContentType to get the current content type
			 * @uses OliCore::$defaultCharset to get the default charset
			 * @uses OliCore::$currentCharset to get the current charset
			 * @return boolean|void
			 */
			public function setContentType($contentType = null, $charset = null, $force = false) {
				if(!$this->contentTypeHasBeenForced OR $force) {
					if($force) $this->contentTypeHasBeenForced = true;
					
					if(!isset($contentType) OR $contentType == 'DEFAULT') $contentType = $this->defaultContentType;
					if(!isset($charset) OR $charset == 'DEFAULT') $charset = $this->defaultCharset;
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
				}
				else
					return false;
			}
			
			/**
			 * Get current content type
			 * 
			 * @uses OliCore::$currentContentType to get the current content type
			 * @return string
			 */
			public function getContentType() {
				return $this->currentContentType;
			}
			
			/**
			 * Get current charset
			 * 
			 * @uses OliCore::$currentContentType to get the current content type
			 * @return string
			 */
			public function getCharset() {
				return $this->currentCharset;
			}
			
			/** ------------------- */
			/**  Cookie Management  */
			/** ------------------- */
			
				/** -------------------------- */
				/**  Create and Delete Cookie  */
				/** -------------------------- */
				
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
						if(!setcookie($name, $value, time() + $expireDelay, '/', $eachDomain, $secure, $httpOnly)) {
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
			
				/** ----------- */
				/**  Get Infos  */
				/** ----------- */
				
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
			
			/** ------------ */
			/**  _POST vars  */
			/** ------------ */
		
				/** -------------------------- */
				/**  Create and Delete Cookie  */
				/** -------------------------- */
				
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
					return $this->setCookie($this->postVarsCookieName, $postVars, $this->postVarsCookieExpireDelay, '/', $this->postVarsCookieDomain, $this->postVarsCookieSecure, $this->postVarsCookieHttpOnly);
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
					if(!$this->postVarsProtection) return $this->deleteCookie($this->postVarsCookieName, '/', $this->postVarsCookieDomain, $this->postVarsCookieSecure, $this->postVarsCookieHttpOnly);
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
					return $this->setCookie($this->postVarsCookieName, $this->getRawPostVars(), $this->postVarsCookieExpireDelay, '/', $this->postVarsCookieDomain, $this->postVarsCookieSecure, $this->postVarsCookieHttpOnly);
				}
				
				/** ----------- */
				/**  Get Infos  */
				/** ----------- */
				
				/**
				 * Get post vars cookie name
				 * 
				 * @uses OliCore::$postVarsCookieName to get the post vars cookie name
				 * @return string Returns the post vars cookie name
				 */
				public function getPostVarsCookieName() {
					return $this->postVarsCookieName;
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
					$postVars = $this->getCookieContent($this->postVarsCookieName, $rawResult);
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
					return isset($whatVar) ? empty($this->getPostVars($whatVar)) : $this->isEmptyCookie($this->postVarsCookieName);
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
					return isset($whatVar) ? $this->getPostVars($whatVar) !== null : $this->isExistCookie($this->postVarsCookieName);
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
		
		/** ---------------- */
		/**  HTML Functions  */
		/** ---------------- */
		
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
		public function loadStyle($url, $loadNow = true, $minimize = false) {
			if($minimize) $codeLine = '<style type="text/css">' . $this->minimizeStyle(file_get_contents($url)) . '</style>';
			else $codeLine = '<link rel="stylesheet" type="text/css" href="' . $url . '">';
			
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
		 * @uses OliCore::getDataUrl() to get data url
		 * @return void
		 */
		public function loadLocalStyle($url, $loadNow = true, $minimize = false) {
			$this->loadStyle($this->getDataUrl() . $url, $loadNow, $minimize);
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
		public function loadCdnStyle($url, $loadNow = true, $minimize = false) {
			$this->loadStyle($this->getCdnUrl() . $url, $loadNow, $minimize);
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
		public function loadScript($url, $loadNow = true, $minimize = false) {
			if($minimize) $codeLine = '<script type="text/javascript">' . $this->minimizeScript(file_get_contents($url)) . '</script>';
			else $codeLine = '<script type="text/javascript" src="' . $url . '"></script>';
			
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
		 * @uses OliCore::getDataUrl() to get data url
		 * @return void
		 */
		public function loadLocalScript($url, $loadNow = true, $minimize = false) {
			$this->loadScript($this->getDataUrl() . $url, $loadNow, $minimize);
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
		public function loadCdnScript($url, $loadNow = true, $minimize = false) {
			$this->loadScript($this->getCdnUrl() . $url, $loadNow, $minimize);
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
			echo PHP_EOL;
			foreach($this->htmlLoaderList as $eachCodeLine) {
				echo array_shift($this->htmlLoaderList) . PHP_EOL;
			}
		}
		
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
		
		/** -------------------- */
		/**  URL Read Functions  */
		/** -------------------- */
		
		/**
		 * Get url parameter
		 * 
		 * $param variable values:
		 * null => Full Url (e.g. 'http://hello.example.com/page/param')
		 * 'base' => Get base url (e.g. 'http://hello.example.com/')
		 * 'allbases' => Get all bases urls (e.g. ['http://hello.example.com/', 'http://example.com/'])
		 * 'alldomains' => Get all domains (e.g. ['hello.example.com', 'example.com'])
		 * 'fulldomain' => Get domain (e.g. 'hello.example.com')
		 * 'domain' => Get main domain (e.g. 'example.com')
		 * 'subdomain' => Get subdomains (e.g. 'hello')
		 * 'all' => All url fragments
		 * 'params' => All parameters fragments
		 * 0 => Url without any parameters (same as base url)
		 * 1 => First parameter: file name parameter (e.g. 'page')
		 * # => Other parameters (e.g. 2 => 'param')
		 * 
		 * @param integer|string|null|void $param Parameter to get
		 * 
		 * @uses OliCore::getSetting() to get url setting
		 * @uses OliCore::$fileNameParam to get the file name parameter
		 * @return string|array|boolean Parameter wanted
		 */
		public function getUrlParam($param = null) {
			if(!isset($param) OR $param < 0) return (!empty($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
			else {
				$urlSetting = !empty($this->getSetting('url')) ? (!is_array($this->getSetting('url')) ? [$this->getSetting('url')] : $this->getSetting('url')) : null;
				if(in_array($param, ['allbases', 'alldomains'], true)) {
					$allBases = $allDomains = [];
					foreach($urlSetting as $eachUrl) {
						preg_match("/^(https?:\/\/)?(((?:[w]{3}\.)?(?:[\da-z\.-]+\.)*(?:[\da-z-]+\.(?:[a-z\.]{2,6})))\/?(?:.)*)/", $eachUrl, $matches);
						$allBases[] = ($matches[1] ?: (!empty($_SERVER['HTTPS']) ? 'https://' : 'http://')) . $matches[2];
						$allDomains[] = $matches[3];
					}
					
					if($param == 'allbases') return $allBases;
					else if($param == 'alldomains') return $allDomains;
				}
				else {
					$frationnedUrl = explode('/', $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
					$baseUrl = !empty($_SERVER['HTTPS']) ? 'https://' : 'http://';
					$shortBaseUrl = '';
					
					if(isset($urlSetting)) {
						$baseUrlMatch = false;
						$baseUrl = !empty($_SERVER['HTTPS']) ? 'https://' : 'http://';
						$shortBaseUrl = '';
						$countLoop = 0;
						
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
					if(!isset($urlSetting) OR !$baseUrlMatch) $baseUrl = (!empty($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/';
					
					if(in_array($param, [0, 'base'], true)) return $baseUrl;
					else if(in_array($param, ['fulldomain', 'subdomain', 'domain'], true)) {
						preg_match("/^https?:\/\/(?:[w]{3}\.)?((?:([\da-z\.-]+)\.)*([\da-z-]+\.(?:[a-z\.]{2,6})))\/?/", $baseUrl, $matches);
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
		
		/**
		 * Get full url
		 * 
		 * @uses OliCore::getUrlParam() to get full url
		 * @deprecated OliCore::getUrlParam() can be directly used instead
		 * @return string Full url
		 */
		public function getFullUrl() {
			return $this->getUrlParam();
		}
		
		/**
		 * Get url to data files
		 * 
		 * @uses OliCore::getUrlParam() to get full url
		 * @deprecated OliCore::getUrlParam() can be directly used instead
		 * @return string Full url
		 */
		public function getDataUrl() {
			return $this->getUrlParam(0) . 'content/theme/data/';
		}
		
		/**
		 * Get url to media content
		 * 
		 * @uses OliCore::getUrlParam() to get base url
		 * @return string Full url
		 */
		public function getMediaUrl() {
			return $this->getUrlParam(0) . 'content/media/';
		}
		
		/**
		 * Get url to cdn files
		 * 
		 * @uses OliCore::$cdnUrl to get cdn url
		 * @return string Returns Full url
		 */
		public function getCdnUrl() {
			return $this->cdnUrl;
		}
		
		/** -------------------------- */
		/**  User Language Management  */
		/** -------------------------- */
		
		/**
		 * Get default language
		 * 
		 * @uses OliCore::$defaultLanguage to get default language
		 * @return string Returns default language
		 */
		public function getDefaultLanguage() {
			return $this->defaultLanguage;
		}
		
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
			$this->currentLanguage = (!empty($language) AND $language != 'DEFAULT') ? strtolower($language) : $this->defaultLanguage;
		}
		
		/**
		 * Get current language
		 * 
		 * @uses OliCore::$currentLanguage to get current language
		 * @return string Returns current language
		 */
		public function getCurrentLanguage() {
			return $this->currentLanguage;
		}
		
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
			$language = (!empty($language) AND $language != 'DEFAULT') ? strtolower($language) : $this->defaultLanguage;
			
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
		
		/** ------- */
		/**  Tools  */
		/** ------- */
		
			/** ---------------------- */
			/**  Generators Functions  */
			/** ---------------------- */
			
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
		
			/** ----------------------- */
			/**  Date & Time Functions  */
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
			
			/** ----------------------- */
			/**  Client Infos Functions */
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
						if(preg_match("/^([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)/", $entry, $ip_list)){
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
		public function enableAccountsManagement() {
			$this->accountsManagementStatus = true;
		}
		
		/**
		 * Is accounts management enabled
		 * 
		 * @uses OliCore::$accountsManagementStatus to get accounts management status
		 * @return boolean Accounts management status
		 */
		public function getAccountsManagementStatus() {
			return $this->accountsManagementStatus;
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
				if($tableCode == 'ACCOUNTS') return $this->accountsTable;
				else if($tableCode == 'INFOS') return $this->accountsInfosTable;
				else if($tableCode == 'SESSIONS') return $this->accountsSessionsTable;
				else if($tableCode == 'REQUESTS') return $this->accountsRequestsTable;
				else if($tableCode == 'PERMISSIONS') return $this->accountsPermissionsTable;
				else if($tableCode == 'RIGHTS') return $this->accountsRightsTable;
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
			public function getAccountLines($tableCode, $where = [], $settings = null, $caseSensitive = null, $forceArray = null, $rawResult = null) {
				if(is_string($where)) $where = array('username' => $where);
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
			public function getAccountInfos($tableCode, $whatVar, $where = [], $settings = null, $caseSensitive = null, $forceArray = null, $rawResult = null) {
				if(is_string($where)) $where = array('username' => $where);
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
			public function getSummedAccountInfos($tableCode, $whatVar, $where = [], $caseSensitive = true) {
				if(is_string($where)) $where = array('username' => $where);
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
			public function isEmptyAccountInfos($tableCode, $whatVar, $where = [], $settings = null, $caseSensitive = null) {
				if(is_string($where)) $where = array('username' => $where);
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
			public function isExistAccountInfos($tableCode, $where = [], $caseSensitive = true) {
				if(is_string($where)) $where = array('username' => $where);
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
				if(is_string($where) AND $where != 'all') $where = array('username' => $where);
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
			
			/**
			 * Get right level
			 * 
			 * @param string $userRight User right to get level of
			 * @param boolean|void $caseSensitive Translate is case sensitive or not
			 * 
			 * @uses OliCore::translateUserRight() to translate user right
			 * @return integer Returns user right level
			 */
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
				return $this->getAccountInfos('RIGHTS', 'permissions', array('user_right' => $userRight), $caseSensitive);
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
			 * @uses OliCore::getRightLevel() to get right level
			 * @uses OliCore::getUserRight() to get user right
			 * @return integer Returns user right level
			 */
			public function getUserRightLevel($where = null, $caseSensitive = true) {
				return $this->getRightLevel($this->getUserRight($where, $caseSensitive));
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
				return $this->setCookie($this->authKeyCookieName, $authKey, $expireDelay, '/', $this->authKeyCookieDomain, $this->authKeyCookieSecure, $this->authKeyCookieHttpOnly);
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
				return $this->deleteCookie($this->authKeyCookieName, '/', $this->authKeyCookieDomain, $this->authKeyCookieSecure, $this->authKeyCookieHttpOnly);
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
			public function getAuthKeyCookieName() {
				return $this->authKeyCookieName;
			}
			
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
				return $this->getCookieContent($this->authKeyCookieName);
			}
			
			/**
			 * Verify auth key validity
			 * 
			 * @param string|void $authKey Auth key to check
			 * 
			 * @uses OliCore::getAuthKey() to get the auth key
			 * @uses OliCore::isExistAccountInfos() to get if infos exists in account table
			 * @uses OliCore::getAccountInfos() to get infos from account table
			 * @uses OliCore::updateAccountInfos() to update infos from account table
			 * @uses OliCore::getUrlParam() to get url parameters
			 * @api
			 * @return boolean Returns true if auth key is valid, false otherwise
			 */
			public function verifyAuthKey($authKey = null) {
				$authKey = (!empty($authKey)) ? $authKey : $this->getAuthKey();
				if(!empty($authKey) AND $this->isExistAccountInfos('SESSIONS', array('auth_key' => $authKey)) AND strtotime($this->getAccountInfos('SESSIONS', 'expire_date', array('auth_key' => $authKey))) >= time()) {
					$this->updateAccountInfos('SESSIONS', array('update_date' => date('Y-m-d H:i:s'), 'last_seen_page' => implode('/', $this->getUrlParam('params'))), array('auth_key' => $authKey));
					return true;
				}
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
				$authKey = (!empty($authKey)) ? $authKey : $this->getAuthKey();
				if($this->verifyAuthKey($authKey))
					return $this->getAccountInfos('SESSIONS', 'username', array('auth_key' => $authKey));
				else
					return false;
			}
		
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
			public function getRequestsExpireDelay() {
				return $this->requestsExpireDelay;
			}
			
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
			public function createRequest($username, $action) {
				$requestsMatches['id'] = $this->getLastAccountInfo('REQUESTS', 'id') + 1;
				$requestsMatches['username'] = $username;
				$requestsMatches['activate_key'] = $this->keygen(6, false, true, true);
				$requestsMatches['action'] = $action;
				$requestsMatches['request_date'] = date('Y-m-d H:i:s');
				$requestsMatches['expire_date'] = date('Y-m-d H:i:s', time() + $this->requestsExpireDelay);
				$this->insertAccountLine('REQUESTS', $requestsMatches);
				
				return $requestsMatches['activate_key'];
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
			public function getRegisterVerificationStatus() {
				return $this->registerVerification;
			}
			
			/**
			 * Register a new account
			 * 
			 * @param string $username Username to use
			 * @param string $password Password to set to
			 * @param string $email Request action to set to
			 * 
			 * @uses OliCore::$accountsManagement to get the requests expire delay
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
			public function registerAccount($username, $password, $email) {
				if(!$this->accountsManagement) trigger_error('La gestion de compte n\'est pas activée', E_USER_ERROR);
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
						$accountsMatches['user_right'] = $this->defaultUserRight;
						$this->insertAccountLine('ACCOUNTS', $accountsMatches);
						
						$infosMatches['id'] = $this->getLastAccountInfo('INFOS', 'id') + 1;
						$infosMatches['username'] = $username;
						$this->insertAccountLine('INFOS', $infosMatches);
					
						if($this->registerVerification) {
							$activateKey = $this->createRequest($username, 'activate');
							
							$subject = 'Activation de votre compte';
							$message = 'Bonjour ' . $username . ', <br />';
							$message .= 'Un compte a été créé à votre email. <br />';
							$message .= 'Si vous n\'avez pas créé de compte, veuillez ignorer ce message, <br />';
							$message .= 'Sinon, veuillez vous rendre sur ce lien pour activer votre compte : <br />';
							$message .= '<a href="' . $this->getUrlParam(0) . 'login.php/activate/' . $activateKey . '">' . $this->getUrlParam(0) . 'login.php/activate/' . $activateKey . '</a> <br />';
							$message .= 'Vous avez jusqu\'au ' . date('d/m/Y', strtotime($this->getAccountInfos('REQUESTS', 'expire_date', array('username' => $username))) + $this->requestsExpireDelay) . ' pour activer votre compte, <br />';
							$message .= 'Une fois cette date passée, le code d\'activation ne sera plus valide';
							$headers = 'From: contact@' . $this->getSetting('domain') . "\r\n";
							$headers .= 'MIME-Version: 1.0' . "\r\n";
							$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
							
							if(!mail($email, $subject, utf8_decode($message), $headers)) {
								$this->deleteFullAccount($username);
								return false;
							}
						}
						return true;
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
				if($this->verifyLogin($username, $password)) {
					$username = $this->getAccountInfos('ACCOUNTS', 'username', array('email' => $username), false) ?: $this->getAccountInfos('ACCOUNTS', 'username', $username, false);
					if($this->getUserRightLevel($username, false) >= $this->translateUserRight('USER')) {
						$newAuthKey = $this->keygen(100);
						if(empty($expireDelay) OR $expireDelay <= 0) $expireDelay = 24*3600;
						
						$matches['id'] = $this->getLastAccountInfo('SESSIONS', 'id') + 1;
						$matches['username'] = $username;
						$matches['auth_key'] = $newAuthKey;
						$matches['user_ip'] = $this->getUserIP();
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
			 * @uses OliCore::deleteLinesMySQL() to delete lines from account table
			 * @uses OliCore::deleteAuthKeyCookie() to delete the auth key cookie
			 * @return boolean Returns true if logout succeed, false otherwise
			 */
			public function logoutAccount() {
				if($this->deleteLinesMySQL($this->accountsSessionsTable, array('auth_key' => $this->getAuthKey()))) {
					$this->deleteAuthKeyCookie();
					return true;
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
				return $this->prohibitedUsernames;
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
				return in_array($username, $this->prohibitedUsernames);
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
			$options = [];
			if(!empty($this->hashSalt)) $options['salt'] = $this->hashSalt;
			if(!empty($this->hashCost)) $options['cost'] = $this->hashCost;
			
			return password_hash($password, $this->hashAlgorithm, $options);
		}

}

}
?>