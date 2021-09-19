<?php
/*\
|*|  ---------------------------
|*|  --- [  Error Manager  ] ---
|*|  --- [    from Oli     ] ---
|*|  ---------------------------
|*|
|*|  disabled Error Management for PHP
|*|  Please check OliCore.php file for more info on this framework.
|*|  
|*|  
|*|  fzegkzengimzengIBZEFIJEZFEZBFK
|*|  I REALLY DON'T KNOW WHY DOES THIS FILE STILL EXISTS
\*/

namespace Oli\ErrorManager {

class ExceptionHandler {
	function __construct() {
		set_exception_handler([$this, 'exception_handler']);
	}
	function __destruct() {
		restore_exception_handler();
	}
	
	public function exception_handler($exception) {
		echo 'Exception: "' . $exception->getMessage() . '"';
	}
}

class ErrorHandler {
	function __construct() {
		set_error_handler([$this, 'error_handler']);
	}
	function __destruct() {
		restore_error_handler();
	}
	
	public function error_handler($code, $message, $filepath, $line) {
		if(!($code & error_reporting())) return false;
		
		global $_Oli;
		$filename = substr($filepath, strrpos($filepath, '/', -1) + 1, strlen($filepath) - strrpos($filepath, '/', -1) - 1);
		if($code == E_ERROR) $errorType = "PHP Fatal Error";
		else if($code == E_WARNING) $errorType = "PHP Warning";
		else if($code == E_PARSE) $errorType = "PHP Parse";
		else if($code == E_NOTICE) $errorType = "PHP Notice";
		else if($code == E_STRICT) $errorType = "PHP Strict";
		else if($code == E_USER_ERROR) $errorType = "Fatal Error";
		else if($code == E_USER_WARNING) $errorType = "Warning";
		else if($code == E_USER_NOTICE) $errorType = "Notice";
		else $errorType = "Unknown Error";
		
		if($_Oli->getContentType() == 'text/html') {
			echo '<b>' . $errorType . '</b>: ' . $message . ' in file <b>' . $filename . '</b> (' . $filepath . ') on line <b>' . $line . '</b>';
		}
		else {
			echo $errorType . ': "' . $message . '" in file "' . $filename . '" (' . $filepath . ') on line ' . $line;
		}
		
		if($code == E_ERROR OR $code == E_USER_ERROR) die();
		else echo $_Oli->getContentType() == 'text/html' ? '<br />' : PHP_EOL;
	}
}

}
?>