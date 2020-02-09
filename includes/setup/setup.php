<?php
$result = [];

if(!$_Oli->config['setup_wizard']) die('Sorry. The initial config seem to have been done already.');

if(!empty($_['formdata'])) $formdata = json_decode(base64_decode($_['formdata']), true);
else $formdata = null;

$success = false;
$confirmed = false;

if(!empty($_)) {
	if(empty($_['olisc'])) $result = array('error' => 'Error: The "olisc" parameter is missing');
	else if($_['olisc'] != $_Oli->getOliSecurityCode()) $result = array('error' => 'Error: The Oli Security Code is incorrect.');
	else {
		if(!empty($_['confirm']) AND $_['confirm'] == 'yes') {
			if(\Oli\Config::updateConfig($_Oli, array('setup_wizard' => false), true)) {
				$result = array('error' => false);
				$confirmed = true;
			} else $result = array('error' => 'Error: An error occurred.');
		} else if(empty($_['baseurl'])) $result = array('error' => 'Error: The "baseurl" parameter is missing');
		// else if(!preg_match('/^(?:[a-z0-9-]+\.)+[a-z.]+(?:\/[^\/]+)*\/$/i', $_['baseurl'])) $result = array('error' => 'Error: The "baseurl" parameter syntax is incorrect.');
		else if(empty($_['name'])) $result = array('error' => 'Error: The "name" parameter is missing');
		else {
			$newConfig = array(
				'url' => $_['baseurl'],
				'name' => $_['name'],
				'description' => $_['description'],
				'creation_date' => $_['creation_date'],
				'owner' => $_['owner']);
			
			if(\Oli\Config::updateConfig($_Oli, $newConfig, 'app')) {
				$result = array('error' => false);
				$success = true;
			} else $result = array('error' => 'Error: An error occurred.');
		}
	}
}
?>

<html>
<head>

<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="author" content="Matiboux" />

<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css"
	integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous" />

<title>Oli Setup</title>

</head>
<body>

