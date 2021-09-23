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
|*|  ├ I. Variables
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

namespace Oli;

class AccountsManager
{
	#region I. Variables

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

	/**
	 * Set DB connection
	 *
	 * @param DBWrapper $db
	 *
	 * @return void
	 * @version GAMMA-1.0.0
	 * @updated GAMMA-1.0.0
	 */
	public function setDB(DBWrapper $db): void
	{
		$this->db = $db;
	}

	/**
	 * Reset DB connection
	 *
	 * @return void
	 * @version GAMMA-1.0.0
	 * @updated GAMMA-1.0.0
	 */
	public function removeDB(): void
	{
		$this->db = null;
	}

	#endregion

	#region IV. DB Status

	/**
	 * Is set DB connection
	 *
	 * @return bool Returns whether a DB connection is set.
	 * @version GAMMA-1.0.0
	 * @updated GAMMA-1.0.0
	 */
	public function issetDB(): bool
	{
		return $this->db !== null;
	}

	/**
	 * Is set up DB connection
	 *
	 * @return boolean Returns the MySQL connection status
	 * @version GAMMA-1.0.0
	 * @updated GAMMA-1.0.0
	 */
	public function isSetupDB(): bool
	{
		return $this->db?->isSetupDB() === true;
	}

	/**
	 * Get DB Wrapper object
	 *
	 * @return DBWrapper|null Returns used DB Wrapper object
	 * @deprecated AccountsManager::$db can be accessed directly
	 * @version GAMMA-1.0.0
	 * @updated GAMMA-1.0.0
	 */
	public function getDB(): ?DBWrapper
	{
		return $this->db;
	}

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
		if (!$this->isSetupDB()) return false;

		$status = [];
		foreach (Config::$config['accounts_tables'] as $eachTable)
			if (!$status[] = $this->db->isExistTableSQL($eachTable)) break;

