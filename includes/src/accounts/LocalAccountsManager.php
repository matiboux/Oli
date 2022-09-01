<?php
/*\
|*|  --------------------------------------
|*|  --- [  Accounts Manager for Oli  ] ---
|*|  --------------------------------------
|*|
|*|  This is the Oli class for use as framework.
|*|
|*|  More information about Oli in the README.md file.
|*|  You can find it in the project repository: https://github.com/matiboux/Oli/
\*/

/*\
|*|  ╒════════════════════════╕
|*|  │ :: TABLE OF CONTENT :: │
|*|  ╞════════════════════════╛
|*|  │
|*|  ├ I. Properties
|*|  │ ├ 1. Constants
|*|  │ └ 2. Variables
|*|  ├ II. Magic Methods
|*|  │
|*|  ├ III. Configuration
|*|  │
|*|  ├ IV. DB Status
|*|  │
|*|  └ V. Accounts
|*|    ├ 1. Status
|*|    ├ 2. MySQL
|*|    │ ├ A. Table Codes
|*|    │ ├ B. Read
|*|    │ └ C. Write
|*|    ├ 3. User Rights & Permissions
|*|    │ ├ A. User Rights
|*|    │ └ B. User Permissions
|*|    │   ├ a. General
|*|    │   ├ b. Rights Permissions
|*|    │   └ c. User Permissions
|*|    ├ 4. Auth Key Cookie
|*|    │ ├ A. Create & Delete
|*|    │ └ B. Infos
|*|    ├ 5. User Sessions
|*|    │ ├ A. General
|*|    │ └ B. Auth Cookie
|*|    │   ├ a. Management
|*|    │   └ b. Infos
|*|    ├ 6. User Accounts
|*|    │ ├ A. Requests
|*|    │ ├ B. Register
|*|    │ ├ C. Login
|*|    │ ├ D. Logout
|*|    │ └ E. Accounts Restrictions
|*|    ├ 7. User Avatar
|*|    └ 8. Hash Password
\*/

namespace Oli\Accounts;

use Oli\Config;
use Oli\DB\DBWrapper;
use Oli\OliCore;

class LocalAccountsManager
{
	#region I. Properties

	#region I. 1. Constants

	const TABLE_ACCOUNTS = 'ACCOUNTS'; // Accounts main informations (email, password...)
	const TABLE_INFOS = 'INFOS'; // Accounts additional informations
	const TABLE_SESSIONS = 'SESSIONS'; // Accounts login sessions
	const TABLE_REQUESTS = 'REQUESTS'; // Accounts requests
	const TABLE_LOGS = 'LOG_LIMITS';
	const TABLE_RIGHTS = 'RIGHTS'; // Accounts rights list (permissions groups)
	const TABLE_PERMISSIONS = 'PERMISSIONS'; // Accounts personnal permissions

	#endregion

	#region I. 2. Variables

	/** List of public variables accessible publicly in read-only */
	private static array $readOnlyVars = [
		'Oli',
		'db',
	];

	/** Reference to Oli */
	private OliCore $Oli;

	/** Reference to the database used for accounts management */
	private ?DBWrapper $db = null;

	/** Data Cache */
	private array $cache = [];

	#endregion

	#endregion

	#region II. Magic Methods

	/**
	 * AccountsManager Class Construct function
	 *
	 * @param OliCore $Oli
	 *
	 * @version BETA
	 * @updated BETA-2.0.0
	 */
	public function __construct(OliCore $Oli)
	{
		$this->Oli = $Oli;
	}

	/**
	 * OliCore Class Read-only variables management
	 *
	 * @return mixed Returns the requested variable value if is allowed to read, null otherwise.
	 * @version BETA
	 * @updated BETA-2.0.0
	 */
	public function __get($name): mixed
	{
		if (!in_array($name, self::$readOnlyVars, true)) return null;
		return $this->$name;
	}

	/**
	 * OliCore Class Is Set variables management
	 * This fix the empty() false negative issue on inaccessible variables.
	 *
	 * @return boolean Returns true if the requested variable isn't empty and if is allowed to read, null otherwise.
	 * @version BETA
	 * @updated BETA-2.0.0
	 */
	public function __isset($name): bool
	{
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
		return 'Accounts Manager for ' . $this->Oli;
	}

	#endregion

	#region III. Configuration


	#endregion

	#region V. Accounts

	#region V. 1. Status

	/**
	 * Check if the database is ready for user management
	 *
	 * @return boolean Returns true if local.
	 * @version GAMMA-1.0.0
	 */
	public function isReady(): bool
	{
		return true;
	}

