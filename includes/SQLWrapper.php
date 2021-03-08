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
|*|  ├ II. Magic Methods
|*|  ├ III. Configuration
|*|  ├ IV. Formatting
|*|  └ V. SQL
\*/

namespace Oli {

class SQLWrapper {

	/** -------------- */
	/**  I. Variables  */
	/** -------------- */
	
	// Static DBMS enum
	// This lists all supported DBMS and their aliases
	public static $DBMS = [
		"None" => 0,
		"SQL" => 1,
		"PostgreSQL" => 2,
		"Postgres" => 2,
		"pgsql" => 2,
		"MS SQL Server" => 3,
		"MS SQL" => 3,
		"SQL Azure" => 3,
		"sqlsrv" => 3,
	];
	
	public static $readOnlyVars = [
		'db',
		'dbError',
	];
	
	private $selectedDMBS = 0;
	private $db = null; // MySQL PDO Object (PUBLIC READONLY)
	private $dbError = null; // MySQL PDO Error (PUBLIC READONLY)
	
	// private static $appConfig = null;
	
	// private static $defaultConfig = null;
	// private static $globalConfig = null;
	// private static $localConfig = null;
	
	// public static $rawConfig = null;
	// public static $config = null;
	
	
	/** *** *** */
	
	/** ------------------- */
	/**  II. Magic Methods  */
	/** ------------------- */
	
	public function __construct($dbms, $dbname, ...$args)
	{
		$this->setDMBS($dbms); // Set the DBMS
		
		if ($this->selectedDMBS === 0 || empty($dbname))
			throw new \Exception('Misconfiguration when contructing a SQLWrapper object');
		
		$is_array = is_array($args[0]);
		
		$params = [
			'username' => 'root',
			'password' => '',
			'options' => [],
		];
		
		if ($is_array)
			$params = array_merge($params, $args[0]);
		else
		{
			if (@$args[0] !== null) $params['username'] = $args[0];
			if (@$args[1] !== null) $params['password'] = $args[1];
		}
		
		// Check that $params is accessible here idk
		
		if ($this->selectedDMBS === self::$DBMS["MySQL"])
		{
			if (!$is_array)
			{
				// Get MySQL specific parameters
				if (@$args[2] !== null) $params['host'] = $args[2];
				if (@$args[3] !== null) $params['port'] = $args[3];
				if (@$args[4] !== null) $params['charset'] = $args[4];
			}
			
			$dsn = 'mysql:dbname=' . $dbname;
			if (@$params['host'] !== null) $dsn .= ';host=' . $params['host'];
			if (@$params['port'] !== null) $dsn .= ';port=' . $params['port'];
			if (@$params['charset'] !== null) $dsn .= ';charset=' . $params['charset'];
		}
		else if ($this->selectedDMBS === self::$DBMS["PostgreSQL"])
		{
			if (!$is_array)
			{
				// Get PostgreSQL specific parameters
				if (@$args[2] !== null) $params['host'] = $args[2];
				if (@$args[3] !== null) $params['port'] = $args[3];
			}
			
			$dsn = 'pgsql:dbname=' . $dbname;
			if (@$params['host'] !== null) $dsn .= ';host=' . $params['host'];
			if (@$params['port'] !== null) $dsn .= ';port=' . $params['port'];
		}
		
		// Check that $dsn is accessible here idk
			
		try
		{
			$this->db = new \PDO($dsn, $params['username'],
			                     $params['password'], $params['options']);
		}
		catch(\PDOException $e)
		{
			$this->dbError = $e->getMessage();
			$this->db = null;
		}
	}
	
	public function __get($varname)
	{
		if (in_array($varname, self::readOnlyVars))
			return $this->$varname;
		
		return null;
    }
	
    public function __isset($varname)
	{
        if (in_array($varname, self::readOnlyVars))
			return isset($this->$varname);
		
		return false;
    }
	
	public function __toString()
	{
		return 'SQL Wrapper for Oli';
	}
	
	/** *** *** */
	
	/** -------------------- */
	/**  III. Configuration  */
	/** -------------------- */
	
	private function setDMBS($chosenDBMS)
	{
		foreach(self::$DBMS as $key => $value)
		{
			// Check for the chosen DBMS to match
			// - a value (enum value); or
			// - a key (DBMS name or alias, not case sensitive)
			if ($chosenDBMS === $value
			    || strtolower($chosenDBMS) === strtolower($key))
			{
				$this->selectedDMBS = $value;
				return; // Found a valid DBMS value
			}
		}
		
		// Did not found a valid DBMS value
		throw new \Exception('Expected a valid DBMS value, got: \'' . $chosenDBMS . '\'');
	}
	
