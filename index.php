<?php
/** Load Oli */
if(!defined('ABSPATH')) define('ABSPATH', dirname(__FILE__) . '/');
require_once ABSPATH . 'load.php';

/** Load Content */
if($includePath = $_Oli->loadContent()) include $includePath;
?>