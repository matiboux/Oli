<?php
$_ = array_merge($_GET, $_POST);
$result = [];

?>

<html>
<head>

<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="author" content="Matiboux" />

<title>Oli Admin: Config</title>

</head>
<body>

<h1>Oli Config —</h1>
<p>Update your website config.</p>

<form action="#" method="post" id="form">
	<h2>Love <3</h2>
	
	<p>Work in progress i guess.</p>
	
	<div style="background: #e0e0e0; padding: 10px; margin-bottom: 10px">
		<label>
			input1
			<input type="text" name="input1" value="<?=$_['input1']?>" />
		</label>
		<label><input type="radio" name="type" /> Integer</label>
		<label><input type="radio" name="type" checked /> String</label>
		<label><input type="radio" name="type" /> Array</label>
	</div>
	<div style="background: #e0e0e0; padding: 10px; margin-bottom: 10px">
		<label>
			input2
			<input type="text" name="input2" value="<?=$_['input2']?>" />
		</label>
		<label><input type="radio" name="type" /> Integer</label>
		<label><input type="radio" name="type" checked /> String</label>
		<label><input type="radio" name="type" /> Array</label>
	</div>
	
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