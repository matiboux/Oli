<?php
/*\
|*|  --------------------
|*|  --- [  Script  ] ---
|*|  --------------------
|*|
|*|  This is the Script class for use in script to hold output fields.
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
|*|  ├ II. Constructor
|*|  ├ III. Output fields
|*|  │ ├ 1. General fields
|*|  │ └ 2. Error fields
|*|  └ IV. Output
|*|    ├ 1. Prepare output
|*|    ├ 2. Return output
|*|    └ 3. Print output
\*/

namespace Oli;

/**
 * Script class for use in script to hold output fields
 */
class Script
{
	#region I. Variables

	/** @var array Output fields */
	private array $output = [];

	const ERROR_KEY = 'error';
	const ERROR_CODE_KEY = 'error_code';
	const ERROR_MESSAGE_KEY = 'error_message';

	#endregion

	#region II. Constructor

	public function __construct(bool $defaultFields = true)
	{
		if ($defaultFields)
		{
			$this->output[self::ERROR_KEY] = false;
		}
	}

	#endregion

	#region III. Output fields

	#region III. 1. General fields

	/**
	 * Get a field's value
	 *
	 * @param string $key The field's key
	 * @return mixed The field's value
	 */
	public function get(string $key): mixed
	{
		return @$this->output[$key];
	}

	/**
	 * Set a field's value
	 *
	 * @param string $key The field's key
	 * @param mixed $value The field's value
	 * @return self
	 */
	public function set(string $key, mixed $value): self
	{
		$this->output[$key] = $value;
		return $this;
	}

	#endregion

	#region III. 2. Error fields

	/**
	 * Set the error code's value
	 *
	 * @param string|int $code The error code's value
	 * @return self
	 */
	public function setErrorCode(string|int $code): self
	{
		$this->output[self::ERROR_KEY] = true;
		$this->output[self::ERROR_CODE_KEY] = $code;
		return $this;
	}

	/**
	 * Set the error message's value
	 *
	 * @param string $message The error message's value
	 * @return self
	 */
	public function setErrorMessage(string $message): self
	{
		$this->output[self::ERROR_KEY] = true;
		$this->output[self::ERROR_MESSAGE_KEY] = $message;
		return $this;
	}

	/**
	 * Set the error's values
	 *
	 * @param string|int $code The error code's value
	 * @param string $message The error message's value
	 * @return self
	 */
	public function setError(string|int $code, string $message): self
	{
		$this->output[self::ERROR_KEY] = true;
		$this->output[self::ERROR_CODE_KEY] = $code;
		$this->output[self::ERROR_MESSAGE_KEY] = $message;
		return $this;
	}

	#endregion

	#endregion

	#region IV. Output

	#region IV. 1. Prepare output

	/**
	 * Reorder output with the specified keys first
	 *
	 * @param array $keys Keys for ordering
	 * @param bool $afterErrorField If true, place the error fields first for ordering
	 * @return self
	 */
	public function reorder(array $keys, bool $afterErrorField = false): self
	{
		$output = [];
		if ($afterErrorField)
			array_unshift($keys, self::ERROR_KEY, self::ERROR_CODE_KEY, self::ERROR_MESSAGE_KEY);
		foreach ($keys as $key)
			if (array_key_exists($key, $this->output))
			{
				$output[$key] = $this->output[$key];
				unset($this->output[$key]);
			}
		foreach ($this->output as $key => $value)
			$output[$key] = $value;
		$this->output = $output;
		return $this;
	}

	#endregion

	#region IV. 2. Return output

	/**
	 * Get the script output as an array
	 *
	 * @return array
	 */
	public function getOutput(): array
	{
		return $this->output;
	}

	/**
	 * @return array
	 */
	public function __serialize(): array
	{
		return $this->output;
	}

	/**
	 * Return the script output in a serialized format
	 *
	 * @param array|null $orderKeys Keys for ordering
	 * @param bool $afterErrorField If true, place the error fields first for ordering
	 * @see serialize
	 * @return string
	 */
	public function serialize(?array $orderKeys = null, bool $afterErrorField = false): string
	{
		if ($orderKeys !== null)
			$this->reorder($orderKeys, $afterErrorField);

		return serialize($this->output);
	}

	/**
	 * Return the script output in a JSON format
	 *
	 * @param array|null $orderKeys Keys for ordering
	 * @param bool $afterErrorField If true, place the error fields first for ordering
	 * @return string
	 */
	public function toJSON(?array $orderKeys = null, bool $afterErrorField = false): string
	{
		if ($orderKeys !== null)
			$this->reorder($orderKeys, $afterErrorField);

		return json_encode($this->output);
	}

	#endregion

	#region IV. 3. Print output

	/**
	 * Print the script output in a serialized format
	 *
	 * @param array|null $orderKeys Keys for ordering
	 * @param bool $afterErrorField If true, place the error fields first for ordering
	 * @return static
	 */
	public function printSerialize(?array $orderKeys = null, bool $afterErrorField = false): static
	{
		echo $this->serialize($orderKeys, $afterErrorField);
		return $this;
	}

	/**
	 * Print the script output in a JSON format
	 *
	 * @param array|null $orderKeys Keys for ordering
	 * @param bool $afterErrorField If true, place the error fields first for ordering
	 * @return static
	 */
	public function printJSON(?array $orderKeys = null, bool $afterErrorField = false): static
	{
		echo $this->toJSON($orderKeys, $afterErrorField);
		return $this;
	}

	/**
	 * Exit the program
	 *
	 * @return void
	 */
	public function exit(): void
	{
		exit;
	}

	#endregion

	#endregion
}
