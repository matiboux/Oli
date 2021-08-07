<h1>404</h1>

<p>Page not found!</p>

<?php if ($_Oli->getUrlParam(1) !== 'home') { ?>
	<p>
		&rsaquo;
		<a href="<?=$_Oli->getUrlParam(0)?>">Go to Index page</a>
	</p>
<?php } ?>
