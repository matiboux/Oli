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
		
		<?php if($localConfig === null) { ?>
			<p>\o/ Local Config does not exist</p>
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
						<td class="disabled">
							<?php /*<div class="single" style="display: <?php if(in_array($type['default'], ['number', 'text', 'checkbox'])) { ?>inline-block<?php } else { ?>none<?php } ?>">
								<label><input type="<?=$type['default']?>" name="default[<?=$eachConfig?>]" value="<?=!$hidden ? ($_[$eachVar]['default'] ?: $eachValue['default']) : '[hidden]'?>" <?php if($disabled OR $hidden) { ?>disabled<?php } ?> /> <?php if($type['default'] == 'checkbox') { ?>Yes/No<?php } ?></label> —
							</div>
							<div class="settings" style="background: #f050a0; display: inline-block">
								<label><input type="radio" class="type" name="type[default][<?=$eachConfig?>]" value="null" <?php if($type['default'] == 'null') { ?>checked<?php } ?> /> NULL</label>
								<label><input type="radio" class="type" name="type[default][<?=$eachConfig?>]" value="number" <?php if($type['default'] == 'number') { ?>checked<?php } ?> /> Number</label>
								<label><input type="radio" class="type" name="type[default][<?=$eachConfig?>]" value="text" <?php if($type['default'] == 'text') { ?>checked<?php } ?> /> Text</label>
								<label><input type="radio" class="type" name="type[default][<?=$eachConfig?>]" value="checkbox" <?php if($type['default'] == 'checkbox') { ?>checked<?php } ?> /> Boolean</label>
								<label><input type="radio" class="type" name="type[default][<?=$eachConfig?>]" value="array" <?php if($type['default'] == 'array') { ?>checked<?php } ?> /> Indexed arrays</label>
								<label><input type="radio" class="type" name="type[default][<?=$eachConfig?>]" value="assoc" <?php if($type['default'] == 'assoc') { ?>checked<?php } ?> /> Associative arrays</label>
							</div>
							<div class="multiple" style="display: <?php if(in_array($type['default'], ['array', 'assoc'])) { ?>block<?php } else { ?>none<?php } ?>">
								<?=var_export($eachValue['default'])?>
							</div>*/ ?>
					
							<pre><?=isset($eachValue['default']) ? var_export($eachValue['default']) : null?></pre>
						</td>
						<td class="<?php if($globalConfig === null) { ?>disabled<?php } else if(!isset($eachValue['global'])) { ?>empty<?php } ?>">
							<?php if(!isset($type['global'])) { ?>
								<i>&laquo; Inherit from Default</i>
							<?php } else if($globalConfig !== null) { ?>
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
								<i>&laquo; Inherit from Global</i>
							<?php } else if($localConfig !== null) { ?>
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