<?php
/*\
|*|  -------------------------
|*|  --- [  Oli factory  ] ---
|*|  -------------------------
|*|
|*|  This is the Oli factory class for initializing an Oli instance.
|*|
|*|  ---
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
|*|  └ II. Singleton constructor
\*/

namespace Oli;

use Oli\OliCore;

/**
 * Oli factory class
 */
class Oli
{
	#region I. Variables

	/** @var OliCore|null Oli singleton instance */
	private static ?OliCore $instance = null;

	/** @var string|null Oli singleton instance's class name */
	private static ?string $className = null;

	#endregion

	#region II. Singleton constructor

	/**
	 * Private constructor for non-instantiable class
	 */
	private function __construct()
	{
	}

	/**
	 * Get the Oli singleton instance if it exists, or initialize one
	 *
	 * @param string|null $className The class type of the instance
	 * @param float|null $initTimestamp The timestamp used for initialization
	 *
	 * @return OliCore|null Returns the Oli singleton instance
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

	#endregion
}
