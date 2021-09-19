<?php
if(!$_Oli->config['setup_wizard'])
	die('Sorry. The initial config seem to have been done already.');

$success = false;
$confirmed = false;
$error = null;

$step = 1;
$totalSteps = 3;

if(!empty($_)) {

	// $newAppConfig = [];
	// $newConfig = [];

	// Check for valid Oli Security Code
	if(empty($_['olisc'])) $error = 'The "olisc" parameter is missing';
	else if($_['olisc'] != $_Oli->getOliSecurityCode()) {
		unset($_['olisc']);
		$error = 'The Oli security code is incorrect.';
	}
	
	// Database configuration
	else if(isset($_['use_db'])) {
		$step = 1;
		$newConfig = array('allow_mysql' => $_['use_db'] == 'yes');
		if($_['use_db'] == 'yes') $newConfig['mysql'] = array(
			'database' => $_['db_name'],
			'username' => $_['db_username'],
			'password' => $_['db_password'],
			'hostname' => $_['db_hostname'],
			'charset' => $_['db_charset']);
		
		// if(!\Oli\Config::updateConfig($_Oli, $newConfig, 'local'))
			// $error = 'An error occurred while updating local config.';
		// else
			if($_['use_db'] == 'yes' AND !$_Oli->isSetupMySQL())
			$error = 'The MySQL configuration has failed. PDO Error: ' . $_Oli->dbError;
		else if($_['import_db'] AND $_Oli->runQueryMySQL(file_get_contents(OLISETUPPATH . 'default.sql')) === false)
			$error = 'Couldn\'t import the default SQL configuration. PDO Error: ' . $_Oli->dbError;
		else
			$step++;
	}
	
	// Website information
	else if(isset($_['ws_baseurl'])) {
		$step = 2;
		
		if(empty($_['ws_baseurl'])) $error = 'The "ws_baseurl" parameter is missing';
		// else if(!preg_match('/^(?:[a-z0-9-]+\.)+[a-z.]+(?:\/[^\/]+)*\/$/i', $_['ws_baseurl'])) $error = 'The "ws_baseurl" parameter syntax is incorrect.';
		else if(empty($_['ws_name'])) $error = 'The "ws_name" parameter is missing';
		else {
			$newAppConfig = array(
				'url' => $_['ws_baseurl'],
				'name' => $_['ws_name'],
				'description' => $_['ws_description'],
				'creation_date' => $_['ws_creation_date'],
				'owner' => $_['ws_owner']);
			
			if(!\Oli\Config::updateConfig($_Oli, $newAppConfig, 'app'))
				$error = 'An error occurred while updating app config.';
			else
				$step++;
		}
	}
	
	// Confirm Changes
	else if(!empty($_['confirm']) AND $_['confirm'] == 'yes') {
		$step = 3;
		$newConfig = array('setup_wizard' => false);
		
		if(!\Oli\Config::updateConfig($_Oli, $newConfig, 'local'))
			$error = 'An error occurred while updating local config.';
		else
			$step++;
	}

}

$progress = $step / $totalSteps * 100;
?>

<html>
<head>

<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="author" content="Matiboux" />

<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous" />

<title>Oli Setup</title>

</head>
<body>

