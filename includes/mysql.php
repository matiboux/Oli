<?php
define('MYSQL_DATABASE_NAME', 'database');
define('MYSQL_USERNAME', 'username');
define('MYSQL_PASSWORD', 'password');
define('MYSQL_HOST', 'localhost');

try {
	$db = new PDO('mysql:host=' . MYSQL_HOST . ';dbname=' . MYSQL_DATABASE_NAME, MYSQL_USERNAME, MYSQL_PASSWORD);
}
catch (Exception $e) {
	die('Erreur MySQL : ' . $e->getMessage());
}
?>