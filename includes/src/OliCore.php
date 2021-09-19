<?php
/*\
|*|  ----------------------------------
|*|  --- [  Oli - PHP Framework   ] ---
|*|  --- [  Version GAMMA: 1.0.0  ] ---
|*|  ----------------------------------
|*|  
|*|  Oli is an open source PHP framework designed to help you create your website.
|*|  
|*|  Github repository: https://github.com/matiboux/Oli/
|*|  
|*|  Creator & Developer: Matiboux (Mathieu Guérin)
|*|   → Github: @matiboux – https://github.com/matiboux/
|*|  
|*|  For more info, please read the README.md file.
|*|  You can find it in the project repository (see the Github link above).
|*|  
|*|  --- --- ---
|*|  
|*|  Copyright (C) 2015-2021 Matiboux (Mathieu Guérin)
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
\*/

/*\
|*|  ╒════════════════════════╕
|*|  │ :: TABLE OF CONTENT :: │
|*|  ╞════════════════════════╛
|*|  │
|*|  ├ I. Variables
|*|  ├ II. Constructor & Destructor
|*|  ├ III. Magic Methods
|*|  │
|*|  ///
|*|  │
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

namespace Oli;

abstract class OliCore
{
	// region I. Variables

	/** List of public variables accessible publicly in read-only */
	private static array $readOnlyVars = [
		'initTimestamp',
		'oliInfos',
		'addonsInfos',
		'fileNameParam',
		'contentStatus',
	];

	/** Components infos */
	private ?float $initTimestamp = null; // (PUBLIC READONLY)
	private array $oliInfos = []; // Oli Infos (SPECIAL PUBLIC READONLY)
	private array $addonsInfos = []; // Addons Infos (PUBLIC READONLY)

	/** Databases Management */
	private array $dbs = []; // SQL Wrappers
	// private ?string $defaultdb = null; // Selected DB (default db)
	// private $sql = null; // MySQL PDO Object (PUBLIC READONLY)
	// private $dbError = null; // MySQL PDO Error (PUBLIC READONLY)

	/** Content Management */
	private ?string $fileNameParam = null; // Define Url Param #0 (PUBLIC READONLY)
	private ?string $contentStatus = null; // Content Status (found, not found, forbidden...) (PUBLIC READONLY)

	/** Page Settings */
	private ?string $contentType = null;
	private ?string $charset = null;
	private bool $contentTypeBeenForced = false;
	private array $htmlLoaderList = [];

	/** Post Vars Cookie */
	private bool $postVarsProtection = false;

	/** Data Cache */
	private array $cache = [];

	// endregion

	// region II. Constructor & Destructor

	/**
	 * OliCore Class Construct function
	 *
	 * @return void
	 * @version BETA
	 * @updated BETA-2.0.0
	 */
	public function __construct(?float $initTimestamp = null)
	{
		// Load config
		Config::loadConfig($this);
		// if (file_exists(ABSPATH . '.oliurl'))
		// {
		// $oliUrl = file_get_contents(ABSPATH . '.oliurl');
		// if (!empty($oliUrl) && preg_match('/^(?:[a-z0-9-]+\.)+[a-z.]+(?:\/[^/]+)*\/$/i', $oliUrl) && !empty(Config::$config['settings']) && $oliUrl != Config::$config['settings']['url'])
		// {
		// $this->updateConfig(array('settings' => array_merge(Config::$config['settings'], array('url' => $oliUrl))), true);
		// Config::$config['settings']['url'] = $oliUrl;
		// }
		// }

		// Framework Initialization
		$this->initTimestamp = $initTimestamp ?: microtime(true);
		$this->setContentType('DEFAULT', 'utf-8');

		// Check for debug stutus override
		if (@Config::$config['debug'] === false && @$_GET['oli-debug'] === $this->getSecurityCode())
			Config::$config['debug'] = true;

		// Debug configuration
		if (@Config::$config['debug'] === true) error_reporting(E_ALL);
	}

	/**
	 * OliCore Class Destruct function
	 *
	 * @return void
	 * @version BETA
	 * @updated BETA-2.0.0
	 */
	public function __destruct()
	{
		$this->loadEndHtmlFiles();
	}

	// endregion

	// region III. Magic Methods

	/**
	 * OliCore Class Read-only variables management
	 *
	 * @return mixed Returns the requested variable value if is allowed to read, null otherwise.
	 * @version BETA
	 * @updated BETA-2.0.0
	 */
	public function __get($whatVar)
	{
		if ($whatVar == 'config') return Config::$config;
		if (!in_array($whatVar, self::$readOnlyVars)) return null;
		if ($whatVar == 'oliInfos') return $this->getOliInfos();
		return $this->$whatVar;
	}

	/**
	 * OliCore Class Is Set variables management
	 * This fix the empty() false negative issue on inaccessible variables.
	 *
	 * @return mixed Returns true if the requested variable isn't empty and if is allowed to read, null otherwise.
	 * @version BETA
	 * @updated BETA-2.0.0
	 */
	public function __isset($name)
	{
		if (in_array($name, self::$readOnlyVars, true)) return isset($this->$name);

		return false;
	}

	/**
	 * OliCore Class to String function
	 *
	 * @return string Returns Oli Infos.
	 * @version BETA-1.8.1
	 * @updated BETA-2.0.0
	 */
	public function __toString()
	{
		return $this->getOliInfos('name') . ' (v. ' . $this->getOliInfos('version') . ')';
	}

	// endregion

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
	 * @param mixed|null $whatInfo
	 *
	 * @return array|string|null Returns a short description of Oli.
	 * @version BETA
	 * @updated BETA-2.0.0
	 */
	public function getOliInfos(mixed $whatInfo = null): array|string|null
	{
		// Load Oli Infos if not already loaded
		if (empty($this->oliInfos))
			$this->oliInfos = file_exists(INCLUDESPATH . 'oli-infos.json')
				? json_decode(file_get_contents(INCLUDESPATH . 'oli-infos.json'), true) : null;

		return !empty($whatInfo) ? @$this->oliInfos[$whatInfo] : $this->oliInfos;
	}

	/** Get Team Infos */
	public function getTeamInfos($who = null, $whatInfo = null): mixed
	{
		if (empty($who)) return $this->oliInfos['team'];

		foreach ($this->oliInfos['team'] as $eachMember)
		{
			if ($eachMember['name'] == $who or in_array($who, !is_array($eachMember['nicknames']) ? [$eachMember['nicknames']] : $eachMember['nicknames']))
			{
				if (!empty($whatInfo)) return $eachMember[$whatInfo];
				else return $eachMember;
			}
		}

		return null;
	}

	/** --------------------------- */
	/**  III. 2. Oli Security Code  */
	/** --------------------------- */

	/**
	 * Get Oli Security Code
	 *
	 * @return bool|string Returns Oli Security Code.
	 * @version BETA-2.0.0
	 * @updated GAMMA-1.0.0
	 */
	public function getSecurityCode(): bool|string
	{
		return $this->refreshSecurityCode() ?: file_get_contents(ABSPATH . '.olisc');
	}

	/**
	 * Refresh Oli Security Code
	 *
	 * @return string|boolean Returns Oli Security Code if it was updated, false otherwise.
	 * @version BETA-2.0.0
	 * @updated GAMMA-1.0.0
	 */
	public function refreshSecurityCode()
	{
		if (!file_exists(ABSPATH . '.olisc')
		    || time() > filemtime(ABSPATH . '.olisc') + 3600 * 2 // TODO: Add a config for this?
		    || empty(file_get_contents(ABSPATH . '.olisc')))
		{
			$handle = fopen(ABSPATH . '.olisc', 'w');
			$sc = $this->keygen(6, true, false, true);
			fwrite($handle, $sc);
			fclose($handle);
			return $sc;
		}

		return false;
	}

	/** --------------- */
	/**  III. 3. Tools  */
	/** --------------- */

	/**
	 * Get Execution Time
	 *
	 * @param bool $sinceRequest If true, return the execution time since the request.
	 *
	 * @return float Returns the execution time.
	 */
	public function getExecutionTime(bool $sinceRequest = false): float
	{
		if ($sinceRequest) return microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
		return microtime(true) - Config::$config['init_timestamp'];
	}

	/** *** *** */

	/** ------------------- */
	/**  IV. Configuration  */
	/** ------------------- */

	/** ----------- */
	/**  IV. 1. DB  */
	/** ----------- */

	/**
	 * Add a SQL database
	 *
	 * @param DBWrapper $db
	 * @param string|null $dbname
	 *
	 * @return void
	 * @version GAMMA-1.0.0
	 * @updated GAMMA-1.0.0
	 */
	public function addDB(DBWrapper $db, ?string $dbname = null): void
	{
		if ($dbname === null)
			$dbname = $db->getDBname();

		$this->dbs[$dbname] = $db;
	}

	/**
	 * Remove a SQL database
	 *
	 * @param string $dbname The name of the database to remove.
	 *
	 * @return bool Returns true if the database was successfully removed.
	 * @version GAMMA-1.0.0
	 * @updated GAMMA-1.0.0
	 */
	public function removeDB(string $dbname): bool
	{
		if (array_key_exists($dbname, $this->dbs)) return false;
		unset($this->dbs[$dbname]);
		return true;
	}

	/**
	 * Remove all SQL databases
	 *
	 * @version BETA-1.8.0
	 * @updated GAMMA-1.0.0
	 */
	public function resetDB(): void
	{
		$this->dbs = [];
	}

	/** ---------------- */
	/**  IV. 2. General  */
	/** ---------------- */

	/** Set Settings Tables */
	public function setSettingsTables($tables)
	{
		Config::$config['settings_tables'] = $tables = !is_array($tables) ? [$tables] : $tables;
		$hasArray = false;
		foreach ($tables as $eachTableGroup)
		{
			if (!is_array($eachTableGroup) && !$hasArray) continue;

			$hasArray = true;
			Config::$config['settings_tables'] = $eachTableGroup;
			$this->getUrlParam('base', $hasUsedHttpHostBase);

			if (!$hasUsedHttpHostBase) break;
		}

		$i = 1;
		while ($i <= strlen(Config::$config['media_path']) && $i <= strlen(Config::$config['pages_path']) && substr(Config::$config['media_path'], 0, $i) == substr(Config::$config['pages_path'], 0, $i))
		{
			$contentPath = substr(Config::$config['media_path'], 0, $i);
			$i++;
		}

		define('CONTENTPATH', ABSPATH . ($contentPath ?: 'content/'));
		define('MEDIAPATH', Config::$config['media_path'] ? ABSPATH . Config::$config['media_path'] : CONTENTPATH . 'media/');
		define('PAGESPATH', Config::$config['pages_path'] ? ABSPATH . Config::$config['pages_path'] : CONTENTPATH . 'pages/');
	}

	/** Set Common Files Path */
	public function setCommonPath($path)
	{
		if (empty($path)) return;
		Config::$config['common_path'] = $path;
		if (!defined('COMMONPATH')) define('COMMONPATH', ABSPATH . $path);
	}

	/** --------------- */
	/**  IV. 3. Addons  */
	/** --------------- */

	/** ---------------------- */
	/**  IV. 3. A. Management  */
	/** ---------------------- */

	/** Add Addon */
	public function addAddon($id, $varname)
	{
		$this->addonsInfos[$id]['varname'] = $varname;
	}

	/** Remove Addon */
	// public function removeAddons(...$id) {}
	public function removeAddon($id)
	{
		unset($this->addonsInfos[$id]);
	}

	/** Is exist Addon */
	public function isExistAddon($id)
	{
		return array_key_exists($id, $this->addonsInfos);
	}

	/** Rename Addon */
	public function renameAddon($id, $newId)
	{
		if ($this->isExistAddon($id) and !$this->isExistAddon($newId))
		{
			$this->addonsInfos[$newId] = $this->addonsInfos[$id];
			$this->removeAddon($id);
			return true;
		}
		else return false;
	}

	/** ----------------- */
	/**  IV. 5. B. Infos  */
	/** ----------------- */

	/** Add Addon Infos */
	public function addAddonInfos($id, $infos)
	{
		$this->addonsInfos[$id] = array_merge($this->addonsInfos[$id], !is_array($infos) ? [$infos] : $infos);
	}
	// public function addAddonInfo($id, $infoId, $infoValue) {}

	/** Remove Addon Infos */
	// public function removeAddonInfos($id, ...$infoIds) {}
	public function removeAddonInfo($id, $infoId)
	{
		unset($this->addonsInfos[$id][$infoId]);
	}

	/** Is exist Addon */
	public function isExistAddonInfo($id, $infoId): bool
	{
		return array_key_exists($infoId, $this->addonsInfos[$id]);
	}

	/** Get Addon Infos */
	public function getAddonInfos($id = null, $infoId = null)
	{
		if (!isset($id) or $id == '*') return $this->addonsInfos;
		else if ($this->isExistAddon($id))
		{
			if ($this->isExistAddonInfo($id, $infoId)) return $this->addonsInfos[$id][$infoId];
			else return $this->addonsInfos[$id];
		}
		else return false;
	}

	public function getAddonVar($id)
	{
		if ($this->isExistAddon($id) and $this->isExistAddonInfo($id, 'varname')) return $this->addonsInfos[$id]['varname'];
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
	 * Is setup DB connection
	 *
	 * @param string|null $dbname
	 *
	 * @return bool Returns the MySQL connection status
	 * @version BETA
	 * @updated BETA-2.0.0
	 */
	public function isSetupDB(?string $dbname = null): bool
	{
		return $this->getDB($dbname)?->isSetupDB() === true;
	}

	/**
	 * Get DB Wrapper object
	 *
	 * @param string|null $dbname
	 *
	 * @return DBWrapper|null Returns used DB Wrapper object
	 * @version GAMMA-1.0.0
	 * @updated GAMMA-1.0.0
	 */
	public function getDB(?string $dbname = null): ?DBWrapper
	{
		return @$this->dbs[$dbname !== null ? $dbname : array_key_first($this->dbs)];
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
	 * @return string Returns the path to the file to include.
	 * @version BETA
	 * @updated GAMMA-1.0.0
	 */
	public function loadContent(?array $params = null): string
	{
		if (!is_array($params))
			$params = $this->getUrlParam('params');

		$accessAllowed = null;
		$contentStatus = null;
		$found = null;

		// Default content rules
		$defaultRules = [
			'index' => 'index.php',
			'error' => ['403' => '403.php',
			            '404' => '404.php'],
			'access' => ['*' => ['allow' => '*']],
		];

		// User content rules
		$rules = $this->getContentRules(PAGESPATH . '.olicontent', $defaultRules);

		if (!empty($params))
		{
			$fileName = [];
			$countFileName = 0;

			foreach ($params as $param)
			{
				if (empty($param)) break; // Filename can't be empty.

				$fileName[] = $param;
				++$countFileName;
				$fileNameParam = implode('/', $fileName);
				$slicedFileNameParam = implode('/', array_slice($fileName, 1));

				// Oli Setup
//				if ($fileName[0] == Config::$config['setup_alias']
//				    || (Config::$config['setup_wizard'] && !@Config::$config['debug']))
//				{
				// Assets
//					if ($countFileName > 1 && is_file(OLISETUPPATH . $slicedFileNameParam))
//					{
//						$found = OLISETUPPATH . $slicedFileNameParam;
//						$this->fileNameParam = $fileNameParam;
//						$this->setAutoContentType($fileNameParam);
//						break; // Sub-level pages not allowed.
//					}
//
				// Setup Page
//					else if (is_file(OLISETUPPATH . 'setup.php'))
//					{
//						$found = OLISETUPPATH . 'setup.php';
//						$this->fileNameParam = Config::$config['setup_alias'];
//						continue; // There may be a requested page.
//					}
//				}

				// Oli Login
//				else
				if ($fileName[0] == Config::$config['login_alias'])
				{
					// Assets
					if ($countFileName > 1 && is_file(OLILOGINPATH . $slicedFileNameParam))
					{
						$found = OLILOGINPATH . $slicedFileNameParam;
						$this->fileNameParam = $fileNameParam;
						$this->setAutoContentType($fileNameParam);
						break; // Sub-level pages not allowed.
					}

					// Login Page
					else if (is_file(OLILOGINPATH . 'login.php'))
					{
						$found = OLILOGINPATH . 'login.php';
						$this->fileNameParam = $fileName[0];
						// continue; // There may be a requested page.
					}
				}

				// Oli Admin
				else if ($fileName[0] == Config::$config['admin_alias'])
				{
					// Assets
					if ($countFileName > 1 && is_file(OLIADMINPATH . $slicedFileNameParam))
					{
						$found = OLIADMINPATH . $slicedFileNameParam;
						$this->fileNameParam = $fileNameParam;
						$this->setAutoContentType($fileNameParam);
						break; // Sub-level pages not allowed.
					}

					// Custom Pages
					else if ($countFileName > 1 && is_file(OLIADMINPATH . $slicedFileNameParam . '.php'))
					{
						$found = OLIADMINPATH . $slicedFileNameParam . '.php';
						$this->fileNameParam = $fileNameParam;
						break; // Sub-level pages not allowed.
					}

					// Home Page
					else if (is_file(OLIADMINPATH . 'index.php'))
					{
						$found = OLIADMINPATH . 'index.php';
						$this->fileNameParam = $fileNameParam;
						// continue; // There may be a requested page.
					}
				}

				// Scripts
				else if ($fileName[0] == Config::$config['scripts_alias'])
				{
					$pagesPathOptions = [
						SCRIPTSPATH, // User Scripts
						OLISCRIPTPATH, // Oli Scripts
					];
					foreach ($pagesPathOptions as $pagesPath)
					{
						if (is_file($pagesPath . $slicedFileNameParam))
						{
							$found = $pagesPath . $slicedFileNameParam;
							$this->fileNameParam = $fileNameParam;
							$this->setContentType('JSON');
							break 2; // Break the outer foreach loop
						}
					}
				}

				// User Assets
				else if ($fileName[0] == Config::$config['assets_alias'])
				{
					if (is_file(ASSETSPATH . $slicedFileNameParam))
					{
						$found = ASSETSPATH . $slicedFileNameParam;
						$this->fileNameParam = $fileNameParam;
						$this->setAutoContentType($slicedFileNameParam);
						break;
					}
				}

				// User Media
				else if ($fileName[0] == Config::$config['media_alias'])
				{
					if (is_file(MEDIAPATH . $slicedFileNameParam))
					{
						$found = MEDIAPATH . $slicedFileNameParam;
						$this->fileNameParam = $fileNameParam;
						$this->setAutoContentType($slicedFileNameParam);
						break;
					}
				}

				// User Pages
				else
				{
					$pagesPathOptions = [
						[PAGESPATH, &$rules], // User Pages
						[OLIPAGESPATH, &$defaultRules], // Placeholder Pages
					];
					foreach ($pagesPathOptions as [$pagesPath, $pagesRules])
					{
						// Custom Page (supports sub-directory)
						if (is_file($pagesPath . $fileNameParam . '.php'))
						{
							$accessAllowed = $this->isAccessAllowed($pagesRules['access'], $fileNameParam . '.php');
							if ($accessAllowed)
							{
								$found = $pagesPath . $fileNameParam . '.php';
								$this->fileNameParam = $fileNameParam;
								break; // Break the foreach pages path loop
								// Keep looping over params, looking for sub-level pages
							}
						}

						// Sub-directory Home Page
						else if (is_file($pagesPath . $fileNameParam . '/' . $pagesRules['index']))
						{
							$accessAllowed = $this->isAccessAllowed($pagesRules['access'], $fileNameParam . '/' . $pagesRules['index']);
							if ($accessAllowed)
							{
								$found = $pagesPath . $fileNameParam . '/' . $pagesRules['index'];
								$contentStatus = 'index';
								break; // Break the foreach pages path loop
								// Keep looping over params, looking for sub-level pages
							}
						}

						// Home Page
						else if ($fileName[0] == 'home' && is_file($pagesPath . $pagesRules['index']))
						{
							$accessAllowed = $this->isAccessAllowed($pagesRules['access'], $pagesRules['index']);
							if ($accessAllowed)
							{
								$found = $pagesPath . $pagesRules['index'];
								$contentStatus = 'index';
								break; // Break the foreach pages path loop
								// Keep looping over params, looking for sub-level pages
							}
						}
					}
				}
			}
		}

		// if($this->contentType == 'text/html') echo '<!-- ' . $this . ' -->' . "\n\n";

		// Forbidden
		if ($accessAllowed === false)
		{
			http_response_code(403); // 403 Forbidden
			$this->contentStatus = '403';

			// User 403 error page
			if (file_exists(PAGESPATH . $rules['error']['403']))
				return PAGESPATH . $rules['error']['403'];

			// Oli 403 error page
			if (file_exists(OLIPAGESPATH . $defaultRules['error']['403']))
				return OLIPAGESPATH . $defaultRules['error']['403'];

			die('Error 403: Access forbidden');
		}

		// Found
		else if (!empty($found))
		{
			http_response_code(200); // 200 OK
			$this->contentStatus = $contentStatus ?: 'found';
			return $found;
		}

		// Not Found
		else
		{
			http_response_code(404); // 404 Not Found
			$this->contentStatus = '404';

			// User 404 error page
			if (file_exists(PAGESPATH . $rules['error']['404'])
			    && $this->isAccessAllowed($rules['access'], $rules['error']['404']))
				return PAGESPATH . $rules['error']['404'];

			// Oli 404 error page
			if (file_exists(OLIPAGESPATH . $defaultRules['error']['404'])
			    && $this->isAccessAllowed($defaultRules['access'], $defaultRules['error']['404']))
				return OLIPAGESPATH . $defaultRules['error']['404'];

			die('Error 404: File not found');
		}
	}

	/**
	 * Get content rules
	 *
	 * @return boolean Returns whether or not access to the file is allowed
	 * @version BETA
	 * @updated BETA-2.0.0
	 */
	public function getContentRules(string $filename, array $rules = []): array
	{
		// Try to get the custom content rules
		$rawRules = @file_get_contents($filename);
		if ($rawRules === false) return $rules; // Default rules

		// Split on newlines
		$rawRules = preg_split('/\R+/', $rawRules);
		if ($rawRules === false) return $rules; // Default rules

		foreach ($rawRules as $rawRule)
		{
			// Match string "[ruleType]: [ruleValue]"
			if (!preg_match('/^([^\s]+)\s*:\s*([^\s]+)\s*$/', $rawRule, $matches)) continue;

			$ruleType = strtolower($matches[1]);
			$ruleValue = $matches[2];

			// Invalid type
			if (empty($ruleType)) continue;

			// Error
			if ($ruleType === 'error')
			{
				// Match string "000 file.ext"
				if (!preg_match('/^(\d{3})\s+(.*)$/', $ruleValue, $matches))
					continue;

				$rules['error'][$matches[1]] = $matches[2];
			}

			// Access
			else if ($ruleType === 'access')
			{
				// Match string
				if (!preg_match('/^(?:"?((?:(?<=")[^"]*(?="))|\S*)"?\s+)?(\w+)(?:\s+(.*))?$/', $ruleValue, $matches))
					continue;

				$file = $matches[1] ?: '*';
				$access = strtolower($matches[2]);
				$scope = $matches[3] ?: '*';

				// Initialize the access rules array for the file
				if (@$rules['access'][$file] === null)
					$rules['access'][$file] = [];

				// Global rule
				if ($scope === '*')
					$rules['access'][$file][$access] = '*';

				// Complex rule
				else if (preg_match('/^(?:from\s+(\w+))?\s*(?:to\s+(\w+))?$/', $scope, $rights))
				{
					if (!is_array(@$rules['access'][$file][$access]))
						$rules['access'][$file][$access] = [];

					$rules['access'][$file][$access]['from'] =
						$this->translateUserRight($rights[1]);
					$rules['access'][$file][$access]['to'] =
						$this->translateUserRight($rights[2]);
				}

				// Simple rule
				else $rules['access'][$file][$access] = $this->translateUserRight($scope);
			}

			// Index & Custom rules
			else $rules[$ruleType] = $ruleValue;
		}

		return $rules;
	}

	/**
	 * Access rules check for a file
	 *
	 * @return boolean Returns whether or not access to the file is allowed
	 * @version BETA
	 * @updated BETA-2.0.0
	 */
	public function isAccessAllowed($accessRules, $filename)
	{
		if (empty($accessRules)) return false;

		// File access rules
		if (!empty($accessRules[$filename]))
		{
			$access = $this->isAccessAllowedExplicit($accessRules, $filename);
			if ($access !== null) return $access;
		}

		// Folder access rules
		while (!empty($accessRules[$filename = substr($filename, 0, strrpos($filename, '/'))]))
		{
			$access = $this->isAccessAllowedExplicit($accessRules, $filename . '/*');
			if ($access !== null) return $access;
		}

		// Global access rules
		if (!empty($accessRules['*']))
		{
			$access = $this->isAccessAllowedExplicit($accessRules, '*');
			if ($access !== null) return $access;
		}

		// Default access: denied
		return false;
	}

	/**
	 * Explicit access rules check for a file
	 *
	 * @return boolean|null Returns whether or not access to the file is explicitly allowed, null if implicit
	 * @version BETA-2.0.0
	 * @updated BETA-2.0.0
	 */
	private function isAccessAllowedExplicit($accessRules, $identifier)
	{
		if (@$accessRules[$identifier] === null) return null; // Implicit access rule

		if (@$accessRules[$identifier]['deny'] === '*') return false;
		if (@$accessRules[$identifier]['allow'] === '*') return true;
		if ($this->isAccountsManagementReady() and ($userRightLevel = $this->getUserRightLevel()))
		{
			// Deny checks
			if (($denyfrom = @$accessRules[$identifier]['deny']['from']) !== null and $denyfrom <= $userRightLevel) return false;
			if (($denyto = @$accessRules[$identifier]['deny']['to']) !== null and $denyto >= $userRightLevel) return false;

			// Allow checks
			if (($allowfrom = @$accessRules[$identifier]['allow']['from']) !== null and $allowfrom <= $userRightLevel) return true;
			if (($allowto = @$accessRules[$identifier]['allow']['to']) !== null and $allowto >= $userRightLevel) return true;
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
	 * @return array Returns the settings tables.
	 * @deprecated Directly accessible with OliCore::$config
	 * @version BETA
	 * @updated BETA-2.0.0
	 */
	public function getSettingsTables()
	{
		return Config::$config['settings_tables'];
	}

	/**
	 * Get Setting
	 *
	 * @return string|boolean Returns the requested setting if succeeded.
	 * @version BETA
	 * @updated BETA-2.0.0
	 */
	public function getSetting(string $setting, int $depth = 0)
	{
		$isExist = [];
		if ($this->isSetupDB() and !empty(Config::$config['settings_tables']))
		{
			foreach (($depth > 0 and count(Config::$config['settings_tables']) > $depth) ? array_slice(Config::$config['settings_tables'], $depth) : Config::$config['settings_tables'] as $eachTable)
			{
				$db = $this->getDB();
				if ($db->isExistTableSQL($eachTable))
				{
					$isExist[] = true;
//					if (isset($setting))
//					{
					$optionResult = $db->getInfosSQL($eachTable, 'value', ['name' => $setting]);
					if (!empty($optionResult))
					{
						if ($optionResult == 'null') return '';
						else return $optionResult;
					}
//					}
//					else return false; // $this->getInfosMySQL($eachTable, ['name', 'value']);
				}
				else $isExist[] = false;
			}
		}
		if (!in_array(true, $isExist, true)) return Config::getAppConfig($setting);
		return null;
	}

	/** * @alias OliCore::getSetting() */
//	public function getOption($setting, $depth = 0)
//	{
//		return $this->getSetting($setting, $depth);
//	}

	/** [WIP] Get All Settings */
	// public function getAllSettings() { return $this->getSetting(null); }

	/** ----------------------- */
	/**  VI. 3. Custom Content  */
	/** ----------------------- */

	/**
	 * Get Shortcut Link
	 *
	 * @return boolean Returns true if succeeded.
	 * @version BETA
	 * @updated BETA-2.0.0
	 */
	public function getShortcutLink($shortcut, $caseSensitive = false)
	{
		$db = $this->getDB();
		if (!empty(Config::$config['shortcut_links_table']) and $db->isExistTableSQL(Config::$config['shortcut_links_table']))
			return $db->getInfosSQL(Config::$config['shortcut_links_table'], 'url', ['name' => $shortcut], $caseSensitive);
		return false;
	}

	/** ------------------- */
	/**  VI. 5. HTTP Tools  */
	/** ------------------- */

	/** ------------------------ */
	/**  VI. 5. A. Content Type  */
	/** ------------------------ */

	/** Set Content Type */
	public function setContentType($contentType = null, $charset = null, $force = false)
	{
		if (!$this->contentTypeBeenForced or $force)
		{
			if ($force) $this->contentTypeBeenForced = true;

			if (isset($contentType)) $contentType = strtolower($contentType);
			if ($contentType == 'default') $contentType = strtolower(Config::$config['default_content_type'] ?: 'plain');

			if ($contentType == 'html') $newContentType = 'text/html';
			else if ($contentType == 'css') $newContentType = 'text/css';
			else if (in_array($contentType, ['js', 'javascript'])) $newContentType = 'text/javascript';
			else if ($contentType == 'json') $newContentType = 'application/json';
			else if ($contentType == 'pdf') $newContentType = 'application/pdf';
			else if ($contentType == 'rss') $newContentType = 'application/rss+xml';
			else if ($contentType == 'xml') $newContentType = 'text/xml';
			else if (in_array($contentType, ['debug', 'plain'])) $newContentType = 'text/plain';
			else $newContentType = $contentType;

			if (isset($charset)) $charset = strtolower($charset);
			if (!isset($charset) or $charset == 'default') $charset = Config::$config['default_charset'];

			// error_reporting($contentType == 'debug' || Config::$config['debug'] ? E_ALL : E_ALL & ~E_NOTICE);
			header('Content-Type: ' . $newContentType . ';charset=' . $charset);

			$this->contentType = $newContentType;
			$this->charset = $charset;
			return $newContentType;
		}
		else return false;
	}

	/** Set Content Type Automatically */
	public function setAutoContentType($filename = null, $charset = null, $force = false)
	{
		$contentType = null;
		if (!empty($filename) and preg_match('/\.([^.]+)$/', $filename, $matches))
			$contentType = $matches[1];

		return $this->setContentType($contentType, $charset, $force);
	}

	/** Reset Content Type */
	public function resetContentType()
	{
		return $this->setContentType();
	}

	/** Get Content Type */
	public function getContentType()
	{
		return $this->contentType;
	}

	/** Get Charset */
	public function getCharset()
	{
		return $this->charset;
	}

	/** ----------------------------- */
	/**  VI. 5. B. Cookie Management  */
	/** ----------------------------- */

	/** ----------------------------- */
	/**  VI. 5. B. a. Read Functions  */
	/** ----------------------------- */

	/** Get cookie content */
	public function getCookie($name, $rawResult = false)
	{
		$cookieValue = @$_COOKIE[$name];
		return (!$rawResult && ($arr = json_decode($cookieValue, true)) !== null) ? $arr : $cookieValue;
	}

	public function getCookieContent($name, $rawResult = false)
	{
		$this->getCookie($name, $rawResult);
	}

	/** Is exist cookie */
	public function isExistCookie($name)
	{
		return isset($_COOKIE[$name]);
	}

	/** Is empty cookie */
	public function isEmptyCookie($name)
	{
		return empty($_COOKIE[$name]);
	}

	/** ------------------------------ */
	/**  VI. 5. B. b. Write Functions  */
	/** ------------------------------ */

	/** Set cookie */
	public function setCookie($name, $value, $expireDelay, $path, $domains, $secure = false, $httpOnly = false)
	{
		$value = (is_array($value)) ? json_encode($value) : $value;
		$domains = (!is_array($domains)) ? [$domains] : $domains;
		foreach ($domains as $eachDomain)
		{
			if (!setcookie($name, $value, $expireDelay ? time() + $expireDelay : 0, '/', $eachDomain, $secure, $httpOnly))
			{
				$cookieError = true;
				break;
			}
		}
		return !$cookieError ? true : false;
	}

	/** Delete cookie */
	public function deleteCookie($name, $path, $domains, $secure = false, $httpOnly = false)
	{
		$domains = (!is_array($domains)) ? [$domains] : $domains;
		foreach ($domains as $eachDomain)
		{
			setcookie($name, null, -1, '/', $eachDomain, $secure, $httpOnly);
			if (!setcookie($name, null, -1, '/', $eachDomain, $secure, $httpOnly))
			{
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
	public function getPostVarsCookieName()
	{
		return Config::$config['post_vars_cookie']['name'];
	}

	/** Get post vars */
	public function getPostVars($whatVar = null, $rawResult = false)
	{
		$postVars = $this->getCookie(Config::$config['post_vars_cookie']['name'], $rawResult);
		return isset($whatVar) ? $postVars[$whatVar] : $postVars;
	}

	/** Is empty post vars */
	public function isEmptyPostVars($whatVar = null)
	{
		return isset($whatVar) ? empty($this->getPostVars($whatVar)) : $this->isEmptyCookie(Config::$config['post_vars_cookie']['name']);
	}

	/** Is set post vars */
	public function issetPostVars($whatVar = null)
	{
		return isset($whatVar) ? $this->getPostVars($whatVar) !== null : $this->isExistCookie(Config::$config['post_vars_cookie']['name']);
	}

	/** Is protected post vars */
	public function isProtectedPostVarsCookie()
	{
		return $this->postVarsProtection;
	}

	/** ------------------------------ */
	/**  VI. 5. C. b. Write Functions  */
	/** ------------------------------ */

	/** Set post vars cookie */
	public function setPostVarsCookie($postVars)
	{
		$this->postVarsProtection = true;
		return $this->setCookie(Config::$config['post_vars_cookie']['name'], $postVars, 1, '/', Config::$config['post_vars_cookie']['domain'], Config::$config['post_vars_cookie']['secure'], Config::$config['post_vars_cookie']['http_only']);
	}

	/** Delete post vars cookie */
	public function deletePostVarsCookie()
	{
		if (!$this->postVarsProtection) return $this->deleteCookie(Config::$config['post_vars_cookie']['name'], '/', Config::$config['post_vars_cookie']['domain'], Config::$config['post_vars_cookie']['secure'], Config::$config['post_vars_cookie']['http_only']);
		else return false;
	}

	/** Protect post vars cookie */
	public function protectPostVarsCookie()
	{
		$this->postVarsProtection = true;
		return $this->setCookie(Config::$config['post_vars_cookie']['name'], $this->getRawPostVars(), 1, '/', Config::$config['post_vars_cookie']['domain'], Config::$config['post_vars_cookie']['secure'], Config::$config['post_vars_cookie']['http_only']);
	}

	/** --------------------------- */
	/**  VI. 5. D. Mail Management  */
	/** --------------------------- */

	/**
	 * Get default mail headers
	 *
	 * @return array Returns the default mail headers.
	 * @version BETA-2.0.0
	 * @updated BETA-2.0.0
	 */
	public function getDefaultMailHeaders($toString = false)
	{
		$mailHeaders = [
			'Reply-To: ' . $this->getUrlParam('name') . ' <contact@' . $this->getUrlParam('fulldomain') . '>',
			'From: ' . $this->getUrlParam('name') . ' <noreply@' . $this->getUrlParam('fulldomain') . '>',
			'MIME-Version: 1.0',
			'Content-type: text/html; charset=utf-8',
			'X-Mailer: PHP/' . phpversion(),
		];
		if ($toString) return implode("\r\n", $mailHeaders);
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
	 * @return void
	 * @uses OliCore::$htmlLoaderList to store file into the loader list
	 * @uses OliCore::minimizeStyle() to minimize stylesheet file
	 */
	public function loadStyle($url, $tags = null, $loadNow = null, $minimize = null)
	{
		if (is_bool($tags))
		{
			$minimize = $loadNow;
			$loadNow = $tags;
			$tags = null;
		}
		if (!isset($loadNow)) $loadNow = true;
		if (!isset($minimize)) $minimize = false;

		if ($minimize and empty($tags)) $codeLine = '<style>' . $this->minimizeStyle(file_get_contents($url)) . '</style>';
		else $codeLine = '<link rel="stylesheet" type="text/css" href="' . $url . '" ' . ($tags ?: '') . '>';

		if ($loadNow) echo $codeLine . PHP_EOL;
		else $this->htmlLoaderList[] = $codeLine;
	}

	/**
	 * Load local CSS stylesheet
	 *
	 * @param string $url Data url to the stylesheet
	 * @param boolean|void $loadNow Post vars to check
	 * @param boolean|void $minimize Post vars to check
	 *
	 * @return void
	 * @uses OliCore::getAssetsUrl() to get data url
	 * @uses OliCore::loadStyle() to load stylesheet file
	 */
	public function loadLocalStyle($url, $tags = null, $loadNow = null, $minimize = null)
	{
		$this->loadStyle($this->getAssetsUrl() . $url, $tags, $loadNow, $minimize);
	}

	/** Load common CSS stylesheet */
	public function loadCommonStyle($url, $tags = null, $loadNow = null, $minimize = null)
	{
		$this->loadStyle($this->getCommonAssetsUrl() . $url, $tags, $loadNow, $minimize);
	}

	/**
	 * Load cdn CSS stylesheet
	 *
	 * @param string $url Cdn url to the stylesheet
	 * @param boolean|void $loadNow Post vars to check
	 * @param boolean|void $minimize Post vars to check
	 *
	 * @return void
	 * @uses OliCore::getCdnUrl() to get cdn url
	 * @uses OliCore::loadStyle() to load stylesheet file
	 */
	public function loadCdnStyle($url, $tags = null, $loadNow = null, $minimize = null)
	{
		$this->loadStyle(Config::$config['cdn_url'] . $url, $tags, $loadNow, $minimize);
	}

	/**
	 * Load JS script
	 *
	 * @param string $url Custom full url to the script
	 * @param boolean|void $loadNow Post vars to check
	 * @param boolean|void $minimize Post vars to check
	 *
	 * @return void
	 * @uses OliCore::$htmlLoaderList to store file into the loader list
	 * @uses OliCore::minimizeScript() to minimize script file
	 */
	public function loadScript($url, $tags = null, $loadNow = null, $minimize = null)
	{
		if (is_bool($tags))
		{
			$minimize = $loadNow;
			$loadNow = $tags;
			$tags = null;
		}
		if (!isset($loadNow)) $loadNow = true;
		if (!isset($minimize)) $minimize = false;

		if ($minimize and empty($tags)) $codeLine = '<script type="text/javascript">' . $this->minimizeScript(file_get_contents($url)) . '</script>';
		else $codeLine = '<script type="text/javascript" src="' . $url . '" ' . ($tags ?: '') . '></script>';

		if ($loadNow) echo $codeLine . PHP_EOL;
		else $this->htmlLoaderList[] = $codeLine;
	}

	/**
	 * Load local JS script
	 *
	 * @param string $url Data url to the script
	 * @param boolean|void $loadNow Post vars to check
	 * @param boolean|void $minimize Post vars to check
	 *
	 * @return void
	 * @uses OliCore::getAssetsUrl() to get data url
	 * @uses OliCore::loadScript() to load script file
	 */
	public function loadLocalScript($url, $tags = null, $loadNow = null, $minimize = null)
	{
		$this->loadScript($this->getAssetsUrl() . $url, $tags, $loadNow, $minimize);
	}

	/** Load common JS script */
	public function loadCommonScript($url, $tags = null, $loadNow = null, $minimize = null)
	{
		$this->loadScript($this->getCommonAssetsUrl() . $url, $tags, $loadNow, $minimize);
	}

	/**
	 * Load cdn JS script
	 *
	 * @param string $url Cdn url to the script
	 * @param boolean|void $loadNow Post vars to check
	 * @param boolean|void $minimize Post vars to check
	 *
	 * @return void
	 * @uses OliCore::getCdnUrl() to get cdn url
	 * @uses OliCore::loadScript() to load script file
	 */
	public function loadCdnScript($url, $tags = null, $loadNow = null, $minimize = null)
	{
		$this->loadScript(Config::$config['cdn_url'] . $url, $tags, $loadNow, $minimize);
	}

	/**
	 * Load end html files
	 *
	 * Force the loader list files to load
	 *
	 * @return void
	 * @uses OliCore::$htmlLoaderList to get files from the loader list
	 */
	public function loadEndHtmlFiles()
	{
		if (!empty($this->htmlLoaderList))
		{
			echo PHP_EOL;
			foreach ($this->htmlLoaderList as $eachCodeLine)
			{
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
	public function minimizeStyle($styleCode)
	{
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
	public function minimizeScript($scriptCode)
	{
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
	 * @return string|array|null Returns requested url param if succeeded.
	 * @version BETA
	 * @updated BETA-2.0.0
	 */
	public function getUrlParam($param = null, &$hasUsedHttpHostBase = false): array|string|null
	{
		if ($param === 'get') return $_GET;

		$protocol = (!empty($_SERVER['HTTPS']) or (!empty(Config::$config['force_https']) and Config::$config['force_https'])) ? 'https' : 'http';
		$urlPrefix = $protocol . '://';

		if (!isset($param) or $param < 0 or $param === 'full') return $urlPrefix . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		else if ($param === 'protocol') return $protocol;

		$urlSetting = $this->getSetting('url');
		$urlSetting = !empty($urlSetting) ? (!is_array($urlSetting) ? [$urlSetting] : $urlSetting) : null;

		if (in_array($param, ['allbases', 'alldomains'], true))
		{
			$allBases = $allDomains = [];
			foreach ($urlSetting as $eachUrl)
			{
				preg_match('/^(https?:\/\/)?(((?:[w]{3}\.)?(?:[\da-z\.-]+\.)*(?:[\da-z-]+\.(?:[a-z\.]{2,6})))\/?(?:.)*)/', $eachUrl, $matches);
				$allBases[] = ($matches[1] ?: $urlPrefix) . $matches[2];
				$allDomains[] = $matches[3];
			}

			if ($param === 'allbases') return $allBases;
			else if ($param === 'alldomains') return $allDomains;
		}
		else
		{
			$httpParams = explode('?', $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], 2);
			if ($param === 'getvars' and !empty($httpParams[1])) return explode('&', $httpParams[1]);
			else
			{
				$fractionedUrl = explode('/', $httpParams[0]);
				$lenFractionedUrl = count($fractionedUrl);
				unset($httpParams);

				// Parse escaped slashes
				for ($i = 0; $i < $lenFractionedUrl - 2;)
					if (empty($fractionedUrl[$i]))
					{
						$replacement = $fractionedUrl[$i] . '/' . $fractionedUrl[$i + 1];
						array_splice($fractionedUrl, $i - 1, 3, $replacement);
						$lenFractionedUrl -= 2;
					}
					else $i++;

				$baseUrlMatch = false;
				$baseUrl = $urlPrefix;
				$shortBaseUrl = ''; // IS THIS USEFULL? - It seems.
				$countLoop = 0;

				if (isset($urlSetting))
				{
					foreach ($fractionedUrl as $eachPart)
					{
						if (in_array($baseUrl, $urlSetting) or in_array($shortBaseUrl, $urlSetting))
						{
							$baseUrlMatch = true;
							break;
						}
						else
						{
							$baseUrlMatch = false;
							$baseUrl .= urldecode($eachPart) . '/';
							$shortBaseUrl .= urldecode($eachPart) . '/';
							$countLoop++;
						}
					}
				}

				$hasUsedHttpHostBase = false;
				if (!isset($urlSetting) or !$baseUrlMatch)
				{
					$baseUrl = $urlPrefix . $_SERVER['HTTP_HOST'] . '/';
					$hasUsedHttpHostBase = true;
					$countLoop = 1; // Fix $countLoop value
				}

				if (in_array($param, [0, 'base'], true)) return $baseUrl;
				else if (in_array($param, ['fulldomain', 'subdomain', 'domain'], true))
				{
					if (preg_match('/^https?:\/\/(?:[w]{3}\.)?((?:([\da-z\.-]+)\.)*([\da-z-]+\.(?:[a-z\.]{2,6})))\/?/', $baseUrl, $matches))
					{
						if ($param === 'fulldomain') return $matches[1];
						if ($param === 'subdomain') return $matches[2];
						if ($param === 'domain') return $matches[3];
					}
				}
				else
				{
					$newFractionedUrl[] = $baseUrl;
					if (!empty($this->fileNameParam))
					{
						$i = $countLoop;

						$fileName = [];
						while ($i < $lenFractionedUrl)
						{
							if (!empty($fileName) and implode('/', $fileName) == $this->fileNameParam)
								break;
							else
							{
								$fileName[] = urldecode($fractionedUrl[$countLoop]);
								$i++;
							}
						}

						// fileNameParam found!
						if ($i < $lenFractionedUrl)
						{
							$newFragment = explode('?', implode('/', $fileName), 2)[0];
							if (!empty($newFragment)) $newFractionedUrl[] = $newFragment;

							$countLoop = $i;
							// Not found
						}
						else
						{
							// Override the first element to match fileNameParam
							$newFractionedUrl[] = $this->fileNameParam;

							// Next fragments will be left as-is
							$countLoop++;
						}
					}

					while ($countLoop < $lenFractionedUrl)
					{
						$nextFractionedUrl = urldecode($fractionedUrl[$countLoop]);

						$newFragment = explode('?', $nextFractionedUrl, 2)[0];
						if (!empty($newFragment)) $newFractionedUrl[] = $newFragment;

						$countLoop++;
					}

					if (empty($newFractionedUrl[1])) $newFractionedUrl[1] = 'home';

					if ($param === 'all') return $newFractionedUrl;
					else if ($param === 'params') return array_slice($newFractionedUrl, 1);
					else if ($param === 'last') return $newFractionedUrl[count($newFractionedUrl) - 1];
					else if (preg_match('/^(.+)\+$/', $param, $matches))
					{
						$slice = array_slice($newFractionedUrl, $matches[1]);
						return implode('/', $slice);
					}
					else if (isset($newFractionedUrl[$param])) return $newFractionedUrl[$param];
				}
			}
		}

		return null;
	}

	/** Get Full Url */
	public function getFullUrl()
	{
		return $this->getUrlParam('full');
	}

	/**
	 * Get Scripts Url
	 *
	 * @return string|void Returns the assets url.
	 * @version BETA-2.0.0
	 * @updated BETA-2.0.0
	 */
	public function getScriptsUrl()
	{
		return $this->getUrlParam(0) . Config::$config['scripts_alias'] . '/';
	}

	/**
	 * Get Assets Url
	 *
	 * @return string|void Returns the assets url.
	 * @version BETA-2.0.0
	 * @updated BETA-2.0.0
	 */
	public function getAssetsUrl()
	{
		return $this->getUrlParam(0) . Config::$config['assets_alias'] . '/';
	}

	/**
	 * Get Media Url
	 *
	 * @return string|void Returns the media url.
	 * @version BETA-2.0.0
	 * @updated BETA-2.0.0
	 */
	public function getMediaUrl()
	{
		return $this->getUrlParam(0) . Config::$config['media_alias'] . '/';
	}

	/**
	 * Get Login Url
	 *
	 * @return string|void Returns the login url.
	 * @version BETA-2.0.0
	 * @updated BETA-2.0.0
	 */
	public function getLoginUrl()
	{
		return $this->isExternalLogin() ? Config::$config['external_login_url'] : $this->getUrlParam(0) . (Config::$config['login_alias'] ?: 'oli-login') . '/';
	}

	/**
	 * Get Oli Admin Url
	 *
	 * @return string|void Returns the admin url.
	 * @version BETA-2.0.0
	 * @updated BETA-2.0.0
	 */
	public function getOliAdminUrl()
	{
		return $this->getUrlParam(0) . Config::$config['admin_alias'] . '/';
	}

	/** * @alias OliCore::getOliAdminUrl() */
	public function getAdminUrl()
	{
		return $this->getOliAdminUrl();
	}

	/**
	 * Get Common Assets Url
	 *
	 * @return string|void Returns the common assets url.
	 * @version BETA-2.0.0
	 * @updated BETA-2.0.0
	 */
	public function getCommonAssetsUrl()
	{
		return $this->getUrlParam(0) . Config::$config['common_path'] . Config::$config['common_assets_folder'] . '/';
	}

	/** * @alias OliCore::getCommonAssetsUrl() */
	public function getCommonFilesUrl()
	{
		return $this->getCommonAssetsUrl();
	}

	/**
	 * Get CDN Url
	 *
	 * @return string|void Returns the CDN url.
	 * @version BETA-2.0.0
	 * @updated BETA-2.0.0
	 */
	public function getCdnUrl()
	{
		return Config::$config['cdn_url'];
	}

	/** ---------------------- */
	/**  VI. 8. Utility Tools  */
	/** ---------------------- */

	/** --------------------- */
	/**  VI. 8. A. Templates  */
	/** --------------------- */

	/**
	 * Get Template
	 *
	 * @return string|void Returns template content if found, null otherwise.
	 * @version BETA-1.8.0
	 * @updated BETA-2.0.0
	 */
	public function getTemplate($template, $filter = null, $regex = false)
	{
		if (!empty($template))
		{
			if (file_exists(TEMPLATESPATH . strtolower($template) . '.html')) $templateContent = file_get_contents(TEMPLATESPATH . strtolower($template) . '.html');
			else if (file_exists(INCLUDESPATH . 'templates/' . strtolower($template) . '.html')) $templateContent = file_get_contents(INCLUDESPATH . 'templates/' . strtolower($template) . '.html');

			if (!empty($templateContent))
			{
				if (!empty($filter))
				{
					foreach (!is_array($filter) ? [$filter] : $filter as $eachPattern => $eachReplacement)
					{
						if ($regex) $templateContent = preg_replace($eachPattern, $eachReplacement, $templateContent);
						else $templateContent = str_replace($eachPattern, $eachReplacement, $templateContent);
					}
				}
				return $templateContent ?: null;
			}
			else return null;
		}
		else return null;
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
	 * @return string Returns the requested UUID.
	 * @version BETA-2.0.0
	 * @updated BETA-2.0.0
	 */
	public function uuid($type, ...$args)
	{
		if (in_array($type, ['v4', '4', 4], true)) call_user_func_array([$this, 'uuid4'], $args);
		else if ($type == 'alt') call_user_func_array([$this, 'uuidAlt'], $args);
		else return false;
	}

	/**
	 * UUID v4 Generator Script
	 *
	 * From https://stackoverflow.com/a/15875555/5255556
	 *
	 * @return string Returns the generated UUID.
	 * @version BETA-2.0.0
	 * @updated BETA-2.0.0
	 */
	public function uuid4()
	{
		if (function_exists('random_bytes')) $data = random_bytes(16);
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
	 * @return string Returns the generated UUID.
	 * @link https://github.com/matiboux/Time-Based-Random-UUID
	 * @version 1.0
	 * @author Matiboux <matiboux@gmail.com>
	 */
	function uuidAlt($tp = null)
	{
		if (!empty($tp))
		{
			if (is_array($tp)) $time = ($tp['sec'] * 10000000) + ($tp['usec'] * 10);
			else if (is_numeric($tp)) $time = (int)($tp * 10000000);
			else return false;
		}
		else $time = (int)(gettimeofday(true) * 10000000);
		$time += 0x01B21DD213814000;

		$arr = str_split(dechex($time & 0xffffffff), 4); // time_low (32 bits)
		$high = intval($time / 0xffffffff);
		array_push($arr, dechex($high & 0xffff)); // time_mid (16 bits)
		array_push($arr, dechex(0x4000 | (($high >> 16) & 0x0fff))); // Version (4 bits) + time_high (12 bits)

		// Variant (2 bits) + Cryptographically Secure Pseudo-Random Bytes (62 bits)
		if (function_exists('random_bytes')) $random = random_bytes(8);
		else $random = openssl_random_pseudo_bytes(8);
		$random[0] = chr(ord($random[0]) & 0x3f | 0x80); // Apply variant: Set the two first bits of the random set to 10.

		$uuid = vsprintf('%s%s-%s-%s-%s-%s%s%s', array_merge($arr, str_split(bin2hex($random), 4)));
		return strlen($uuid) == 36 ? $uuid : false;
	}

	/** ------------------- */
	/**  VI. 8. B. b. Misc  */
	/** ------------------- */

	/** Random Number generator */
	public function rand($min = 1, $max = 100)
	{
		if (is_numeric($min) and is_numeric($max))
		{
			if ($min > $max) $min = [$max, $max = $min][0];
			return mt_rand($min, $max);
		}
		else return false;
	}

	public function randomNumber($min = null, $max = null)
	{
		$this->rand($min, $max);
	}

	/** KeyGen built-in script */
	// See https://github.com/matiboux/KeyGen-Lib for the full PHP library.
	public function keygen($length = 12, $numeric = true, $lowercase = true, $uppercase = true, $special = false, $redundancy = true)
	{
		$charactersSet = '';
		if ($numeric) $charactersSet .= '1234567890';
		if ($lowercase) $charactersSet .= 'abcdefghijklmnopqrstuvwxyz';
		if ($uppercase) $charactersSet .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		if ($special) $charactersSet .= '!#$%&\()+-;?@[]^_{|}';

		if (empty($charactersSet) or empty($length) or $length <= 0) return false;
		else
		{
			if ($length > strlen($redundancy) and !$redundancy) $redundancy = true;

			$keygen = '';
			while (strlen($keygen) < $length)
			{
				$randomCharacter = substr($charactersSet, mt_rand(0, strlen($charactersSet) - 1), 1);
				if ($redundancy or !strstr($keygen, $randomCharacter)) $keygen .= $randomCharacter;
			}

			return $keygen;
		}
	}

	/** --------------------------- */
	/**  VI. 8. C. Data Conversion  */
	/** --------------------------- */

	/** Convert Number */
	public function convertNumber($value, $toUnit = null, $precision = null)
	{
		if (preg_match('/^([\d.]+)\s?(\S*)$/i', $value, $matches))
		{
			[$result, $unit] = [floatval($matches[1]), $matches[2]];
			if ($unit != $toUnit)
			{
				$unitsTable = [
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
					'K' => 1000,
				];

				if (!empty($unit) and !empty($unitsTable[$unit])) $result *= $unitsTable[$unit];
				if (!empty($toUnit) and !empty($unitsTable[$toUnit])) $result /= $unitsTable[$toUnit];
			}
			return isset($precision) ? round($result, $precision) : $result;
		}
		else return $value;
	}

	/** Convert File Size */
	public function convertFileSize($size, $toUnit = null, $precision = null)
	{
		if (preg_match('/^([\d.]+)\s?(\S*)$/i', $size, $matches))
		{
			[$result, $unit] = [floatval($matches[1]), $matches[2]];
			if ($unit != $toUnit)
			{
				$unitsTable = [
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
				];

				if (!empty($unit) and !empty($unitsTable[$unit])) $result *= $unitsTable[$unit];
				if (!empty($toUnit) and !empty($unitsTable[$toUnit])) $result /= $unitsTable[$toUnit];
			}
			return isset($precision) ? round($result, $precision) : $result;
		}
		else return $size;
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
	public function dateDifference($startDate, $endDate, $precise, $details = true)
	{
		if (is_string($startDate))
			$startDate = strtotime($startDate);
		if (is_string($endDate))
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

		if ($precise)
		{
			if (!empty($results['years']))
				return ['years' => $results['years'], 'days' => $results['days'], 'hours' => $results['hours'], 'minutes' => $results['minutes'], 'seconds' => $results['seconds']];
			else if (!empty($results['days']))
				return ['days' => $results['total_days'], 'hours' => $results['hours'], 'minutes' => $results['minutes'], 'seconds' => $results['seconds']];
			else if (!empty($results['hours']))
				return ['hours' => $results['total_hours'], 'minutes' => $results['minutes'], 'seconds' => $results['seconds']];
			else if (!empty($results['minutes']))
				return ['minutes' => $results['total_minutes'], 'seconds' => $results['seconds']];
			else
				return ['seconds' => $results['total_seconds']];
		}
		else
		{
			if ($details)
			{
				if (!empty($results['years']))
					return ['years' => $results['years']];
				else if (!empty($results['total_days']))
					return ['days' => $results['total_days']];
				else if (!empty($results['total_hours']))
					return ['hours' => $results['total_hours']];
				else if (!empty($results['total_minutes']))
					return ['minutes' => $results['total_minutes']];
				else
					return ['seconds' => $results['total_seconds']];
			}
			else
			{
				if (!empty($results['years']))
					return $results['years'];
				else if (!empty($results['total_days']))
					return $results['total_days'];
				else if (!empty($results['total_hours']))
					return $results['total_hours'];
				else if (!empty($results['total_minutes']))
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
	public function getUserIP()
	{
		if (!empty($_SERVER['REMOTE_ADDR'])) $client_ip = $_SERVER['REMOTE_ADDR'];
		else if (!empty($_ENV['REMOTE_ADDR'])) $client_ip = $_ENV['REMOTE_ADDR'];
		else $client_ip = 'unknown';

		if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
		{
			$entries = preg_split('[, ]', $_SERVER['HTTP_X_FORWARDED_FOR']);

			foreach ($entries as $entry)
			{
				$entry = trim($entry);
				if (preg_match('/^([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)/', $entry, $ip_list))
				{
					$private_ip = [
						'/^0\./',
						'/^127\.0\.0\.1/',
						'/^192\.168\..*/',
						'/^172\.((1[6-9])|(2[0-9])|(3[0-1]))\..*/',
						'/^10\..*/'];

					$found_ip = preg_replace($private_ip, $client_ip, $ip_list[1]);

					if ($client_ip != $found_ip)
					{
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
	 * @return void
	 * @uses OliCore::$accountsManagementStatus to set accounts management status
	 */
	// public function enableAccountsManagement() {
	// $this->accountsManagementStatus = true;
	// }

	/**
	 * Is accounts management enabled
	 *
	 * @return boolean Accounts management status
	 * @uses OliCore::$accountsManagementStatus to get accounts management status
	 */
	public function getAccountsManagementStatus()
	{
		return $this->isAccountsManagementReady();
	}

	/**
	 * Check if the database is ready for user management
	 *
	 * @return bool Returns true if local.
	 * @version BETA-2.0.0
	 * @updated BETA-2.0.0
	 */
	public function isAccountsManagementReady(): bool
	{
		if (!$this->isSetupDB()) return false;

		$status = [];
		$db = $this->getDB();
		foreach (Config::$config['accounts_tables'] as $eachTable)
			if (!$status[] = $db->isExistTableSQL($eachTable)) break;

		return !in_array(false, $status, true);
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
	 *
	 * @return string|void Returns account table name if succeeded, null otherwise.
	 * @version BETA
	 * @updated BETA-2.0.0
	 * @uses OliCore::$accountsTables To get account tables names
	 *
	 */
	public function translateAccountsTableCode($tableCode)
	{
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
	 * @return array|boolean Returns first info from specified table
	 * @uses OliCore::translateAccountsTableCode() to translate account table code
	 * @uses OliCore::getFirstInfoMySQL() to get first info from table
	 */
	public function getFirstAccountInfo($tableCode, $whatVar, $rawResult = false)
	{
		$db = $this->getDB(); // TODO: Config for the DB to use
		return $db->getFirstInfoSQL($this->translateAccountsTableCode($tableCode) ?: $tableCode, $whatVar, $rawResult);
	}

	/**
	 * Get first line from account table
	 *
	 * @param string $tableCode Table code of the table to get data from
	 * @param boolean|void $rawResult Return raw result or not
	 *
	 * @return array|boolean Returns first line from specified table
	 * @uses OliCore::translateAccountsTableCode() to translate account table code
	 * @uses OliCore::getFirstLineMySQL() to get first line from table
	 */
	public function getFirstAccountLine($tableCode, $rawResult = false)
	{
		$db = $this->getDB(); // TODO: Config for the DB to use
		return $db->getFirstLineSQL($this->translateAccountsTableCode($tableCode) ?: $tableCode, $rawResult);
	}

	/**
	 * Get last info from table
	 *
	 * @param string $tableCode Table code of the table to get data from
	 * @param string $whatVar Variable to get
	 * @param boolean|void $rawResult Return raw result or not
	 *
	 * @return array|boolean Returns last info from specified table
	 * @uses OliCore::translateAccountsTableCode() to translate account table code
	 * @uses OliCore::getLastInfoMySQL() to get last info from table
	 */
	public function getLastAccountInfo($tableCode, $whatVar, $rawResult = false)
	{
		$db = $this->getDB(); // TODO: Config for the DB to use
		return $db->getLastInfoSQL($this->translateAccountsTableCode($tableCode) ?: $tableCode, $whatVar, $rawResult);
	}

	/**
	 * Get last line from account table
	 *
	 * @param string $tableCode Table code of the tableTable to get data from
	 * @param boolean|void $rawResult Return raw result or not
	 *
	 * @return array|boolean Returns last line from specified table
	 * @uses OliCore::translateAccountsTableCode() to translate account table code
	 * @uses OliCore::getLastLineMySQL() to get last line from table
	 */
	public function getLastAccountLine($tableCode, $rawResult = false)
	{
		$db = $this->getDB(); // TODO: Config for the DB to use
		return $db->getLastLineSQL($this->translateAccountsTableCode($tableCode) ?: $tableCode, null, $rawResult);
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
	 * @return array|boolean Returns lines from specified table
	 * @uses OliCore::getLinesMySQL() to get lines from table
	 * @uses OliCore::translateAccountsTableCode() to translate account table codes
	 *
	 * @version BETA
	 * @updated BETA-2.0.0
	 */
	public function getAccountLines($tableCode, $where = null, $settings = null, $caseSensitive = null, $forceArray = null, $rawResult = null)
	{
		if (!isset($where))
		{
			if ($this->isLoggedIn()) $where = ['uid' => $this->getLoggedUser()];
			else return false;
		}
		else if (!is_array($where) and $where != 'all') $where = ['uid' => $where];

		$db = $this->getDB(); // TODO: Config for the DB to use
		return $db->getLinesSQL($this->translateAccountsTableCode($tableCode) ?: $tableCode, $where, $settings, $caseSensitive, $forceArray, $rawResult);
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
	 * @return mixed Returns infos from specified table
	 * @uses OliCore::getInfosMySQL() to get infos from table
	 * @uses OliCore::translateAccountsTableCode() to translate account table code
	 *
	 * @version BETA
	 * @updated BETA-2.0.0
	 */
	public function getAccountInfos($tableCode, $whatVar, $where = null, $settings = null, $caseSensitive = null, $forceArray = null, $rawResult = null)
	{
		if (!isset($where))
		{
			if ($this->isLoggedIn()) $where = ['uid' => $this->getLoggedUser()];
			else return null;
		}
		else if (!is_array($where) and $where != 'all') $where = ['uid' => $where];

		$db = $this->getDB(); // TODO: Config for the DB to use
		return $db->getInfosSQL($this->translateAccountsTableCode($tableCode) ?: $tableCode, $whatVar, $where, $settings, $caseSensitive, $forceArray, $rawResult);
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
	 * @return mixed Returns summed infos from specified table
	 * @uses OliCore::getSummedInfosMySQL() to get summed infos from table
	 * @uses OliCore::translateAccountsTableCode() to translate account table code
	 *
	 * @version BETA
	 * @updated BETA-2.0.0
	 */
	public function getSummedAccountInfos($tableCode, $whatVar, $where = null, $caseSensitive = true)
	{
		if (!isset($where))
		{
			if ($this->isLoggedIn()) $where = ['uid' => $this->getLoggedUser()];
			else return false;
		}
		else if (!is_array($where) and $where != 'all') $where = ['uid' => $where];

		$db = $this->getDB(); // TODO: Config for the DB to use
		return $db->getSummedInfosSQL($this->translateAccountsTableCode($tableCode) ?: $tableCode, $whatVar, $where, $caseSensitive, $forceArray, $rawResult);
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
	 * @return boolean Returns true if infos are empty, false otherwise
	 * @uses OliCore::translateAccountsTableCode() to translate account table code
	 *
	 * @version BETA
	 * @updated BETA-2.0.0
	 * @uses OliCore::isEmptyInfosMySQL() to get if infos are empty in table
	 */
	public function isEmptyAccountInfos($tableCode, $whatVar, $where = null, $settings = null, $caseSensitive = null)
	{
		if (!isset($where))
		{
			if ($this->isLoggedIn()) $where = ['uid' => $this->getLoggedUser()];
			else return false;
		}
		else if (!is_array($where) and $where != 'all') $where = ['uid' => $where];

		$db = $this->getDB(); // TODO: Config for the DB to use
		return $db->isEmptyInfosSQL($this->translateAccountsTableCode($tableCode) ?: $tableCode, $whatVar, $where, $settings, $caseSensitive);
	}

	/**
	 * Is exist infos in account table
	 *
	 * @param string $tableCode Table code of the table to get data from
	 * @param string|array|void $where Where to get data from
	 * @param boolean|void $caseSensitive Where is case sensitive or not
	 *
	 * @return boolean Returns true if infos exists, false otherwise
	 * @uses OliCore::translateAccountsTableCode() to translate account table code
	 *
	 * @version BETA
	 * @updated BETA-2.0.0
	 * @uses OliCore::isExistInfosMySQL() to get if infos exists in table
	 */
	public function isExistAccountInfos($tableCode, $where = null, $caseSensitive = true)
	{
		if (!isset($where))
		{
			if ($this->isLoggedIn()) $where = ['uid' => $this->getLoggedUser()];
			else return false;
		}
		else if (!is_array($where) and $where != 'all') $where = ['uid' => $where];

		$db = $this->getDB(); // TODO: Config for the DB to use
		return $db->isExistInfosSQL($this->translateAccountsTableCode($tableCode) ?: $tableCode, $where, $caseSensitive);
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
	 * @return boolean Returns true if the request succeeded, false otherwise
	 * @uses OliCore::translateAccountsTableCode() to translate account table code
	 * @uses OliCore::insertLineMySQL() to insert lines in table
	 */
	public function insertAccountLine($tableCode, $what, &$errorInfo = null)
	{
		$db = $this->getDB(); // TODO: Config for the DB to use
		return $db->insertLineSQL($this->translateAccountsTableCode($tableCode) ?: $tableCode, $what, $errorInfo);
	}

	/**
	 * Update infos from account table
	 *
	 * @param string $tableCode Table code of the table to update infos from
	 * @param array $what What to replace data with
	 * @param string|array|void $where Where to update data
	 *
	 * @return boolean Return true if the request succeeded, false otherwise
	 * @uses OliCore::translateAccountsTableCode() to translate account table code
	 *
	 * @version BETA
	 * @updated BETA-2.0.0
	 * @uses OliCore::updateInfosMySQL() to update infos in table
	 */
	public function updateAccountInfos($tableCode, $what, $where = null, &$errorInfo = null)
	{
		if (!isset($where))
		{
			if ($this->isLoggedIn()) $where = ['uid' => $this->getLoggedUser()];
			else return false;
		}
		else if (!is_array($where) and $where != 'all') $where = ['uid' => $where];

		$db = $this->getDB(); // TODO: Config for the DB to use
		return $db->updateInfosSQL($this->translateAccountsTableCode($tableCode) ?: $tableCode, $what, $where, $errorInfo);
	}

	/**
	 * Update account username
	 *
	 * @param string $newUsername New username for the user
	 * @param string $oldUsername Current username of the user
	 *
	 * @return boolean Return true if the requests succeeded, false otherwise
	 * @uses OliCore::updateAccountInfos() to update infos from account table
	 */
	public function updateAccountUsername($newUsername, $oldUsername)
	{
		return ($this->updateAccountInfos('ACCOUNTS', ['username' => $newUsername], $oldUsername)
		        && $this->updateAccountInfos('INFOS', ['username' => $newUsername], $oldUsername)
		        && $this->updateAccountInfos('SESSIONS', ['username' => $newUsername], $oldUsername)
		        && $this->updateAccountInfos('REQUESTS', ['username' => $newUsername], $oldUsername));
	}

	/**
	 * Delete lines from an account table
	 *
	 * @return boolean Returns true if the request succeeded, false otherwise.
	 * @version BETA
	 * @updated BETA-2.0.0
	 */
	public function deleteAccountLines($tableCode, $where, &$errorInfo = null)
	{
		if (!is_array($where) and $where !== 'all' and strpos($where, ' ') === false) $where = ['uid' => $where];

		$db = $this->getDB(); // TODO: Config for the DB to use
		return $db->deleteLinesSQL($this->translateAccountsTableCode($tableCode) ?: $tableCode, $where, $errorInfo);
	}

	/**
	 * Delete full account
	 *
	 * @param string|array $where Where to delete user
	 *
	 * @return boolean Returns true if the requests succeeded, false otherwise
	 * @uses OliCore::translateAccountsTableCode() to translate account table code
	 *
	 * @version BETA
	 * @updated BETA-2.0.0
	 * @uses OliCore::deleteLinesMySQL() to delete lines from table
	 */
	public function deleteFullAccount($where)
	{
		return ($this->deleteAccountLines('ACCOUNTS', $where)
		        && $this->deleteAccountLines('INFOS', $where)
		        && $this->deleteAccountLines('SESSIONS', $where)
		        && $this->deleteAccountLines('REQUESTS', $where)
		        && $this->deleteAccountLines('PERMISSIONS', $where));
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
	 * @return boolean Returns true if the requests succeeded, false otherwise
	 * @uses OliCore::isExistAccountInfos() to get if infos exists in account table
	 */
	public function verifyUserRight($userRight, $caseSensitive = true)
	{
		return !empty($userRight) and $this->isExistAccountInfos('RIGHTS', ['user_right' => $userRight], $caseSensitive);
	}

	/**
	 * Translate User Right
	 *
	 * @return string|null Returns translated user right if succeeded.
	 * @version BETA
	 * @updated BETA-2.0.0
	 */
	public function translateUserRight($userRight, $caseSensitive = true)
	{
		// Local user rights management
		if ($this->isLocalLogin())
		{
			$userRightsTable = [0 => 'VISITOR',
			                    1 => 'ROOT'];

			// Check for Level to User Right translation
			if (@$userRightsTable[$userRight] !== null)
				return $userRightsTable[$userRight];

			if ($caseSensitive)
			{
				// Check for User Right -> Level translation (case-sensitive)
				foreach ($userRightsTable as $level => $rightName)
					if ($rightName === $userRight) return $level;
			}
			else
			{
				// Check for User Right -> Level translation (non case-sensitive)
				foreach ($userRightsTable as $level => $rightName)
					if (strtolower($rightName) === strtolower($userRight)) return $level;
			}
		}
		else if ($this->isAccountsManagementReady() and !empty($userRight))
		{
			// Check for Level -> User Right translation
			if ($returnValue = $this->getAccountInfos('RIGHTS', 'user_right', ['level' => $userRight], $caseSensitive)) return $returnValue;

			// Check for User Right -> Level translation
			if ($returnValue = $this->getAccountInfos('RIGHTS', 'level', ['user_right' => $userRight], $caseSensitive)) return (int)$returnValue;
		}

		return null; // Failure
	}

	/**
	 * Get right permissions
	 *
	 * @param string $userRight User right to get permissions of
	 * @param bool $caseSensitive Translate is case sensitive or not
	 *
	 * @return array Returns user right permissions
	 * @uses OliCore::getAccountInfos() to get infos from account table
	 */
	public function getRightPermissions(string $userRight, bool $caseSensitive = true): ?array
	{
		return $this->getAccountInfos('RIGHTS', 'permissions', ['user_right' => $userRight], $caseSensitive) ?: null;
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
	 * @return array|boolean Returns lines from specified table
	 * @uses OliCore::getAccountLines() to get lines from account table
	 */
	public function getRightsLines($where = [], $settings = null, $caseSensitive = null, $forceArray = null, $rawResult = null)
	{
		if (!is_array($where)) $where = ['uid' => $where];
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
	 * @return mixed Returns infos from specified table
	 * @uses OliCore::getAccountInfos() to get infos from account table
	 */
	public function getRightsInfos($whatVar = null, $where = [], $settings = null, $caseSensitive = null, $forceArray = null, $rawResult = null)
	{
		if (empty($whatVar)) $whatVar = 'user_right';
		return $this->getAccountInfos('RIGHTS', $whatVar, $where, $settings, $caseSensitive, $forceArray, $rawResult);
	}

	/**
	 * Get User Right
	 *
	 * @param null $where
	 * @param bool $caseSensitive
	 *
	 * @return int|string|null Returns the user right.
	 * @version BETA
	 * @updated BETA-2.0.0
	 */
	public function getUserRight($where = null, bool $caseSensitive = true): int|string|null
	{
		if ($this->isLocalLogin() and !empty($this->getLocalRootInfos())) return $this->isLoggedIn() ? 'ROOT' : 'VISITOR';

		if (empty($where))
		{
			if ($this->isLoggedIn()) $where = ['uid' => $this->getLoggedUser()];
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
	 * @return string Returns user right level (integer as string)
	 * @uses OliCore::getUserRight() to get user right
	 */
	public function getUserRightLevel($where = null, $caseSensitive = true)
	{
		if ($userRight = $this->getUserRight($where, $caseSensitive)) return (int)$this->translateUserRight($userRight);
		return 0; // Default user right level (visitor)
	}

	/**
	 * Get user right permissions
	 *
	 * @param null $where Where to get data from
	 * @param bool $caseSensitive Translate is case sensitive or not
	 *
	 * @return array|null Returns user right permissions
	 * @uses OliCore::getUserRight() to get user right
	 * @uses OliCore::getRightPermissions() to get right permissions
	 */
	public function getUserRightPermissions($where = null, bool $caseSensitive = true): ?array
	{
		return $this->getRightPermissions($this->getUserRight($where, $caseSensitive));
	}

	/**
	 * Update user right
	 *
	 * @param string $userRight New right to set to the user
	 * @param array $what What to replace data with
	 * @param string|array $where Where to update data
	 *
	 * @return boolean Returns true if the request succeeded, false otherwise
	 * @uses OliCore::updateAccountInfos() to update infos in account table
	 * @uses OliCore::verifyUserRight() to verify user right syntax
	 */
	public function updateUserRight($userRight, $where = null)
	{
		$userRight = strtoupper($userRight);

		if ($this->verifyUserRight($userRight)) return $this->updateAccountInfos('ACCOUNTS', ['user_right' => $userRight], $where);
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
	public function getUserOwnPermissions($permission)
	{
	}

	/** Get user permissions */
	public function getUserPermissions($permission)
	{
	}

	/** Is User Permitted */
	public function isUserPermitted($permission)
	{
	}

	/** ---------------------------------- */
	/**  VII. 3. B. b. Rights Permissions  */
	/** ---------------------------------- */

	/** Set Right Permissions */
	public function setRightPermissions($permissions, $userRight)
	{
	}

	/** Add Right Permissions */
	public function addRightPermissions($permissions, $userRight)
	{
	}

	/** Remove Right Permissions */
	public function removeRightPermissions($permissions, $userRight)
	{
	}

	/** Delete Right Permissions */
	public function deleteRightPermissions($userRight)
	{
	}

	/** Is Right Permitted */
	public function isRightPermitted($permission)
	{
	}

	/** -------------------------------- */
	/**  VII. 3. B. c. User Permissions  */
	/** -------------------------------- */

	/** Set User Permissions */
	public function setUserPermissions($permissions, $userRight)
	{
	}

	/** Add User Permissions */
	public function addUserPermissions($permissions, $userRight)
	{
	}

	/** Remove User Permissions */
	public function removeUserPermissions($permissions, $userRight)
	{
	}

	/** Delete User Permissions */
	public function deleteUserPermissions($userRight)
	{
	}

	/** ------------------------- */
	/**  VII. -. Auth Key Cookie  */
	/** ------------------------- */

	/** ---------------------------- */
	/**  VII. -. A. Create & Delete  */
	/** ---------------------------- */

	/** Set Auth Key cookie */
	public function setAuthKeyCookie($authKey, $expireDelay)
	{
		return $this->setCookie(Config::$config['auth_key_cookie']['name'], $authKey, $expireDelay, '/', Config::$config['auth_key_cookie']['domain'], Config::$config['auth_key_cookie']['secure'], Config::$config['auth_key_cookie']['http_only']);
	}

	/** Delete Auth Key cookie */
	public function deleteAuthKeyCookie()
	{
		return $this->deleteCookie(Config::$config['auth_key_cookie']['name'], '/', Config::$config['auth_key_cookie']['domain'], Config::$config['auth_key_cookie']['secure'], Config::$config['auth_key_cookie']['http_only']);
	}

	/** ------------------------------- */
	/**  VII. -. B. Get Auth Key Infos  */
	/** ------------------------------- */

	/** Get Auth Key cookie name */
	public function getAuthKeyCookieName()
	{
		return Config::$config['auth_key_cookie']['name'];
	}

	/** Auth Key cookie content */
	// public function getAuthKey() { return $this->cache['authKey'] ?: $this->cache['authKey'] = $this->getCookie(Config::$config['auth_key_cookie']['name']); }
	public function isExistAuthKey()
	{
		return $this->isExistCookie(Config::$config['auth_key_cookie']['name']);
	}

	public function isEmptyAuthKey()
	{
		return $this->isEmptyCookie(Config::$config['auth_key_cookie']['name']);
	}

	// Get Auth Key
	// MOVED

	/**
	 * Is User Logged In?
	 *
	 * @return boolean Returns true if logged out successfully, false otherwise.
	 * @version BETA-2.0.0
	 * @updated BETA-2.0.0
	 */
	public function isLoggedIn($authKey = null)
	{
		if (!isset($authKey)) $authKey = $this->getAuthKey();

		if (empty($authKey)) return false;

		$sessionInfos = ($this->isLocalLogin() and !$this->isExternalLogin()) ? $this->getLocalRootInfos() : $this->getAccountLines('SESSIONS', ['auth_key' => hash('sha512', $authKey)]);
		return strtotime($sessionInfos['expire_date']) >= time();
	}

	/** @alias OliCore::isLoggedIn() */
	public function verifyAuthKey($authKey = null)
	{
		return $this->isLoggedIn($authKey);
	}

	/**
	 * Get Logged User
	 *
	 * @return string|boolean Returns the uid if logged in, false otherwise.
	 * @version BETA-2.0.0
	 * @updated BETA-2.0.0
	 */
	public function getLoggedUser($authKey = null)
	{
		if (empty($authKey)) $authKey = $this->getAuthKey();

		if (!$this->isLoggedIn($authKey)) return null;
		if ($this->isLocalLogin() and !$this->isExternalLogin()) return $this->getLocalRootInfos()['username'];
		return $this->getAccountInfos('SESSIONS', 'uid', ['auth_key' => hash('sha512', $authKey)]);
	}

	/**
	 * Get User Name
	 *
	 * @return string|boolean Returns the username of user, false otherwise.
	 * @version BETA-2.0.0
	 * @updated BETA-2.0.0
	 */
	public function getName($uid, &$type = null)
	{
		if ($this->isExistAccountInfos('ACCOUNTS', ['uid' => $uid]))
		{
			if ($name = $this->getAccountInfos('ACCOUNTS', 'username', $uid))
			{
				$type = 'username';
				return $name;
			}
			if ($name = $this->getAccountInfos('ACCOUNTS', 'email', $uid))
			{
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
	 * @return string|boolean Returns the user name if logged in, false otherwise.
	 * @version BETA-2.0.0
	 * @updated BETA-2.0.0
	 */
	public function getLoggedName($authKey = null, &$type = null)
	{
		if ($this->isLoggedIn($authKey))
		{
			if ($this->isLocalLogin())
			{
				$type = 'local';
				return 'root';
			}
			if ($uid = $this->getLoggedUser($authKey))
				return $this->getName($uid, $type);
		}
		return null;
	}

	/**
	 * Get User Username
	 *
	 * @return string|boolean Returns the username of user, false otherwise.
	 * @version BETA-2.0.0
	 * @updated BETA-2.0.0
	 */
	public function getUsername($uid)
	{
		return $this->getAccountInfos('ACCOUNTS', 'username', ['uid' => $uid]) ?: false;
	}

	/**
	 * Get Logged Username
	 *
	 * @return string|boolean Returns the username if logged in, false otherwise.
	 * @version BETA-2.0.0
	 * @updated BETA-2.0.0
	 */
	public function getLoggedUsername($authKey = null)
	{
		if ($this->isLoggedIn($authKey))
		{
			if ($this->isLocalLogin()) return 'root';
			if ($uid = $this->getLoggedUser($authKey)
			    and $name = $this->getAccountInfos('ACCOUNTS', 'username', $uid)) return $name;
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
	public function getAuthKeyOwner($authKey = null)
	{
		return $this->getLoggedUsername($authKey);
	}

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
	 * @return boolean Returns true if succeeded, false otherwise.
	 * @version BETA-1.8.0
	 * @updated BETA-2.0.0
	 */
	public function setAuthCookie($authKey, $expireDelay = null)
	{
		return $this->setCookie(Config::$config['auth_key_cookie']['name'], $authKey, $expireDelay, '/', Config::$config['auth_key_cookie']['domain'], Config::$config['auth_key_cookie']['secure'], Config::$config['auth_key_cookie']['http_only']);
	}

	/**
	 * Delete Auth Cookie
	 *
	 * @return boolean Returns true if succeeded, false otherwise.
	 * @version BETA-1.8.0
	 * @updated BETA-2.0.0
	 */
	public function deleteAuthCookie()
	{
		return $this->deleteCookie(Config::$config['auth_key_cookie']['name'], '/', Config::$config['auth_key_cookie']['domain'], Config::$config['auth_key_cookie']['secure'], Config::$config['auth_key_cookie']['http_only']);
	}

	/** --------------------- */
	/**  VII. 5. B. a. Infos  */
	/** --------------------- */

	/** Get Auth Cookie name */
	public function getAuthIDCookieName()
	{
		return Config::$config['auth_key_cookie']['name'];
	}

	/** Is exist Auth Cookie */
	public function isExistAuthID()
	{
		return $this->isExistCookie(Config::$config['auth_key_cookie']['name']);
	}

	/** Is empty Auth Cookie */
	public function isEmptyAuthID()
	{
		return $this->isEmptyCookie(Config::$config['auth_key_cookie']['name']);
	}

	/**
	 * Get Auth Key
	 *
	 * @return string Returns the Auth Key.
	 * @version BETA-1.8.0
	 * @updated BETA-2.0.0
	 */
	public function getAuthKey()
	{
		if (empty($this->cache['authKey'])) $this->cache['authKey'] = $this->getCookie(Config::$config['auth_key_cookie']['name']);
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
	public function getRequestsExpireDelay()
	{
		return Config::$config['request_expire_delay'];
	}

	/**
	 * Create a new request
	 *
	 * @return string|boolean Returns the request activate key.
	 * @version BETA
	 * @updated BETA-2.0.0
	 */
	public function createRequest($uid, $action, &$requestTime = null)
	{
		if (!$this->isAccountsManagementReady()) trigger_error('Sorry, the user management has been disabled.', E_USER_ERROR);
		else
		{
			$requestsMatches['activate_key'] = hash('sha512', $activateKey = $this->keygen(6, true, false, true));
			$requestsMatches['uid'] = $uid;
			$requestsMatches['action'] = $action;
			$requestsMatches['request_date'] = date('Y-m-d H:i:s', $requestTime = time());
			$requestsMatches['expire_date'] = date('Y-m-d H:i:s', $requestTime + Config::$config['request_expire_delay']);

			if ($this->insertAccountLine('REQUESTS', $requestsMatches)) return $activateKey;
			else return false;
		}
	}

	/** --------------------- */
	/**  VII. 5. B. Register  */
	/** --------------------- */

	/** Is register verification enabled */
	// -- Deprecated --
	public function isRegisterVerificationEnabled()
	{
		return Config::$config['account_activation'];
	}

	public function getRegisterVerificationStatus()
	{
		return Config::$config['account_activation'];
	}

	/**
	 * Register a new Account
	 *
	 * $mailInfos syntax: (array) [ subject, message, headers ]
	 *
	 * @return string|boolean Returns the activate key or true if succeeded, false otherwise.
	 * @version BETA
	 * @updated BETA-2.0.0
	 */
	public function registerAccount($email, $password, $oliSC = null, $mailInfos = [])
	{
		// if(!empty($password)) {
		if (is_array($oliSC) or is_bool($oliSC)) $mailInfos = [$oliSC, $oliSC = null][0];

		if (!empty($oliSC) and $oliSC == $this->getSecurityCode()) $isRootRegister = true;
		else if ($this->isAccountsManagementReady() and Config::$config['allow_register']) $isRootRegister = false;
		else $isRootRegister = null;

		if ($isRootRegister !== null)
		{
			if ($this->isLocalLogin())
			{
				if ($isRootRegister and !empty($hashedPassword = $this->hashPassword($password)))
				{
					$handle = fopen(OLIPATH . '.oliauth', 'w');
					$result = fwrite($handle, json_encode(['password' => $hashedPassword], JSON_FORCE_OBJECT | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
					fclose($handle);
					return $result ? true : false;
				}
				else return false;
			}
			else if (!empty($email) and $this->isAccountsManagementReady() and (Config::$config['allow_register'] or $isRootRegister))
			{
				/** Account Clean-up Process */
				if ($uid = $this->getAccountInfos('ACCOUNTS', 'uid', ['email' => $email], false) and $this->getUserRightLevel(['email' => $email]) == $this->translateUserRight('NEW-USER') and (!$expireDate = $this->getAccountInfos('REQUESTS', 'expire_date', ['uid' => $uid, 'action' => 'activate'], false) or strtotime($expireDate) < time())) $this->deleteFullAccount($uid);
				unset($uid);

				if (!$this->isExistAccountInfos('ACCOUNTS', ['email' => $email], false) and (!$isRootRegister or !$this->isExistAccountInfos('ACCOUNTS', ['user_right' => 'ROOT'], false)))
				{
					/** Hash the password (may be empty) */
					$hashedPassword = $this->hashPassword($password);

					/** Generate a new uid */
					do
					{
						$uid = $this->uuidAlt();
					} while ($this->isExistAccountInfos('ACCOUNTS', $uid, false));

					/** Set other account parameters */
					$userRight = $isRootRegister ? 'ROOT' : (!Config::$config['account_activation'] ? 'USER' : 'NEW-USER');

					/** Register Account */
					$this->insertAccountLine('ACCOUNTS', ['uid' => $uid, 'password' => $hashedPassword, 'email' => $email, 'register_date' => date('Y-m-d H:i:s'), 'user_right' => $userRight]);
					$this->insertAccountLine('INFOS', ['uid' => $uid]);
					$this->insertAccountLine('PERMISSIONS', ['uid' => $uid]);

					/** Allow to force-disabled account mail activation */
					if ($mailInfos !== false)
					{
						/** Generate Activate Key (if activation needed) */
						if (Config::$config['account_activation']) $activateKey = $this->createRequest($uid, 'activate');

						$subject = (!empty($mailInfos) and is_assoc($mailInfos)) ? $mailInfos['subject'] : 'Your account has been created!';
						$message = (!empty($mailInfos) and is_assoc($mailInfos)) ? $mailInfos['message'] : null;
						if (!isset($message))
						{
							$message .= '<p><b>Welcome</b>, your account has been successfully created! ♫</p>';
							if (!empty($activateKey))
							{
								$message .= '<p>One last step! Before you can log into your account, you need to <a href="' . $this->getUrlParam(0) . 'login/activate/' . $activateKey . '">activate your account</a> by clicking on this previous link, or by copying this url into your browser: ' . $this->getUrlParam(0) . 'login/activate/' . $activateKey . '.</p>';
								$message .= '<p>Once your account is activated, this activation link will be deleted. If you choose not to use it, it will automaticaly expire in ' . ($days = floor(Config::$config['request_expire_delay'] / 3600 / 24)) . ($days > 1 ? ' days' : ' day') . ', then you won\'t be able to use it anymore and anyone will be able to register using the same email you used.</p>';
							}
							else $message .= '<p>No further action is needed: your account is already activated. You can easily log into your account from <a href="' . $this->getUrlParam(0) . 'login/">our login page</a>, using your email, and – of course – your password.</p>';
							if (!empty(Config::$config['allow_recover'])) $message .= '<p>If you ever lose your password, you can <a href="' . $this->getUrlParam(0) . 'login/recover">recover your account</a> using your email: a confirmation mail will be sent to you on your demand.</p> <hr />';

							$message .= '<p>Your user ID: <i>' . $uid . '</i> <br />';
							$message .= 'Your hashed password (what we keep stored): <i>' . $hashedPassword . '</i> <br />';
							$message .= 'Your email: <i>' . $email . '</i> <br />';
							$message .= 'Your rights level: <i>' . $userRight . '</i></p>';
							$message .= '<p>Your password is kept secret and stored hashed in our database. <b>Do not give your password to anyone</b>, including our staff.</p> <hr />';

							$message .= '<p>Go on our website – <a href="' . $this->getUrlParam(0) . '">' . $this->getUrlParam(0) . '</a> <br />';
							$message .= 'Login – <a href="' . $this->getUrlParam(0) . 'login/">' . $this->getUrlParam(0) . 'login/</a> <br />';
							if (!empty(Config::$config['allow_recover'])) $message .= 'Recover your account – <a href="' . $this->getUrlParam(0) . 'login/recover">' . $this->getUrlParam(0) . 'login/recover</a></p>';
						}
						$headers = (!empty($mailInfos) and is_assoc($mailInfos)) ? $mailInfos['headers'] : $this->getDefaultMailHeaders();
						if (is_array($headers)) $headers = implode("\r\n", $headers);

						$mailResult = mail($email, $subject, $this->getTemplate('mail', ['__URL__' => $this->getUrlParam(0), '__NAME__' => $this->getSetting('name') ?: 'Oli Mailling Service', '__SUBJECT__' => $subject, '__CONTENT__' => $message]), $headers);
					}

					if (!$activateKey or $mailResult) return $uid;
					else
					{
						$this->deleteFullAccount($uid);
						return false;
					}
				}
				else return false;
			}
			else return false;
		}
		else return false;
		// } else return false;
	}

	/** ------------------ */
	/**  VII. 5. C. Login  */
	/** ------------------ */

	/**
	 * Check if the login process is considered to be local
	 *
	 * @return boolean Returns true if local.
	 * @version BETA-2.0.0
	 * @updated BETA-2.0.0
	 */
	public function isLocalLogin()
	{
		return !$this->isAccountsManagementReady() or !Config::$config['allow_login'];
	}

	/**
	 * Check if the login process is handled by an external login page
	 *
	 * @return boolean Returns true if external.
	 * @version BETA-2.0.0
	 * @updated BETA-2.0.0
	 */
	public function isExternalLogin()
	{
		return preg_match('/^https?:\/\/(?:[w]{3}\.)?((?:([\da-z\.-]+)\.)*([\da-z-]+\.(?:[a-z\.]{2,6})))\/?(\S+)$/i', Config::$config['external_login_url']);
	}

	/**
	 * Get Local Root User informations
	 *
	 * @return array|boolean Returns Local Root User informations if they exist, false otherwise.
	 * @version BETA-2.0.0
	 * @updated BETA-2.0.0
	 */
	public function getLocalRootInfos($whatVar = null)
	{
		if (file_exists(OLIPATH . '.oliauth'))
		{
			$localRootInfos = json_decode(file_get_contents(OLIPATH . '.oliauth'), true);
			return !empty($whatVar) ? $localRootInfos[$whatVar] : $localRootInfos;
		}
		else return false;
	}

	/**
	 * Verify login informations
	 *
	 * @return boolean Returns true if valid login infos.
	 * @version BETA
	 * @updated BETA-2.0.0
	 */
	public function verifyLogin($logid, $password = null)
	{
		if (empty($password)) $password = [$logid, $logid = null][0];

		if (empty($password)) return false;
		else if ($this->isLocalLogin()) return !empty($rootUserInfos = $this->getLocalRootInfos()) and password_verify($password, $rootUserInfos['password']);
		else if (!empty($logid))
		{
			$uid = $this->getAccountInfos('ACCOUNTS', 'uid', ['uid' => $logid, 'username' => $logid, 'email' => $logid], ['where_or' => true], false);
			if ($userPassword = $this->getAccountInfos('ACCOUNTS', 'password', $uid, false)) return password_verify($password, $userPassword);
			else return false;
		}
		else return false;
	}

	/**
	 * Handle the login process
	 *
	 * @return string|boolean Returns the auth key if logged in successfully, false otherwise.
	 * @version BETA
	 * @updated BETA-2.0.0
	 */
	public function loginAccount($logid, $password, $expireDelay = null, $setCookie = true)
	{
		if ($this->isExternalLogin()) return null;
		else if ($this->verifyLogin($logid, $password))
		{
			if ($this->isLocalLogin()) $uid = $logid;
			else
			{
				$uid = $this->getAccountInfos('ACCOUNTS', 'uid', ['uid' => $logid, 'username' => $logid, 'email' => $logid], ['where_or' => true], false);
				if ($this->needsRehashPassword($this->getAccountInfos('ACCOUNTS', 'password', $uid))) $this->updateAccountInfos('ACCOUNTS', ['password' => $this->hashPassword($password)], $uid);
			}

			if ($this->isLocalLogin() or $this->getUserRightLevel($uid) >= $this->translateUserRight('USER'))
			{
				$now = time();
				if (empty($expireDelay) or $expireDelay <= 0) $expireDelay = Config::$config['default_session_duration'] ?: 2 * 3600;

				$authKey = $this->keygen(Config::$config['auth_key_length'] ?: 32);
				if (!empty($authKey))
				{
					$result = null;
					if (!$this->isLocalLogin())
					{ //!?
						// if(!$this->isLocalLogin() OR $this->isExternalLogin()) { //!?
						// if(!$this->isLocalLogin() AND !$this->isExternalLogin()) { //!?
						/** Cleanup Process */
						// $this->deleteAccountLines('SESSIONS', '`update_date` < NOW() - INTERVAL 2 DAY');
						$this->deleteAccountLines('SESSIONS', '"update_date" < NOW() - INTERVAL \'2 DAY\'');

						if ($this->isExistAccountInfos('SESSIONS', ['auth_key' => hash('sha512', $authKey)])) $this->deleteAccountLines('SESSIONS', ['auth_key' => hash('sha512', $authKey)]);

						$now = time();
						$result = $this->insertAccountLine('SESSIONS', [
							'uid' => $uid,
							'auth_key' => hash('sha512', $authKey),
							'creation_date' => date('Y-m-d H:i:s', $now),
							'ip_address' => $this->getUserIP(),
							'user_agent' => $_SERVER['HTTP_USER_AGENT'],
							'login_date' => date('Y-m-d H:i:s', $now),
							'expire_date' => date('Y-m-d H:i:s', $now + $expireDelay),
							'update_date' => date('Y-m-d H:i:s', $now),
							'last_seen_page' => $this->getUrlParam(0) . implode('/', $this->getUrlParam('params')),
						]);
					}
					else
					{
						$rootUserInfos = $this->getLocalRootInfos();
						$handle = fopen(OLIPATH . '.oliauth', 'w');
						$result = fwrite($handle, json_encode(array_merge($rootUserInfos, [
							'auth_key' => hash('sha512', $authKey),
							'ip_address' => $this->getUserIP(),
							'login_date' => date('Y-m-d H:i:s', $now),
							'expire_date' => date('Y-m-d H:i:s', $now + $expireDelay),
						]),                                   JSON_FORCE_OBJECT | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
						fclose($handle);
					}

					if ($setCookie) $this->setAuthCookie($authKey, Config::$config['auth_key_cookie']['expire_delay'] ?: 3600 * 24 * 7);
					$this->cache['authKey'] = $authKey;

					return $result ? $authKey : false;
				}
				else return false;
			}
			else return false;
		}
		else return false;
	}

	/** ------------------- */
	/**  VII. 5. D. Logout  */
	/** ------------------- */

	/**
	 * Log out from a session
	 *
	 * @return boolean Returns true if logged out successfully, false otherwise.
	 * @version BETA
	 * @updated BETA-2.0.0
	 */
	public function logoutAccount($authKey = null, $deleteCookie = true)
	{
		if ($this->isLoggedIn($authKey))
		{
			if ($this->isLocalLogin())
			{
				$rootUserInfos = $this->getLocalRootInfos();
				$handle = fopen(OLIPATH . '.oliauth', 'w');
				$result = fwrite($handle, json_encode(array_merge($rootUserInfos, ['login_date' => null, 'expire_date' => null]), JSON_FORCE_OBJECT | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
				fclose($handle);
			}
			else $result = $this->deleteAccountLines('SESSIONS', ['auth_key' => hash('sha512', $authKey ?: $this->getAuthKey())]);

			if ($deleteCookie) $this->deleteAuthCookie();
			return $result ? true : false;
		}
		else return false;
	}

	/**
	 * Log out an account on all sessions
	 *
	 * @return boolean Returns true if logged out successfully, false otherwise.
	 * @version BETA
	 * @updated BETA-2.0.0
	 */
	public function logoutAllAccount($uid = null, $deleteCookie = false)
	{
		if ($this->isLocalLogin())
		{
			$rootUserInfos = $this->getLocalRootInfos();
			$handle = fopen(OLIPATH . '.oliauth', 'w');
			$result = fwrite($handle, json_encode(array_merge($rootUserInfos, ['login_date' => null, 'expire_date' => null]), JSON_FORCE_OBJECT | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
			fclose($handle);
		}
		else
		{
			if (empty($uid)) $uid = $this->getLoggedUser();
			$result = !empty($uid) ? $this->deleteAccountLines('SESSIONS', ['uid' => $uid]) : false;
		}

		if ($deleteCookie) $this->deleteAuthCookie();
		return $result ? true : false;
	}

	/** ---------------------------------- */
	/**  VII. 5. E. Accounts Restrictions  */
	/** ---------------------------------- */

	/** Get prohibited usernames */
	public function getProhibitedUsernames()
	{
		return Config::$config['prohibited_usernames'];
	}

	/** Is prohibited username? */
	public function isProhibitedUsername($username)
	{
		if (empty($usernae)) return null;
		else if (in_array($username, Config::$config['prohibited_usernames'])) return true;
		else
		{
			$found = false;
			foreach (Config::$config['prohibited_usernames'] as $eachProhibitedUsername)
			{
				if (stristr($username, $eachProhibitedUsername))
				{
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
	 * @return string Returns method.
	 * @version BETA-2.0.0
	 * @updated BETA-2.0.0
	 */
	public function getUserAvatarMethod($uid = null)
	{
		return $this->getAccountInfos('ACCOUNTS', 'avatar_method', $uid) ?: 'default';
	}

	/**
	 * Get Logged Avatar Method
	 *
	 * @return string Returns method.
	 * @version BETA-2.0.0
	 * @updated BETA-2.0.0
	 */
	public function getLoggedAvatarMethod()
	{
		return $this->getUserAvatarMethod();
	}

	/**
	 * Get User Avatar
	 *
	 * @return string Returns url.
	 * @version BETA-2.0.0
	 * @updated BETA-2.0.0
	 */
	public function getUserAvatar($uid = null, $selector = null, $size = null)
	{
		if (empty($uid)) $uid = $this->getLoggedUser();
		if (empty($selector)) $selector = $this->getUserAvatarMethod($uid);

		if ($selector == 'gravatar') return 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($this->getAccountInfos('ACCOUNTS', 'email', $uid)))) . (!empty($size) ? '?s=' . $size : null); // File Extension not necessary here.
		else if ($selector == 'custom' and !empty($filetype = $this->getAccountInfos('ACCOUNTS', 'avatar_filetype', $uid)) and file_exists(MEDIAPATH . 'avatars/' . $uid . '.' . $filetype)) return $this->getMediaUrl() . 'avatars/' . $uid . '.' . $filetype;
		else return $this->getMediaUrl() . 'default-avatar.png';
	}

	/**
	 * Get Logged User Avatar
	 *
	 * @return string Returns url.
	 * @version BETA-2.0.0
	 * @updated BETA-2.0.0
	 */
	public function getLoggedAvatar($selector = null, $size = null)
	{
		return $this->getUserAvatar(null, $selector, $size);
	}

	/**
	 * Save User Avatar
	 *
	 * @return string Returns url.
	 * @version BETA-2.0.0
	 * @updated BETA-2.0.0
	 */
	public function saveUserAvatar($filename, $filetype, $uid = null)
	{
		if (empty($uid)) $uid = $this->getLoggedUser();
		if (is_uploaded_file($filename))
		{
			// Check if the avatars/ folder exists
			if (!file_exists(MEDIAPATH . 'avatars/')) mkdir(MEDIAPATH . 'avatars/');
			else $this->deleteUserAvatar($uid); // Delete the current custom user avatar (if it exists)

			// Save the new custom user avatar
			return move_uploaded_file($filename, MEDIAPATH . 'avatars/' . $uid . '.' . $filetype) and $this->updateAccountInfos('ACCOUNTS', ['avatar_filetype' => $filetype], $uid);
		}
		else return false;
	}

	/**
	 * Delete User Avatar
	 *
	 * Return values:
	 * - true: Successfully deleted the user avatar file (see PHP unlink() docs)
	 * - false: Failed to delete the file (see PHP unlink() docs)
	 * - null: Actually did nothing; the file probably does not exists.
	 *
	 * @return bool Returns true on success.
	 * @version BETA-2.0.0
	 * @updated BETA-2.0.0
	 */
	public function deleteUserAvatar($uid = null)
	{
		if (file_exists(MEDIAPATH . 'avatars/'))
		{
			$currentFiletype = $this->getAccountInfos('ACCOUNTS', 'avatar_filetype', $uid);
			if (!empty($currentFiletype) and file_exists(MEDIAPATH . 'avatars/' . $uid . '.' . $currentFiletype)) return unlink(MEDIAPATH . 'avatars/' . $uid . '.' . $currentFiletype);
			else return null;
		}
		else return null;
	}

	/** ----------------------- */
	/**  VII. 7. Hash Password  */
	/** ----------------------- */

	/** Hash Password */
	public function hashPassword($password)
	{
		if (!empty($password))
		{
			if (!empty(Config::$config['password_hash']['salt'])) $hashOptions['salt'] = Config::$config['password_hash']['salt'];
			if (!empty(Config::$config['password_hash']['cost'])) $hashOptions['cost'] = Config::$config['password_hash']['cost'];
			return password_hash($password, Config::$config['password_hash']['algorithm'], $hashOptions ?: []);
		}
		else return null;
	}

	public function needsRehashPassword($password)
	{
		if (!empty(Config::$config['password_hash']['salt'])) $hashOptions['salt'] = Config::$config['password_hash']['salt'];
		if (!empty(Config::$config['password_hash']['cost'])) $hashOptions['cost'] = Config::$config['password_hash']['cost'];
		return password_needs_rehash($password, Config::$config['password_hash']['algorithm'], $hashOptions);
	}
}
