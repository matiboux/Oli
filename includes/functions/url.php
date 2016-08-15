<?php
function getBaseUrl() {
	global $db;
	$query = $db->query('SELECT * FROM options WHERE name = \'url\'');
	while ($data = $query->fetch()) {
		return $data['value'];
	}
	$query->closeCursor();
}
function getUrlParam($paramId) {
	$url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	$frationnedUrl = explode('/', $url);
	$newFrationnedUrl = array();
	$tempSiteUrl = 'http://';
	$countForEachLoop = 0;
	$countWhileLoop = 0;
	foreach($frationnedUrl as $partUrl) {
		if($tempSiteUrl != getBaseUrl()) {
			$tempSiteUrl = $tempSiteUrl . $partUrl . '/';
			$countForEachLoop = $countForEachLoop + 1;
		}
	}
	$countWhileLoop = $countForEachLoop - 1;
	$FirstWhileLoop = false;
	while(!empty($frationnedUrl[$countWhileLoop])) {
		if(!$FirstWhileLoop) {
			array_push($newFrationnedUrl, $tempSiteUrl);
		}
		else {
			array_push($newFrationnedUrl, $frationnedUrl[$countWhileLoop]);
		}
		$FirstWhileLoop = true;
		$countWhileLoop = $countWhileLoop + 1;
	}
	return $newFrationnedUrl[$paramId];
}
function getDataUrl() {
	return THEME . 'data/';
}
?>