	#endregion

	#region V. 5. User Sessions

	#region V. 5. A. Status

	/**
	 * Is User Logged In?
	 *
	 * @return boolean Returns true if logged out successfully, false otherwise.
	 * @since GAMMA-1.0.0
	 */
	public function isLoggedIn($authKey = null): bool
	{
		if (!isset($authKey))
			$authKey = $this->getAuthKey();

		if (empty($authKey))
			return false;

		$sessionInfos = $this->getLocalRootInfos();
		if (@$sessionInfos['auth_key'] !== $authKey)
			return false;

		$expireTime = strtotime($sessionInfos['expire_date']);
		return $expireTime !== false && $expireTime > time();
	}

	#endregion

	#region V. 5. B. Infos

	/**
	 * Get Auth Key
	 *
	 * @return string Returns the Auth Key.
	 * @version BETA-1.8.0
	 * @updated BETA-2.0.0
	 */
	public function getAuthKey(): string
	{
		if (@$this->cache['authKey'] === null)
			$this->cache['authKey'] = $this->getAuthCookie();

		return $this->cache['authKey'];
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
		if (empty($authKey))
			$authKey = $this->getAuthKey();

		if (!$this->isLoggedIn($authKey))
			return null;

		return $this->getLocalRootInfos()['username'];
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
		if (!$this->isLoggedIn($authKey))
			return null;

		$type = 'local';
		return 'root';
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
		if (!$this->isLoggedIn($authKey))
			return null;

		return 'root';
	}

	#endregion

	#region V. 5. C. Auth Cookie

	#region V. 5. C. a. Management

	/**
	 * Set Auth Cookie
	 *
	 * @return boolean Returns true if succeeded, false otherwise.
	 * @version BETA-1.8.0
	 * @updated BETA-2.0.0
	 */
	public function setAuthCookie($authKey, $expireDelay)
	{
		return $this->Oli->setCookie(@Config::$config['auth_key_cookie']['name'], $authKey, $expireDelay, '/',
									 @Config::$config['auth_key_cookie']['domain'],
									 @Config::$config['auth_key_cookie']['secure'],
									 @Config::$config['auth_key_cookie']['http_only']);
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
		return $this->Oli->deleteCookie(@Config::$config['auth_key_cookie']['name'], '/',
		                                @Config::$config['auth_key_cookie']['domain'],
										@Config::$config['auth_key_cookie']['secure'],
										@Config::$config['auth_key_cookie']['http_only']);
	}

	#endregion

	#region V. 5. C. b. Infos

	/** Get Auth Cookie name */
	public function getAuthCookieName(): string
	{
		return @Config::$config['auth_key_cookie']['name'];
	}

	/** Get Auth Cookie value */
	public function getAuthCookie(): string
	{
		return $this->Oli->getCookie(@Config::$config['auth_key_cookie']['name']);
	}

	/** Is exist Auth Key Cookie */
	public function isExistAuthCookie(): bool
	{
		return $this->Oli->isExistCookie(@Config::$config['auth_key_cookie']['name']);
	}

	/** Is empty Auth Cookie */
	public function isEmptyAuthCookie(): bool
	{
		return $this->Oli->isEmptyCookie(@Config::$config['auth_key_cookie']['name']);
	}

	#endregion

	#endregion

	#endregion

	#region V. 6. User Accounts

	#region V. 6. A. Requests

	/** Get the requests expire delay */
	public function getRequestsExpireDelay()
	{
		return @Config::$config['request_expire_delay'];
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
		if (!$this->isReady())
			trigger_error('Sorry, the user management has been disabled.', E_USER_ERROR);
		else
		{
			$requestsMatches['activate_key'] = hash('sha512', $activateKey = $this->Oli->keygen(6, true, false, true));
			$requestsMatches['uid'] = $uid;
			$requestsMatches['action'] = $action;
			$requestsMatches['request_date'] = date('Y-m-d H:i:s', $requestTime = time());
			$requestsMatches['expire_date'] = date('Y-m-d H:i:s', $requestTime + @Config::$config['request_expire_delay']);

			if ($this->insertAccountLine(self::TABLE_REQUESTS, $requestsMatches))
				return $activateKey;
		}

		return false;
	}

	#endregion

	#region V. 6. B. Register

	/**
	 * Is register enabled?
	 *
	 * @return boolean Returns true if login is enabled
	 */
	public function isRegisterEnabled(): bool
	{
		// return @Config::$config['allow_register'] && !$this->isLocalLogin();
		return @Config::$config['allow_login'] && @Config::$config['allow_register'] && !$this->isLocalLogin();
	}

