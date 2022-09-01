<?php
use Oli\Script;

$script = new Script();
$scriptOrderArgs = [['authKey', 'expireTime'], true];

/** @var \Oli\OliCore $_Oli */
/** @var \Oli\Accounts\AccountsManager Accounts Manager */
$_AM = $_Oli->getAccountsManager();

if ($_AM === null)
	$script->setError('ACCOUNTS_MANAGEMENT_DISABLED', 'User Management is disabled.')
	       ->printJSON(...$scriptOrderArgs)->exit();

if (!$_AM->isLoginEnabled())
	$script->setError('LOGIN_DISABLED', 'Login is disabled.')
	       ->printJSON(...$scriptOrderArgs)->exit();

// $isExternalLogin = $_AM->isExternalLogin();
$isLocalLogin = $_AM->isLocalLogin();
// $isLoggedIn = $_AM->isLoggedIn();

// if (!$isLocalLogin)
// 	$_AM->deleteAccountLines('LOG_LIMITS', 'action = \'login\' AND last_trigger < date_sub(now(), INTERVAL 1 HOUR)');

$logid = @$_['logid'] ?? @$_['username'];
if (empty($logid))
	$script->setError('LOGID_MISSING', 'Parameter "logid" is missing.')
	       ->printJSON(...$scriptOrderArgs)->exit();

