<?php
$params = array_merge($_GET, $_POST);
$result = [];

if(!empty($params)) {
	if($params['action'] == 'setAuthKey') {
		if(!$_Oli->config['user_management']) $result = array('error' => 'Error: User Management is disabled');
		if(empty($params['authKey'])) $result = array('error' => 'Error: "authKey" parameter is missing');
		if(empty($params['userID'])) $result = array('error' => 'Error: "userID" parameter is missing');
		else if($_Oli->verifyAuthKey()) $result = array('error' => 'Error: Cannot overwrite a valid authKey');
		else {
			$_Oli->setUserIDCookie($params['userID'], null);
			$_Oli->setAuthKeyCookie($params['authKey'], $params['extendedDelay'] ? $_Oli->config['extended_session_duration'] : $_Oli->config['default_session_duration']);
			$result = array('error' => false, 'authKey' => $params['authKey'], 'userID' => $params['userID'], 'expireDelay' => $params['extendedDelay'] ? $_Oli->config['extended_session_duration'] : $_Oli->config['default_session_duration']);
		}
	} else $result = array('error' => 'Error: "Action" parameter is missing');
} else $result = array('error' => 'Error: No parameters provided');

die(!empty($result) ? json_encode($result, JSON_FORCE_OBJECT) : 'Unknown script result.');
?>