	/**
	 * Is root registed?
	 *
	 * @return boolean Returns true if root is registed
	 */
	public function isRootRegistered(): bool
	{
		$localRootInfos = $this->getLocalRootInfos();
		return !empty($localRootInfos['password']);
	}

	/**
	 * Is root register enabled?
	 *
	 * @return boolean Returns true if root register is enabled
	 */
	public function isRootRegisterEnabled(): bool
	{
		return @Config::$config['allow_login'] && !$this->isRootRegistered();
	}

	/** Is register verification enabled */
	public function isRegisterVerificationEnabled(): bool
	{
		return @Config::$config['account_activation'] && $this->isRegisterEnabled();
	}

	public function getRegisterVerificationStatus(): bool
	{
		return @Config::$config['account_activation'];
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
		if (!empty($oliSC) && $oliSC == $this->Oli->getSecurityCode()) $isRootRegister = true;
		else if ($this->isReady() && @Config::$config['allow_register']) $isRootRegister = false;
		else $isRootRegister = null;

		if ($isRootRegister !== null)
		{
			if ($isRootRegister && !empty($hashedPassword = $this->hashPassword($password)))
			{
				$handle = fopen(OLIPATH . '.oliauth', 'w');
				$result = fwrite($handle, json_encode(['password' => $hashedPassword], JSON_FORCE_OBJECT | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
				fclose($handle);
				return $result ? true : false;
			}
		}

		return false;
	}

	#endregion

	#region V. 6. C. Login

	/**
	 * Is login enabled?
	 *
	 * @return boolean Returns true if login is enabled
	 */
	public function isLoginEnabled(): bool
	{
		// return @Config::$config['allow_login'] || $this->isLocalLogin();
		return @Config::$config['allow_login'];
	}

	/**
	 * Check if the login process is considered to be local
	 *
	 * @return boolean Returns true if local.
	 * @version BETA-2.0.0
	 * @updated BETA-2.0.0
	 */
	public function isLocalLogin()
	{
		// return !$this->isReady() || !@Config::$config['allow_login'];
		return !$this->isReady();
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
		return preg_match('/^https?:\/\/(?:[w]{3}\.)?((?:([\da-z\.-]+)\.)*([\da-z-]+\.(?:[a-z\.]{2,6})))\/?(\S+)$/i',
		                  @Config::$config['external_login_url']);
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

		return false;
	}

	/**
	 * Verify login informations
	 *
	 * @return bool Returns true if valid login infos.
	 * @version BETA
	 * @updated BETA-2.0.0
	 */
	public function verifyLogin($logid, $password = null): bool
	{
		if (empty($password))
			$password = [$logid, $logid = null][0];

		if (empty($password))
			return false;

		if ($this->isLocalLogin())
		{
			$rootUserInfos = $this->getLocalRootInfos();
			return !empty($rootUserInfos) && password_verify($password, $rootUserInfos['password']);
		}

		if (!empty($logid))
		{
			$uid = $this->getAccountInfos(self::TABLE_ACCOUNTS, 'uid', ['uid' => $logid, 'username' => $logid, 'email' => $logid], ['where_or' => true], false);
			$userPassword = $this->getAccountInfos(self::TABLE_ACCOUNTS, 'password', $uid, false);
			return $userPassword ? password_verify($password, $userPassword) : false;
		}

		return false;
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
		if ($this->isExternalLogin())
			return null;

		if ($this->verifyLogin($logid, $password))
		{
			if ($this->isLocalLogin())
				$uid = $logid;
			else
			{
				$uid = $this->getAccountInfos(self::TABLE_ACCOUNTS, 'uid', ['uid' => $logid, 'username' => $logid, 'email' => $logid], ['where_or' => true], false);
				if ($this->needsRehashPassword($this->getAccountInfos(self::TABLE_ACCOUNTS, 'password', $uid)))
					$this->updateAccountInfos(self::TABLE_ACCOUNTS, ['password' => $this->hashPassword($password)], $uid);
			}

			if ($this->isLocalLogin() || $this->getUserRightLevel($uid) >= $this->translateUserRight('USER'))
			{
				$now = time();
				if (empty($expireDelay) || $expireDelay <= 0)
					$expireDelay = @Config::$config['default_session_duration'] ?: 2 * 3600;

				$authKey = $this->Oli->keygen(@Config::$config['auth_key_length'] ?: 32);
				if (!empty($authKey))
				{
					$result = null;
					if (!$this->isLocalLogin())
					{ //!?
						// if(!$this->isLocalLogin() || $this->isExternalLogin()) { //!?
						// if(!$this->isLocalLogin() && !$this->isExternalLogin()) { //!?
						/** Cleanup Process */
						// $this->deleteAccountLines(self::TABLE_SESSIONS, '`update_date` < NOW() - INTERVAL 2 DAY');
						$this->deleteAccountLines(self::TABLE_SESSIONS, '"update_date" < NOW() - INTERVAL \'2 DAY\'');

						if ($this->isExistAccountInfos(self::TABLE_SESSIONS, ['auth_key' => hash('sha512', $authKey)])) $this->deleteAccountLines(self::TABLE_SESSIONS, ['auth_key' => hash('sha512', $authKey)]);

						$now = time();
						$result = $this->insertAccountLine(self::TABLE_SESSIONS,
							[
								'uid' => $uid,
								'auth_key' => hash('sha512', $authKey),
								'creation_date' => date('Y-m-d H:i:s', $now),
								'ip_address' => $this->Oli->getUserIP(),
								'user_agent' => $_SERVER['HTTP_USER_AGENT'],
								'login_date' => date('Y-m-d H:i:s', $now),
								'expire_date' => date('Y-m-d H:i:s', $now + $expireDelay),
								'update_date' => date('Y-m-d H:i:s', $now),
								'last_seen_page' => $this->Oli->getUrlParam(0) . implode('/', $this->Oli->getUrlParam('params')),
							]);
					}
					else
					{
						$rootUserInfos = $this->getLocalRootInfos();
						$rootUserInfos = array_merge($rootUserInfos,
							[
								'auth_key' => hash('sha512', $authKey),
								'ip_address' => $this->Oli->getUserIP(),
								'login_date' => date('Y-m-d H:i:s', $now),
								'expire_date' => date('Y-m-d H:i:s', $now + $expireDelay),
							]);

						$handle = fopen(OLIPATH . '.oliauth', 'w');
						$result = fwrite($handle, json_encode($rootUserInfos, JSON_FORCE_OBJECT | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
						fclose($handle);
					}

					if ($setCookie)
						$this->setAuthCookie($authKey, @Config::$config['auth_key_cookie']['expire_delay'] ?: 3600 * 24 * 7);

					$this->cache['authKey'] = $authKey;

					if ($result)
						return $authKey;
				}
			}
		}

		return false;
	}

	#endregion

	#region V. 6. D. Logout

	/**
	 * Log out the current session
	 *
	 * @return boolean Returns true if logged out successfully, false otherwise.
	 * @version BETA
	 * @updated BETA-2.0.0
	 */
	public function logoutSession($authKey = null, $deleteCookie = true)
	{
		if (!$this->isLoggedIn($authKey))
			return false;

		if ($this->isLocalLogin())
		{
			$rootUserInfos = $this->getLocalRootInfos();
			$rootUserInfos = array_merge($rootUserInfos, ['login_date' => null, 'expire_date' => null]);

			$handle = fopen(OLIPATH . '.oliauth', 'w');
			$result = fwrite($handle, json_encode($rootUserInfos, JSON_FORCE_OBJECT | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
			fclose($handle);
		}
		else
		{
			$authKey = hash('sha512', $authKey ?: $this->getAuthKey());
			$result = $this->deleteAccountLines(self::TABLE_SESSIONS, ['auth_key' => $authKey]);
		}

		if ($deleteCookie)
			$this->deleteAuthCookie();

		return $result ? true : false;
	}

	/**
	 * Log out all sessions
	 *
	 * @return boolean Returns true if logged out successfully, false otherwise.
	 * @version BETA
	 * @updated BETA-2.0.0
	 */
	public function logoutAccount($uid = null, $deleteCookie = false)
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
			$result = !empty($uid) ? $this->deleteAccountLines(self::TABLE_SESSIONS, ['uid' => $uid]) : false;
		}

		if ($deleteCookie)
			$this->deleteAuthCookie();

		return $result ? true : false;
	}

	#endregion

	#region V. 6. E. Accounts Restrictions

	/** Get prohibited usernames */
	public function getProhibitedUsernames(): mixed
	{
		return @Config::$config['prohibited_usernames'];
	}

	/** Get prohibited username words */
	public function getProhibitedUsernameWords(): mixed
	{
		return @Config::$config['prohibited_username_words'];
	}

	/** Is prohibited username? */
	public function isProhibitedUsername($username): bool
	{
		if (empty($username)) return false;

		if (is_array(@Config::$config['prohibited_usernames']))
			foreach (Config::$config['prohibited_usernames'] as $prohibitedUsername)
				if (stripos($username, $prohibitedUsername) === 0)
					return true;

		if (is_array(@Config::$config['prohibited_username_words']))
			foreach (Config::$config['prohibited_username_words'] as $prohibitedWord)
				if (stripos($username, $prohibitedWord) !== false)
					return true;

		return false;
	}

	#endregion

	#endregion

	#region V. 7. User Avatar

	/**
	 * Get User Avatar Method
	 *
	 * @return string Returns method.
	 * @version BETA-2.0.0
	 * @updated BETA-2.0.0
	 */
	public function getUserAvatarMethod($uid = null): string
	{
		return $this->getAccountInfos(self::TABLE_ACCOUNTS, 'avatar_method', $uid) ?: 'default';
	}

	/**
	 * Get Logged Avatar Method
	 *
	 * @return string Returns method.
	 * @version BETA-2.0.0
	 * @updated BETA-2.0.0
	 */
	public function getLoggedAvatarMethod(): string
	{
		return $this->getUserAvatarMethod();
	}

	/**
	 * Get User Avatar
	 *
	 * @return string Returns user avatar url
	 * @version BETA-2.0.0
	 * @updated BETA-2.0.0
	 */
	public function getUserAvatar($uid = null, $selector = null, $size = null): string
	{
		if (empty($uid)) $uid = $this->getLoggedUser();
		if (empty($selector)) $selector = $this->getUserAvatarMethod($uid);

		if ($selector == 'gravatar')
		{
			$email = $this->getAccountInfos(self::TABLE_ACCOUNTS, 'email', $uid);
			return 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($email))) . (!empty($size) ? '?s=' . $size : null); // File Extension not necessary here
		}

		if ($selector == 'custom' && !empty($filetype = $this->getAccountInfos(self::TABLE_ACCOUNTS, 'avatar_filetype', $uid)) && file_exists(MEDIAPATH . 'avatars/' . $uid . '.' . $filetype))
			return $this->Oli->getMediaUrl() . 'avatars/' . $uid . '.' . $filetype;

		return $this->Oli->getMediaUrl() . 'default-avatar.png';
	}

	/**
	 * Get Logged User Avatar
	 *
	 * @return string Returns logged user avatar url
	 * @version BETA-2.0.0
	 * @updated BETA-2.0.0
	 */
	public function getLoggedAvatar($selector = null, $size = null): string
	{
		return $this->getUserAvatar(null, $selector, $size);
	}

	/**
	 * Save User Avatar
	 *
	 * @return boolean
	 * @version BETA-2.0.0
	 * @updated BETA-2.0.0
	 */
	public function saveUserAvatar($filename, $filetype, $uid = null): bool
	{
		if (empty($uid)) $uid = $this->getLoggedUser();

		if (is_uploaded_file($filename))
		{
			// Check if the 'avatars/' folder exists
			if (!file_exists(MEDIAPATH . 'avatars/'))
				mkdir(MEDIAPATH . 'avatars/');
			else
				// Delete the current custom user avatar (if it exists)
				$this->deleteUserAvatar($uid);

			// Save the new custom user avatar
			return move_uploaded_file($filename, MEDIAPATH . 'avatars/' . $uid . '.' . $filetype)
			       && $this->updateAccountInfos(self::TABLE_ACCOUNTS, ['avatar_filetype' => $filetype], $uid);
		}

		return false;
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
	public function deleteUserAvatar($uid = null): bool
	{
		if (file_exists(MEDIAPATH . 'avatars/'))
		{
			$currentFiletype = $this->getAccountInfos(self::TABLE_ACCOUNTS, 'avatar_filetype', $uid);
			if (!empty($currentFiletype) && file_exists(MEDIAPATH . 'avatars/' . $uid . '.' . $currentFiletype))
				return unlink(MEDIAPATH . 'avatars/' . $uid . '.' . $currentFiletype);
		}

		return false;
	}

	#endregion

	#region V. 8. Hash Password

	/** Hash Password */
	public function hashPassword($password): string|false|null
	{
		return password_hash($password,
		                     @Config::$config['password_hash']['algorithm'],
		                     @Config::$config['password_hash'] ?? []);
	}

	public function needsRehashPassword($password): bool
	{
		return password_needs_rehash($password,
		                             @Config::$config['password_hash']['algorithm'],
		                             @Config::$config['password_hash'] ?? []);
	}

	#endregion

	#endregion
}
