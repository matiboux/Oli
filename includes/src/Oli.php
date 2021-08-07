<?php
/*\
|*|  ---------------------------
|*|  --- [  Oli Framework  ] ---
|*|  ---------------------------
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
\*/

namespace Oli;

class Oli
{
	// region I. Variables

	private static ?OliCore $instance = null;
	private static ?string $className = null;

	// endregion

	// region II. Singleton constructor

	private function __construct()
	{
	} // Non-instantiable

	/**
	 * @param string|null $className
	 * @param float|null $initTimestamp
	 *
	 * @return OliCore|null
	 */
	public static function getInstance(?string $className = null, ?float $initTimestamp = null): ?OliCore
	{
		if ($className !== null)
		{
			if (self::$instance === null)
			{
				self::$instance = new $className($initTimestamp);
				self::$className = $className;
				return self::$instance;
			}
			if (self::$className !== $className) return null;
		}

		return self::$instance;
	}

	// endregion
}
