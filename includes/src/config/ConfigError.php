<?php
/*\
|*|  -----------------------------
|*|  --- [  Oli ConfigError  ] ---
|*|  -----------------------------
|*|  
|*|  This is the static config registry for Oli.
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
|*|  └ IV. Getters
\*/

namespace Oli;

class ConfigError
{
	// region I. Variables

//	/** @var int Config error type */
//	private int $type;

	/** @var int Config error level */
	private int $level;

	/** @var string Config error message */
	private string $message;

	// endregion

	// region II. Constructors

//	/**
//	 * @param int $type
//	 * @param int $level
//	 * @param string $message
//	 */
//	public function __construct(int $type, int $level, string $message)
	/**
	 * @param int $level
	 * @param string $message
	 */
	public function __construct(int $level, string $message)
	{
//		$this->type = $type;
		$this->level = $level;
		$this->message = $message;
	}

	/**
	 * @param string $message
	 *
	 * @return ConfigError
	 */
	public static function info(string $message): ConfigError
	{
		return new ConfigError(ConfigErrorLevel::Info, $message);
	}

	/**
	 * @param string $message
	 *
	 * @return ConfigError
	 */
	public static function warning(string $message): ConfigError
	{
		return new ConfigError(ConfigErrorLevel::Warning, $message);
	}

	/**
	 * @param string $message
	 *
	 * @return ConfigError
	 */
	public static function error(string $message): ConfigError
	{
		return new ConfigError(ConfigErrorLevel::Error, $message);
	}

	// endregion

	// region III. Magic Methods

	/**
	 * Get read-only variables
	 *
	 * @return mixed Returns the requested variable value if it is allowed to read, null otherwise.
	 * @version GAMMA-1.0.0
	 * @updated GAMMA-1.0.0
	 */
	public function __get($name): mixed
	{
		return match ($name)
		{
			'type' => $this->type,
			'level' => $this->level,
			'message' => $this->message,
			default => null,
		};
	}

	// endregion

	// region IV. Getters

	/**
	 * @return int
	 */
	public function getType(): int
	{
		return $this->type;
	}

	/**
	 * @return int
	 */
	public function getLevel(): int
	{
		return $this->level;
	}

	/**
	 * @return string
	 */
	public function getMessage(): string
	{
		return $this->message;
	}

	// endregion
}