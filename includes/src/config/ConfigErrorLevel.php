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

namespace Oli;

final class ConfigErrorLevel
{
	private function __construct()
	{} // Non-instantiable

	public const Info = 0;
	public const Warning = 1;
	public const Error = 2;
}