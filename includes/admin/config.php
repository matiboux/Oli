<?php
$result = [];

if(!$_Oli->isLoggedIn()) header('Location: ' . $_Oli->getLoginUrl());
else if($_Oli->getUserRightLevel() < $_Oli->translateUserRight('ROOT')) header('Location: ' . $_Oli->getAdminUrl());

/** Create Config File */
// .../create-file/ $level
if($_Oli->getUrlParam(2) == 'create-file') {
	if(empty($_Oli->getUrlParam(3))) $result = array('error' => 'Error: Url param 3 $level missing.');
	else if($_Oli->getUrlParam(3) == 'global') {
		if(file_exists(OLIPATH . 'config.global.json')) $result = array('error' => 'Error: file exists already.');
		else if($_Oli->saveConfig([], 'global')) $result = array('success' => true);
		else $result = array('error' => 'Error: An error occurred.');
	} else if($_Oli->getUrlParam(3) == 'local') {
		if(file_exists(ABSPATH . 'config.json')) $result = array('error' => 'Error: file exists already.');
		else if($_Oli->saveConfig([], 'local')) $result = array('success' => true);
		else $result = array('error' => 'Error: An error occurred.');
	} else $result = array('error' => 'Error: $level invalid.');

/** Delete Config */
// .../delete-config/ $level / $config
} else if($_Oli->getUrlParam(2) == 'delete-config') {
	if(empty($_Oli->getUrlParam(3))) $result = array('error' => 'Error: Url param 3 $level missing.');
	else if(empty($_Oli->getUrlParam(4))) $result = array('error' => 'Error: Url param 4 $config missing.');
	else if($_Oli->getUrlParam(3) == 'global') {
		$globalConfig = $_Oli->getGlobalConfig();
		if($_Oli->saveConfig(array_diff_key($globalConfig, array($_Oli->getUrlParam(4) => '#killme')), 'global', true)) $result = array('success' => true);
		else $result = array('error' => 'Error: An error occurred.');
	} else if($_Oli->getUrlParam(3) == 'local') {
		$localConfig = $_Oli->getLocalConfig();
		if($_Oli->saveConfig(array_diff_key($localConfig, array($_Oli->getUrlParam(4) => '#killme')), 'local', true)) $result = array('success' => true);
		else $result = array('error' => 'Error: An error occurred.');
	} else $result = array('error' => 'Error: $level invalid.');

/** With Form Data */
} else if(!empty($_)) {
	/** Add Config */
	// .../add-config/
	// POST ['config', 'global', 'local']
	if($_Oli->getUrlParam(2) == 'add-config') {
		if(empty($_['config'])) $result = array('error' => 'Error: config missing.');
		else {
			$status = [];
			if(!empty($_['global'])) $status[] = $_Oli->saveConfig(array($_['config'] => json_decode($_['global'])), 'global');
			if(!empty($_['local'])) $status[] = $_Oli->saveConfig(array($_['config'] => json_decode($_['local'])), 'local');
			
			if(!in_array(false, $status, true)) $result = array('success' => true);
			else $result = array('error' => 'Error: An error occurred.');
		}
	
	/** Update Config */
	// .../update-config/ $level / $config
	// POST ['value']
	} else if($_Oli->getUrlParam(2) == 'update-config') {
		if(empty($_Oli->getUrlParam(3))) $result = array('error' => 'Error: Url param 3 $level missing.');
		else if(empty($_Oli->getUrlParam(4))) $result = array('error' => 'Error: Url param 4 $config missing.');
		else {
			if($_['value'] == 'null' OR json_decode($_['value']) !== null) $value = json_decode($_['value']);
			else $value = $_['value'];
			
			if($_Oli->getUrlParam(3) == 'global') {
				if($_Oli->saveConfig(array($_Oli->getUrlParam(4) => $value), 'global')) $result = array('success' => true);
				else $result = array('error' => 'Error: An error occurred.');
			} else if($_Oli->getUrlParam(3) == 'local') {
				if($_Oli->saveConfig(array($_Oli->getUrlParam(4) => $value), 'local')) $result = array('success' => true);
				else $result = array('error' => 'Error: An error occurred.');
			} else $result = array('error' => 'Error: $level invalid.');
		}
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
	
	<?php /*<form action="#" method="post" id="form">*/ ?>
	<h2>Your Website Configuration</h2>
	<p>Work in progress!</p>
	
	<?php /*<button type="submit">Submit</button>*/ ?>
	
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
	
	<?php /*if($globalConfig === null) { ?>
		<p>\o/ Global Config does not exist</p>
	<?php }*/ ?>
	
	<?php /*if($localConfig === null) { ?>
		<p>\o/ Local Config does not exist</p>
	<?php }*/ ?>
	
	<table>
		<thead>
			<tr>
				<th>Config</th>
				<th>Default</th>
				<th>
					<?php if($globalConfig === null) { ?>
						<a href="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/create-file/global/'?> " class="btn">Create Global config</a>
					<?php } else { ?>Global<?php } ?>
				</th>
				<th>
					<?php if($localConfig === null) { ?>
						<a href="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/create-file/local/'?> " class="btn">Create Local config</a>
					<?php } else { ?>Local<?php } ?>
				</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach($config as $eachConfig => $eachValue) { ?>
				<?php
				$type = [];
				foreach($eachValue as $config => $value) {
					if(!isset($value)) $type[$config] = 'null';
					else if(is_assoc($value)) $type[$config] = 'assoc';
					else if(is_array($value)) $type[$config] = 'array';
					else if(is_bool($value)) $type[$config] = 'checkbox';
					else if(is_integer($value) OR is_float($eachValue)) $type[$config] = 'number';
					else $type[$config] = 'text';
				}
				?>
				
				<tr>
					<td><?=$eachConfig?></td>
					
					<?php /** Default Config – Non editable */ ?>
					<td class="<?php if(!isset($type['default'])) { ?>empty<?php } else { ?>disabled<?php } ?>">
						<?php if(isset($type['default'])) { ?>
							<pre><?=$eachValue['default'] !== null ? var_export($eachValue['default'], true) : 'null'?></pre>
						<?php } ?>
					</td>
					
					<?php /** Global Config */ ?>
					<td class="<?php if($globalConfig === null) { ?>disabled<?php } else if(!isset($eachValue['global'])) { ?>empty<?php } ?>">
						<?php if(!isset($type['global'])) { ?>
							<i>&laquo; Inherit from Default</i>
							<?php if($globalConfig !== null) { ?>
								<form action="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/update-config/global/' . urlencode($eachConfig) . '/'?>" method="post" class="create">
									<textarea name="value"><?=isset($type['default']) ? json_encode($eachValue['default']) : 'null'?></textarea>
									<button type="submit">Create Config!</button> Format: JSON, or String
								</form>
							<?php } ?>
						<?php } else if($globalConfig !== null) { ?>
							<?php if(is_object($eachValue['global'])) $eachValue['global'] = (array) $eachValue['global']; ?>
							<pre><?=$eachValue['global'] !== null ? var_export($eachValue['global'], true) : 'null'?></pre>
							<form action="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/update-config/global/' . urlencode($eachConfig) . '/'?>" method="post" class="update">
								<textarea name="value"><?=json_encode($eachValue['global'])?></textarea>
								<button type="submit">Update Config</button> Format: JSON, or String
							</form>
							<a href="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/delete-config/global/' . urlencode($eachConfig) . '/'?>" class="btn">Delete config</a>
						<?php } ?>
					</td>
					
					<?php /** Local Config */ ?>
					<td class="<?php if($localConfig === null) { ?>disabled<?php } else if(!isset($eachValue['local'])) { ?>empty<?php } ?>">
						<?php if(!isset($type['local'])) { ?>
							<i>&laquo; Inherit from Global</i>
							<?php if($localConfig !== null) { ?>
								<form action="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/update-config/local/' . urlencode($eachConfig) . '/'?>" method="post" class="create">
									<textarea name="value"><?=isset($type['global']) ? json_encode($eachValue['global']) : isset($type['default']) ? json_encode($eachValue['default']) : 'null'?></textarea>
									<button type="submit">Create Config!</button> Format: JSON, or String
								</form>
							<?php } ?>
						<?php } else if($localConfig !== null) { ?>
							<?php if(is_object($eachValue['local'])) $eachValue['local'] = (array) $eachValue['local']; // There a thing: Right after updting a value, the value is read as an object. After refreshing, it's read as an array.. (wtf? i don't know) ?>
							<pre><?=$eachValue['local'] !== null ? var_export($eachValue['local'], true) : 'null'?></pre>
							<form action="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/update-config/local/' . urlencode($eachConfig) . '/'?>" method="post" class="update">
								<textarea name="value"><?=json_encode($eachValue['local'])?></textarea>
								<button type="submit">Update Config</button> Format: JSON, or String
							</form>
							<a href="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/delete-config/local/' . urlencode($eachConfig) . '/'?>" class="btn">Delete config</a>
						<?php } ?>
					</td>
				</tr>
			<?php } ?>
			<tr class="add">
				<form action="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/add-config/'?>" method="post">
					<td>
						(+)
						<input type="text" name="config" /> <br />
						Once all fields are completed, 
						<button type="submit">Add config</button>
					</td>
					<td>Default config cannot be modified.</td>
					<td>
						<textarea name="global" placeholder="Leave blank for Inherit"></textarea>
						Format: JSON
					</td>
					<td>
						<textarea name="local" placeholder="Leave blank for Inherit"></textarea>
						Format: JSON
					</td>
				</form>
			</tr>
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
	
	<?php /*<button type="submit">Submit</button>*/ ?>
	
	<?php /*</form>*/ ?>

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