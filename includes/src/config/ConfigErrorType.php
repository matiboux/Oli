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

final class ConfigErrorType
{
	private function __construct()
	{} // Non-instantiable

	// Default error type
	public const Default = 0;

	// Parsing error
	public const Parsing = 1;
}