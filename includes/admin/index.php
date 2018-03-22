<?php
$params = array_merge($_GET, $_POST);
$result = [];

if(!$_Oli->isLoggedIn()) header('Location: ' . $_Oli->getUrlParam(0) . ($_Oli->config['login_alias'] ?: 'oli-login/'));

?>

<html>
<head>

<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="author" content="Matiboux" />

<title>Oli Admin</title>

</head>
<body>

<h1>Oli Admin —</h1>
<p>Welcome on the Oli Admin, <?=$_Oli->getLoggedUsername()?>!</p>

<ul>
	<li><a href="<?=$_Oli->getUrlParam(0) . ($_Oli->config['login_alias'] ?: 'oli-login/')?>">Update my account infos (Login page)</a></li> <br />
	<li><a href="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/config'?>">Edit your website config</a></li>
	<li><a href="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/mysql'?>">Edit your mysql config</a></li>
</ul>

</body>
</html>