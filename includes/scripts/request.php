<?php
$_ = array_merge($_GET, $_POST);
$result = [];

if(!empty($_)) {
	if($_['action'] == 'setUserID' OR $_['action'] == 'setLoginInfos') {
		if(!$_Oli->isAccountsManagementReady()) $result = array('error' => 'Error: User Management is disabled');
		else if(empty($_['authKey'])) $result = array('error' => 'Error: "authKey" parameter is missing');
		else if(empty($_['userID'])) $result = array('error' => 'Error: "userID" parameter is missing');
		else if($_Oli->verifyAuthKey()) $result = array('error' => 'Error: Cannot overwrite a valid authKey');
		else {
			$_Oli->setUserIDCookie($_['userID'] . '::' . $_['authKey'], $_Oli->config['user_id_cookie']['expire_delay'] ?: 3600*24*7);
			$result = array('error' => false, 'authKey' => $_['authKey'], 'userID' => $_['userID'], 'expireDelay' => $expireDelay);
		}
	} else $result = array('error' => 'Error: "Action" parameter is missing');
	if(!empty($result)) echo json_encode($result);
	
	if(!empty($_['next'])) {
		// $_['next'] = json_decode($_['next'], true);
		$next = array_shift($_['next']);
		// $_['next'] = !empty($_['next']) ? json_encode($_['next']) : null;
		
		header('Location: ' . (substr($next, -1) == '/' ? $next : $next . '/') . 'request.php' . '?' . http_build_query($_));
	} else if(!empty($_['callback'])) header('Location: ' . $_['callback']);
} else echo json_encode(array('error' => 'Error: No parameters provided'));

// die(!empty($result) ? json_encode($result) : array('error' => 'Unknown script result.'));
exit;
?>