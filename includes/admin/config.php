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

</head>
<body>

<?php var_dump($_Oli->config); ?>

<h1>Oli Config —</h1>
<p>Update your website config.</p>

<form action="#" method="post" id="form">
	<h2>Love <3</h2>
	
	<p>Work in progress i guess.</p>
	
	<?php
	foreach($_Oli->config as $eachVar => $eachValue) { ?>
		<?php
		if(in_array($eachVar, ['init_timestamp'])) $disabled = true;
		else $disabled = false;
		
		if(is_array($eachValue)) $type = 'assoc';
		else if(is_array($eachValue)) $type = 'array';
		else if(is_integer($eachValue) OR is_float($eachValue)) $type = 'number';
		else $type = 'text';
		?>
		
		<div class="config" style="background: #e0e0e0; padding: 10px; margin-bottom: 10px">
			<span><b><?=$eachVar?></b> —</span>
			<div class="single" style="display: <?php if(in_array($type, ['number', 'text'])) { ?>inline-block<?php } else { ?>none<?php } ?>">
				<label><input type="<?=$type?>" name="input1" value="<?=$_[$eachVar] ?: $eachValue?>" <?php if($disabled) { ?>disabled<?php } ?> /></label>
			</div>
			<div class="settings" style="display: inline-block">
				<label><input type="radio" class="type" name="type[<?=$eachVar?>]" <?php if($type == 'number') { ?>checked<?php } ?> /> Number</label>
				<label><input type="radio" class="type" name="type[<?=$eachVar?>]" <?php if($type == 'text') { ?>checked<?php } ?> /> Text</label>
				<label><input type="radio" class="type" name="type[<?=$eachVar?>]" <?php if($type == 'array') { ?>checked<?php } ?> /> Indexed arrays</label>
				<label><input type="radio" class="type" name="type[<?=$eachVar?>]" <?php if($type == 'assoc') { ?>checked<?php } ?> /> Associative arrays</label>
			</div>
			<div class="multiple" style="display: <?php if(in_array($type, ['array', 'assoc'])) { ?>block<?php } else { ?>none<?php } ?>">
				<?php foreach($_[$eachVar] ?: $eachValue as $eachBVar => $eachBValue) { ?>
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
				<?php } ?>
			</div>
		</div>
	<?php } ?>
	
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