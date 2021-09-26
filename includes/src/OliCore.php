<?php
/*\
|*|  ----------------------------------
|*|  --- [  Oli - PHP Framework   ] ---
|*|  --- [  Version GAMMA: 1.0.0  ] ---
|*|  ----------------------------------
|*|
|*|  Oli is an open source PHP framework designed to help you create your website.
|*|
|*|  --- --- ---
|*|
|*|  Copyright (c) 2015-2021 Matiboux
|*|  https://matiboux.me
|*|
|*|  More information about Oli in the README.md file.
|*|  You can find it in the project repository: https://github.com/matiboux/Oli
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
|*|  ├ IV. Oli
|*|  │ ├ 1. Oli Infos
|*|  │ ├ 2. Oli Security Code
|*|  │ └ 3. Tools
|*|  │
|*|  ├ V. Configuration
|*|  │ ├ 1. DB
|*|  │ ├ 2. General
|*|  │ └ 3. Addons
|*|  │   ├ A. Management
|*|  │   └ B. Infos
|*|  │
|*|  ├ VI. DB
|*|  │
|*|  ├ VII. General
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
|*|  └ VIII. Accounts
\*/

namespace Oli;

use Oli\Config;

abstract class OliCore
{
	#region I. Variables

	/** List of public variables accessible publicly in read-only */
	private static array $readOnlyVars = [
		'initTimestamp',
		'addonsInfos',
		'fileNameParam',
		'contentStatus',
	];

	/** Components infos */
	private ?float $initTimestamp = null; // (PUBLIC READONLY)
	private array $addonsInfos = []; // Addons Infos (PUBLIC READONLY)

	/** Databases Management */
	private array $dbs = []; // SQL Wrappers
	// private ?string $defaultdb = null; // Selected DB (default db)
	// private $sql = null; // MySQL PDO Object (PUBLIC READONLY)
	// private $dbError = null; // MySQL PDO Error (PUBLIC READONLY)

	private ?AccountsManager $accountsManager = null;

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

	#endregion

	#region II. Constructor & Destructor

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
		// 	$oliUrl = file_get_contents(ABSPATH . '.oliurl');
		// 	if (!empty($oliUrl) && preg_match('/^(?:[a-z0-9-]+\.)+[a-z.]+(?:\/[^/]+)*\/$/i', $oliUrl) && !empty(Config::$config['settings']) && $oliUrl != Config::$config['settings']['url'])
		// 	{
		// 		$this->updateConfig(array('settings' => array_merge(Config::$config['settings'], array('url' => $oliUrl))), true);
		// 		Config::$config['settings']['url'] = $oliUrl;
		// 	}
		// }

		// Framework Initialization
		$this->initTimestamp = $initTimestamp ?: microtime(true);
		$this->setContentType('DEFAULT', 'utf-8');

		// Check for debug stutus override
		if (@Config::$config['debug'] === false && @$_GET['oli-debug'] === $this->getSecurityCode())
			Config::$config['debug'] = true;

		// Debug configuration
		if (@Config::$config['debug'] === true) error_reporting(E_ALL);

		// Initialize the accounts manager if enabled
		if (@Config::$config['accounts_management'])
			$this->accountsManager = new AccountsManager($this);
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

	#endregion

	#region III. Magic Methods

	/**
	 * OliCore Class Read-only variables management
	 *
	 * @return mixed Returns the requested variable value if is allowed to read, null otherwise.
	 * @version BETA
	 * @updated BETA-2.0.0
	 */
	public function __get($name): mixed
	{
		if ($name === 'config') return Config::$config;
		if ($name === 'oliInfos') return $this->getOliInfos();
		if (!in_array($name, self::$readOnlyVars, true)) return null;
		return $this->$name;
	}

