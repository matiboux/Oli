<?php
$params = array_merge($_GET, $_POST);
$result = [];

?>

<html>
<head>

<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="author" content="Matiboux" />

<title>Oli Admin</title>

</head>
<body>

<h1>Oli Login Page —</h1>

<form action="#" method="post" id="form">
	<h2>Login / Register</h2>
	<input type="text" name="username" placeholder="Username" value="<?=$_POST['username']?>" /> <br />
	<input type="password" name="password" placeholder="Password" /> <br />
	<input type="text" name="olisc" placeholder="Oli Security Code" value="<?=$_POST['olisc']?>" /> (if registering) <br />
	<button type="submit">Verify my identity</button>
</form>

</body>
</html>