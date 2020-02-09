<?php
$result = [];

if(!$_Oli->config['setup_wizard']) die('Sorry. The initial config seem to have been done already.');

if(!empty($_['formdata'])) $formdata = json_decode(base64_decode($_['formdata']), true);
else $formdata = null;

if(!empty($_)) {
	if(empty($_['olisc'])) $result = array('error' => 'Error: The "olisc" parameter is missing');
	else if($_['olisc'] != $_Oli->getOliSecurityCode()) $result = array('error' => 'Error: The Oli Security Code is incorrect.');
	else {
		if(!empty($_['confirm']) AND $_['confirm'] == 'yes') {
			if($_Oli->updateConfig(array('setup_wizard' => false), true)) $result = array('error' => false, '_POST' => $_POST);
			else $result = array('error' => 'Error: An error occurred.', '_POST' => $_POST);
		} else if(empty($_['baseurl'])) $result = array('error' => 'Error: The "baseurl" parameter is missing');
		else if(!preg_match('/^(?:[a-z0-9-]+\.)+[a-z.]+(?:\/[^\/]+)*\/$/i', $_['baseurl'])) $result = array('error' => 'Error: The "baseurl" parameter syntax is incorrect.');
		else if(empty($_['name'])) $result = array('error' => 'Error: The "name" parameter is missing');
		else {
			$newConfig = array(
				'url' => $_['baseurl'],
				'name' => $_['name'],
				'description' => $_['description'],
				'creation_date' => $_['creation_date'],
				'owner' => $_['owner']);
			
			if($_Oli->updateConfig($newConfig, 'app')) $result = array('error' => false, 'olisc' => $_POST['olisc'], '_POST' => $_POST);
			else $result = array('error' => 'Error: An error occurred.', '_POST' => $_POST);
		}
	}
} else $result = array('error' => 'Error: No parameters provided');
?>

<html>
<head>

<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="author" content="Matiboux" />

<title>Oli Admin: Setup</title>

</head>
<body>

<?php if(!empty($result)) { ?><p><u><b>Script logs</b>:</u> <code><?=json_encode($result, JSON_FORCE_OBJECT)?></code></p><?php } ?>

<?php if(!empty($result) AND $result['error'] !== false) { ?>
	<h1>Oli Initial Setup —</h1>
	<p>Looks like your website lacks basic config. Please follow the instructions to setup your website.</p>

	Step <button type="button" onclick="nextStep(1);">1</button>
	<button type="button" onclick="nextStep(2);">2</button>
	<button type="button" onclick="nextStep(3);">3</button>
	<button type="button" onclick="nextStep(4);">4</button>

	<form action="#" method="post" id="form">
		<div class="step" step="1">
			<h2>Step 1/4 — Verify your identity</h2>
			<?php if($_Oli->refreshOliSecurityCode()) { ?>
				<p><i>New security code generated in <code>/.olisc</code>.</i></p>
			<?php } else { ?>
				<p><i>Security code previously generated in <code>/.olisc</code>.</i></p>
			<?php } ?>
			
			<p>In order to verify that you are the owner of this website, please type in below the generated security code. You can find the file containing the security code in the main folder of your website.</p>
			<input type="password" name="olisc" placeholder="Oli Security Code" value="<?=$_POST['olisc']?>" />
			<button type="button" onclick="nextStep();">Verify my identity</button>
		</div>
			
		<div class="step" step="2" style="display: none">
			<h2>Step 2/4 — Define the base url</h2>
			<?php $baseurl = $_Oli->getUrlParam(0); ?>
			<p>To ensure that your website works properly, please select which one of these addresses should be the base url (or home page) of your website.</p>
			<p><i>By default, Oli uses the full domain name as the website base url. The base url currently used by the framework is "<?=$_Oli->getUrlParam('base')?>".</i></p>
			<select name="baseurl" />
				<option value="<?=implode(array_slice(explode('://', $baseurl), 1))?>"><?=$baseurl?></option>
				<?php foreach($_Oli->getUrlParam('params') as $eachUrlPart) {
					$baseurl .= $eachUrlPart . '/'; ?>
					<option value="<?=implode(array_slice(explode('://', $baseurl), 1))?>"><?=$baseurl?></option>
				<?php } ?>
			</select>
			<button type="button" onclick="nextStep();">Confirm this address</button>
		</div>
			
		<div class="step" step="3" style="display: none">
			<h2>Step 3/4 — Your website basic infos</h2>
			<p>Finally, let's add some basic information that makes the identity of your website.</p>
			Website name*: <input type="text" name="name" placeholder="Name" value="<?=$_POST['name'] ?: $_Oli->getSetting('name')?>" /> <br />
			Website description: <input type="text" name="description" placeholder="Description" value="<?=$_POST['description'] ?: $_Oli->getSetting('description')?>" /> <br />
			Website creation date: <input type="date" name="creation_date" placeholder="Creation date" value="<?=$_POST['creation_date'] ?: $_Oli->getSetting('creation_date')?>" />
			<button type="button" onclick="document.querySelector('[name=\'creation_date\']').value = (new Date()).toJSON().substr(0, 10);">Today</button> <br />
			Website owner: <input type="text" name="owner" placeholder="Owner" value="<?=$_POST['owner'] ?: $_Oli->getSetting('owner')?>" /> <br />
			<button type="button" onclick="nextStep();">Confirm those infos</button>
		</div>
			
		<div class="step" step="4" style="display: none">
			<h2>Step 4/4 — Confirm</h2>
			<p>Please confirm that the information below in correct. If not, please go back and fix the errors.</p>
			
			<h3>Summary of the data you're sending</h3>
			<ul class="data-summary"></ul>
			
			<button type="submit">Submit</button>
		</div>
		
	</form>
<?php } else if(!empty($result['olisc'])) { ?>
	<h2>Confirm that everything works!</h2>
	<p>If the default home page doesn't appear, something wrong happened..</p>
	
	<iframe src="<?=$_Oli->getUrlParam(0)?>?oli-debug=<?=$result['olisc']?>" width="300" height="100"></iframe>
	<form action="#" method="post" id="form">
		<p>Does it work?</p>
		<input type="hidden" name="olisc" value="<?=$result['olisc']?>" />
		<select name="confirm" />
			<option value="yes">Yes!</option>
			<option value="no">No..</option>
		</select>
		<button type="submit">Submit</button>
	</form>
<?php } else { ?>
	<h2>Thank you! ♥</h2>
	<p>Huge thanks for using my framework! Please consider supporting my work and checking out my other projects.</p>
	<p>You can also become a contributor of the project! Check out <a href="https://github.com/matiboux/Oli/">Oli's Github repository</a>.</p>
	
	<p><a href="<?=$_Oli->getUrlParam(0)?>">Visit your website! ♥</a>.</p>
<?php } ?>

<script src="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1)?>/script.js" />

</body>
</html>