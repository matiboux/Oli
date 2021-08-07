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

class Oli extends OliCore
{
	// region I. Variables

	private static ?Oli $instance = null;

	// endregion

	// region II. Singleton constructor

	private function __construct(?float $initTimestamp = null)
	{
		parent::__construct($initTimestamp);
	}

	public static function getInstance(?float $initTimestamp = null): ?Oli
	{
		if (self::$instance === null)
			self::$instance = new self($initTimestamp);

		return self::$instance;
	}

	// endregion
}
