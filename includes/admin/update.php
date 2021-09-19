<?php
$result = [];

if(!$_Oli->isLoggedIn()) header('Location: ' . $_Oli->getLoginUrl());
else if($_Oli->getUserRightLevel() < $_Oli->translateUserRight('ROOT')) header('Location: ' . $_Oli->getAdminUrl());
?>

<html>
<head>

<?php include INCLUDESPATH . 'admin/head.php'; ?>
<title>Oli Admin</title>

</head>
<body>

<?php include INCLUDESPATH . 'admin/navbar.php'; ?>

<div id="header" class="header">
	<h1>Oli Update</h1>
	<p>Check for framework updates.</p>
</div>

<div id="main">
	<?php $oliInfos = $_Oli->getOliInfos(); ?>
	<p>Current version: <?=$oliInfos['version']?></p>
	<?php $releases = json_decode(file_get_contents($oliInfos['repository']['api'] . '/releases', false, stream_context_create(array(
		'http' => array(
			'method' => 'GET',
			'header' => ['User-Agent: Oli']
		)))), true); ?>
	<p>Latest release: <?=$releases[0]['tag_name']?></p>
</div>

<?php include INCLUDESPATH . 'admin/footer.php'; ?>

</body>
</html>