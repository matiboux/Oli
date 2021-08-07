<?php
// *** Load Oli
if(!defined('ABSPATH')) define('ABSPATH', __DIR__ . '/');
require_once ABSPATH . 'load.php';

// *** Load content
if ($includePath = $_Oli->loadContent()) include $includePath;
