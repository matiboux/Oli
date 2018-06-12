<?php
$_ = array_merge($_GET, $_POST);
$result = [];

if(!$_Oli->isLoggedIn()) header('Location: ' . $_Oli->getLoginUrl());
else if($_Oli->getUserRightLevel() < $_Oli->translateUserRight('ROOT')) header('Location: ' . $_Oli->getAdminUrl());

else if($_Oli->getUrlParam(2) == 'basics') {
	if($_Oli->isExistTableMySQL('settings')) $result = array('error' => 'Error: The "settings" table already exists.');
	if($_Oli->isExistTableMySQL('shortcut_links')) $result = array('error' => 'Error: The "shortcut_links" table already exists.');
	else {
		if($_Oli->runQueryMySQL(file_get_contents(INCLUDESPATH . 'admin/basics.default.sql'))) $result = array('error' => false);
		else $result = array('error' => 'Error: Could not install the basic mysql config.', 'db_error' => $_Oli->dbError);
	}
} else if($_Oli->getUrlParam(2) == 'accounts_management') {
	if($_Oli->getUrlParam(2) == 'enable') {
		// if($_Oli->updateConfig(array('allow_accounts_management' => true), true, false)) $result = array('error' => false, 'database' => $_['database']);
		// else $result = array('error' => 'Error: Could not enable the MySQL management.');
	} else if($_Oli->getUrlParam(2) == 'disable') {
		// if($_Oli->updateConfig(array('allow_accounts_management' => false), true, false)) $result = array('error' => false, 'database' => $_['database']);
		// else $result = array('error' => 'Error: Could not disable the MySQL management.');
	} else if(!$this->isAccountsManagementReady()) {
		
	}
} else if($_Oli->getUrlParam(2) == 'enable') {
	if($_Oli->updateConfig(array('allow_mysql' => true), true, false)) $result = array('error' => false, 'database' => $_['database']);
	else $result = array('error' => 'Error: Could not enable the MySQL management.');
} else if($_Oli->getUrlParam(2) == 'disable') {
	if($_Oli->updateConfig(array('allow_mysql' => false), true, false)) $result = array('error' => false, 'database' => $_['database']);
	else $result = array('error' => 'Error: Could not disable the MySQL management.');
} else if(!empty($_)) {
	if(empty($_['database'])) $result = array('error' => 'Error: The database is missing.');
	else {
		$status = $_Oli->updateConfig(array('mysql' => array('database' => $_['database'], 'username' => isset($_['username']) ? $_['username'] : null, 'password' => isset($_['password']) ? $_['password'] : null, 'hostname' => isset($_['hostname']) ? $_['hostname'] : null, 'charset' => isset($_['charset']) ? $_['charset'] : null)), true, false);
		
		if($status) $result = array('error' => false, 'database' => $_['database']);
		else $result = array('error' => 'Error: An error occurred.');
	}
}
?>

<html>
<head>

<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="author" content="Matiboux" />

<!-- CSS Frameworks -->
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/css/bootstrap-reboot.min.css" />
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/css/bootstrap-grid.min.css" />
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.13/css/all.css" integrity="sha384-DNOHZ68U8hZfKXOrtjWvjxusGo9WQnrNx2sqG0tfsghAvtVlRW3tvkXWZh58N9jp" crossorigin="anonymous" />

<!-- JavaScript Frameworks -->
<script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>

<link rel="stylesheet" type="text/css" href="<?=$_Oli->getAdminAssetsUrl()?>style.css" />
<title>Oli MySQL Config</title>

</head>
<body>

<div id="navbar">
	<ul class="navbar-nav float-left">
		<li><a href="<?=$_Oli->getUrlParam(0)?>"><i class="fa fa-globe"></i></a></li>
		<li><a href="<?=$_Oli->getAdminUrl()?>"><i class="fa fa-home"></i></a></li>
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
	<h1>Oli Config: MySQL</h1>
	<p>Update your website mysql settings.</p>
