<?php
if(!defined('INCLUDES')) {
	define('INCLUDES', ASBPATH . 'includes/');
}

require_once ABSPATH . 'includes/config.php';
require_once ABSPATH . 'includes/mysql.php';
require_once ABSPATH . 'includes/class.php';
require_once ABSPATH . 'includes/functions.php';

if(getUrlParam(1) == '' && file_exists(THEME . 'index.php')) {
	include THEME . 'index.php';
}
else if(file_exists(THEME . getUrlParam(1) . '.php')) {
	include THEME . getUrlParam(1) . '.php';
}
else if(file_exists(THEME . '404.php')) {
	include THEME . '404.php';
}
else {
	echo 'ERROR 404: FICHIER NON TROUVE';
}
?>