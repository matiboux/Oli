<?php
function getName() {
	global $db;
	$query = $db->query('SELECT * FROM options WHERE name = \'name\'');
	while ($data = $query->fetch()) {
		return $data['value'];
	}
	$query->closeCursor();
}
function getDescription() {
	global $db;
	$query = $db->query('SELECT * FROM options WHERE name = \'description\'');
	while ($data = $query->fetch()) {
		return $data['value'];
	}
	$query->closeCursor();
}
function getStatus() {
	global $db;
	$query = $db->query('SELECT * FROM options WHERE name = \'status\'');
	while ($data = $query->fetch()) {
		return $data['value'];
	}
	$query->closeCursor();
}
?>