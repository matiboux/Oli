<?php
$result = [];

if (!empty($_))
{
	if ($_['action'] == 'setUserID' or $_['action'] == 'setLoginInfos')
	{
		if (!$_Oli->isAccountsManagementReady()) $result = ['error' => 'Error: User Management is disabled'];
		else if (empty($_['authKey'])) $result = ['error' => 'Error: "authKey" parameter is missing'];
		// else if(empty($_['userID'])) $result = array('error' => 'Error: "userID" parameter is missing');
		else if ($_Oli->isLoggedIn()) $result = ['error' => 'Error: Cannot overwrite a valid authKey'];
		else
		{
			$_Oli->setAuthKeyCookie($_['authKey'], $_Oli->config['auth_key_cookie']['expire_delay'] ?: 3600 * 24 * 7);
			$result = ['error' => false, 'authKey' => $_['authKey'], 'expireDelay' => $expireDelay];
		}
	}
	else $result = ['error' => 'Error: "Action" parameter is missing'];
	if (!empty($result)) echo json_encode($result);

	if (!empty($_['next']))
	{
		// $_['next'] = json_decode($_['next'], true);
		$next = array_shift($_['next']);
		// $_['next'] = !empty($_['next']) ? json_encode($_['next']) : null;

		header('Location: ' . (substr($next, -1) == '/' ? $next : $next . '/') . 'request.php' . '?' . http_build_query($_));
	}
	else if (!empty($_['callback'])) header('Location: ' . $_['callback']);
}
else echo json_encode(['error' => 'Error: No parameters provided']);

// die(!empty($result) ? json_encode($result) : ['error' => 'Unknown script result.']);
exit;