	/**
	 * OliCore Class Is Set variables management
	 * This fix the empty() false negative issue on inaccessible variables.
	 *
	 * @return bool Returns true if the requested variable isn't empty and if is allowed to read, null otherwise.
	 * @version BETA
	 * @updated BETA-2.0.0
	 */
	public function __isset($name): bool
	{
		if (in_array($name, ['config', 'oliInfos'], true)) return true;
		if (!in_array($name, self::$readOnlyVars, true)) return false;
		return isset($this->$name);
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

	#endregion

	#region IV. Oli

	#region IV. 1. Oli Infos

	/**
	 * Get Oli Infos
	 *
	 * @param mixed|null $whatInfo
	 *
	 * @return array|string|null Returns a short description of Oli.
	 * @version BETA
	 * @updated BETA-2.0.0
	 */
	public function getOliInfos(mixed $info = null): array|string|null
	{
		// Load Oli Infos if not already loaded
		if (empty($this->cache['oliInfos']))
			$this->cache['oliInfos'] = file_exists(INCLUDESPATH . 'oli-infos.json')
				? json_decode(file_get_contents(INCLUDESPATH . 'oli-infos.json'), true) : null;

		return !empty($info) ? @$this->cache['oliInfos'][$info] : $this->cache['oliInfos'];
	}

	/** Get Team Infos */
	public function getTeamInfos($who = null, $info = null): mixed
	{
		$oliTeam = @$this->cache['oliInfos']['team'];
		if (empty($oliTeam)) return null;

		if (empty($who)) return $oliTeam;

		foreach ($oliTeam as $member)
		{
			if ($member['name'] == $who or in_array($who, !is_array($member['nicknames']) ? [$member['nicknames']] : $member['nicknames']))
			{
				if (!empty($info)) return $member[$info];
				else return $member;
			}
		}

		return null;
	}

	#endregion

	#region IV. 2. Oli Security Code

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

	#endregion

	#region IV. 3. Tools

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

	#endregion

	#endregion

	#region V. Configuration

	#region V. 1. DB

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

		if ($this->accountsManager && !$this->accountsManager->issetDB())
			$this->accountsManager->setDB($db);
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

	#endregion

	#region V. 2. General

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

	#endregion

	#region V. 3. Addons

	#region V. 3. A. Management

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

	#endregion

	#region V. 3. B. Infos

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

	#endregion

	#endregion

	#endregion

	#region VI. DB

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

	#endregion

	#region VII. General

	#region VII. 1. Load Website

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

					// Pages
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
				else if ($countFileName > 1 && $fileName[0] == Config::$config['scripts_alias'])
				{
					$pagesPathOptions = [
						SCRIPTSPATH, // User Scripts
						OLISCRIPTPATH, // Oli Scripts
					];
					foreach ($pagesPathOptions as $pagesPath)
					{
						if (is_file($pagesPath . $slicedFileNameParam . '.php'))
						{
							$found = $pagesPath . $slicedFileNameParam . '.php';
							$this->fileNameParam = $fileNameParam;
							$this->setContentType('JSON');
							break 2; // Break the outer foreach loop
						}
					}
				}

				// User Assets
				else if ($countFileName > 1 && $fileName[0] == Config::$config['assets_alias'])
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
				else if ($countFileName > 1 && $fileName[0] == Config::$config['media_alias'])
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
						$this->accountsManager?->translateUserRight($rights[1]);
					$rules['access'][$file][$access]['to'] =
						$this->accountsManager?->translateUserRight($rights[2]);
				}

				// Simple rule
				else $rules['access'][$file][$access] = $this->accountsManager?->translateUserRight($scope);
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
		if ($this->accountsManager?->isReady() && ($userRightLevel = $this->accountsManager->getUserRightLevel()))
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

	#endregion

	#region VII. 2. Settings

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

	#endregion

	#region VII. 3. Custom Content

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

	#endregion

	#region VII. 5. HTTP Tools

	#region VII. 5. A. Content Type

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

	#endregion

	#region VII. 5. B. Cookie Management

	#region VII. 5. B. a. Read Functions

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

	#endregion

	#region VII. 5. B. b. Write Functions

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

	#endregion

	#endregion

	#region VII. 5. C. _POST vars

	#region VII. 5. C. a. Read Functions

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

	#endregion

	#region VII. 5. C. b. Write Functions

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

	#endregion

	#endregion

	#region VII. 5. D. Mail Management

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

	#endregion

	#endregion

	#region VII. 6. HTML Tools

	#region VII. 6. A. File Loaders

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

	#endregion

	#region VII. 6. B. File Minimizers

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

	#endregion

	#endregion

	#region VII. 7. Url Functions

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
		return $this->accountsManager?->isExternalLogin()
			? Config::$config['external_login_url']
			: $this->getUrlParam(0) . (Config::$config['login_alias'] ?: 'oli-login') . '/';
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

	#endregion

	#region VII. 8. Utility Tools

	#region VII. 8. A. Templates

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

	#endregion

	#region VII. 8. B. Generators

	#region VII. 8. B. a. UUID

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

	#endregion

	#region VII. 8. B. b. Misc

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

	#endregion

	#endregion

	#region VII. 8. C. Data Conversion

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

	#endregion

	#region VII. 8. D. Date & Time

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

	#endregion

	#region VII. 8. E. Client Infos

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

	#endregion

	#endregion

	#endregion

	#region VIII. Accounts

	/**
	 * Get the accounts manager
	 *
	 * @return AccountsManager|null Returns the accounts manager if enabled
	 * @version GAMMA-1.0.0
	 */
	public function getAccountsManager(): ?AccountsManager
	{
		return $this->accountsManager;
	}

	/**
	 * Get the accounts manager
	 *
	 * @return bool Returns whether the accounts manager is enabled or not
	 * @version GAMMA-1.0.0
	 */
	public function isAccountsManaged(): bool
	{
		return $this->accountsManager !== null;
	}

	#endregion
}
