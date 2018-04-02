<?php
$_ = array_merge($_GET, $_POST);
$result = [];

if(!empty($_)) {
	if($_['action'] == 'setUserID' OR $_['action'] == 'setLoginInfos') {
		if(!$_Oli->config['user_management']) $result = array('error' => 'Error: User Management is disabled');
		else if(empty($_['authKey'])) $result = array('error' => 'Error: "authKey" parameter is missing');
		else if(empty($_['userID'])) $result = array('error' => 'Error: "userID" parameter is missing');
		else if($_Oli->verifyAuthKey()) $result = array('error' => 'Error: Cannot overwrite a valid authKey');
		else {
			$_Oli->setUserIDCookie($_['userID'] . '::' . $_['authKey'], $this->config['user_id_cookie']['expire_delay'] ?: 3600*24*7);
			$result = array('error' => false, 'authKey' => $_['authKey'], 'userID' => $_['userID'], 'expireDelay' => $expireDelay);
		}
	} else if($_['action'] == 'removeLoginInfos') { /* Deprecated */
		if(!$_Oli->config['user_management']) $result = array('error' => 'Error: User Management is disabled');
		
		if($_Oli->verifyAuthKey()) $result = array('error' => 'Error: Cannot remove valid login infos');
		else {
			$_Oli->deleteAuthKeyCookie();
			$result = array('error' => false);
		}
	} else $wrongAction = true;
	
	if($wrongAction) $result = array('error' => 'Error: "Action" parameter is missing');
	else if(!empty($_['next'])) {
		// $_['next'] = json_decode($_['next'], true);
		$next = array_shift($_['next']);
		// $_['next'] = !empty($_['next']) ? json_encode($_['next'], JSON_FORCE_OBJECT) : null;
		
		header('Location: ' . (substr($next, -1) == '/' ? $next : $next . '/') . 'request.php' . '?' . http_build_query($_));
	} else if(!empty($_['callback'])) header('Location: ' . $_['callback']);
} else $result = array('error' => 'Error: No parameters provided');

die(!empty($result) ? json_encode($result, JSON_FORCE_OBJECT) : array('error' => 'Unknown script result.'));
?>