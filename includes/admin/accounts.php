<?php
$params = array_merge($_GET, $_POST);
$result = [];

if(!$_Oli->isLoggedIn()) header('Location: ' . $_Oli->getLoginUrl());
?>

<html>
<head>

<?php include INCLUDESPATH . 'admin/head.php'; ?>
<title>Oli Admin</title>

</head>
<body>

<?php include INCLUDESPATH . 'admin/navbar.php'; ?>

<div id="header" class="header">
	<h1>Accounts Basic Settings</h1>
	<p>Here you can change your account main informations.</p>
</div>

<div id="main">
	<div class="row">
		<div class="col-md-3" style="margin-bottom: 10px">
			<select class="btn btn-primary" name="select" style="width: 100%">
				<option value="home">— Choose an option</option>
				<option value="set-username">Set my username</option>
				<option value="change-password">Change my password</option>
			</select>
		</div>
		<div class="form col-md-9" id="home">
			<p>Welcome</p>
		</div>
		<div class="form col-md-9" id="set-username" style="display: none">
			<p>Set username</p>
		</div>
		<div class="form col-md-9" id="change-password" style="display: none">
			<p>Change password</p>
		</div>
	</div>
</div>

<?php include INCLUDESPATH . 'admin/footer.php'; ?>

<script>
$('[name="select"]').change(function() {
	$('#main .form').hide();
	$('#' + $(this).val()).fadeIn();
});
</script>

</body>
</html>