<div class="container">
	<h1>— <b>Oli</b> Setup</h1>

	<?php if($success) { ?>
		<h2>Confirm that everything works!</h2>
		<p>Verify that the default home page is properly displayed in the preview below</p>
		
		<iframe src="<?=$_Oli->getUrlParam(0)?>?oli-debug=<?=$_['olisc']?>" style="width: 100%; max-height: 200px"></iframe>
		
		<p>Does it work? If not, something wrong happened..</p>
		
		<form action="#" method="post" id="form">
			<input type="hidden" name="olisc" value="<?=$_['olisc']?>" />
			
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
		</form>
	<?php } else if($confirmed) { ?>
		<h2>Thank you! ♥</h2>
		
		<p>Huge thanks for using my framework! Please consider supporting my work and checking out my other projects.</p>
		<p>You can also become a contributor of the project! Check out <a href="https://github.com/matiboux/Oli/">Oli's Github repository</a>.</p>
		
		<p><a href="<?=$_Oli->getUrlParam(0)?>">Visit your website! ♥</a>.</p>
	<?php } else { ?>
		<p>
			It looks like your website lacks basic config.
			Please follow the instructions below to setup your website.
		</p>

		<div class="progress">
			<div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-info" role="progressbar"
				style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
		</div>
		<hr />

		<div class="d-flex align-items-center">
			<div class="btn-group" role="group" aria-label="Button group with nested dropdown">
				<button type="button" class="btn btn-secondary btn-sm" gotoStep="1">1</button>
				<button type="button" class="btn btn-secondary btn-sm" gotoStep="2">2</button>
				<button type="button" class="btn btn-secondary btn-sm" gotoStep="3">3</button>
				<button type="button" class="btn btn-secondary btn-sm" gotoStep="4">4</button>
			</div>
			
			<div id="alert" class="alert alert-danger small ml-3 mb-0 px-2 py-1" role="alert"
				<?php if(empty($result['error'])) { ?>style="display: none">
				<?php } else { ?>><b>Script error:</b> <?=$result['error']?><?php } ?>
			</div>
		</div>
		<hr />

		<form action="#" method="post" id="form">
			<div class="step-wrapper" step="1">
				<h2>Step 1/4 – Verify your identity</h2>
				<?php $_Oli->refreshOliSecurityCode(); ?>
				
				<p>Welcome on the setup wizard for Oli.</p>
				<p>
					For obvious security reasons, please verify that you are the owner of this website.
					Type in below the generated security codeare required to verify that you own this website by typing in the generated secondary code.
					You can find it in this file: <code>/.olisc</code> (located in the main folder of your website).
				</p>
				
				<div class="form-group row">
					<div class="col-sm-10">
						<input class="form-control" type="password" name="olisc" placeholder="Oli Security Code" value="<?=$_POST['olisc']?>" />
					</div>
					<button class="btn btn-primary col-sm-2" type="submit">Confirm</button>
				</div>
			</div>
				
			<div class="step-wrapper" step="2" style="display: none">
				<h2>Step 2/4 – Define the base url</h2>
				<?php $baseurl = $_Oli->getUrlParam(0); ?>
				
				<p>To ensure that your website works properly, please select which one of these addresses should be the base url (or home page) of your website.</p>
				<p><i>By default, Oli uses the full domain name as the website base url. The base url currently used by the framework is "<?=$_Oli->getUrlParam('base')?>".</i></p>
				
				<div class="form-group row">
					<div class="col-sm-10">
						<select class="form-control" name="baseurl" />
							<option value="<?=implode(array_slice(explode('://', $baseurl), 1))?>"><?=$baseurl?></option>
							<?php foreach($_Oli->getUrlParam('params') as $eachUrlPart) {
								$baseurl .= $eachUrlPart . '/'; ?>
								<option value="<?=implode(array_slice(explode('://', $baseurl), 1))?>"><?=$baseurl?></option>
							<?php } ?>
						</select>
					</div>
					<button class="btn btn-primary col-sm-2" type="submit">Confirm</button>
				</div>
			</div>
				
			<div class="step-wrapper" step="3" style="display: none">
				<h2>Step 3/4 – Your website basic infos</h2>
				
				<p>Finally, let's add some basic information that makes the identity of your website.</p>
				
				<div class="form-group row">
					<label for="name" class="col-sm-2 col-form-label">Website Name</label>
					<div class="col-sm-10">
						<input type="text" class="form-control" name="name" placeholder="Name" value="<?=$_['name'] ?: $_Oli->getSetting('name')?>" />
					</div>
				</div>
				<div class="form-group row">
					<label for="description" class="col-sm-2 col-form-label">Description</label>
					<div class="col-sm-10">
						<input type="text" class="form-control" name="description" placeholder="Description" value="<?=$_POST['description'] ?: $_Oli->getSetting('description')?>" />
					</div>
				</div>
				<div class="form-group row">
					<label for="creation_date" class="col-sm-2 col-form-label">Creation date</label>
					<div class="input-group col-sm-10">
						<input type="date" class="form-control" name="creation_date" placeholder="Creation date" value="<?=$_POST['creation_date'] ?: $_Oli->getSetting('creation_date')?>" />
						<div class="input-group-append">
							<button class="btn btn-secondary" type="button" onclick="document.querySelector('[name=\'creation_date\']').value = (new Date()).toJSON().substr(0, 10);">Today</button>
						</div>
					</div>
				</div>
				<div class="form-group row">
					<label for="owner" class="col-sm-2 col-form-label">Owner</label>
					<div class="col-sm-10">
						<input type="text" class="form-control" name="owner" placeholder="Owner" value="<?=$_POST['owner'] ?: $_Oli->getSetting('owner')?>" />
					</div>
				</div>
				<div class="form-group row">
					<div class="col-sm-10 offset-sm-2">
						<button class="btn btn-primary" type="submit">Confirm</button>
					</div>
				</div>
			</div>
				
			<div class="step-wrapper" step="4" style="display: none">
				<h2>Step 4/4 – Confirm</h2>
				<p>Please confirm that the information below in correct. If not, please go back and fix the errors.</p>
				
				<h3>Summary of the data you're sending</h3>
				<table class="table table-sm">
					<thead>
						<tr>
							<th scope="col">Field</th>
							<th scope="col">Value</th>
						</tr>
					</thead>
					<tbody class="data-summary"></tbody>
				</table>
				
				<button class="btn btn-primary" type="submit">Confirm</button>
			</div>
			
		</form>
	<?php } ?>
	<hr />
	
	<p>Powered by <a href="<?=$_Oli->getOliInfos('url')?>"><b><?=$_Oli->getOliInfos('name')?></b></a>, Bootstrap, jQuery.</p>

</div>

<script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha256-pasqAKBDmFT4eHoN2ndd6lN370kFiGUFyTiUHWhU7k8=" crossorigin="anonymous"></script>
<script src="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1)?>/script.js"></script>

</body>
</html>