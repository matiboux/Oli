<?php
use Oli\Script;

$script = new Script();
$scriptOrderArgs = [['authKey', 'expireDelay'], true];

if (empty($_['action']))
	$script->setErrorMessage('Parameter "action" is missing.')
	       ->printJSON(...$scriptOrderArgs)->exit();

if ($_['action'] === 'setUserID' || $_['action'] === 'setLoginInfos')
{
	/** @var \Oli\OliCore $_Oli */
	/** @var \Oli\AccountsManager Accounts Manager */
	$_AM = $_Oli->getAccountsManager();

	if ($_AM === null || !$_AM->isReady())
		$script->setErrorMessage('User Management is disabled.')
			   ->printJSON(...$scriptOrderArgs)->exit();

	if (empty($_['authKey']))
		$script->setErrorMessage('Parameter "authKey" is missing.')
		       ->printJSON(...$scriptOrderArgs)->exit();

	// if (empty($_['userID']))
		// $script->setErrorMessage('Parameter "userID" is missing.')
		//        ->printJSON(...$scriptOrderArgs)->exit();

	if ($_AM->isLoggedIn())
		$script->setErrorMessage('Cannot overwrite a valid authentication key.')
		       ->printJSON(...$scriptOrderArgs)->exit();

	$expireDelay = $_OliConfig['auth_key_cookie']['expire_delay'] ?: 3600 * 24 * 7;
	$_AM->setAuthKeyCookie($_['authKey'], $expireDelay);
	$script->set('authKey', $_['authKey']);
	$script->set('expireDelay', $expireDelay);
}

else
	$script->setErrorMessage('Parameter "action" is invalid.')
	       ->printJSON(...$scriptOrderArgs)->exit();

if (!empty($_['next']))
{
	// $_['next'] = json_decode($_['next'], true);
	$next = array_shift($_['next']);
	// $_['next'] = !empty($_['next']) ? json_encode($_['next']) : null;

	header('Location: ' . (substr($next, -1) == '/' ? $next : $next . '/') . 'request.php' . '?' . http_build_query($_));
}
else if (!empty($_['callback']))
	header('Location: ' . $_['callback']);

$script->printJSON(...$scriptOrderArgs)->exit();