<div class="container pt-3 pb-5">
	<h1 class="mb-3">— <b>Oli</b> Setup</h1>

	<?php if($step > $totalSteps) { ?>
		<h2>Thank you! ♥</h2>
		
		<p>Huge thanks for using my framework! Please consider supporting my work and checking out my other projects.</p>
		<p>You can also become a contributor of the project! Check out <a href="https://github.com/matiboux/Oli/">Oli's Github repository</a>.</p>
		
		<p><a href="<?=$_Oli->getUrlParam(0)?>">Visit your website! ♥</a>.</p>
	<?php } else { ?>
		<div class="progress">
			<div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-info" role="progressbar"
				style="width: <?=$progress ?: 0?>%" aria-valuenow="<?=$progress ?: 0?>" aria-valuemin="0" aria-valuemax="100">
				Step <?=$step ?: 0?> / <?=$totalSteps?>
			</div>
		</div>
		
		<div id="alert" class="alert alert-danger small mt-3 px-2 py-1" role="alert"
			<?php if(empty($error)) { ?>style="display: none">
			<?php } else { ?>><b>Script error:</b> <?=$error?><?php } ?>
		</div>
		<hr />

		<!--<div class="d-flex align-items-center">
			<div class="btn-group" role="group" aria-label="Button group with nested dropdown">
				<button type="button" class="btn btn-primary btn-sm" gotoStep="1">1</button>
				<button type="button" class="btn btn-secondary btn-sm" gotoStep="2">2</button>
				<button type="button" class="btn btn-secondary btn-sm" gotoStep="3">3</button>
				<button type="button" class="btn btn-secondary btn-sm" gotoStep="4">4</button>
				<button type="button" class="btn btn-secondary btn-sm" gotoStep="5">5</button>
			</div>
			
			<div id="alert" class="alert alert-danger small ml-3 mb-0 px-2 py-1" role="alert"
				<?php if(empty($error)) { ?>style="display: none">
				<?php } else { ?>><b>Script error:</b> <?=$error?><?php } ?>
			</div>
		</div>
		<hr />-->

		<form action="#" method="post" id="form">
		
			<?php if(empty($_['olisc'])) { ?>
				<p>Welcome on the setup wizard for Oli.</p>
				<p>
					It looks like your website lacks basic config.
					Please follow the instructions below to setup your website and get started!
				</p>
				<hr />
				
				<h2>&raquo; Verify your identity</h2>
				
				<p>For obvious security reasons, please verify that you are the owner of this website.</p>
				
				<div class="form-group">
					<label for="oliscInput">Oli Security Code</label>
					<input id="oliscInput" class="form-control" name="olisc" type="password" placeholder="Oli Security Code" value="<?=$_['olisc']?>" />
					
					<?php if($_Oli->refreshOliSecurityCode()) { ?>
						<small id="oliscHelp" class="form-text text-muted">Type in the new security code generated in <code>/.olisc</code> (located in the main folder of your website).</small>
					<?php } else { ?>
						<small id="oliscHelp" class="form-text text-muted">Type in the security code previously generated in <code>/.olisc</code> (located in the main folder of your website).</small>
					<?php } ?>
				</div>
			<?php } else { ?>
				<p>The Oli Security Code was good and memorized. Keep going!</p>
				<input id="oliscInput" name="olisc" type="hidden" value="<?=$_['olisc']?>" />
			<?php } ?> <hr />
			
			<?php if($step == 1) { ?>
				<h2>&raquo; Database configuration</h2>
				
				<p>A website usually comes with a database. Do you have one?</p>
				
				<div class="form-check">
					<input class="form-check-input" type="radio" name="use_db" id="use_db_yes" value="yes" <?php if(!isset($_['use_db']) || $_['use_db'] == 'yes') { ?>checked<?php } ?> />
					<label class="form-check-label" for="use_db_yes">
						Yes, I want to use a MySQL database!
					</label>
				</div>
				<div class="form-check mb-3">
					<input class="form-check-input" type="radio" name="use_db" id="use_db_no" value="no" <?php if(isset($_['use_db']) && $_['use_db'] != 'yes') { ?>checked<?php } ?> />
					<label class="form-check-label" for="use_db_no">
						No, keep the website without one for now.
					</label>
				</div>
				
				<div id="mysql-form" class="card mb-3">
					<div class="card-body">
						<div class="form-group">
							<label for="db_name">Database Name</label>
							<input type="text" class="form-control" name="db_name" value="<?=$_['db_name'] ?: $_OliConfig['mysql']['database']?>" />
						</div>
						<div class="form-group">
							<label for="db_username">Username</label>
							<input type="text" class="form-control" name="db_username" placeholder="root" value="<?=$_POST['db_username'] ?: $_OliConfig['mysql']['username']?>" />
						</div>
						<div class="form-group">
							<label for="db_password">Password</label>
							<input type="password" class="form-control" name="db_password" value="<?=$_POST['db_password'] ?: $_OliConfig['mysql']['password']?>" />
						</div>
						<div class="form-group">
							<label for="db_hostname">Hostname</label>
							<input type="text" class="form-control" name="db_hostname" placeholder="localhost" value="<?=$_POST['db_hostname'] ?: $_OliConfig['mysql']['hostname']?>" />
						</div>
						<div class="form-group">
							<label for="db_charset">Charset</label>
							<input type="text" class="form-control" name="db_charset" placeholder="utf8" value="<?=$_POST['db_charset'] ?: $_OliConfig['mysql']['charset']?>" />
						</div>
						<hr />
						
						<p>Do you want to import the default SQL configuration for Oli?</p>
						
						<div class="form-check">
							<input class="form-check-input" type="radio" name="import_db" id="import_db_yes" value="yes" <?php if(!isset($_['import_db']) || $_['import_db'] == 'yes') { ?>checked<?php } ?> />
							<label class="form-check-label" for="import_db_yes">
								Yes, load the default SQL configuration for Oli!
							</label>
						</div>
						<div class="form-check">
							<input class="form-check-input" type="radio" name="import_db" id="import_db_no" value="no" <?php if(isset($_['import_db']) && $_['import_db'] != 'yes') { ?>checked<?php } ?> />
							<label class="form-check-label" for="import_db_no">
								No, my database is already set up.
							</label>
						</div>
					</div>
				</div>
				<hr />
				
				<div class="form-group">
					<button class="btn btn-primary" type="submit">Confirm</button>
				</div>
			
			<?php } else if($step == 2) { ?>
				<h2>&raquo; Define the base url</h2>
				<?php $ws_baseurl = $_Oli->getUrlParam(0); ?>
				
				<p>To ensure that your website works properly, please select which one of these addresses should be the base url (or home page) of your website.</p>
				<p><i>By default, Oli uses the full domain name as the website base url. The base url currently used by the framework is "<?=$_Oli->getUrlParam('base')?>".</i></p>
				
				<div class="form-group">
					<select class="form-control" name="ws_baseurl" />
						<option value="<?=implode(array_slice(explode('://', $ws_baseurl), 1))?>"><?=$ws_baseurl?></option>
						<?php foreach($_Oli->getUrlParam('params') as $eachUrlPart) {
							$ws_baseurl .= $eachUrlPart . '/'; ?>
							<option value="<?=implode(array_slice(explode('://', $ws_baseurl), 1))?>"><?=$ws_baseurl?></option>
						<?php } ?>
					</select>
				</div>
				<hr />
				
				<h2>&raquo; Your website basic infos</h2>
				
				<p>Finally, let's add some basic information that makes the identity of your website.</p>
				
				<div class="form-group">
					<label for="ws_name">Website Name</label>
					<input type="text" class="form-control" name="ws_name" placeholder="Name" value="<?=$_['ws_name'] ?: $_Oli->getSetting('name')?>" />
				</div>
				<div class="form-group">
					<label for="ws_description">Description</label>
					<input type="text" class="form-control" name="ws_description" placeholder="Description" value="<?=$_['ws_description'] ?: $_Oli->getSetting('description')?>" />
				</div>
				<div class="form-group">
					<label for="ws_creation_date">Creation date</label>
					<div class="input-group">
						<input type="date" class="form-control" name="ws_creation_date" placeholder="Creation date" value="<?=$_['ws_creation_date'] ?: $_Oli->getSetting('creation_date')?>" />
						<div class="input-group-append">
							<button class="btn btn-secondary" type="button" onclick="document.querySelector('[name=\'ws_creation_date\']').value = (new Date()).toJSON().substr(0, 10);">Today</button>
						</div>
					</div>
				</div>
				<div class="form-group">
					<label for="ws_owner">Owner</label>
					<input type="text" class="form-control" name="ws_owner" placeholder="Owner" value="<?=$_['ws_owner'] ?: $_Oli->getSetting('owner')?>" />
				</div>
				<hr />
				
				<div class="form-group">
					<button class="btn btn-primary" type="submit">Confirm</button>
				</div>
			
			<?php } else if($step == 3) { ?>
				<h2>&raquo; Confirm that everything works</h2>
				
				<p>Verify that the default home page is properly displayed in the preview below</p>
				
				<iframe src="<?=$_Oli->getUrlParam(0)?>?oli-debug=<?=$_['olisc']?>" style="width: 100%; max-height: 200px"></iframe>
				
				<!--<p>Please confirm that the information below in correct. If not, please go back and fix the errors.</p>
				
				<h3>Summary of the data you're sending</h3>
				<table class="table table-sm">
					<thead>
						<tr>
							<th scope="col">Field</th>
							<th scope="col">Value</th>
						</tr>
					</thead>
					<tbody class="data-summary"></tbody>
				</table>-->
				
				<p>Does it work? If not, something wrong happened..</p>
				
				<div class="form-group row">
					<div class="col-sm-10">
						<select class="form-control" name="confirm" />
							<option selected disabled>Choose...</option>
							<option value="yes">Yes!</option>
							<option value="no">No..</option>
						</select>
					</div>
					<button class="btn btn-primary col-sm-2" type="submit">Confirm</button>
				</div>
			
			<?php } else { ?>
				<h2>&raquo; An error occurred</h2>
				
				<p>Something went wrong. Please <a href="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1)?>">try again</a>.</p>
			<?php } ?>
			
		</form>
	<?php } ?>
	<hr />
	
	<p>Powered by <a href="<?=$_Oli->getOliInfos('url')?>"><b><?=$_Oli->getOliInfos('name')?></b></a>, Bootstrap, jQuery.</p>

</div>

<script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha256-pasqAKBDmFT4eHoN2ndd6lN370kFiGUFyTiUHWhU7k8=" crossorigin="anonymous"></script>
<script src="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1)?>/script.js"></script>

</body>
</html>