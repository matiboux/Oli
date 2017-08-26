<?php
$params = array_merge($_GET, $_POST);
$result = [];

if(empty($params['template'])) $result['error'] = 'Missing "template" parameter.';
else if(!$template = $_Oli->getTemplate($params['template'])) $result['error'] = 'Unknown or Invalid Template.';
else if($params['htmlpreview']) {
	$_Oli->setContentType('HTML');
	die($template);
} else {
	$result['error'] = false;
	$result['template'] = $template;
}

die(!empty($result) ? json_encode($result, JSON_FORCE_OBJECT) : array('error' => 'An unknown error occurred.'));
?>