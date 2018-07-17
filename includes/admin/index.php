<?php
$result = [];

if(!$_Oli->isLoggedIn()) header('Location: ' . $_Oli->getLoginUrl());
?>

<html>
<head>

<?php include INCLUDESPATH . 'admin/head.php'; ?>
<title>Oli Admin</title>

</head>
<body>

<?php include INCLUDESPATH . 'admin/navbar.php'; ?>

<div id="header" class="header">
	<h1>Oli Admin Panel</h1>
	<p>Welcome on the Oli Admin Panel.</p>
</div>

<div id="main">
	<p>Welcome on the Oli Admin, <?=$_Oli->getLoggedName()?>! \o/</p>
	
	<h2>User Management</h2>
	<ul>
		<li><a href="<?=$_Oli->getLoginUrl() . '/account-settings'?>">Update my account infos (Login page)</a></li>
	</ul>
	
	<h2>Website Management</h2>
	<ul>
		<li><a href="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/config'?>">Edit your website config</a></li>
		<li><a href="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/mysql'?>">Edit your mysql config</a></li>
	</ul>
</div>

<?php include INCLUDESPATH . 'admin/footer.php'; ?>

</body>
</html>