</div>

<div id="main">
	<?php if(!empty($result)) { ?><p><u><b>Script logs</b>:</u> <code><?=json_encode($result, JSON_FORCE_OBJECT)?></code></p><?php } ?>

	<?php if(!$_Oli->config['allow_mysql']) { ?>
		<p>
			<b>MySQL management is disabled</b> on your website.
			<a href="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1)?>/enable" class="btn btn-primary">Enable MySQL</a>
		</p>
	<?php } else { ?>
		<p>
			<b>MySQL management is enabled</b> on your website.
			<a href="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1)?>/disable" class="btn btn-danger">Disable MySQL</a>
		</p>
	<?php } ?>

	<?php if(!$_Oli->isSetupMySQL()) { ?>
		<p>
			Your website <b>is not connected</b> to any mysql database.
		</p>
		<?php if(!empty($_Oli->dbError) AND $_Oli->config['allow_mysql']) { ?>
			<p>
				An error occurred while connecting to the "<?=$_Oli->config['mysql']['database']?>" database: <br />
				<code><?=$_Oli->dbError?></code>
			</p>
		<?php } else if(!empty($_Oli->config['mysql'])) { ?>
			<p>
				However, your website is already configured to connect to the "<?=$_Oli->config['mysql']['database']?>" database.
			</p>
		<?php } ?>
	<?php } else { ?>
		<p>
			Your website <b>is connected</b> to the "<?=$_Oli->config['mysql']['database']?>" database.
		</p>
		
		<?php if(!$_Oli->isExistTableMySQL('settings') OR !$_Oli->isExistTableMySQL('shortcut_links')) { ?>
			<p>
				Your database does not have the basic tables.
				<a href="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1)?>/basics" class="btn btn-danger">Add the Basics</a>
			</p>
		<?php } ?>
		
		<?php if(!$_Oli->isAccountsManagementReady()) { ?>
			<p>
				Your database does not allow your website to allow accounts management.
				<a href="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1)?>/accounts_management" class="btn btn-danger">Add Accounts Management</a>
			</p>
		<?php } else { ?>
			<p>
				Your database allows accounts management.
				
				<?php if(!$_Oli->config['allow_accounts_management']) { ?>
					<a href="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1)?>/accounts_management/enable" class="btn btn-primary">Enable Accounts Management</a>
				<?php } else { ?>
					<a href="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1)?>/accounts_management/disable" class="btn btn-danger">Disable Accounts Management</a>
				<?php } ?>
			</p>
		<?php } ?>
	<?php } ?> <hr />

	<form action="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1)?>/" method="post" id="form">
		<h2>Update your website MySQL config</h2>
		
		Database Name: <input type="text" name="database" placeholder="Database Name" value="<?=$_['database'] ?: $_Oli->config['mysql']['database']?>" /> <br />
		Username: <input type="text" name="username" placeholder="Username" value="<?=$_['username'] ?: $_Oli->config['mysql']['username']?>" /> <br />
		Password: <input type="text" name="password" placeholder="Password" value="<?=$_['password'] ?: $_Oli->config['mysql']['password']?>" /> <br />
		Hostname: <input type="text" name="hostname" placeholder="Hostname" value="<?=$_['hostname'] ?: $_Oli->config['mysql']['hostname']?>" /> <br />
		Charset: <input type="text" name="charset" placeholder="Charset" value="<?=$_['charset'] ?: $_Oli->config['mysql']['charset']?>" /> <br />
		
		<button type="submit">Submit</button>
	</form>
</div>

<footer id="footer">
	<p class="float-left">Powered by <b><?=$_Oli->oliInfos['name']?></b>, <?=$_Oli->oliInfos['short_description']?></p>
	<p class="float-right">Version <?=$_Oli->oliInfos['version']?></p>
</footer>

</body>
</html>