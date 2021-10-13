<?php
/*\
|*|  -----------------------------
|*|  --- [  Oli SQL Wrapper  ] ---
|*|  -----------------------------
|*|
|*|  This is the static SQL wrapper for Oli.
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
|*|  ├ II. Constructors
|*|  ├ III. Magic Methods
|*|  ├ IV. Configuration
|*|  ├ V. Getters
|*|  │ ├ 1. DB object
|*|  │ ├ 2. DB parameters
|*|  │ └ 3. DB type (DBMS)
|*|  ├ VI. Formatting
|*|  └ VII. SQL
|*|    ├ 1. SQL Read Methods
|*|    ├ 2. SQL Write Methods
|*|    └ 3. SQL Database Edit Methods
|*|      ├ A. SQL Database Table Edit Methods
|*|      └ B. SQL Database Column Edit Methods
\*/

namespace Oli\DB;

use Exception;
use PDO;
use PDOException;

abstract class DBWrapper
{
	// region I. Variables

	/** Read-only variables */
	private static array $readOnlyVars = [
		'db',
		'dbError',
		'dbname',
		'host',
		'port',
		'username',
		'password',
		'options',
	];

	/** PDO Instance */
	protected ?PDO $db = null; // PDO Instance
	protected array|string|null $dbError = null; // PDO Error

	/** Database options */
	protected ?string $dbname = null;
	protected ?string $host = null;
	protected ?string $port = null;
	protected ?string $username = null;
	protected ?string $password = null;
	protected ?string $options = null;

	// endregion

	// region II. Constructors

	/**
	 * DBWrapper constructor
	 *
	 * @throws Exception
	 * @version GAMMA-1.0.0
	 * @updated GAMMA-1.0.0
	 */
	public function __construct(array|null $args)
	{
		// Parse arguments
		$this->parseArgs($args);

		try
		{
			// Create the PDO instance
			$this->db = $this->buildPDO();
		}
		catch (PDOException $e)
		{
			// Catch exception
			$this->dbError = $e->getMessage();
			$this->db = null;
		}
	}

	// endregion

	// region III. Magic Methods

	/** ------------------- */
	/**  II. Magic Methods  */
	/** ------------------- */

	public function __get($name)
	{
		if (in_array($name, self::$readOnlyVars, true))
			return $this->$name;

		return null;
	}

	public function __isset($name)
	{
		if (in_array($name, self::$readOnlyVars, true))
			return isset($this->$name);

		return null;
	}

	public abstract function __toString();

	// endregion

	// region IV. Configuration

	/**
	 * Parse DB arguments
	 *
	 * @param array|null $args
	 *
	 * @throws Exception
	 */
	public function parseArgs(?array $args): void
	{
		if (empty(@$args['dbname']))
			throw new Exception('dbname cannot not be empty');

		$this->dbname = $args['dbname'];
		$this->host = @$args['host'] ?: 'localhost';
		$this->port = @$args['port'];
		$this->username = @$args['username'] ?: 'root';
		$this->password = @$args['password'];

		$this->options = @$args['options'];
		// Child classes should extend this method for specific configuration
	}

	/**
	 * @return PDO
	 */
	public abstract function buildPDO(): PDO;

	// endregion

	// region V. Getters

	// region V. 1. DB object

	public function getDB(): ?PDO
	{
		return $this->db;
	}

	public function isSetupDB(): bool
	{
		return $this->db !== null;
	}

	// endregion

	// region V. 2. DB parameters

	public function getDBname(): ?string
	{
		return $this->dbname;
	}

	public function getHost(): ?string
	{
		return $this->host;
	}

	public function getPort(): ?string
	{
		return $this->port;
	}

	public function getUsername(): ?string
	{
		return $this->username;
	}

	public function getPassword(): ?string
	{
		return $this->password;
	}

	public function getOptions(): ?string
	{
		return $this->options;
	}

	// endregion

	// region V. 3. DB type (DBMS)

	public abstract function getType(): int;

	public function isMySQL(): bool
	{
		return false;
	}

	public function isPostgreSQL(): bool
	{
		return false;
	}

	public function isSQLServer(): bool
	{
		return false;
	}

	// endregion

	// endregion

	// region VI. Formatting

	public abstract function formatIdentifier($value): string;

	public abstract function formatString($value): string;

	// endregion

	// region VII. SQL

	// region VII. 1. SQL Read Methods

