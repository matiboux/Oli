<?php
$_ = array_merge($_GET, $_POST);
$result = [];

if(!$_Oli->isLoggedIn()) header('Location: ' . $_Oli->getLoginUrl());
else if($_Oli->getUserRightLevel() < $_Oli->translateUserRight('ROOT')) header('Location: ' . $_Oli->getAdminUrl());

/** Create Config */
// .../create-config/ $level / $config
else if($_Oli->getUrlParam(2) == 'create-config') {
	

/** Delete Config */
// .../delete-config/ $level / $config
} else if($_Oli->getUrlParam(2) == 'delete-config') {
	

/** With Form Data */
} else if(!empty($_)) {
	/** Add Config */
	// .../add-config/
	// POST ['config', 'global', 'local']
	if($_Oli->getUrlParam(2) == 'add-config') {
		// if(empty($_['database'])) $result = array('error' => 'Error: The database is missing.');
		// else {
			// $status = $_Oli->updateConfig(array('mysql' => array('database' => $_['database'], 'username' => isset($_['username']) ? $_['username'] : null, 'password' => isset($_['password']) ? $_['password'] : null, 'hostname' => isset($_['hostname']) ? $_['hostname'] : null, 'charset' => isset($_['charset']) ? $_['charset'] : null)), true, false);
			
			// if($status) $result = array('error' => false, 'database' => $_['database']);
			// else $result = array('error' => 'Error: An error occurred.');
		// }
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
	
	<?php if($globalConfig === null) { ?>
		<p>\o/ Global Config does not exist</p>
	<?php } ?>
	
	<?php if($localConfig === null) { ?>
		<p>\o/ Local Config does not exist</p>
	<?php } ?>
	
	<style>
	.btn { display: block }
	</style>
	
	<table>
		<thead>
			<tr>
				<th>Config</th>
				<th>Default</th>
				<th>
					<?php if($globalConfig === null) { ?>
						<a href="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/create-config/global/'?> " class="btn">Create Global config</a>
					<?php } else { ?>Global<?php } ?>
				</th>
				<th>
					<?php if($localConfig === null) { ?>
						<a href="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/create-config/local/'?> " class="btn">Create Local config</a>
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
					<td class="disabled"> <?php /** Default Config – Non editable */ ?>
						<pre><?=isset($eachValue['default']) ? var_export($eachValue['default']) : null?></pre>
					</td>
					<td class="<?php if($globalConfig === null) { ?>disabled<?php } else if(!isset($eachValue['global'])) { ?>empty<?php } ?>">
						<?php if(!isset($type['global'])) { ?>
							<?php if($globalConfig !== null) { ?><a href="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/create-config/global/' . urlencode($eachConfig) . '/'?> " class="btn">Create config</a><?php } ?>
							<i>&laquo; Inherit from Default</i>
						<?php } else if($globalConfig !== null) { ?>
							<a href="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/delete-config/global/' . urlencode($eachConfig) . '/'?> " class="btn">Delete config</a>
							<div class="single" style="display: <?php if(in_array($type['global'], ['number', 'text', 'checkbox'])) { ?>inline-block<?php } else { ?>none<?php } ?>">
								<label><input type="<?=$type['global']?>" name="default[<?=$eachConfig?>]" value="<?=!$hidden ? ($_[$eachVar]['global'] ?: $eachValue['global']) : '[hidden]'?>" <?php if($disabled OR $hidden) { ?>disabled<?php } ?> <?php if($type['global'] == 'checkbox' AND !$hidden AND ($_[$eachVar]['global'] ?: $eachValue['global'])) { ?>checked<?php } ?> /> <?php if($type['global'] == 'checkbox') { ?>Yes/No<?php } ?></label> —
							</div>
							<div class="settings" style="background: #f050a0; display: inline-block">
								<label><input type="radio" class="type" name="type[default][<?=$eachConfig?>]" value="null" <?php if($type['global'] == 'null') { ?>checked<?php } ?> /> NULL</label>
								<label><input type="radio" class="type" name="type[default][<?=$eachConfig?>]" value="number" <?php if($type['global'] == 'number') { ?>checked<?php } ?> /> Number</label>
								<label><input type="radio" class="type" name="type[default][<?=$eachConfig?>]" value="text" <?php if($type['global'] == 'text') { ?>checked<?php } ?> /> Text</label>
								<label><input type="radio" class="type" name="type[default][<?=$eachConfig?>]" value="checkbox" <?php if($type['global'] == 'checkbox') { ?>checked<?php } ?> /> Boolean</label>
								<label><input type="radio" class="type" name="type[default][<?=$eachConfig?>]" value="array" <?php if($type['global'] == 'array') { ?>checked<?php } ?> /> Indexed arrays</label>
								<label><input type="radio" class="type" name="type[default][<?=$eachConfig?>]" value="assoc" <?php if($type['global'] == 'assoc') { ?>checked<?php } ?> /> Associative arrays</label>
							</div>
							<div class="multiple" style="display: <?php if(in_array($type['global'], ['array', 'assoc'])) { ?>block<?php } else { ?>none<?php } ?>">
								<pre><?=var_export($eachValue['global'])?></pre>
							</div>
						<?php } ?>
						
						<?=''//isset($eachValue['global']) ? var_export($eachValue['global']) : null?>
					</td>
					<td class="<?php if($localConfig === null) { ?>disabled<?php } else if(!isset($eachValue['local'])) { ?>empty<?php } ?>">
						<?php if(!isset($type['local'])) { ?>
							<?php if($localConfig !== null) { ?><a href="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/create-config/local/' . urlencode($eachConfig) . '/'?> " class="btn">Create config</a><?php } ?>
							<i>&laquo; Inherit from Global</i>
						<?php } else if($localConfig !== null) { ?>
							<a href="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/delete-config/local/' . urlencode($eachConfig) . '/'?> " class="btn">Delete config</a>
							<div class="single" style="display: <?php if(in_array($type['local'], ['number', 'text', 'checkbox'])) { ?>inline-block<?php } else { ?>none<?php } ?>">
								<label><input type="<?=$type['local']?>" name="default[<?=$eachConfig?>]" value="<?=!$hidden ? ($_[$eachVar]['local'] ?: $eachValue['local']) : '[hidden]'?>" <?php if($disabled OR $hidden) { ?>disabled<?php } ?> <?php if($type['local'] == 'checkbox' AND !$hidden AND ($_[$eachVar]['local'] ?: $eachValue['local'])) { ?>checked<?php } ?> /> <?php if($type['local'] == 'checkbox') { ?>Yes/No<?php } ?></label> —
							</div>
							<div class="settings" style="background: #f050a0; display: inline-block">
								<label><input type="radio" class="type" name="type[default][<?=$eachConfig?>]" value="null" <?php if($type['local'] == 'null') { ?>checked<?php } ?> /> NULL</label>
								<label><input type="radio" class="type" name="type[default][<?=$eachConfig?>]" value="number" <?php if($type['local'] == 'number') { ?>checked<?php } ?> /> Number</label>
								<label><input type="radio" class="type" name="type[default][<?=$eachConfig?>]" value="text" <?php if($type['local'] == 'text') { ?>checked<?php } ?> /> Text</label>
								<label><input type="radio" class="type" name="type[default][<?=$eachConfig?>]" value="checkbox" <?php if($type['local'] == 'checkbox') { ?>checked<?php } ?> /> Boolean</label>
								<label><input type="radio" class="type" name="type[default][<?=$eachConfig?>]" value="array" <?php if($type['local'] == 'array') { ?>checked<?php } ?> /> Indexed arrays</label>
								<label><input type="radio" class="type" name="type[default][<?=$eachConfig?>]" value="assoc" <?php if($type['local'] == 'assoc') { ?>checked<?php } ?> /> Associative arrays</label>
							</div>
							<div class="multiple" style="display: <?php if(in_array($type['local'], ['array', 'assoc'])) { ?>block<?php } else { ?>none<?php } ?>">
								<pre><?=var_export($eachValue['local'])?></pre>
							</div>
						<?php } ?>
						
						<?=''//isset($eachValue['local']) ? var_export($eachValue['local']) : null?>
					</td>
				</tr>
			<?php } ?>
			<tr style="background: rgba(160, 80, 240, .1)">
				<form action="#" method="post" id="form">
					<td>
						(+)
						<input type="text" name="config" /> <br />
						Once all fields are completed, 
						<button type="submit">Add config</button>
					</td>
					<td>Default config cannot be modified.</td>
					<td>
						<input type="text" name="global" placeholder="Leave blank for Inherit" />
					</td>
					<td>
						<input type="text" name="local" placeholder="Leave blank for Inherit" />
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