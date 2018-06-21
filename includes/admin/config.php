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
		<h2>Love <3</h2>
		
		<p>Work in progress i guess.</p>
		
		<?php
		function parseConfig($config, $tree = []) {
			foreach($config as $eachVar => $eachValue) { ?>
				<?php
				if(in_array($tree[0] ?: $eachVar, ['init_timestamp', 'allow_mysql', 'settings'])) $disabled = true;
				else $disabled = false;
				if(in_array($tree[0] ?: $eachVar, ['mysql'])) $hidden = true;
				else $hidden = false;
				
				if(!isset($eachValue)) $type = 'null';
				else if(is_assoc($eachValue)) $type = 'assoc';
				else if(is_array($eachValue)) $type = 'array';
				else if(is_bool($eachValue)) $type = 'checkbox';
				else if(is_integer($eachValue) OR is_float($eachValue)) $type = 'number';
				else $type = 'text';
				
				$name = !empty($tree) ? $tree[0] . '[' . (count($tree) > 1 ? implode('][', array_slice($tree, 1)) . '][' : '') . $eachVar . ']' : $eachVar;
				?>
				
				<div class="config">
					<span><b><?=$eachVar?></b> —</span>
					<div class="single" style="display: <?php if(in_array($type, ['number', 'text', 'checkbox'])) { ?>inline-block<?php } else { ?>none<?php } ?>">
			<label><input type="<?=$type?>" name="<?=$name?>" value="<?=!$hidden ? ($_[$eachVar] ?: $eachValue) : '[hidden]'?>" <?php if($disabled OR $hidden) { ?>disabled<?php } ?> /> <?php if($type == 'checkbox') { ?>Yes/No<?php } ?></label> —
					</div>
					<div class="settings" style="display: inline-block">
						<label><input type="radio" class="type" name="type[<?=$name?>]" value="null" <?php if($type == 'null') { ?>checked<?php } ?> /> NULL</label>
						<label><input type="radio" class="type" name="type[<?=$name?>]" value="number" <?php if($type == 'number') { ?>checked<?php } ?> /> Number</label>
						<label><input type="radio" class="type" name="type[<?=$name?>]" value="text" <?php if($type == 'text') { ?>checked<?php } ?> /> Text</label>
						<label><input type="radio" class="type" name="type[<?=$name?>]" value="checkbox" <?php if($type == 'checkbox') { ?>checked<?php } ?> /> Boolean</label>
						<label><input type="radio" class="type" name="type[<?=$name?>]" value="array" <?php if($type == 'array') { ?>checked<?php } ?> /> Indexed arrays</label>
						<label><input type="radio" class="type" name="type[<?=$name?>]" value="assoc" <?php if($type == 'assoc') { ?>checked<?php } ?> /> Associative arrays</label>
					</div>
					<div class="multiple" style="display: <?php if(in_array($type, ['array', 'assoc'])) { ?>block<?php } else { ?>none<?php } ?>">
						<?php /*foreach($_[$eachVar] ?: $eachValue as $eachBVar => $eachBValue) { ?>
							<?php
							if(is_integer($eachValue) OR is_float($eachValue)) $type = 'number';
							else $type = 'text';
							?>
							
							<div style="display: inline-block">
								<label><input type="" name="input1" value="<?=$eachBVar?>" <?php if($disabled) { ?>disabled<?php } ?> /></label> =>
								<label><input type="<?=$type?>" name="input1" value="<?=$eachBValue?>" <?php if($disabled) { ?>disabled<?php } ?> /></label>
							</div>
							<div class="settings" style="display: inline-block">
								<label><input type="radio" class="type" name="type[<?=$eachVar?>][<?=$eachBVar?>]" <?php if($type == 'number') { ?>checked<?php } ?> /> Number</label>
								<label><input type="radio" class="type" name="type[<?=$eachVar?>][<?=$eachBVar?>]" <?php if($type == 'text') { ?>checked<?php } ?> /> Text</label>
							</div> <br />
						<?php }*/ if(in_array($type, ['array', 'assoc'])) parseConfig($_[$eachVar] ?: $eachValue, array_merge($tree, [$eachVar])); ?>
					</div>
				</div>
			<?php }
		} parseConfig($_Oli->config); ?>
		
		<button type="submit">Submit</button>
		
	</form>

	<script>
	// TODO: Disable submit by enter
	var step = 1;
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
	}
	</script>
</div>

<?php include INCLUDESPATH . 'admin/footer.php'; ?>

</body>
</html>