	public function runQuerySQL($query, $fetchStyle = true): ?array
	{
		if (!$this->isSetupDB())
			return null;

		$query = $this->db->prepare($query);
		if ($query->execute())
		{
			if (is_bool($fetchStyle))
				$fetchStyle = $fetchStyle ? PDO::FETCH_ASSOC : null;

			$res = $query->fetchAll($fetchStyle);
			return $res !== false ? $res : null;
		}
		else
		{
			$this->dbError = $query->errorInfo();
			return null;
		}
	}

	public function getDataSQL($table, ...$params): ?array
	{
		if (!$this->isSetupDB())
			return null;

		// Select rows
		$select = '*';
		if (!empty($params[0]))
		{
			if (is_array($params[0]) && preg_grep("/^\S+$/", $params[0]) == $params[0])
				$select = implode(", ", array_shift($params));
			else if (!str_contains($params[0], ' '))
				$select = array_shift($params);
		}

		// Fetch Style
		$lastparam = $params[count($params) - 1];
		if (!empty($lastparam) && is_integer($lastparam))
			$fetchStyle = implode(", ", array_pop($params));
		else
			$fetchStyle = true;

		// Custom parameters
		$queryParams = null;
		if (!empty($params))
			foreach ($params as $eachParam)
				if (!empty($eachParam)) $queryParams .= ' ' . $eachParam;

		$query = 'SELECT ' . $select . ' FROM ' . $this->formatIdentifier($table) . $queryParams;
		return $this->runQuerySQL($query, $fetchStyle);
	}

	public function getFirstInfoSQL($table, $whatVar, $orderBy = null, $rawResult = false)
	{
		if (!$this->isSetupDB())
			return null;

		$orderBy = !empty($orderBy) ? 'ORDER BY ' . $this->formatIdentifier($orderBy) . ' ASC' : null;
		$limit = 'LIMIT 1';
		$dataSQL = $this->getDataSQL($table, $whatVar, $orderBy, $limit);
		if ($dataSQL === null)
			return null;

		$dataSQL = $dataSQL[0];
		if (empty($dataSQL))
			return null;

		// ???
		if (!$rawResult)
		{
			$dataSQL = array_map(function ($value) {
				return (!is_array($value) and is_array($decodedValue = json_decode($value, true))) ? $decodedValue : $value;
			}, $dataSQL);
		}

		return $dataSQL;
	}

	public function getFirstLineSQL($table, $orderBy = null, $rawResult = false)
	{
		return $this->getFirstInfoSQL($table, null, $orderBy, $rawResult);
	}

	public function getLastInfoSQL($table, $whatVar, $orderBy = null, $rawResult = false)
	{
		if (!$this->isSetupDB())
			return null;

		$orderBy = !empty($orderBy) ? 'ORDER BY ' . $this->formatIdentifier($orderBy) . ' DESC' : null;
		$limit = $orderBy ? 'LIMIT 1' : null;
		$dataSQL = $this->getDataSQL($table, $whatVar, $orderBy, $limit);
		if ($dataSQL === null)
			return null;

		$dataSQL = $orderBy ? $dataSQL[0] : array_reverse($dataSQL)[0];
		if (empty($dataSQL))
			return null;

		// ???
		if (!$rawResult)
		{
			$dataSQL = array_map(function ($value) {
				return (!is_array($value) and is_array($decodedValue = json_decode($value, true))) ? $decodedValue : $value;
			}, $dataSQL);
		}

		return $whatVar !== null ? $dataSQL[$whatVar] : $dataSQL;
	}

	public function getLastLineSQL($table, $orderBy = null, $rawResult = false)
	{
		return $this->getLastInfoSQL($table, null, $orderBy, $rawResult);
	}

