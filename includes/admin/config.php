<?php
$_ = array_merge($_GET, $_POST);
$result = [];

if($_Oli->getUserRightLevel() < $_Oli->translateUserRight('ROOT')) header('Location: ' . $_Oli->getUrlParam(0) . ($_Oli->config['admin_alias'] ?: 'oli-admin/'));

?>

<html>
<head>

<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="author" content="Matiboux" />

<title>Oli Admin: Config</title>

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

<?php var_dump($_Oli->config); ?>

<h1>Oli Config —</h1>
<p>Update your website config.</p>

<form action="#" method="post" id="form">
	<h2>Love <3</h2>
	
	<p>Work in progress i guess.</p>
	
	<?php
	function parseConfig($config, $tree = []) {
		foreach($config as $eachVar => $eachValue) { ?>
			<?php
			if(in_array($tree[0] ?: $eachVar, ['init_timestamp', 'settings'])) $disabled = true;
			else $disabled = false;
			if(in_array($tree[0] ?: $eachVar, ['mysql'])) $hidden = true;
			else $hidden = false;
			
			if(is_array($eachValue)) $type = 'assoc';
			else if(is_array($eachValue)) $type = 'array';
			else if(is_integer($eachValue) OR is_float($eachValue)) $type = 'number';
			else $type = 'text';
			?>
			
			<div class="config">
				<span><b><?=$eachVar?></b> —</span>
				<div class="single" style="display: <?php if(in_array($type, ['number', 'text'])) { ?>inline-block<?php } else { ?>none<?php } ?>">
		<label><input type="<?=$type?>" name="<?=!empty($tree) ? $tree[0] . '[' . (count($tree) > 1 ? implode('][', array_slice($tree, 1)) . '][' : '') . $eachVar . ']' : $eachVar?>" value="<?=!$hidden ? ($_[$eachVar] ?: $eachValue) : '[hidden]'?>" <?php if($disabled OR $hidden) { ?>disabled<?php } ?> /></label>
				</div>
				<div class="settings" style="display: inline-block">
					<label><input type="radio" class="type" name="type[<?=$eachVar?>]" <?php if($type == 'number') { ?>checked<?php } ?> /> Number</label>
					<label><input type="radio" class="type" name="type[<?=$eachVar?>]" <?php if($type == 'text') { ?>checked<?php } ?> /> Text</label>
					<label><input type="radio" class="type" name="type[<?=$eachVar?>]" <?php if($type == 'array') { ?>checked<?php } ?> /> Indexed arrays</label>
					<label><input type="radio" class="type" name="type[<?=$eachVar?>]" <?php if($type == 'assoc') { ?>checked<?php } ?> /> Associative arrays</label>
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

</body>
</html>