	/** *** *** */
	
	/** ---------------- */
	/**  IV. Formatting  */
	/** ---------------- */
	
	public function formatIdentifier($identifier)
	{
		switch ($this->selectedDMBS)
		{
			// MySQL
			case self::$DBMS['MySQL']:
				return '`' . $identifier . '`';
			
			// PostgreSQL
			case self::$DBMS['PostgreSQL']:
				return '"' . $identifier . '"';
			
			// Microsoft SQL Server
			case self::$DBMS['MS SQL']:
				return '[' . $identifier . ']';
			
			// Standard SQL
			default:
				return '"' . $identifier . '"';
		}
	}
	
	public function formatString($identifier)
	{
		// Standard SQL
		return '\'' . $identifier . '\'';
	}
	
	/** *** *** */
	
	/** --------- */
	/**  V. SQL  */
	/** --------- */

		/** -------------- */
		/**  V. 1. Status  */
		/** -------------- */
		
		public function isSetupSQL()
		{
			return $this->selectedDMBS != 0 && $this->db !== null;
		}
	
		/** ------------ */
		/**  V. 2. Read  */
		/** ------------ */
		
		public function runQuerySQL($query, $fetchStyle = true)
		{
			if (!$this->isSetupSQL())
				return null;
			
			$query = $this->db->prepare($query);
			if ($query->execute())
			{
				if (is_bool($fetchStyle))
					$fetchStyle = $fetchStyle ? \PDO::FETCH_ASSOC : null;
				
				return $query->fetchAll($fetchStyle);
			}
			else
			{
				$this->dbError = $query->errorInfo();
				return null;
			}
		}
		
