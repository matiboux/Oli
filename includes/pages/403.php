<h1>403</h1>

<p>Access forbidden!</p>

<?php if ($_Oli->getUrlParam(1) !== 'home') { ?>
	<p>
		&rsaquo;
		<a href="<?=$_Oli->getUrlParam(0)?>">Go to Index page</a>
	</p>
<?php } ?>
