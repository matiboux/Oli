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

namespace Oli\DB;

final class DBMS
{
	private function __construct()
	{
	} // Non-instantiable

	// None
	public const None = 0;

	// MySQL
	public const MySQL = 1;
	public const mysql = 1;

	// PostgreSQL
	public const PostgreSQL = 2;
	public const pgsql = 2;

	// Microsoft SQL Server
	public const SQLServer = 3;
	public const mssql = 3;
	public const sqlsrv = 3;
}
