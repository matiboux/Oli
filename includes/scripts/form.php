<?php
$_Oli->setPostVarsCookie(array_merge($_GET, $_POST));
header('Location: ' . ($_GET['callback'] ? urldecode($_GET['callback']) : $_SERVER['HTTP_REFERER']));