	public function getInfosSQL($table, $whatVar = null, $where = null, $settings = null, $caseSensitive = null, $forceArray = null, $rawResult = null)
	{
		if (!$this->isSetupDB())
			return null;

		// Parameters management
		if (is_bool($settings))
		{
			$rawResult = $forceArray;
			$forceArray = $caseSensitive;
			$caseSensitive = $settings;
			$settings = null;
		}
		if (!isset($caseSensitive)) $caseSensitive = true;
		if (!isset($forceArray)) $forceArray = false;
		if (!isset($rawResult)) $rawResult = false;

		// Additional settings
		$whereGlue = ' AND ';
		if (!empty($settings))
		{
			if (is_assoc($settings))
			{
				$settings = array_filter($settings);
				if (isset($settings['order_by']))
					$settings[] = 'ORDER BY ' . array_pull($settings, 'order_by');
				if (isset($settings['limit']))
				{
					if (isset($settings['from']))
						$settings[] = 'LIMIT ' . array_pull($settings, 'limit') . ' OFFSET ' . array_pull($settings, 'from');
					else if (isset($settings['offset']))
						$settings[] = 'LIMIT ' . array_pull($settings, 'limit') . ' OFFSET ' . array_pull($settings, 'offset');
					else
						$settings[] = 'LIMIT ' . array_pull($settings, 'limit');
				}

				// $startFromId = (isset($settings['fromId']) AND $settings['fromId'] > 0) ? $settings['fromId'] : 1;

				if (isset($settings['where_and']))
				{
					if (!array_pull($settings, 'where_and'))
						$whereGlue = ' OR ';
				}
				else if (isset($settings['where_or'])
				         && !array_pull($settings, 'where_and'))
					$whereGlue = ' OR ';
			}

			if (is_array($settings))
				$settings = implode(' ', $settings);
		}
		else
			$settings = null;

		// Where condition
		if (in_array($where, [null, 'all', '*'], true))
			$where = 'TRUE';
		else if (is_assoc($where))
			$where = array_map(function ($key, $value) use ($caseSensitive) {
				if (is_array($value))
					$value = json_encode($value);

				// if(!$caseSensitive) return 'LOWER(`' . $key . '`) = \'' . strtolower(is_array($value) ? json_encode($value) : $value) . '\'';
				// else return '`' . $key . '` = \'' . (is_array($value) ? json_encode($value) : $value) . '\'';
				// if(!$caseSensitive) return 'LOWER("' . $key . '") = \'' . strtolower(is_array($value) ? json_encode($value) : $value) . '\'';
				// else return '"' . $key . '" = \'' . (is_array($value) ? json_encode($value) : $value) . '\'';

				if (!$caseSensitive)
					return 'LOWER(' . $this->formatIdentifier($key) . ') = ' . $this->formatString(strtolower($value));
				else
					return $this->formatIdentifier($key) . ' = ' . $this->formatString($value);
			}, array_keys($where), array_values($where));

		if (empty($where))
			return null;

		if (is_array($where))
			$where = implode($whereGlue, $where);

		// Data Processing
		$dataSQL = $this->getDataSQL($table, $whatVar, 'WHERE ' . $where, $settings);
		if (empty($dataSQL) or !is_array($dataSQL))
			return null;

		if (!$rawResult)
		{
			$dataSQL = array_map(function ($value) {
				if (is_array($value) and count($value) == 1)
					$value = array_values($value)[0];
				if (is_array($value))
					return array_map(function ($value) {
						if (!is_array($value) and is_array($decodedValue = json_decode($value, true)))
							return $decodedValue;
						else
							return $value;
					}, $value);
				else if (is_array($decodedValue = json_decode($value, true)))
					return $decodedValue;
				else
					return $value;
			}, $dataSQL);
		}

		// Return the data
		return ($forceArray or count($dataSQL) > 1) ? $dataSQL : array_values($dataSQL)[0];
	}

	public function getLinesSQL($table, $where = null, $settings = null, $caseSensitive = null, $forceArray = null, $rawResult = null)
	{
		return $this->getInfosSQL($table, null, $where, $settings, $caseSensitive, $forceArray, $rawResult);
	}

	public function getSummedInfosSQL($table, $whatVar = null, $where = null, $settings = null, $caseSensitive = null)
	{
		if (!$this->isSetupDB())
			return null;

		$infosSQL = $this->getInfosSQL($table, $whatVar, $where, $settings, $caseSensitive, true);
		if (empty($infosSQL))
			return null;

		$summedInfos = 0;
		foreach ($infosSQL as $eachValue)
			if (is_numeric($eachValue))
				$summedInfos += $eachValue;

		return $summedInfos;
	}

	public function countInfosSQL($table, $where = null, $settings = null, $caseSensitive = null)
	{
		$result = $this->getInfosSQL($table, "COUNT(1)", $where, $settings, $caseSensitive);
		if ($result === null)
			return null;

		return (int)$result;
	}

	public function isExistInfosSQL($table, $where = null, $settings = null, $caseSensitive = null)
	{
		return $this->countInfosSQL($table, $where, $settings, $caseSensitive);
	}

