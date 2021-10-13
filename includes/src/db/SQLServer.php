<?php
/*\
|*|  -----------------------
|*|  --- [  Oli MySQL  ] ---
|*|  -----------------------
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
|*|  ├ IV. Getters
|*|  │ ├ 1. DB parameters
|*|  │ └ 2. DB type (DBMS)
|*|  └ V. Formatting
\*/

namespace Oli\DB;

use PDO;

class SQLServer extends DBWrapper
{
	// region I. Variables

	/** Read-only variables */
	private static array $readOnlyVars = [
		// 'unixSocket',
	];

//	/** Database options */
//	protected ?string $unixSocket = null;

	// endregion

	// Constructor is inherited

	// region II. Magic Methods

	public function __get($name)
	{
		$parentGet = parent::__get($name);
		if ($parentGet !== null) return $parentGet;

		if (in_array($name, self::$readOnlyVars, true))
			return $this->$name;

		return null;
	}

	public function __isset($name)
	{
		$parentIsset = parent::__isset($name);
		if ($parentIsset !== null) return $parentIsset;

		if (in_array($name, self::$readOnlyVars, true))
			return isset($this->$name);

		return false;
	}

	public function __toString()
	{
		return 'SQLServer Wrapper';
	}

	// endregion

	// region III. Configuration

	public function parseArgs(?array $args): void
	{
		parent::parseArgs($args);

		if (!$this->port) $this->port = '1433'; // SQL Server default port
	}

	public function buildPDO(): PDO
	{
		$dsn = 'sqlsrv:Database=' . $this->dbname;
		if ($this->host !== null)
		{
			$dsn .= ';Server=' . $this->host;
			if ($this->port !== null)
				$dsn .= ',' . $this->port;
		}

		// Missing support for many elements of the DSN

		return new PDO($dsn, $this->username, $this->password, $this->options);
	}

	// endregion

	// region IV. Getters

	// region IV. 1. DB parameters

//	public function getUnixSocket(): ?string
//	{
//		return $this->unixSocket;
//	}

	// endregion

	// region IV. 2. DB type (DBMS)

	public function getType(): int
	{
		return DBMS::SQLServer;
	}

	public function isSQLServer(): bool
	{
		return true;
	}

	// endregion

	// endregion

	// region V. Formatting

	public function formatIdentifier($value): string
	{
		return '[' . $value . ']'; // Microsoft SQL Server
	}

	public function formatString($value): string
	{
		return '\'' . $value . '\''; // Standard SQL
	}

	// endregion
}
