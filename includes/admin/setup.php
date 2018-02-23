<?php
$params = array_merge($_GET, $_POST);
$result = [];

if(!empty($params['formdata'])) $formdata = json_decode(base64_decode($params['formdata']), true);
else $formdata = null;

if(!empty($params)) {
	$result = array('_POST' => $_POST);
	// if($params['action'] == 'setLoginInfos') {
		// if(!$_Oli->config['user_management']) $result = array('error' => 'Error: User Management is disabled');
		// else if(empty($params['authKey'])) $result = array('error' => 'Error: "authKey" parameter is missing');
		// else if(empty($params['userID'])) $result = array('error' => 'Error: "userID" parameter is missing');
		// else if($_Oli->verifyAuthKey()) $result = array('error' => 'Error: Cannot overwrite a valid authKey');
		// else {
			// $_Oli->setUserIDCookie($params['userID'], null);
			// $_Oli->setAuthKeyCookie($params['authKey'], $expireDelay = $params['extendedDelay'] ? $_Oli->config['extended_session_duration'] : $_Oli->config['default_session_duration']);
			
			// $result = array('error' => false, 'authKey' => $params['authKey'], 'userID' => $params['userID'], 'expireDelay' => $expireDelay);
		// }
	// } else if($params['action'] == 'removeLoginInfos') {
		// if(!$_Oli->config['user_management']) $result = array('error' => 'Error: User Management is disabled');
		
		// if($_Oli->verifyAuthKey()) $result = array('error' => 'Error: Cannot remove valid login infos');
		// else {
			// $_Oli->deleteAuthKeyCookie();
			// $result = array('error' => false);
		// }
	// } else $wrongAction = true;
	
	// if($wrongAction) $result = array('error' => 'Error: "Action" parameter is missing');
	// else if(!empty($params['next'])) {
		$params['next'] = json_decode($params['next'], true);
		// $next = array_shift($params['next']);
		$params['next'] = !empty($params['next']) ? json_encode($params['next'], JSON_FORCE_OBJECT) : null;
		
		// header('Location: ' . (substr($next, -1) == '/' ? $next : $next . '/') . 'request.php' . '?' . http_build_query($params));
	// } else if(!empty($params['callback'])) header('Location: ' . $params['callback']);
} else $result = array('error' => 'Error: No parameters provided');

$result = json_encode(!empty($result) ? $result : array('error' => 'Unknown script result.'), JSON_FORCE_OBJECT);
// die(!empty($result) ? json_encode($result, JSON_FORCE_OBJECT) : array('error' => 'Unknown script result.'));
?>

<html>
<head>

<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="author" content="Matiboux" />

<title>Oli Admin: Setup</title>

</head>
<body>

<p><u><b>Script logs</b>:</u> <code><?=$result?></code></p>

<h1>Oli Initial Setup —</h1>
<p>Looks like your website lacks basic config. Please follow the instructions to setup your website.</p>

Step <button type="button" onclick="nextStep(1);">1</button>
<button type="button" onclick="nextStep(2);">2</button>
<button type="button" onclick="nextStep(3);">3</button>
<button type="button" onclick="nextStep(4);">4</button>

<form action="#" method="post">
	<div class="step" step="1">
		<h2>Step 1/4 — Verify your identity</h2>
		<?php if(!file_get_contents(ABSPATH . '.olisc') OR time() > filemtime(ABSPATH . '.olisc') + 3600*2 OR empty(file_get_contents(ABSPATH . '.olisc'))) {
			$handle = fopen(ABSPATH . '.olisc', 'w');
			fwrite($handle, $_Oli->keygen(6, true, false, true));
			fclose($handle); ?>
			<p><i>New security code generated in <code>/.olisc</code>.</i></p>
		<?php } else { ?>
			<p><i>Security code previously generated in <code>/.olisc</code>.</i></p>
		<?php } ?>
		
		<p>In order to verify that you are the owner of this website, please type in below the generated security code. You can find the file containing the security code in the main folder of your website.</p>
		<input type="text" name="olisc" placeholder="Oli Security Code" value="<?=$_POST['olisc']?>" />
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
		Website name*: <input type="text" name="name" placeholder="Name" value="<?=$_POST['name']?>" /> <br />
		Website description: <input type="text" name="description" placeholder="Description" value="<?=$_POST['description']?>" /> <br />
		Website creation date: <input type="date" name="creation_date" placeholder="Creation date" value="<?=$_POST['creation_date']?>" />
		<button type="button" onclick="document.querySelector('[name=\'creation_date\']').value = (new Date()).toJSON().substr(0, 10);">Today</button> <br />
		Website owner: <input type="text" name="owner" placeholder="Owner" value="<?=$_POST['owner']?>" /> <br />
		<button type="button" onclick="nextStep();">Confirm those infos</button>
	</div>
		
	<div class="step" step="4" style="display: none">
		<h2>Step 4/4 — Thank you! ♥</h2>
		<p>Huge thanks for using my framework! Please consider supporting my work and checking out my other projects.</p>
		<p>You can also become a contributor of the project! Check out <a href="https://github.com/OliFramework/Oli/">Oli's Github repository</a>.</p>
		
		<p class="summary">Summary of the data you're sending: <ul></ul></p>
		
		<button type="submit">Submit</button>
	</div>
</form>

<script>
// TODO: Disable submit by enter
var step = 1;
var nextStep = function(next) {
	var error = null;
	next = next || step+1;
	
	if(next <= 0 || next > 4) return false;
	else if(next > 1 && document.querySelector('[name="olisc"]').value == '') error = 'Step 1 error';
	else if(next > 2 && document.querySelector('[name="baseurl"]').value == '') error = 'Step 2 error';
	else if(next > 3 && document.querySelector('[name="name"]').value == '') error = 'Step 3 error';
	
	if(error) {
		alert(error);
		return false;
	} else {
		var nextStepElem = document.querySelector('[step="' + next + '"]')
		if(nextStepElem !== null) {
			for(elem of document.querySelectorAll('.step')) {
				elem.style.display = "none";
			}
			document.querySelector('[step="' + next + '"]').style.display = "block";
			step = next;
		} else alert('An error occurred!');
	}
}
</script>

</body>
</html>