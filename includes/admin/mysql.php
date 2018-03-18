<?php
$_ = array_merge($_GET, $_POST);
$result = [];

// var_dump($_Oli->isExistTableMySQL('settings'));

if($_Oli->getUserRightLevel() < $_Oli->translateUserRight('ROOT')) header('Location: ' . $_Oli->getUrlParam(0) . ($_Oli->config['admin_alias'] ?: 'oli-admin/'));

else if($_Oli->getUrlParam(2) == 'enable') {
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

<title>Oli Admin: MySQL Config</title>

<style>
@import url("https://fonts.googleapis.com/css?family=Roboto:300,400,700");
html { position: relative; min-height: 100% }
body { font-family: 'Roboto', sans-serif; background: #f8f8f8; height: 100%; margin: 0; color: #202020; font-size: 14px; overflow-x: hidden }
@media (max-width: 420px) { body { font-size: 12px } }

form .config { background: #e0e0e0; padding: 10px; border: 1px solid #d0d0d0 }
form .config + .config { margin-top: 10px }
form > .config .multiple { margin-top: 10px }
form > .config > .multiple > .config { background: #d0d0d0; border-color: #c0c0c0 }
form > .config > .multiple > .config > .multiple > .config { background: #c0c0c0; border-color: #b0b0b0 }
</style>

</head>
<body>

<?php if(!empty($result)) { ?><p><u><b>Script logs</b>:</u> <code><?=json_encode($result, JSON_FORCE_OBJECT)?></code></p><?php } ?>

<?php var_dump($_Oli->db); ?>

<h1>Oli Config: MySQL —</h1>
<p>Update your website mysql settings.</p>

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
		<?php if(!empty($_Oli->dbError)) { ?> <br />
			An error occurred while connecting to the "<?=$_Oli->config['mysql']['database']?>" database: <br />
			<?=$_Oli->dbError?>
		<?php } else if(!empty($_Oli->config['mysql'])) { ?> <br />
			However, your website is already configured to connect to the "<?=$_Oli->config['mysql']['database']?>" database.
		<?php } ?>
	</p>
<?php } else { ?>
	<p>
		Your website <b>is connected</b> to the "<?=$_Oli->config['mysql']['database']?>" database.
	</p>
	
	<?php /*if( ! settings ) { ?>
		<p>
			Your database cannot store your website settings.
			<a href="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1)?>/settings" class="btn btn-danger">Add Settings</a>
		</p>
	<?php } else { ?>
		<p>
			---
			
			<?php if(!$_Oli->config['allow_user_management']) { ?>
				<a href="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1)?>/settings/enable" class="btn btn-primary">Enable Settings</a>
			<?php } else { ?>
				<a href="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1)?>/settings/disable" class="btn btn-danger">Disable Settings</a>
			<?php } ?>
		</p>
	<?php }*/ ?>
	
	<?php if(!$_Oli->isUserManagementReady()) { ?>
		<p>
			Your database does not allow your website to allow general user management.
			<a href="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1)?>/user_management" class="btn btn-danger">Add User Management</a>
		</p>
	<?php } else { ?>
		<p>
			Your database allows general user management.
			
			<?php if(!$_Oli->config['allow_user_management']) { ?>
				<a href="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1)?>/user_management/enable" class="btn btn-primary">Enable User Management</a>
			<?php } else { ?>
				<a href="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1)?>/user_management/disable" class="btn btn-danger">Disable User Management</a>
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

</body>
</html>