	public function isEmptyInfosSQL($table, $whatVar = null, $where = null, $settings = null, $caseSensitive = null)
	{
		return empty($this->getInfosSQL($table, $whatVar, $where, $settings, $caseSensitive));
	}

	// endregion

	// region VII. 2. SQL Write Methods

	public function insertLineSQL($table, $what, &$errorInfo = null): bool
	{
		if (!$this->isSetupDB())
			return false;

		$matches = null;

		// What to insert
		if (is_assoc($what))
		{
			$queryVars = [];
			$queryValues = [];
			$matches = [];

			foreach ($what as $row => $value)
			{
				$queryVars[] = $this->formatIdentifier($row);
				$queryValues[] = '?'; // ':' . $row;

				if (is_array($value))
					$value = json_encode($value);

				// $matches[$row] = $value;
				$matches[] = $value;
			}

			$what = '(' . implode(', ', $queryVars) . ')' .
			        ' VALUES (' . implode(', ', $queryValues) . ')';
		}

		if (empty($what))
			return false; // Nothing to update

		// Prepare query
		$query = $this->db->prepare(
			'INSERT INTO ' . $this->formatIdentifier($table) .
			' ' . $what);
		if ($query === false)
			return false; // Query failed

		// Execute query
		$res = $query->execute($matches);

		$errorInfo = $query->errorInfo();
		$query->closeCursor(); // Failsafe
		return $res;
	}

	public function updateInfosSQL($table, $what, $where, &$errorInfo = null): bool
	{
		if (!$this->isSetupDB())
			return false;

		$matches = [];

		// What to update
		if (is_assoc($what))
		{
			$queryWhat = [];
			foreach ($what as $row => $value)
			{
				$queryWhat[] = $this->formatIdentifier($row) . ' = ?'; //:what_' . $row;

				if (is_array($value))
					$value = json_encode($value);

				// $matches['what_' . $row] = $value;
				$matches[] = $value;
			}

			$what = $queryWhat;
		}
		if (is_array($what))
			$what = implode(', ', $what);

		if (empty($what))
			return true; // Nothing to update

		// Where condition
		if (in_array($where, ['all', '*'], true))
			$where = 'TRUE'; // Require an explicit selector
		else if (is_assoc($where))
		{
			$queryWhere = [];
			foreach ($where as $row => $value)
			{
				$queryWhere[] = $this->formatIdentifier($row) . ' = ?'; // :where_' . $row;

				if (is_array($value))
					$value = json_encode($value);

				// $matches['where_' . $row] = $value;
				$matches[] = $value;
			}

			$where = $queryWhere;
		}
		if (is_array($where))
			$where = implode(' AND ', $where);

		if (empty($where))
			return false; // Prevent dangerous or invalid requests

		// Prepare query
		$query = $this->db->prepare(
			'UPDATE ' . $this->formatIdentifier($table) .
			' SET ' . $what . ' WHERE ' . $where);
		if ($query === false)
			return false; // Query failed

		// Execute query
		$res = $query->execute($matches);

		$errorInfo = $query->errorInfo();
		$query->closeCursor(); // Failsafe
		return $res;
	}

	public function deleteLinesSQL($table, $where, &$errorInfo = null): bool
	{
		if (!$this->isSetupDB())
			return false;

		$matches = [];

		// Where condition
		if (in_array($where, ['all', '*'], true))
			$where = 'TRUE'; // Require an explicit selector
		else if (is_assoc($where))
		{
			$queryWhere = [];
			foreach ($where as $row => $value)
			{
				$queryWhere[] = $this->formatIdentifier($row) . ' = ?'; // :where_' . $row;

				if (is_array($value))
					$value = json_encode($value);

				// $matches['where_' . $row] = $value;
				$matches[] = $value;
			}

			$where = $queryWhere;
		}
		if (is_array($where))
			$where = implode(' AND ', $where);

		if (empty($where))
			return false; // Prevent dangerous or invalid requests

		$query = $this->db->prepare(
			'DELETE FROM ' . $this->formatIdentifier($table) .
			' WHERE ' . $where);
		if ($query === false)
			return false; // Query failed

		// Execute query
		$res = $query->execute($matches);

		$errorInfo = $query->errorInfo();
		$query->closeCursor(); // Failsafe
		return $res;
	}

	// endregion

	// region VII. 3. SQL Database Edit Methods