		public function getDataSQL($table, ...$params)
		{
			if (!$this->isSetupSQL())
				return null;
			
			// Select rows
			$select = '*';
			if (!empty($params[0]))
			{
				if (is_array($params[0]) && preg_grep("/^\S+$/", $params[0]) == $params[0])
					$select = implode(", ", array_shift($params));
				else if (strpos($params[0], ' ') === false)
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
			if (!$this->isSetupSQL())
				return null;
			
			$orderBy = !empty($orderBy) ? 'ORDER BY ' . $this->formatIdentifier($orderBy) . ' ASC' : null;
			$limit = 'LIMIT 1';
			$dataSQL = $this->getDataSQL($table, $whatVar, $orderBy, $limit);
			if ($dataSQL === null)
				return null;
			
			$dataSQL = $dataSQL[0];
			if(empty($dataSQL))
				return null;
			
			// ???
			if(!$rawResult)
			{
				$dataSQL = array_map(function($value) {
						return (!is_array($value) AND is_array($decodedValue = json_decode($value, true))) ? $decodedValue : $value;
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
			if (!$this->isSetupSQL())
				return null;
			
			$orderBy = !empty($orderBy) ? 'ORDER BY ' . $this->formatIdentifier($orderBy) . ' DESC' : null;
			$limit = $orderBy ? 'LIMIT 1' : null;
			$dataSQL = $this->getDataSQL($table, $whatVar, $orderBy, $limit);
			if ($dataSQL === null)
				return null;
			
			$dataSQL = $orderBy ? $dataSQL[0] : array_reverse($dataSQL)[0];
			if(empty($dataSQL))
				return null;
			
			// ???
			if(!$rawResult)
			{
				$dataSQL = array_map(function($value) {
						return (!is_array($value) AND is_array($decodedValue = json_decode($value, true))) ? $decodedValue : $value;
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
			if (!$this->isSetupSQL())
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
				$where = array_map(function($key, $value) use ($caseSensitive) {
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
			if (empty($dataSQL) OR !is_array($dataSQL))
				return null;
			
			if (!$rawResult)
			{
				$dataSQL = array_map(function($value) {
						if (is_array($value) AND count($value) == 1)
							$value = array_values($value)[0];
						if (is_array($value))
							return array_map(function($value)
								{
									if (!is_array($value) AND is_array($decodedValue = json_decode($value, true)))
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
			return ($forceArray OR count($dataSQL) > 1) ? $dataSQL : array_values($dataSQL)[0];
		}
		
		public function getLinesSQL($table, $where = null, $settings = null, $caseSensitive = null, $forceArray = null, $rawResult = null)
		{
			return $this->getInfosSQL($table, null, $where, $settings, $caseSensitive, $forceArray, $rawResult);
		}
		
		public function getSummedInfosSQL($table, $whatVar = null, $where = null, $settings = null, $caseSensitive = null)
		{
			if (!$this->isSetupSQL())
				return null;
			
			$infosSQL = $this->getInfosSQL($table, $whatVar, $where, $settings, $caseSensitive, true);
			if (empty($infosSQL))
				return null;
			
			$summedInfos = 0;
			foreach ($infosSQL as $eachValue)
				if (is_numeric($eachValue))
					$summedInfos += $eachInfo;
			
			return $summedInfos;
		}
		
		public function countInfosSQL($table, $where = null, $settings = null, $caseSensitive = null)
		{
			$result = $this->getInfosSQL($table, "COUNT(1)", $where, $settings, $caseSensitive);
			if ($result === null)
				return null;
			
			return (int) $result;
		}
		
		public function isExistInfosSQL($table, $where = null, $settings = null, $caseSensitive = null)
		{
			return $this->countInfosSQL($table, $where, $settings, $caseSensitive);
		}
		
		public function isEmptyInfosSQL($table, $whatVar = null, $where = null, $settings = null, $caseSensitive = null)
		{
			return empty($this->getInfosSQL($table, $whatVar, $where, $settings, $caseSensitive));
		}
		
		/** ------------- */
		/**  V. 3. Write  */
		/** ------------- */
		
		public function insertLineSQL($table, $matches, &$errorInfo = null)
		{
			if (!$this->isSetupSQL())
				return null;
			
			foreach($matches as $row => $value)
			{
				$queryVars[] = $this->formatIdentifier($row);
				$queryValues[] = '?'; // ':' . $row;
				
				if (is_array($value))
					$value = json_encode($value);
				
				// $matches[$row] = $value;
				$matches[] = $value;
			}
			
			// Prepare query
			$query = $this->db->prepare(
				'INSERT INTO ' . $this->formatIdentifier($table) .
				' (' . implode(', ', $queryVars) . ')' .
				' VALUES(' . implode(', ', $queryValues) . ')');
			if ($query === false)
				return false; // Query failed
			
			// Execute query
			$res = $query->execute($matches);
			
			$errorInfo = $query->errorInfo();
			$query->closeCursor(); // Failsafe
			return $res;
		}
		
		public function updateInfosSQL($table, $what, $where, &$errorInfo = null)
		{
			if (!$this->isSetupSQL())
				return null;
			
			$matches = [];
			
			// What to update
			if (is_assoc($what))
			{
				$queryWhat = [];
				foreach($what as $row => $value)
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
				foreach($where as $row => $value) {
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
				' SET '  . $what . ' WHERE ' . $where);
			if ($query === false)
				return false; // Query failed
			
			// Execute query
			$res = $query->execute($matches);
			
			$errorInfo = $query->errorInfo();
			$query->closeCursor(); // Failsafe
			return $res;
		}
		
		public function deleteLinesSQL($table, $where, &$errorInfo = null)
		{
			if (!$this->isSetupSQL())
				return null;
			
			$matches = [];
			
			// Where condition
			if (in_array($where, ['all', '*'], true))
				$where = 'TRUE'; // Require an explicit selector
			else if (is_assoc($where))
			{
				$queryWhere = [];
				foreach($where as $row => $value) {
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
		
		/** ---------------------- */
		/**  V. 4. Database Edits  */
		/** ---------------------- */
		
			/** ----------------- */
			/**  V. 4. A. Tables  */
			/** ----------------- */
			
			public function createTableSQL($table, $columns, &$errorInfo = null) {
				if (!$this->isSetupSQL())
					return null;
				
				if (is_assoc($columns))
				{
					$tableFields = [];
					foreach($columns as $row => $options)
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
			
			public function isExistTableSQL($table, &$errorInfo = null) {
				if (!$this->isSetupSQL())
					return null;
				
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
			
			public function clearTableSQL($table, &$errorInfo = null) {
				if (!$this->isSetupSQL())
					return null;
				
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
			
			public function deleteTableSQL($table, &$errorInfo = null) {
				if (!$this->isSetupSQL())
					return null;
				
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
			
			/** ------------------ */
			/**  V. 4. B. Columns  */
			/** ------------------ */
			
			public function addColumnTableSQL($table, $column, $type, &$errorInfo = null) {
				if (!$this->isSetupSQL())
					return null;
				
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
			
			public function updateColumnTableSQL($table, $column, $type, &$errorInfo = null) {
				if (!$this->isSetupSQL())
					return null;
				
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
			
			public function renameColumnTableSQL($table, $oldColumn, $newColumn, $type = null, &$errorInfo = null) {
				if (!$this->isSetupSQL())
					return null;
				
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
			
			public function deleteColumnTableSQL($table, $column, &$errorInfo = null) {
				if (!$this->isSetupSQL())
					return null;
				
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

}

}
?>