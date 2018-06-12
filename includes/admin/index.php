<?php
$params = array_merge($_GET, $_POST);
$result = [];

if(!$_Oli->isLoggedIn()) header('Location: ' . $_Oli->getLoginUrl());

?>

<html>
<head>

<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="author" content="Matiboux" />

<link rel="stylesheet" type="text/css" href="<?=$_Oli->getAdminAssetsUrl()?>style.css" />

<title>Oli Admin</title>

</head>
<body>

<div id="header"><h1>Oli Admin — Home</h1></div>
<div id="main">
<p>Welcome on the Oli Admin, <?=$_Oli->getLoggedUsername()?>!</p>

<ul>
	<li><a href="<?=$_Oli->getUrlParam(0) . ($_Oli->config['login_alias'] ?: 'oli-login/')?>">Update my account infos (Login page)</a></li> <br />
	<li><a href="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/config'?>">Edit your website config</a></li>
	<li><a href="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/mysql'?>">Edit your mysql config</a></li>
</ul>

</div>

</body>
</html>