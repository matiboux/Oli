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

<!-- CSS Frameworks -->
<!--<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/css/bootstrap.min.css" integrity="sha384-9gVQ4dYFwwWSjIDZnLEWnxCjeSWFphJiwGPXr1jddIhOegiu1FwO5qRGvFXOdJZ4" crossorigin="anonymous" />-->
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/css/bootstrap-reboot.min.css" />
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/css/bootstrap-grid.min.css" />
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.13/css/all.css" integrity="sha384-DNOHZ68U8hZfKXOrtjWvjxusGo9WQnrNx2sqG0tfsghAvtVlRW3tvkXWZh58N9jp" crossorigin="anonymous" />

<!-- JavaScript Frameworks -->
<script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
<!--<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js" integrity="sha384-cs/chFZiN24E4KMATLdqdvsezGxaGsi4hLGOzlXwp5UZB1LY//20VyM2taTB4QvJ" crossorigin="anonymous"></script>-->
<!--<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/js/bootstrap.min.js" integrity="sha384-uefMccjFJAIv6A+rW+L4AHf99KvxDjWSu1z9VI8SKNVmz4sk7buKt/6v9KI65qnm" crossorigin="anonymous"></script>-->

<link rel="stylesheet" type="text/css" href="<?=$_Oli->getAdminAssetsUrl()?>style.css" />
<title>Oli Admin</title>

</head>
<body>

<div id="navbar">
	<ul class="navbar-nav float-left">
		<li><a href="<?=$_Oli->getUrlParam(0)?>"><i class="fa fa-globe fa-fw"></i> <span class="d-none d-sm-inline"><?=explode('/', $_Oli->getUrlParam(0), 3)[2]?></span><span class="d-sm-none">Website</span></a></li>
		<li><a href="<?=$_Oli->getAdminUrl()?>"><i class="fa fa-home fa-fw"></i> Oli Admin</a></li>
	</ul>
	<ul class="navbar-nav float-right">
		<?php if($_Oli->isLoggedIn()) { ?>
			<li><a href=""><img src="<?=$_Oli->getMediaUrl()?>default-avatar.png" /> <?=$_Oli->getLoggedUsername()?></a></li>
			<li><a href=""><i class="fa fa-user-cog"></i></a></li>
		<?php } else { ?>
			<li><a href=""><i class="fa fa-sign-in-alt fa-fw"></i> Login</a></li>
		<?php } ?>
	</ul>
</div>

<div id="header" class="header">
	<h1>Oli Admin Panel</h1>
	<p>Welcome on the Oli Admin Panel.</p>
</div>

<div id="main">
	<p>Welcome on the Oli Admin, <?=$_Oli->getLoggedUsername()?>! \o/</p>
	
	<p>User Management:</p>
	<ul>
		<li><a href="<?=$_Oli->getUrlParam(0) . ($_Oli->config['login_alias'] ?: 'oli-login/')?>">Update my account infos (Login page)</a></li>
	</ul>
	
	<p>Website Management:</p>
	<ul>
		<li><a href="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/config'?>">Edit your website config</a></li>
		<li><a href="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/mysql'?>">Edit your mysql config</a></li>
	</ul>
</div>

<footer id="footer">
	<p class="float-left">Powered by <b><?=$_Oli->oliInfos['name']?></b>, <?=$_Oli->oliInfos['short_description']?></p>
	<p class="float-right">Version <?=$_Oli->oliInfos['version']?></p>
</footer>

</body>
</html>