<?php
function includeThemeFile($fileName) {
	if(preg_match('#.php$#i', '')) {
		if(file_exists(THEME . $fileName . '.php')) {
			include THEME . $fileName . '.php';
		}
		else {
			echo 'falsepreg';
			return false;
		}
	}
	else {
		if(file_exists(THEME . $fileName)) {
			include THEME . $fileName;
		}
		else {
			echo 'falsenotpreg';
			return false;
		}
	}
}
?>