	// region VII. 3. A. SQL Database Table Edit Methods

	public function createTableSQL($table, $columns, &$errorInfo = null): bool
	{
		if (!$this->isSetupDB())
			return false;

		if (is_assoc($columns))
		{
			$tableFields = [];
			foreach ($columns as $row => $options)
				$tableFields[] = $row . ' ' . $options;

			$columns = implode(', ', $tableFields);
		}

		$query = $this->db->prepare(
			'CREATE TABLE ' . $this->formatIdentifier($table) . '(' . $columns . ')');
		if ($query === false)
			return false; // Query failed

		// Execute query
		$res = $query->execute();

		$errorInfo = $query->errorInfo();
		$query->closeCursor(); // Failsafe
		return $res;
	}

	public function isExistTableSQL($table, &$errorInfo = null): bool
	{
		if (!$this->isSetupDB())
			return false;

		$query = $this->db->prepare(
			'SELECT 1 FROM ' . $this->formatIdentifier($table));
		if ($query === false)
			return false; // Query failed

		// Execute query
		$res = $query->execute();

		$errorInfo = $query->errorInfo();
		$query->closeCursor(); // Failsafe
		return $res;
	}

	public function clearTableSQL($table, &$errorInfo = null): bool
	{
		if (!$this->isSetupDB())
			return false;

		$query = $this->db->prepare(
			'TRUNCATE TABLE ' . $this->formatIdentifier($table));
		if ($query === false)
			return false; // Query failed

		// Execute query
		$res = $query->execute();

		$errorInfo = $query->errorInfo();
		$query->closeCursor(); // Failsafe
		return $res;
	}

	public function deleteTableSQL($table, &$errorInfo = null): bool
	{
		if (!$this->isSetupDB())
			return false;

		$query = $this->db->prepare(
			'DROP TABLE ' . $this->formatIdentifier($table));
		if ($query === false)
			return false; // Query failed

		// Execute query
		$res = $query->execute();

		$errorInfo = $query->errorInfo();
		$query->closeCursor(); // Failsafe
		return $res;
	}

	// endregion

	// region VII. 3. B. SQL Database Column Edit Methods

	public function addColumnTableSQL($table, $column, $type, &$errorInfo = null): bool
	{
		if (!$this->isSetupDB())
			return false;

		$query = $this->db->prepare(
			'ALTER TABLE ' . $this->formatIdentifier($table) . ' ADD ' . $column . ' ' . $type);
		if ($query === false)
			return false; // Query failed

		// Execute query
		$res = $query->execute();

		$errorInfo = $query->errorInfo();
		$query->closeCursor(); // Failsafe
		return $res;
	}

	public function updateColumnTableSQL($table, $column, $type, &$errorInfo = null): bool
	{
		if (!$this->isSetupDB())
			return false;

		$query = $this->db->prepare(
			'ALTER TABLE ' . $this->formatIdentifier($table) . ' MODIFY ' . $column . ' ' . $type);
		if ($query === false)
			return false; // Query failed

		// Execute query
		$res = $query->execute();

		$errorInfo = $query->errorInfo();
		$query->closeCursor(); // Failsafe
		return $res;
	}

	public function renameColumnTableSQL($table, $oldColumn, $newColumn, $type = null, &$errorInfo = null): bool
	{
		if (!$this->isSetupDB())
			return false;

		$query = $this->db->prepare(
			'ALTER TABLE ' . $this->formatIdentifier($table) . (isset($type) ? ' CHANGE ' : ' RENAME COLUMN ') . $oldColumn . (isset($type) ? ' ' : ' TO ') . $newColumn . (isset($type) ? ' ' . $type : ''));
		if ($query === false)
			return false; // Query failed

		// Execute query
		$res = $query->execute();

		$errorInfo = $query->errorInfo();
		$query->closeCursor(); // Failsafe
		return $res;
	}

	public function deleteColumnTableSQL($table, $column, &$errorInfo = null): bool
	{
		if (!$this->isSetupDB())
			return false;

		$query = $this->db->prepare(
			'ALTER TABLE ' . $this->formatIdentifier($table) . ' DROP ' . $column . ')');
		if ($query === false)
			return false; // Query failed

		// Execute query
		$res = $query->execute();

		$errorInfo = $query->errorInfo();
		$query->closeCursor(); // Failsafe
		return $res;
	}

	// endregion

	// endregion

	// endregion
}
