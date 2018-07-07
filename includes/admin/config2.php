<?php
$_ = array_merge($_GET, $_POST);
$result = [];

if(!$_Oli->isLoggedIn()) header('Location: ' . $_Oli->getLoginUrl());
else if($_Oli->getUserRightLevel() < $_Oli->translateUserRight('ROOT')) header('Location: ' . $_Oli->getAdminUrl());

else if(!empty($_)) {
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

<?php include INCLUDESPATH . 'admin/head.php'; ?>
<title>Oli Config</title>

</head>
<body>

<?php include INCLUDESPATH . 'admin/navbar.php'; ?>

<div id="header" class="header">
	<h1>Oli Config</h1>
	<p>Update your website config.</p>
</div>

<div id="main">
	<?php if(!empty($result)) { ?><p><u><b>Script logs</b>:</u> <code><?=json_encode($result, JSON_FORCE_OBJECT)?></code></p><?php } ?>
	
	<form action="#" method="post" id="form">
		<h2>Your Website Configuration</h2>
		
		<p>Work in progress!</p>
		
		<button type="submit">Submit</button>
		
		
		<?php $config = [];
		$defaultConfig = $_Oli->getDefaultConfig();
		$globalConfig = $_Oli->getGlobalConfig();
		$localConfig = $_Oli->getLocalConfig();
		if(is_array($defaultConfig)) {
			foreach ($defaultConfig as $key => $value) {
				$config[$key]['default'] = $value;
			}
		}
		if(is_array($globalConfig)) {
			foreach($globalConfig as $key => $value) {
				$config[$key]['global'] = $value;
			}
		}
		if(is_array($localConfig)) {
			foreach($localConfig as $key => $value) {
				$config[$key]['local'] = $value;
			}
		} ?>
		
		<?php if($globalConfig === null) { ?>
			<p>\o/ Global Config does not exist</p>
		<?php } ?>
		
		<table>
			<thead>
				<tr>
					<th>Config</th>
					<th>Default</th>
					<th>Global</th>
					<th>Local</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach($config as $eachConfig => $eachValue) { ?>
					<tr>
						<td><?=$eachConfig?></td>
						<td class="<?php if(!isset($eachValue['default'])) { ?>empty<?php } else { ?>disabled<?php } ?>">
							<?=isset($eachValue['default']) ? var_dump($eachValue['default']) : null?>
						</td>
						<td class="<?php if($globalConfig === null) { ?>disabled<?php } else if(!isset($eachValue['global'])) { ?>empty<?php } else  ?>">
							<?=isset($eachValue['global']) ? var_dump($eachValue['global']) : null?>
						</td>
						<td class="<?php if($localConfig === null) { ?>disabled<?php } else if(!isset($eachValue['local'])) { ?>empty<?php } ?>">
							<?=isset($eachValue['local']) ? var_dump($eachValue['local']) : null?>
						</td>
					</tr>
				<?php } ?>
			</tbody>
			<tfoot>
				<?php /*<tr>
					<th></th>
					<th></th>
					<th></th>
					<th></th>
				</tr>*/ ?>
			</tfoot>
		</table>
		
		<button type="submit">Submit</button>
		
	</form>

	<script>
	// TODO: Disable submit by enter
	/*var step = 1;
	var nextStep = function(next) {
		var error = null;
		var last = 4;
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
				for(var elem of document.querySelectorAll('.step')) {
					elem.style.display = "none";
				}
				
				if(next == last) {
					nextStepElem.querySelector('.data-summary').innerHTML = '';
					for(var pair of (new FormData(document.querySelector('#form'))).entries()) {
						var node = document.createElement('li');
						node.appendChild(document.createTextNode(pair[0] + ' → "' + pair[1] + '"'));
						document.querySelector('.data-summary').appendChild(node);
					}
				}
				
				document.querySelector('[step="' + next + '"]').style.display = "block";
				step = next;
			} else alert('An error occurred!');
		}
	}*/
	</script>
</div>

<?php include INCLUDESPATH . 'admin/footer.php'; ?>

</body>
</html>