		return !in_array(false, $status, true);
	}

	#endregion

	#region V. 2. MySQL

	#region V. 2. A. Table Codes

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
	public function translateAccountsTableCode(string $tableCode)
	{
		$tableCode = strtolower($tableCode);
		return @Config::$config['accounts_tables'][$tableCode];
	}

	#endregion

	#region V. 2. B. Read

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
	public function getFirstAccountInfo(string $tableCode, string $whatVar, bool $rawResult = false)
	{
		return $this->db->getFirstInfoSQL($this->translateAccountsTableCode($tableCode) ?: $tableCode,
		                                  $whatVar, $rawResult);
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
	public function getFirstAccountLine(string $tableCode, bool $rawResult = false)
	{
		return $this->db->getFirstLineSQL($this->translateAccountsTableCode($tableCode) ?: $tableCode,
		                                  $rawResult);
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
	public function getLastAccountInfo(string $tableCode, string $whatVar, bool $rawResult = false)
	{
		return $this->db->getLastInfoSQL($this->translateAccountsTableCode($tableCode) ?: $tableCode,
		                                 $whatVar, $rawResult);
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
	public function getLastAccountLine(string $tableCode, bool $rawResult = false)
	{
		return $this->db->getLastLineSQL($this->translateAccountsTableCode($tableCode) ?: $tableCode,
		                                 null, $rawResult);
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
	public function getAccountLines(string $tableCode, array|string|null $where = null, $settings = null, $caseSensitive = null, $forceArray = null, $rawResult = null)
	{
		if (!isset($where))
		{
			if ($this->isLoggedIn()) $where = ['uid' => $this->getLoggedUser()];
			else return false;
		}
		else if (!is_array($where) && $where != 'all') $where = ['uid' => $where];

		return $this->db->getLinesSQL($this->translateAccountsTableCode($tableCode) ?: $tableCode,
		                              $where, $settings, $caseSensitive, $forceArray, $rawResult);
	}

	/**
	 * Get infos from account table
	 *
	 * @param string $tableCode Table code of the table to get data from
	 * @param array|string $whatVar What var(s) to return
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
	public function getAccountInfos(string $tableCode, array|string|null $whatVar, $where = null, $settings = null, $caseSensitive = null, $forceArray = null, $rawResult = null)
	{
		if (!isset($where))
		{
			if ($this->isLoggedIn()) $where = ['uid' => $this->getLoggedUser()];
			else return null;
		}
		else if (!is_array($where) and $where != 'all') $where = ['uid' => $where];

		return $this->db->getInfosSQL($this->translateAccountsTableCode($tableCode) ?: $tableCode,
		                              $whatVar, $where, $settings, $caseSensitive, $forceArray, $rawResult);
	}

	/**
	 * Get summed infos from account table
	 *
	 * @param string $tableCode Table code of the table to get data from
	 * @param array|string $whatVar What var(s) to return
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
	public function getSummedAccountInfos(string $tableCode, $whatVar, $where = null, $caseSensitive = true)
	{
		if (!isset($where))
		{
			if ($this->isLoggedIn()) $where = ['uid' => $this->getLoggedUser()];
			else return false;
		}
		else if (!is_array($where) and $where != 'all') $where = ['uid' => $where];
		return $this->db->getSummedInfosSQL($this->translateAccountsTableCode($tableCode) ?: $tableCode,
		                                    $whatVar, $where, $caseSensitive);
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
	public function isEmptyAccountInfos(string $tableCode, $whatVar, $where = null, $settings = null, $caseSensitive = null)
	{
		if (!isset($where))
		{
			if ($this->isLoggedIn()) $where = ['uid' => $this->getLoggedUser()];
			else return false;
		}
		else if (!is_array($where) and $where != 'all') $where = ['uid' => $where];

		return $this->db->isEmptyInfosSQL($this->translateAccountsTableCode($tableCode) ?: $tableCode,
		                                  $whatVar, $where, $settings, $caseSensitive);
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

		return $this->db->isExistInfosSQL($this->translateAccountsTableCode($tableCode) ?: $tableCode,
		                                  $where, $caseSensitive);
	}

	#endregion

	#region V. 2. C. Write

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
		return $this->db->insertLineSQL($this->translateAccountsTableCode($tableCode) ?: $tableCode,
		                                $what, $errorInfo);
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

		return $this->db->updateInfosSQL($this->translateAccountsTableCode($tableCode) ?: $tableCode,
		                                 $what, $where, $errorInfo);
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
		return $this->db->deleteLinesSQL($this->translateAccountsTableCode($tableCode) ?: $tableCode,
		                                 $where, $errorInfo);
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

	#endregion

	#endregion

	#region V. 3. User Rights & Permissions

	#region V. 3. A. User Rights

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
		return !empty($userRight)
		       && $this->isExistAccountInfos('RIGHTS', ['user_right' => $userRight], $caseSensitive);
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
		else if ($this->isReady() && !empty($userRight))
		{
			// Check for Level -> User Right translation
			$returnValue = $this->getAccountInfos('RIGHTS', 'user_right', ['level' => $userRight], $caseSensitive);
			if ($returnValue !== null)
				return $returnValue;

			// Check for User Right -> Level translation
			$returnValue = $this->getAccountInfos('RIGHTS', 'level', ['user_right' => $userRight], $caseSensitive);
			if ($returnValue !== null)
				return (int)$returnValue;
		}

		return null; // Failure
	}

	/**
	 * Get right permissions
	 *
	 * @param string $userRight User right to get permissions of
	 * @param boolean|void $caseSensitive Translate is case sensitive or not
	 *
	 * @return array Returns user right permissions
	 * @uses OliCore::getAccountInfos() to get infos from account table
	 */
	public function getRightPermissions($userRight, $caseSensitive = true)
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
	 * @return string Returns the user right.
	 * @version BETA
	 * @updated BETA-2.0.0
	 */
	public function getUserRight($where = null, $caseSensitive = true)
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
	 * @param string|array|void $where Where to get data from
	 * @param boolean|void $caseSensitive Translate is case sensitive or not
	 *
	 * @return array|null Returns user right permissions
	 * @uses OliCore::getUserRight() to get user right
	 * @uses OliCore::getRightPermissions() to get right permissions
	 */
	public function getUserRightPermissions($where = null, $caseSensitive = true)
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

	#endregion

	#region V. 3. B. User Permissions

	/*\
	|*|      -[ WORK IN PROGRESS ]-
	|*|  USER PERMISSIONS WILL BE ADDED
	|*|        IN A FUTURE UPDATE
	|*|    (RESCHEDULED FOR BETA 2.1)
	\*/

	#region V. 3. B. a. General

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

	#endregion

	#region V. 3. B. b. Rights Permissions

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

	#endregion

	#region V. 3. B. c. User Permissions

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

	#endregion

	#endregion

	#endregion

	#region V. 4. Auth Key Cookie

	#region V. 4. A. Create & Delete

	/** Set Auth Key cookie */
	public function setAuthKeyCookie($authKey, $expireDelay)
	{
		return $this->Oli->setCookie(Config::$config['auth_key_cookie']['name'], $authKey, $expireDelay, '/', Config::$config['auth_key_cookie']['domain'], Config::$config['auth_key_cookie']['secure'], Config::$config['auth_key_cookie']['http_only']);
	}

	/** Delete Auth Key cookie */
	public function deleteAuthKeyCookie()
	{
		return $this->Oli->deleteCookie(Config::$config['auth_key_cookie']['name'], '/', Config::$config['auth_key_cookie']['domain'], Config::$config['auth_key_cookie']['secure'], Config::$config['auth_key_cookie']['http_only']);
	}

	#endregion

	#region V. 4. B. Get Auth Key Infos

	/** Get Auth Key cookie name */
	public function getAuthKeyCookieName()
	{
		return Config::$config['auth_key_cookie']['name'];
	}

	/** Auth Key cookie content */
	// public function getAuthKey() { return $this->cache['authKey'] ?: $this->cache['authKey'] = $this->getCookie(Config::$config['auth_key_cookie']['name']); }
	public function isExistAuthKey()
	{
		return $this->Oli->isExistCookie(Config::$config['auth_key_cookie']['name']);
	}

	public function isEmptyAuthKey()
	{
		return $this->Oli->isEmptyCookie(Config::$config['auth_key_cookie']['name']);
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

	#endregion

	#endregion

	#region V. 5. User Sessions

	#region V. 5. A. General

	#endregion

	#region V. 5. B. Auth Cookie

	#region V. 5. B. a. Management

	/**
	 * Set Auth Cookie
	 *
	 * @return boolean Returns true if succeeded, false otherwise.
	 * @version BETA-1.8.0
	 * @updated BETA-2.0.0
	 */
	public function setAuthCookie($authKey, $expireDelay = null)
	{
		return $this->Oli->setCookie(Config::$config['auth_key_cookie']['name'], $authKey, $expireDelay, '/', Config::$config['auth_key_cookie']['domain'], Config::$config['auth_key_cookie']['secure'], Config::$config['auth_key_cookie']['http_only']);
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
		return $this->Oli->deleteCookie(Config::$config['auth_key_cookie']['name'], '/', Config::$config['auth_key_cookie']['domain'], Config::$config['auth_key_cookie']['secure'], Config::$config['auth_key_cookie']['http_only']);
	}

	#endregion

	#region V. 5. B. b. Infos

	/** Get Auth Cookie name */
	public function getAuthIDCookieName()
	{
		return Config::$config['auth_key_cookie']['name'];
	}

	/** Is exist Auth Cookie */
	public function isExistAuthID()
	{
		return $this->Oli->isExistCookie(Config::$config['auth_key_cookie']['name']);
	}

	/** Is empty Auth Cookie */
	public function isEmptyAuthID()
	{
		return $this->Oli->isEmptyCookie(Config::$config['auth_key_cookie']['name']);
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
		if (empty($this->cache['authKey'])) $this->cache['authKey'] = $this->Oli->getCookie(Config::$config['auth_key_cookie']['name']);
		return $this->cache['authKey'];
	}

	#endregion

	#endregion

	#endregion

	#region V. 6. User Accounts

	#region V. 6. A. Requests

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
		if (!$this->isReady()) trigger_error('Sorry, the user management has been disabled.', E_USER_ERROR);
		else
		{
			$requestsMatches['activate_key'] = hash('sha512', $activateKey = $this->Oli->keygen(6, true, false, true));
			$requestsMatches['uid'] = $uid;
			$requestsMatches['action'] = $action;
			$requestsMatches['request_date'] = date('Y-m-d H:i:s', $requestTime = time());
			$requestsMatches['expire_date'] = date('Y-m-d H:i:s', $requestTime + Config::$config['request_expire_delay']);

			if ($this->insertAccountLine('REQUESTS', $requestsMatches)) return $activateKey;
			else return false;
		}
	}

	#endregion

	#region V. 6. B. Register

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

		if (!empty($oliSC) and $oliSC == $this->Oli->getSecurityCode()) $isRootRegister = true;
		else if ($this->isReady() and Config::$config['allow_register']) $isRootRegister = false;
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
			else if (!empty($email) and $this->isReady() and (Config::$config['allow_register'] or $isRootRegister))
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
						$uid = $this->Oli->uuidAlt();
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
								$message .= '<p>One last step! Before you can log into your account, you need to <a href="' . $this->Oli->getUrlParam(0) . 'login/activate/' . $activateKey . '">activate your account</a> by clicking on this previous link, or by copying this url into your browser: ' . $this->Oli->getUrlParam(0) . 'login/activate/' . $activateKey . '.</p>';
								$message .= '<p>Once your account is activated, this activation link will be deleted. If you choose not to use it, it will automaticaly expire in ' . ($days = floor(Config::$config['request_expire_delay'] / 3600 / 24)) . ($days > 1 ? ' days' : ' day') . ', then you won\'t be able to use it anymore and anyone will be able to register using the same email you used.</p>';
							}
							else $message .= '<p>No further action is needed: your account is already activated. You can easily log into your account from <a href="' . $this->Oli->getUrlParam(0) . 'login/">our login page</a>, using your email, and – of course – your password.</p>';
							if (!empty(Config::$config['allow_recover'])) $message .= '<p>If you ever lose your password, you can <a href="' . $this->Oli->getUrlParam(0) . 'login/recover">recover your account</a> using your email: a confirmation mail will be sent to you on your demand.</p> <hr />';

							$message .= '<p>Your user ID: <i>' . $uid . '</i> <br />';
							$message .= 'Your hashed password (what we keep stored): <i>' . $hashedPassword . '</i> <br />';
							$message .= 'Your email: <i>' . $email . '</i> <br />';
							$message .= 'Your rights level: <i>' . $userRight . '</i></p>';
							$message .= '<p>Your password is kept secret and stored hashed in our database. <b>Do not give your password to anyone</b>, including our staff.</p> <hr />';

							$message .= '<p>Go on our website – <a href="' . $this->Oli->getUrlParam(0) . '">' . $this->Oli->getUrlParam(0) . '</a> <br />';
							$message .= 'Login – <a href="' . $this->Oli->getUrlParam(0) . 'login/">' . $this->Oli->getUrlParam(0) . 'login/</a> <br />';
							if (!empty(Config::$config['allow_recover'])) $message .= 'Recover your account – <a href="' . $this->Oli->getUrlParam(0) . 'login/recover">' . $this->Oli->getUrlParam(0) . 'login/recover</a></p>';
						}
						$headers = (!empty($mailInfos) and is_assoc($mailInfos)) ? $mailInfos['headers'] : $this->Oli->getDefaultMailHeaders();
						if (is_array($headers)) $headers = implode("\r\n", $headers);

						$mailResult = mail($email, $subject, $this->Oli->getTemplate('mail', ['__URL__' => $this->Oli->getUrlParam(0), '__NAME__' => $this->Oli->getSetting('name') ?: 'Oli Mailling Service', '__SUBJECT__' => $subject, '__CONTENT__' => $message]), $headers);
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

	#endregion

	#region V. 6. C. Login

	/**
	 * Check if the login process is considered to be local
	 *
	 * @return boolean Returns true if local.
	 * @version BETA-2.0.0
	 * @updated BETA-2.0.0
	 */
	public function isLocalLogin()
	{
		return !$this->isReady() or !Config::$config['allow_login'];
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

				$authKey = $this->Oli->keygen(Config::$config['auth_key_length'] ?: 32);
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
						$handle = fopen(OLIPATH . '.oliauth', 'w');
						$result = fwrite($handle, json_encode(array_merge($rootUserInfos, [
							'auth_key' => hash('sha512', $authKey),
							'ip_address' => $this->Oli->getUserIP(),
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

	#endregion

	#region V. 6. D. Logout

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

	#endregion

	#region V. 6. E. Accounts Restrictions

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
		else if ($selector == 'custom' and !empty($filetype = $this->getAccountInfos('ACCOUNTS', 'avatar_filetype', $uid)) and file_exists(MEDIAPATH . 'avatars/' . $uid . '.' . $filetype)) return $this->Oli->getMediaUrl() . 'avatars/' . $uid . '.' . $filetype;
		else return $this->Oli->getMediaUrl() . 'default-avatar.png';
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

	#endregion

	#region V. 8. Hash Password

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

	#endregion

	#endregion
}