if ($isLocalLogin)
{
	// Check that the local account exists
	if (!$_AM->isRootRegistered())
		$script->setError('LOCAL_NOT_REGISTERED', 'The local account has not been registered.')
		       ->printJSON(...$scriptOrderArgs)->exit();
}
else
{
	// $userIdAttempts = $_AM->getDB()->runQuerySQL('SELECT COUNT(1) as attempts FROM `' . $_AM->translateAccountsTableCode('LOG_LIMITS') . '` WHERE action = \'login\' AND user_id = \'' . $_AM->getAuthID() . '\' AND last_trigger >= date_sub(now(), INTERVAL 1 HOUR)')[0]['attempts'] ?: 0;
	// if (!$isLocalLogin && $userIdAttempts >= $config['maxUserIdAttempts'])
		// $script->setErrorMessage('<b>Anti brute-force</b> – Due to too many login attempts (' . $userIdAttempts . '), your user ID has been blocked and therefore you cannot login. Please try again later, or <a href="' . $_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/unlock' . '">unlock your account</a>.')
		//        ->printJSON(...$scriptOrderArgs)->exit();

	$userIPAttempts = $_AM->getDB()->runQuerySQL('SELECT COUNT(1) as attempts FROM `' . $_AM->translateAccountsTableCode('LOG_LIMITS') . '` WHERE action = \'login\' AND ip_address = \'' . $_Oli->getUserIP() . '\' AND last_trigger >= date_sub(now(), INTERVAL 1 HOUR)')[0]['attempts'] ?: 0;
	if ($userIPAttempts >= $config['maxUserIPAttempts'])
		$script->setErrorMessage('<b>Anti brute-force</b> – Due to too many login attempts (' . $userIPAttempts . '), your IP address has been blocked and therefore you cannot login. Please try again later, or <a href="' . $_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/unlock' . '">unlock your account</a>.')
		       ->printJSON(...$scriptOrderArgs)->exit();
}

if (empty($_['password']))
	$script->setError('PASSWORD_MISSING', 'Parameter "password" is missing.')
	       ->printJSON(...$scriptOrderArgs)->exit();

$uid = !$isLocalLogin
       ? $_AM->getAccountInfos('ACCOUNTS', 'uid', ['uid' => $_['logid'], 'username' => $_['logid'], 'email' => $_['logid']], ['where_or' => true], false)
	   : null;
$localRoot = !empty($_AM->getLocalRootInfos()) ? true : false;

if (!$uid AND !$localRoot)
	// $script->setError('LOGID_INVALID', 'Sorry, no account is associated with the login ID you used.')
	$script->setError('LOGIN_INVALID', 'Sorry, the account was not found or the password is wrong.')
	       ->printJSON(...$scriptOrderArgs)->exit();

if (!$isLocalLogin)
{
	$userRightLevel = $_AM->getUserRightLevel($uid, false);

	if ($userRightLevel == $_AM->translateUserRight('NEW-USER'))
		$script->setError('ACCOUNT_NOT_ACTIVATED', 'Sorry, the account associated with that login ID is not yet activated.')
		       ->printJSON(...$scriptOrderArgs)->exit();

	if ($userRightLevel == $_AM->translateUserRight('BANNED'))
		$script->setError('ACCOUNT_BANNED', 'Sorry, the account associated with that login ID is banned and is not allowed to log in.')
		       ->printJSON(...$scriptOrderArgs)->exit();

	if ($userRightLevel < $_AM->translateUserRight('USER'))
		$script->setError('ACCOUNT_DISABLED', 'Sorry, the account associated with that login ID is not allowed to log in.')
		       ->printJSON(...$scriptOrderArgs)->exit();

	$uidAttempts = $_AM->getDB()->runQuerySQL('SELECT COUNT(1) as attempts FROM `' . $_AM->translateAccountsTableCode('LOG_LIMITS') . '` WHERE action = \'login\' AND uid = \'' . $uid . '\' AND last_trigger >= date_sub(now(), INTERVAL 1 HOUR)')[0]['attempts'] ?: 0;
	if ($uidAttempts >= $config['maxUidAttempts'])
		$script->setErrorMessage('E:<b>Anti brute-force</b> – Due to too many login attempts (' . $uidAttempts . '), this account has been blocked and therefore you cannot login. Please try again later, or <a href="' . $_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/unlock' . '">unlock your account</a>.')
		       ->printJSON(...$scriptOrderArgs)->exit();
}

if (!$_AM->verifyLogin($_['logid'], $_['password']))
{
	if (!$isLocalLogin)
		$_AM->insertAccountLine('LOG_LIMITS', array('id' => $_AM->getLastAccountInfo('LOG_LIMITS', 'id') + 1, 'uid' => $uid, 'ip_address' => $_Oli->getUserIP(), 'action' => 'login', 'last_trigger' => date('Y-m-d H:i:s')));

	$script->setError('LOGIN_INVALID', 'Sorry, the account was not found or the password is wrong.')
	       ->printJSON(...$scriptOrderArgs)->exit();
}

$loginDuration = $_['rememberMe'] ? $_OliConfig['extended_session_duration'] : $_OliConfig['default_session_duration'];
$authKey = $_AM->loginAccount($_['logid'], $_['password'], $loginDuration);

if (!$authKey)
	$script->setError('LOGIN_INVALID', 'Sorry, the account was not found or the password is wrong.')
	       ->printJSON(...$scriptOrderArgs)->exit();

// 'action' => 'setLoginInfos',
// 'authKey' => $_AM->getAuthKey(),
// 'extendedDelay' => $_['rememberMe'] ? true : false,
// 'next' => array_slice($_OliConfig['associated_websites'], 1),
// 'callback' => $_['referer'] ?: $_Oli->getFullUrl())),
$script->set('authKey', $_AM->getAuthKey());
$script->set('extendedDelay', $_['rememberMe'] ? true : false);

if (!empty($_OliConfig['associated_websites'])
	&& preg_match('/^(https?:\/\/)?([-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6})\b([-a-zA-Z0-9@:%_\+.~#?&\/=]*)$/',
					$_OliConfig['associated_websites'][0], $matches))
{
	$url = ($matches[1] ?: 'http://') . $matches[2] . (substr($matches[3], -1) == '/' ? $matches[3] : '/') . 'request.php';
	header('Location: ' . $url . '?' . http_build_query(array('action' => 'setLoginInfos', 'authKey' => $_AM->getAuthKey(), 'extendedDelay' => $_['rememberMe'] ? true : false, 'next' => array_slice($_OliConfig['associated_websites'], 1), 'callback' => $_['referer'] ?: $_Oli->getFullUrl())));
}

else if (!empty($_['referer']) && !strstr($_['referer'], '/' . $_Oli->getUrlParam(1)))
	header('Location: ' . $_['referer']);

// else
	// header('Location: ' . $_Oli->getUrlParam(0));

$scriptState = $STATE[STATE_LOGGED];
$showAntiBruteForce = true;
$isLoggedIn = true;
$resultCode = 'S:You are now succesfully logged in.';

$script->set('authKey', $authKey);
$script->set('expireTime', $loginDuration);

// $script->setErrorMessage('An error occurred while logging you in.')
//        ->printJSON(...$scriptOrderArgs)->exit();

if (!empty($_['callback']))
	header('Location: ' . $_['callback']);

$script->printJSON(...$scriptOrderArgs)->exit();
