<?php
/*\
|*|  ----------------------------
|*|  --- [  Oli Login page  ] ---
|*|  ----------------------------
|*|  
|*|  Oli Github repository: https://github.com/OliFramework/Oli/
|*|  
|*|  For Oli version BETA-1.8.0 and prior versions,
|*|  the source code of the Oli Login page was hosted on a standalone Github repository.
|*|  → https://github.com/OliFramework/Oli-Login-Page
|*|  
|*|  Original Login Page based on Andy Tran's template (http://codepen.io/andytran/pen/PwoQgO)
|*|  
|*|  --- --- ---
|*|  
|*|  Stuff to do next:
|*|  - Add captcha for registering.
|*|  
|*|  Stuff to do on Oli:
|*|  - Add support and config for login limits.
\*/

/** Login management is disabled by the current config */
// if(!$_Oli->config['user_management'] OR !$_Oli->config['allow_login']) header('Location: ' . $_Oli->getUrlParam(0));

/** *** *** */

$_ = array_merge($_GET, $_POST);

$config = array(
	'maxUserIdAttempts' => 3,
	'maxUserIPAttempts' => 5,
	'maxUidAttempts' => 8
);

$isExternalLogin = $_Oli->isExternalLogin();
$isLocalLogin = $_Oli->isLocalLogin();
$isLoggedIn = $_Oli->isLoggedIn();

$ignoreFormData = false; // Ignore Form Data - Allow the script to prevent a form from using data from another.
$showAntiBruteForce = false; // Display the Anti Brute Force stats.

/** Script State Variable */
$scriptState = null; // Default value


/** LIST OF VALUES [$scriptState] - But sometimes uppercased. */
/** And [Is Script State Allowed?] */
// - 'LOGIN' Log into your account.
	$isLoginAllowed = ($isLocalLogin OR $_Oli->config['allow_login']);
// - 'LOGGED' Logged in.
	// $isLoggedAllowed = $isLoggedIn;
// - 'REGISTER' Create an account.
	$isRegisterAllowed = (!$isLocalLogin AND $_Oli->config['allow_register']);
// .. 'registered' Account created. (?)
// - 'ROOT-REGISTER' Create a root account.
	if($isLocalLogin) $isRootRegisterAllowed = empty($_Oli->getLocalRootInfos());
	else $isRootRegisterAllowed = !$_Oli->isExistAccountInfos('ACCOUNTS', array('user_right' => 'ROOT'), false);
// .. 'root-registered' >> 'login' Root account created.
// - 'ACTIVATE' Activate your account.
	$isActivateAllowed = (!$isLocalLogin AND $_Oli->config['account_activation'] AND $_Oli->config['allow_register']);
// - 'RECOVER' Recover your account.
	// $isRecoverAllowed = !$isLocalLogin;
// - 'RECOVER-PASSWORD' Change your password. (through RECOVER request)
// - 'ACCOUNT-SETTINGS' Account settings. (through LOGGED)
// - 'EDIT-PASSWORD' Change your password. (through LOGGED)
	$isEditPasswordAllowed = ($isLoggedIn OR !$isLocalLogin);
// .. 'edited-password' >> 'login' Password changed.
// - 'SET-USERNAME' Set your username. (through LOGGED)
	$isSetUsernameAllowed = ($isLoggedIn AND !$isLocalLogin);
// - 'UNLOCK' Request your account to be unlocked.
	// $isUnlockAllowed = !$isLocalLogin;
// - 'UNLOCK-SUBMIT' Unlock your account.
// .. 'unlock-submited' >> 'login' Account unlocked.

/** *** *** */

/** Background cleanup process */
if(!empty($_) AND !$isLocalLogin) {
	$_Oli->runQueryMySQL('DELETE FROM `' . $_Oli->translateAccountsTableCode('REQUESTS') . '` WHERE expire_date < now()');
	$_Oli->runQueryMySQL('DELETE FROM `' . $_Oli->translateAccountsTableCode('SESSIONS') . '` WHERE expire_date < now() OR (expire_date IS NULL AND update_date < date_sub(now(), INTERVAL 7 DAY))');
}

/** --- */

/** Login handled by an external instance of Oli, or an external script. */
if($isExternalLogin) header('Location: ' . $_Oli->getLoginUrl());

/** Account Password Edit */
else if(in_array($_Oli->getUrlParam(2), ['edit-password', 'change-password']) AND $isEditPasswordAllowed) {
	/**	Password Edit (is Logged In) */
	if($isLoggedIn) {
		$scriptState = 'edit-password';
		if(!empty($_)) {
			if(empty($_['password'])) $resultCode = 'E:Please enter your current password.';
			else if(empty($_['newPassword'])) $resultCode = 'E:Please enter the new password you want to set.';
			else if(!$_Oli->verifyLogin($_Oli->getLoggedUser(), $_['password'])) $resultCode = 'E:The current password is incorrect.';
			else if(empty($hashedPassword = $_Oli->hashPassword($_['newPassword']))) $resultCode = 'E:The new password couldn\'t be hashed.';
			else if($isLocalLogin) {
				$handle = fopen(CONTENTPATH . '.oliauth', 'w');
				if(fwrite($handle, json_encode($_Oli->getLocalRootInfos(), array('password' => $hashedPassword), JSON_FORCE_OBJECT))) {
					$_Oli->logoutAllAccount(); // Log out all sessions
					$scriptState = 'login';
					$ignoreFormData = true;
					$resultCode = 'S:Your password has been successfully updated.';
				} else $resultCode = 'E:An error occurred when updating your password.';
				fclose($handle);
			} else if($_Oli->updateAccountInfos('ACCOUNTS', array('password' => $hashedPassword), $_Oli->getLoggedUser())) {
				$_Oli->logoutAllAccount(); // Log out all sessions
				$resultCode = 'S:Your password has been successfully updated.';
			} else $resultCode = 'E:An error occurred when updating your password.';
		}
	
	/** Account Recovery (not Logged In) */
	} else if(!$isLocalLogin) {
		$scriptState = 'recover-password';
		
		if(!empty($_)) $activateKey = $_['activateKey'];
		else $activateKey = $_Oli->getUrlParam(3) ?: null;
		
		if(!empty($activateKey)) $requestInfos = $_Oli->getAccountLines('REQUESTS', array('activate_key' => hash('sha512', $activateKey)));
		
		if(!empty($_)) {
			if(empty($_['activateKey'])) $resultCode = 'E:The Activate Key is missing.';
			else if(empty($requestInfos)) $resultCode = 'E:Sorry, the request you asked for does not exist.';
			else if($requestInfos['action'] != 'change-password') $resultCode = 'E:The request you triggered does not allow you to change your password.';
			else if(time() > strtotime($requestInfos['expire_date'])) $resultCode = 'E:Sorry, the request you triggered has expired.';
			else {
				/** Deletes all the user sessions, change the user password and delete the request */
				if(!$_Oli->logoutAllAccount($requestInfos['uid'])) $resultCode = 'E:An error occurred while changing your password (#1).';
				else if(!$_Oli->updateAccountInfos('ACCOUNTS', array('password' => $_Oli->hashPassword($_['newPassword'])), $requestInfos['uid'])) $resultCode = 'E:An error occurred while changing your password (#2).';
				else if(!$_Oli->deleteAccountLines('REQUESTS', array('activate_key' => hash('sha512', $_['activateKey'])))) $resultCode = 'E:An error occurred while changing your password (#3).';
				else {
					$scriptState = 'login';
					$ignoreFormData = true;
					$resultCode = 'S:Your password has been successfully changed!';
				}
			}
		} else $_['activateKey'] = $activateKey ?: null;
		
		
	
	/** An error occurred..? (This scenario isn't supposed to happen) */
	} else $resultCode = 'E:An error occurred..?';
}

/** Logged user */
else if($isLoggedIn) {
	$scriptState = 'logged';
	
	/** Disconnect the user */
	if($_Oli->getUrlParam(2) == 'logout') {
		if($_Oli->logoutAccount()) {
			// if(!empty($_Oli->config['associated_websites']) AND preg_match('/^(https?:\/\/)?([-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6})\b([-a-zA-Z0-9@:%_\+.~#?&\/=]*)$/', $_Oli->config['associated_websites'][0], $matches)) {
				// $url = ($matches[1] ?: 'http://') . $matches[2] . (substr($matches[3], -1) == '/' ? $matches[3] : '/') . 'request.php';
				// header('Location: ' . $url . '?' . http_build_query(array('action' => 'removeLoginInfos', 'next' => array_slice($_Oli->config['associated_websites'], 1), 'callback' => $_Oli->getFullUrl())));
			// } else {
				$scriptState = 'login';
				$resultCode = 'S:You have been successfully disconnected.';
			// }
		} else $resultCode = 'E:An error occurred while disconnecting you.';
	
	// } else if(in_array($_Oli->getUrlParam(2), ['edit-password', 'change-password'])) {
	// This shouldn't be able to get this far. 
	
	/** Account Settings */
	} else if($_Oli->getUrlParam(2) == 'account-settings') {
		$scriptState = 'account-settings';
	
	/** Account Username Edit */
	} else if($_Oli->getUrlParam(2) == 'set-username' AND $isSetUsernameAllowed) {
		$scriptState = 'set-username';
		if(!empty($_)) {
			if($_Oli->isProhibitedUsername($_['username']) === false) $resultCode = 'E:You\'re not allowed to use this username.';
			else if(!empty($_['username']) AND $_Oli->isExistAccountInfos('ACCOUNTS', array('username' => $_['username']))) $resultCode = 'E:This username is already used.';
			else if($_Oli->updateAccountInfos('ACCOUNTS', array('username' => $_['username']), $_Oli->getLoggedUser())) {
				$scriptState = 'logged';
				$ignoreFormData = true;
				$resultCode = 'S:Your username has been successfully set.';
			} else $resultCode = 'E:An error occurred when updating your username.';
		}
	
	} else {
		/** Redirect the user */
		// if(!empty($_SERVER['HTTP_REFERER']) AND !strstr($_SERVER['HTTP_REFERER'], '/' . $_Oli->getUrlParam(1))) header('Location: ' . $_SERVER['HTTP_REFERER']);
		
		/** Notice the user */
		$resultCode = 'I:You\'re already logged in, ' . $_Oli->getLoggedName() . '.';
	}

/** Activate an account */
// } else if($_Oli->getUrlParam(2) == 'activate' AND $_Oli->config['account_activation'] AND !$isLocalLogin) {
} else if($_Oli->getUrlParam(2) == 'activate' AND $isActivateAllowed) {
	$scriptState = 'activate';
	if(empty($_['activateKey'] ?: $_Oli->getUrlParam(3))) $resultCode = 'E:Please enter your activate key.';
	else if(!$requestInfos = $_Oli->getAccountLines('REQUESTS', array('activate_key' => hash('sha512', $_['activateKey'] ?: $_Oli->getUrlParam(3))))) $resultCode = 'E:Sorry, the request you asked for does not exist.';
	else if($requestInfos['action'] != 'activate') $resultCode = 'E:The request you triggered does not allow you to activate any account.';
	else if(time() > strtotime($requestInfos['expire_date'])) $resultCode = 'E:Sorry, the request you triggered has expired.';
	else if($_Oli->updateUserRight('USER', $requestInfos['username']) AND $_Oli->deleteAccountLines('REQUESTS', array('activate_key' => hash('sha512', $_['activateKey'] ?: $_Oli->getUrlParam(3))))) {
		$scriptState = 'login';
		$resultCode = 'S:Your account has been successfully activated!';
	} else $resultCode = 'E:An error occurred while activating your account.';

/** Recover an account */
} else if($_Oli->getUrlParam(2) == 'recover') {
	if($isRootRegisterAllowed) $scriptState = 'root-register';
	// else if($isLocalLogin) {
		// $resultCode = 'I:You cannot recover a root local account. If you\'re the website owner, you can delete the <code>/content/.oliauth</code> file and create another account.';
		// $scriptState = 'login';
	// } else {
	else {
		$scriptState = 'recover';
		if(!empty($_) AND !$isLocalLogin) {
			if(empty($_['email'])) $resultCode = 'E:Please enter your email.';
			else if(!$uid = $_Oli->getAccountInfos('ACCOUNTS', 'uid', array('email' => trim($_['email'])))) $resultCode = 'E:Sorry, no account is associated with the email you entered.';
			else if($requestInfos = $_Oli->getAccountLines('REQUESTS', array('uid' => $uid, 'action' => 'change-password')) AND time() <= strtotime($requestInfos['expire_date'])) $resultCode = 'E:Sorry, a change-password request already exists for that account, please check your mail inbox.';
			else if($activateKey = $_Oli->createRequest($uid, 'change-password')) {
				$subject = 'One more step to change your password';
				$message .= '<p><b>Hi ' . $_Oli->getName($uid) . '</b>!</p>';
				$message .= '<p>A new request has been created for changing your account password. <br />';
				$message .= 'To set your new password, just click on <a href="' . $_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/change-password/' . $activateKey . '">this link</a> and follow the instructions. <br />';
				$message .= 'This request will expire after ' . floor($expireDelay = $_Oli->getRequestsExpireDelay() /3600 /24) . ' ' . ($expireDelay > 1 ? 'days' : 'day') . '. After that, the link will be desactivated and the request deleted.</p>';
				$message .= '<p>If you can\'t open the link, just copy this in your browser: ' . $_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/change-password/' . $activateKey . '.</p>';
				$message .= '<p>If you didn\'t want to change your password or didn\'t ask for this request, please just ignore this mail.</p>';
				
				if(mail($_['email'], $subject, $_Oli->getTemplate('mail', array('__URL__' => $_Oli->getUrlParam(0), '__NAME__' => $_Oli->getSetting('name') ?: 'Oli Mailling Service', '__SUBJECT__' => $subject, '__CONTENT__' => $message)), $_Oli->getDefaultMailHeaders(true))) {
					$scriptState = 'recover-password';
					$ignoreFormData = true;
					$resultCode = 'S:The request has been successfully created and a mail has been sent to you.';
				} else {
					$_Oli->deleteAccountLines('REQUESTS', array('activate_key' => $activateKey));
					$resultCode = 'D:An error occurred while sending the mail to you.';
				}
			} else $resultCode = 'E:An error occurred while creating the change-password request.';
		}
	}

/** Unlock an account */
} else if($_Oli->getUrlParam(2) == 'unlock' AND !$isLocalLogin) {
	/** Ask the unlock code */
	if(($_['unlockKey'] ?: $_Oli->getUrlParam(3)) === null) {
		$scriptState = 'unlock';
		if(!empty($_)) {
			$username = trim($_['username']);
			
			if(empty($username)) $resultCode = 'E:Please enter your username or your email.';
			else {
				$isExistByUsername = $_Oli->isExistAccountInfos('ACCOUNTS', $username, false);
				$emailOwner = $_Oli->getAccountInfos('ACCOUNTS', 'username', array('email' => $username), false);
				if(!$isExistByUsername AND $emailOwner) {
					$email = $username;
					$username = $emailOwner;
				}
				
				if(!$isExistByUsername AND !$emailOwner) $resultCode = 'E:Sorry, no account is associated with the username or email you entered.';
				else if(($uidAttempts = $_Oli->runQueryMySQL('SELECT COUNT(1) as attempts FROM `' . $_Oli->translateAccountsTableCode('LOG_LIMITS') . '` WHERE action = \'login\' AND username = \'' . $username . '\' AND last_trigger >= date_sub(now(), INTERVAL 1 HOUR)')[0]['attempts'] ?: 0) < 1) $resultCode = 'E:Sorry, no failed login attempts has been recorded for this account.';
				else if($requestInfos = $_Oli->getAccountLines('REQUESTS', array('username' => $username, 'action' => 'unlock')) AND time() <= strtotime($requestInfos['expire_date'])) $resultCode = 'E:Sorry, an unlock request already exists for that account, please check your mail inbox.';
				else if($activateKey = $_Oli->createRequest($username, 'unlock')) {
					$subject = 'One more step to unlock your account';
					$message .= '<p><b>Hello ' . $username . '</b></p>';
					$message .= '<p>Just one last step to unlock your account. If you tried logging in and got blocked after multiple attempts, just click on <a href="' . $_Oli->getUrlParam(0)  . $_Oli->getUrlParam(1) . '/unlock/' . $activateKey . '">this link</a> to unlock your account. <br />';
					$message .= 'This request will expire after ' . floor($expireDelay = $_Oli->getRequestsExpireDelay() /3600 /24) . ' ' . ($expireDelay > 1 ? 'days' : 'day') . '. After that, the link will be desactivated and the request deleted.</p>';
					$message .= '<p>If you can\'t open the link, just copy this in your browser: ' . $_Oli->getUrlParam(0)  . $_Oli->getUrlParam(1) . '/unlock/' . $activateKey . '.</p>';
					
					if(mail($email ?: $_Oli->getAccountInfos('ACCOUNTS', 'email', $username, false), $subject, $_Oli->getTemplate('mail', array('__URL__' => $_Oli->getUrlParam(0), '__NAME__' => $_Oli->getSetting('name') ?: 'Oli Mailling Service', '__SUBJECT__' => $subject, '__CONTENT__' => $message)), $_Oli->getDefaultMailHeaders(true))) {
						$scriptState = 'unlock-submit';
						$ignoreFormData = true;
						$resultCode = 'S:The request has been successfully created and a mail has been sent to you.';
					} else {
						$_Oli->deleteAccountLines('REQUESTS', array('activate_key' => $activateKey));
						$resultCode = 'D:An error occurred while sending the mail to you.';
					}
				} else $resultCode = 'E:An error occurred while creating the unlock request.';
			}
		}
	
	/** Submit the unlock code */
	} else {
		$scriptState = 'unlock-submit';
		
		if(empty($_['unlockKey'] ?: $_Oli->getUrlParam(3))) $resultCode = 'E:Please enter the unlock key.';
		else if(!$requestInfos = $_Oli->getAccountLines('REQUESTS', array('activate_key' => hash('sha512', $_['unlockKey'] ?: $_Oli->getUrlParam(3))))) $resultCode = 'E:No request with this unlock key has been found.';
		else if($requestInfos['action'] != 'unlock') $resultCode = 'E:The request you triggered does not allow you to unlock your account.';
		else if(time() > strtotime($requestInfos['expire_date'])) $resultCode = 'E:Sorry, the request you triggered has expired.';
		else {
			/** Deletes all the account log limits and delete the request */
			if($_Oli->deleteAccountLines('LOG_LIMITS', $requestInfos['username']) AND $_Oli->deleteAccountLines('REQUESTS', array('activate_key' => hash('sha512', $_['unlockKey'] ?: $_Oli->getUrlParam(3))))) {
				$scriptState = 'login';
				$ignoreFormData = true;
				// $hideUnlockUI = true;
				$resultCode = 'S:Your account has been successfully unlocked!';
			} else $resultCode = 'E:An error occurred while changing your password.';
		}
	}
	
/** Create a new account */
// WIP / Fix the allowed vars?
} else if($isRegisterState = ($_Oli->getUrlParam(2) == 'register' AND $isRegisterAllowed) OR $isRootRegisterState = (in_array($_Oli->getUrlParam(2), ['root-register', 'root']) AND $isRootRegisterAllowed)) {
	if($isRegisterState) $scriptState = 'register';
	else if($isRootRegisterState) $scriptState = 'root-register';
	
	if(!empty($_)) {
		/** Password Checks */
		if(empty($_['password'])) $resultCode = 'E:Please enter a password.';
		else if(strlen($_['password']) < 6) $resultCode = 'E:Your password must be at least 6 characters long.';
		
		/** Root Register Checks */
		else if($isRootRegisterState AND empty($_['olisc'])) $resultCode = 'E:Please enter the Oli Security Code.';
		else if($isRootRegisterState AND $_['olisc'] != $_Oli->getOliSecurityCode()) $resultCode = 'E:The Oli Security Code is incorrect.';
		
		/** Not Local Login Checks */
		else if(!$isLocalLogin AND empty($_['email'] = strtolower(trim($_['email'])))) $resultCode = 'E:Please enter your email.';
		else if(!$isLocalLogin AND !preg_match('/^[-_a-z0-9]+(?:\.?[-_a-z0-9]+)*@[^\s]+(?:\.[a-z]+)$/i', $_['email'])) $resultCode = 'E:The email is incorrect. Make sure you only use letters, numbers, hyphens, underscores or periods.';
		else if(!$isLocalLogin AND $_Oli->isExistAccountInfos('ACCOUNTS', array('email' => $_['email']), false)) $resultCode = 'E:Sorry, this email is already associated with an existing account.';
		else if(!$isLocalLogin AND $isRootRegisterState AND $_Oli->isExistAccountInfos('ACCOUNTS', array('user_right' => 'ROOT'), false)) $resultCode = 'E:Sorry, there is already an existing root account.';
		
		/** Global Register */
		else if($_Oli->registerAccount(!$isLocalLogin ? $_['email'] : null, $_['password'], $isRootRegisterState ? $_['olisc'] : null)) {
			$scriptState = 'login';
			$ignoreFormData = true;
			
			if($isRootRegisterState) {
				$isRootRegisterAllowed = false;
				$resultCode = 'S:Your account has been successfully created as a root account.';
			} else if($_Oli->config['account_activation']) $resultCode = 'S:Your account has been successfully created. You received an email to activate your account.';
			else $resultCode = 'S:Your account has been successfully created. You can now log in.';
		} else $resultCode = 'E:An error occurred while creating your account..';
	}

/** Login */
} else if($isLoginAllowed) {
	$scriptState = 'login';
	
	/** Want to log out, but not logged in */
	if($_Oli->getUrlParam(2) == 'logout') $resultCode = 'I:You are already disconnected.';
	
	/** Login */
	else if(!empty($_)) {
		if(!$isLocalLogin) $_Oli->deleteAccountLines('LOG_LIMITS', 'action = \'login\' AND last_trigger < date_sub(now(), INTERVAL 1 HOUR)');
		
		$_['logid'] = $_['logid'] ? trim($_['logid']) : null;
		if(!$isLocalLogin AND empty($_['logid'])) $resultCode = 'E:Please enter your login ID.';
		// else if(!$isLocalLogin AND ($userIdAttempts = $_Oli->runQueryMySQL('SELECT COUNT(1) as attempts FROM `' . $_Oli->translateAccountsTableCode('LOG_LIMITS') . '` WHERE action = \'login\' AND user_id = \'' . $_Oli->getAuthID() . '\' AND last_trigger >= date_sub(now(), INTERVAL 1 HOUR)')[0]['attempts'] ?: 0) >= $config['maxUserIdAttempts']) $resultCode = 'E:<b>Anti brute-force</b> – Due to too many login attempts (' . $userIdAttempts . '), your user ID has been blocked and therefore you cannot login. Please try again later, or <a href="' . $_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/unlock' . '">unlock your account</a>.';
		else if(!$isLocalLogin AND ($userIPAttempts = $_Oli->runQueryMySQL('SELECT COUNT(1) as attempts FROM `' . $_Oli->translateAccountsTableCode('LOG_LIMITS') . '` WHERE action = \'login\' AND ip_address = \'' . $_Oli->getUserIP() . '\' AND last_trigger >= date_sub(now(), INTERVAL 1 HOUR)')[0]['attempts'] ?: 0) >= $config['maxUserIPAttempts']) $resultCode = 'E:<b>Anti brute-force</b> – Due to too many login attempts (' . $userIPAttempts . '), your IP address has been blocked and therefore you cannot login. Please try again later, or <a href="' . $_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/unlock' . '">unlock your account</a>.';
		else if(empty($_['password'])) $resultCode = 'E:Please enter your password.';
		else {
			$uid = !$isLocalLogin ? $_Oli->getAccountInfos('ACCOUNTS', 'uid', array('uid' => $_['logid'], 'username' => $_['logid'], 'email' => $_['logid']), array('where_or' => true), false) : false;
			$localRoot = !empty($_Oli->getLocalRootInfos()) ? true : false;
			
			if(!$uid AND !$localRoot) $resultCode = 'E:Sorry, no account is associated with the login ID you used.';
			else if(!$isLocalLogin AND $_Oli->getUserRightLevel($uid, false) == $_Oli->translateUserRight('NEW-USER')) $resultCode = 'E:Sorry, the account associated with that login ID is not yet activated.';
			else if(!$isLocalLogin AND $_Oli->getUserRightLevel($uid, false) == $_Oli->translateUserRight('BANNED')) $resultCode = 'E:Sorry, the account associated with that login ID is banned and is not allowed to log in.';
			else if(!$isLocalLogin AND $_Oli->getUserRightLevel($uid, false) < $_Oli->translateUserRight('USER')) $resultCode = 'E:Sorry, the account associated with that login ID is not allowed to log in.';
			
			else if(!$isLocalLogin AND ($uidAttempts = $_Oli->runQueryMySQL('SELECT COUNT(1) as attempts FROM `' . $_Oli->translateAccountsTableCode('LOG_LIMITS') . '` WHERE action = \'login\' AND uid = \'' . $uid . '\' AND last_trigger >= date_sub(now(), INTERVAL 1 HOUR)')[0]['attempts'] ?: 0) >= $config['maxUidAttempts']) $resultCode = 'E:<b>Anti brute-force</b> – Due to too many login attempts (' . $uidAttempts . '), this account has been blocked and therefore you cannot login. Please try again later, or <a href="' . $_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/unlock' . '">unlock your account</a>.';
			
			else if($_Oli->verifyLogin($_['logid'], $_['password'])) {
				$loginDuration = $_['rememberMe'] ? $_Oli->config['extended_session_duration'] : $_Oli->config['default_session_duration'];
				if($_Oli->loginAccount($_['logid'], $_['password'], $loginDuration)) {
					if(!empty($_Oli->config['associated_websites']) AND preg_match('/^(https?:\/\/)?([-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6})\b([-a-zA-Z0-9@:%_\+.~#?&\/=]*)$/', $_Oli->config['associated_websites'][0], $matches)) {
						$url = ($matches[1] ?: 'http://') . $matches[2] . (substr($matches[3], -1) == '/' ? $matches[3] : '/') . 'request.php';
						header('Location: ' . $url . '?' . http_build_query(array('action' => 'setLoginInfos', 'authKey' => $_Oli->getAuthKey(), 'extendedDelay' => $_['rememberMe'], 'next' => array_slice($_Oli->config['associated_websites'], 1), 'callback' => $_['referer'] ?: $_Oli->getFullUrl())));
					} else if(!empty($_['referer']) AND !strstr($_['referer'], '/' . $_Oli->getUrlParam(1))) header('Location: ' . $_['referer']);
					// else header('Location: ' . $_Oli->getUrlParam(0));
					
					$scriptState = 'logged';
					$showAntiBruteForce = true;
					$isLoggedIn = true;
					$resultCode = 'S:You are now succesfully logged in.';
				} else $resultCode = 'E:An error occurred while logging you in.';
			} else {
				if(!$isLocalLogin) $_Oli->insertAccountLine('LOG_LIMITS', array('id' => $_Oli->getLastAccountInfo('LOG_LIMITS', 'id') + 1, 'uid' => $uid, 'ip_address' => $_Oli->getUserIP(), 'action' => 'login', 'last_trigger' => date('Y-m-d H:i:s')));
				$resultCode = 'E:Sorry, the password you used is wrong.';
			}
		}
	}
}

/** Nothing's happening because this is an error */
else $resultCode = 'E:It seems you are not allowed to do anything here.';
?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="description" content="<?=$_Oli->getSetting('name')?> login page" />
<meta name="keywords" content="<?=$_Oli->getSetting('name')?>,Oli,Login,Page" />
<meta name="author" content="Matiboux" />
<title>Login - <?php echo $_Oli->getSetting('name') ?: 'Oli Framework'; ?></title>

<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.1.0/css/all.css" integrity="sha384-lKuwvrZot6UHsBSfcMvOkWwlCMgc0TaWr+30HWe3a4ltaBwTZhyTEggF5tJv8tbt" crossorigin="anonymous" />
<style>
@import url('https://fonts.googleapis.com/css?family=Roboto:400,700');
html { position: relative; min-height: 100% }
body { font-family: 'Roboto', sans-serif; background: #f8f8f8; height: 100%; margin: 0; color: #808080; font-size: 14px; overflow-x: hidden }
@media (max-width: 420px) { body { font-size: 12px } }

a { color: #50a0f0; text-decoration: none }
a:hover, a:focus { color: #4080c0; text-decoration: underline }

/** Header */
#header { margin: 50px 30px; text-align: center; color: #303030; letter-spacing: 1px }
#header h1 { margin: 0 0 20px; font-size: 40px; font-weight: bold }
#header h1 a { background: #4080c0; display: inline-block; padding: 5px 10px; color: #fff; text-decoration: none; border-radius: 10px }
#header p { font-size: 14px }
#header p:not(.description) { color: #808080; font-size: 12px }
@media (max-width: 420px) {
	#header { margin: 30px 10px }
	#header h1 { font-size: 32px }
	#header p { font-size: 12px; }
	#header p:not(.description) { font-size: 10px; } }

/** Message / Module */
.message, #module { position: relative; background: #fff; width: 100%; max-width: 320px; min-height: 20px; margin: 30px auto; border-top: 5px solid #808080; box-shadow: 0 0 10px rgba(0, 0, 0, .2) }

/** Message */
.message { border-top: 5px solid #808080 }
.message.message-info { border-top-color: #4080c0 }
.message.message-success { border-top-color: #40c040 }
.message.message-error { border-top-color: #c04040 }
.message .summary { padding: 5px; font-size: 14px; text-align: center; cursor: pointer }
.message .summary:hover, .message .summary:focus { color: #4080c0 }
.message .summary:before { content: '— ' }
.message .summary:after { content: ' —' }
.message .content { padding: 20px 40px }
.message .summary + .content { display: none }
.message h2 { color: #555; font-size: 16px; font-weight: 400; line-height: 1 }
.message ul { padding-left: 20px }
.message a:hover { text-decoration: underline }
@media (max-width: 420px) {
	.message, #module { margin-top: 20px; margin-bottom: 20px }
	.message .content { padding: 20px 30px } }
@media (max-width: 340px) { .message, #module { width: auto; margin-left: 10px; margin-right: 10px } }

/** Module */
#module { border-top: 5px solid #4080c0 }
#module .toggle { position: absolute; background: #4080c0; top: 0; right: 0; width: 30px; height: 30px; line-height: 32px; margin: -5px 0 0; color: #fff; font-size: 14px; text-align: center; cursor: pointer }
#module .toggle .tooltip { position: absolute; display: block; background: #808080; top: 8px; right: 40px; width: auto; min-height: 10px; padding: 5px; font-size: 10px; line-height: 1; text-transform: uppercase; white-space: nowrap }
#module .toggle .tooltip:before { content: ''; position: absolute; display: block; top: 5px; right: -5px; border-top: 5px solid transparent; border-bottom: 5px solid transparent; border-left: 5px solid #808080 }

#module .form { display: block; padding: 40px }
#module .form ~ .form { display: none }
#module .form h2 { margin: 20px 0; color: #4080c0; font-size: 18px; font-weight: 400; line-height: 1 }
#module .form p, #module .form ul, #module .form hr, #module .form button, #module .form .btn, #module .form input, #module .form .checkbox, #module .form .radio { margin: 20px 0 0 }
#module .form p.help-block { padding: 5px 10px; color: #808080; border-left: 3px solid #c9c9c9 }
#module .form ul { padding-left: 20px }
#module .form hr { margin-left: 10px; margin-right: 10px; border: 0; border-top: 1px solid #c0c0c0 }
#module .form input { display: block; width: 100%; padding: 10px 15px; font-size: 14px; font-weight: 400; border: 1px solid #e0e0e0; box-sizing: border-box; outline: none; -webkit-transition: border .3s ease; -moz-transition: border .3s ease; -o-transition: border .3s ease; transition: border .3s ease }
#module .form input:focus { border: 1px solid #4080c0; color: #303030 }
#module .form .checkbox, #module .form .radio { display: block; padding: 0 10px; -webkit-transition: border .3s ease; -moz-transition: border .3s ease; -o-transition: border .3s ease; transition: border .3s ease }
#module .form .checkbox > label, #module .form .radio > label { cursor: pointer }
#module .form .checkbox input[type=checkbox], #module .form .radio input[type=radio] { display: initial; width: 15px; height: 15px; margin: 0 2px; vertical-align: middle }
#module button, #module .btn { display: block; background: #4080c0; padding: 10px 15px; color: #fff; font-size: 14px; text-align: center; text-decoration: none; cursor: pointer; border: 0; transition: background .3s ease }
#module button { width: 100% }
#module button:hover, #module button:focus, #module .btn:hover, #module .btn:focus { background: #306090 }
#module .cta { background: #f8f8f8; width: 100%; color: #c0c0c0; font-size: 12px; text-align: center }
#module .cta:nth-child(even) { background: #f0f0f0 } 
#module .cta > * { display: block; padding: 15px 40px; color: #808080; font-size: 12px; text-align: center }
#module .cta a { text-decoration: none }
#module .cta a:hover, #module .cta a:focus { color: #404040 }
@media (max-width: 420px) {
	#module .toggle { width: 25px; height: 25px; line-height: 26px; font-size: 12px }
	#module .toggle .tooltip { top: 7px; right: 32px; padding: 3px 4px; font-size: 9px }
	#module .toggle .tooltip:before { top: 4px; right: -4px; border-width: 4px }
	#module .form { padding: 30px }
	#module h2 { margin: 0 0 15px; font-size: 16px }
	#module p { margin: 0 0 15px }
	#module input { margin: 0 0 15px; font-size: 12px }
	#module .checkbox, #module .radio { margin: 0 0 15px }
	#module button, #module .btn { font-size: 12px } }
#module .form *:first-child { margin-top: 0 !important }
#module .form *:last-child { margin-bottom: 0 !important }

#footer { margin: 30px 10px; text-align: center; letter-spacing: 1px }
#footer p { font-size: 12px }
#footer p .fa { color: #4080c0 }
#footer p a { color: #4080c0; font-weight: bold; text-decoration: none }
@media (max-width: 420px) {
	#footer { margin: 20px 10px }
	#footer p { font-size: 10px } }

.text-info { color: #4080c0 }
.text-success { color: #40c040 }
.text-error { color: #c04040 }

.mt-0 { margin-top: 0 !important }
.mt-1 { margin-top: 10px !important }
/*.mt-2 { margin-top: 20px !important }*/
</style>

</head>
<body>

<!-- ScriptState: <?php var_dump($scriptState); ?> -->
<div id="header">
	<h1><a href="<?php echo $_Oli->getUrlParam(0); ?>"><?php echo $_Oli->getSetting('name'); ?></a></h1>
	<p class="description"><?php echo $_Oli->getSetting('description'); ?></p>
	<?php if($isLocalLogin) { ?><p><b>Local login</b> (restricted to the root user)</p><?php } ?>
</div>

<?php if($showAntiBruteForce) { ?>
	<div class="message">
		<div class="summary">Anti brute-force system</div>
		<div class="content">
			Your login attempts in the last hour: <br />
			<?php /*- by user id: <b><?=$userIdAttempts ?: ($_Oli->runQueryMySQL('SELECT COUNT(1) as attempts FROM `' . $_Oli->translateAccountsTableCode('LOG_LIMITS') . '` WHERE action = \'login\' AND user_id = \'' . $_Oli->getAuthID() . '\' AND last_trigger >= date_sub(now(), INTERVAL 1 HOUR)')[0]['attempts'] ?: 0)?></b> (max. <?=$config['maxUserIdAttempts']?>) <br />*/ ?>
			- by IP address: <b><?=$userIPAttempts ?: ($_Oli->runQueryMySQL('SELECT COUNT(1) as attempts FROM `' . $_Oli->translateAccountsTableCode('LOG_LIMITS') . '` WHERE action = \'login\' AND ip_address = \'' . $_Oli->getUserIP() . '\' AND last_trigger >= date_sub(now(), INTERVAL 1 HOUR)')[0]['attempts'] ?: 0)?></b> (max. <?=$config['maxUserIPAttempts']?>) <br />
			<?php if(!empty($_['uid'])) { ?>- by uid (<?=$_['uid']?>): <b><?=$uidAttempts ?: ($_Oli->runQueryMySQL('SELECT COUNT(1) as attempts FROM `' . $_Oli->translateAccountsTableCode('LOG_LIMITS') . '` WHERE action = \'login\' AND uid = \'' . $_['uid'] . '\' AND last_trigger >= date_sub(now(), INTERVAL 1 HOUR)')[0]['attempts'] ?: 0)?></b> (max. <?=$config['maxUidAttempts']?>)<?php } ?>
		</div>
	</div>
<?php } ?>

<?php if(isset($resultCode)) { ?>
	<?php
	list($prefix, $message) = explode(':', $resultCode, 2);
	if($prefix == 'I') $type = 'message-info';
	else if($prefix == 'S') $type = 'message-success';
	else if($prefix == 'E') $type = 'message-error';
	?>
	<div class="message <?php echo $type; ?>">
		<div class="content"><?php echo $message; ?></div>
	</div>
<?php } ?>

<?php if($ignoreFormData) $_ = []; ?>

<div id="module">
	<div class="toggle" style="display: none">
		<i class="fas"></i>
		<div class="tooltip"></div>
	</div>
	
	<?php if(in_array($scriptState, ['logged', 'recover', 'edit-password', 'recover-password', 'set-username', 'account-settings'])) { ?>
		<?php $showLoggedLinksCTA = true; ?>
		<?php if($scriptState == 'recover') { ?>
			<div class="form" data-icon="fa-sync-alt" data-text="Recover" style="display: <?php if($scriptState == 'recover') { ?>block<?php } else { ?>none<?php } ?>">
				<h2>Recover your account</h2>
				<?php if(!$isLocalLogin) { ?>
					<form action="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/recover'?>" method="post">
						<input type="email" name="email" value="<?=$_['email']?>" placeholder="Email address" />
						<button type="submit">Recover</button>
						
						<p>An email will be sent to you with instructions to continue.</p>
					</form>
				<?php } else { ?>
					<p>Sorry, but you <span class="text-error">can't</span> recover a local account.</p>
					<p>If you are the owner of the website, delete the <code>/content/.oliauth</code> file and <span class="text-info">create a new local root account</span>.</p>
				<?php } ?>
			</div>
		<?php } ?>
		
		<?php if(in_array($scriptState, ['recover', 'edit-password', 'recover-password'])) { ?>
			<div class="form" data-icon="fa-edit" data-text="Password Edit" style="display: <?php if(in_array($scriptState, ['edit-password', 'recover-password'])) { ?>block<?php } else { ?>none<?php } ?>">
				<h2>Edit your password</h2>
				
				<form action="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/change-password'?><?php if(!empty($requestInfos)) { ?>?activateKey=<?=urlencode($_Oli->getUrlParam(3) ?: $_['activateKey'])?><?php } ?>" method="post">
					<?php if($isLoggedIn) { ?>
						<p>You are logged in as <b><?=$_Oli->getLoggedName() ?: 'unknown user'?></b>.</p>
						<input type="password" name="password" placeholder="Current password" />
					
					<?php } else if(!$isLocalLogin) { ?>
						<?php if(!empty($requestInfos)) { ?>
							<p>Request for <b><?=$_Oli->getName($requestInfos['uid'])?></b>.</p>
						<?php } ?>
						<input type="text" name="activateKey" value="<?=$_Oli->getUrlParam(3) ?: $_['activateKey']?>" placeholder="Activation Key" <?php if(!empty($requestInfos)) { ?>disabled<?php } ?> />
					
					<?php } else { ?>
						<p>Something went wrong..</p>
					<?php } ?>

					<input type="password" name="newPassword" placeholder="New password" />
					<button type="submit">Update Password</button>
					
					<p>You'll be disconnected from all your devices.</p>
				</form>
			</div>
		
		<?php } else if($scriptState == 'set-username') { ?>
			<div class="form" data-icon="fa-edit" data-text="Password Edit" style=": <?php if($scriptState == 'set-username') { ?>block<?php } else { ?>none<?php } ?>">
				<h2>Set your username</h2>
				<form action="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/set-username'?>" method="post">
					<?php if(empty($username = $_Oli->getLoggedUsername() ?: '')) { ?>
						<p>Make your account publicly visible by setting a username.
						Of course, you can remove or change your username at any time.</p>
					<?php } else { ?>
						<p>Be careful, changing or removing your username will allow others to use it, and will make links using it outdated.</p>
					<?php } ?>
					
					<input type="text" name="username" value="<?=$username?>" placeholder="Username" />
					<?php if(empty($username)) { ?>
						<button type="submit">Set Username</button>
					<?php } else { ?>
						<button type="submit">Update Username</button>
					<?php } ?>
					
					<p>Your username represents your public identity on the platform.</p>
				</form>
			</div>
		
		<?php } else { ?>
			<?php $showLoggedLinksCTA = false; ?>
			<div class="form" data-icon="fa-sign-out-alt" data-text="Logout & Links" style="display:<?php if($scriptState == 'logged') { ?>block<?php } else { ?>none<?php } ?>">
				<h2>You are logged in</h2>
				<p>You can tap on the top-right icon to change your password. You can also click on one of those links to navigate on the website.</p>
				
				<?php if(!empty($_SERVER['HTTP_REFERER']) AND !strstr($_SERVER['HTTP_REFERER'], '/' . $_Oli->getUrlParam(1))) { ?>
					<a href="<?=$_SERVER['HTTP_REFERER']?>" class="btn">&laquo; Go back</a>
					<p class="mt-1">&rsaquo; Go back to <?=$_SERVER['HTTP_REFERER']?></p>
				<?php } ?>
				<a href="<?=$_Oli->getUrlParam(0)?>" class="btn">Website home page</a>
				<?php if($_Oli->getUserRightLevel() >= $_Oli->translateUserRight('ROOT')) { ?>
					<a href="<?=$_Oli->getOliAdminUrl()?>" class="btn mt-1">Oli Admin panel</a>
				<?php } ?> <hr />
				
				<a href="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/logout'?>" class="btn">Log out</a>
				
				<p>By using this website, you agree that we're using a cookie to keep you logged in. Otherwise, please log out.</p>
			</div>
		<?php } ?>
		
		<?php if($scriptState != 'recover') { ?>
			<div class="form" data-icon="fa-cog" data-text="Account Settings" style="display: <?php if($scriptState == 'account-settings') { ?>block<?php } else { ?>none<?php } ?>">
				<h2>Account Settings</h2>
				
				<a href="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/edit-password'?>" class="btn">Edit Password</a>
				<a href="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/set-username'?>" class="btn mt-1">Set Username</a>
				
				<p>Manage your basic account settings.</p>
			</div>
		<?php } ?>
		
		<?php if(!$isLoggedIn) { ?>
			<div class="cta"><a href="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1)?>/">Log into your account</a></div>
		<?php } else if($showLoggedLinksCTA) { ?>
			<div class="cta"><a href="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1)?>/">Logout & Links</a></div>
		<?php } ?>
	
	<?php } else if(in_array($scriptState, ['unlock', 'unlock-submit'])) { ?>
		<div class="form" data-icon="fa-key" data-text="Generate Unlock Key" style="display: <?php if($scriptState == 'unlock') { ?>block<?php } else { ?>none<?php } ?>">
			<h2>Generate an unlock key</h2>
			<p>In order to unlock your account, an unlock key will be generated and sent to you by email.</p>
			<form action="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/unlock'?>" method="post">
				<input type="text" name="username" value="<?=$_['username'] ?: $_Oli->getUrlParam(3)?>" placeholder="Username" />
				<button type="submit">Generate</button>
				
				<p>An email will be sent to you with instructions to continue.</p>
			</form>
		</div>
		
		<div class="form" data-icon="fa-unlock" data-text="Submit Unlock Key" style="display:<?php if($scriptState == 'unlock-submit') { ?>block<?php } else { ?>none<?php } ?>">
			<h2>Unlock your account</h2>
			<form action="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/unlock'?>" method="post">
				<input type="text" name="unlockKey" value="<?=$_['unlockKey'] ?: $_Oli->getUrlParam(3)?>" placeholder="Unlock Key" />
				<button type="submit">Unlock your account</button>
				
				<p>This will reset the Anti-BruteForce stats.</p>
			</form>
		</div>
		
		<div class="cta"><a href="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1)?>/">Log into your account</a></div>
	
	<?php } else if(in_array($scriptState, ['login', 'register', 'activate', 'root-register'])) { ?>
		<?php if($isLoginAllowed) { ?>
			<div class="form" data-icon="fa-sign-in-alt" data-text="Login" style="display:<?php if($scriptState == 'login') { ?>block<?php } else { ?>none<?php } ?>">
				<h2>Log into your account</h2>
				<form action="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/'?>" method="post">
					<?php if(!empty($_['referer']) OR !empty($_SERVER['HTTP_REFERER'])) { ?>
						<input type="hidden" name="referer" value="<?=$_['referer'] ?: $_SERVER['HTTP_REFERER']?>" />
					<?php } ?>
					
					<p>Log in using <b>your email</b>, your user ID, or your username (if set).</p>
					<?php if(!$isLocalLogin) { ?><input type="text" name="logid" value="<?=$_['logid']?>" placeholder="Login ID" /><?php } ?>
					<input type="password" name="password" value="<?=$_['password']?>" placeholder="Password" />
					<div class="checkbox"><label><input type="checkbox" name="rememberMe" <?php if(!isset($_['rememberMe']) OR $_['rememberMe']) { ?>checked<?php } ?> /> « Run clever boy, and remember me »</label></div>
					<button type="submit">Login</button>
					
					<p>A cookie will be created to keep you logged in to your account.</p>
				</form>
			</div>
		<?php } ?>
		
		<?php if($isRegisterAllowed) { ?>
			<div class="form" data-icon="fa-pencil-alt" data-text="Register" style="display: <?php if($scriptState == 'register') { ?>block<?php } else { ?>none<?php } ?>;">
				<h2>Create a new account</h2>
				<form action="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/register'?>" method="post">
					<input type="email" name="email" value="<?=$_['email']?>" placeholder="Email address" />
					<input type="password" name="password" value="<?=$_['password']?>" placeholder="Password" />
					
<?php /*<?php function captcha($captcha) {
	$width = strlen($captcha) * 10 + 200;
	$height = 20;
	$midHeight = $height / 2;
	
	$image = imagecreate($width, $height);
	$white = imagecolorallocate($image, 255, 255, 255);
	$gray = imagecolorallocate($image, 128, 128, 128);
	$black = imagecolorallocate($image, 0, 0, 0);
	
	imagestring($image, 5, strlen($captcha) /2 + 100 , $midHeight - 8, $captcha, $gray); //$black);
	imagerectangle($image, 0, 0, $width - 1, $height - 1, $gray); // La bordure
	
	imageline($image, 102, $midHeight, $width - 102, $midHeight, $black);
	imageline($image, 102, mt_rand(2, $height), $width - 102, mt_rand(2, $height), $black);
	
	imagepng($image);
	imagedestroy($image);
} ?>
<?php
ob_start();
captcha($keygen = $_Oli->keygen(4, true, false, true));
$captchaImage = ob_get_contents();
ob_end_clean(); ?>
<img src="data:image/png;base64,<?=base64_encode($captchaImage)?>" alt="Captcha" />
					<input type="text" name="captcha" placeholder="Captcha (wip)" disabled />*/ ?>
					
					<button type="submit">Register</button>
				</form>
			</div>
		<?php } ?>
		
		<?php if($isActivateAllowed) { ?>
			<div class="form" data-icon="fa-unlock" data-text="Activate" style="display: <?php if($scriptState == 'activate') { ?>block<?php } else { ?>none<?php } ?>;">
				<h2>Activate your account</h2>
				<form action="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/activate'?>" method="post">
					<input type="text" name="activateKey" value="<?=$_['activateKey']?>" placeholder="Activate Key" />
					<button type="submit">Activate</button>
				</form>
			</div>
		<?php } ?>
		
		<?php if($isRootRegisterAllowed) { ?>
			<div class="form" data-icon="fa-star" data-text="Root Register" style="display: <?php if($scriptState == 'root-register') { ?>block<?php } else { ?>none<?php } ?>;">
				<h2>Create a root account</h2>
				<p>Be <span class="text-error">careful</span>. Only the owner of the website should use this form. <br />
				<span class="text-info">Verify your identity</span> by typing the <?php if($_Oli->refreshOliSecurityCode()) { ?>new<?php } ?> security code generated in the <code>/.olisc</code> file.</p>
				<form action="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/root'?>" method="post">
					<?php /*<input type="text" name="username" value="<?=$_['username']?>" placeholder="Username" /> */ ?>
					<?php if(!$isLocalLogin) { ?><input type="email" name="email" value="<?=$_['email']?>" placeholder="Email address" /><?php } ?>
					<input type="password" name="password" value="<?=$_['password']?>" placeholder="Password" />
					<input type="text" name="olisc" value="<?=$_['olisc']?>" placeholder="Oli Security Code" />
					<button type="submit">Register as Root</button>
				</form>
			</div>
		<?php } ?>
		
		<?php if(!$isLocalLogin) { ?>
			<div class="cta"><a href="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1)?>/recover">Forgot your password?</a></div>
		<?php } ?>
		<?php if($userIdAttempts >= $config['maxUserIdAttempts'] OR $userIPAttempts >= $config['maxUserIPAttempts'] OR $uidAttempts >= $config['maxUidAttempts']) { ?>
			<div class="cta"><a href="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1)?>/unlock<?php if(!empty($_['username'])) { ?>/<?=$_['username']?><?php } ?>">Unlock your account <?php if(!empty($_['username'])) { ?>(<?=$_['username']?>)<?php } ?></a></div>
		<?php } ?>
	
	<?php } else { ?>
		<div class="form" data-icon="fa-times" data-text="Error">
			<h2>An error occurred</h2>
			<p>Either you're not allowed to do anything on this page or an error occurred. Please report it to the admin.</p>
			<p>For debug purposes, here's the script state value: <b><?=!empty($scriptState) ? $scriptState : var_dump($scriptState)?></b>.</p>
		</div>
		<?php /*<div class="form" data-icon="fa-phone" data-text="Contact" >
			<h2>Contact an admin</h2>
			<p>Later maybe.</p>
		</div>*/ ?>
	<?php } ?>
</div>

<div id="footer">
	<p><?=$_Oli?></p>
</div>

<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
<script>
var updateForm = function(setup) {
	var length = $('.form').length;
	if(length > 1) {
		$('.toggle').show();
		var index = $('.form').index($('.form:visible'));
		var nextIndex = (index+1) % length;
		if(!setup) var futureIndex = (index+2) % length;
		
		if(setup) $('.toggle').children('.fas').addClass($('.form').eq(nextIndex).attr('data-icon')); 
		else $('.toggle').children('.fas').removeClass($('.form').eq(nextIndex).attr('data-icon')).addClass($('.form').eq(futureIndex).attr('data-icon'));
		
		$('.toggle').children('.tooltip').text($('.form').eq(setup ? nextIndex : futureIndex).attr('data-text'));
		return nextIndex;
	}
};

$(document).ready(function() { updateForm(true); });
$(document).on('click', '.toggle', function() {
	var nextIndex = updateForm();
	$('.form:visible').animate({ height: 'toggle', 'padding-top': 'toggle', 'padding-bottom': 'toggle', opacity: 'toggle' }, 'slow');
	$($('.form')[nextIndex]).animate({ height: 'toggle', 'padding-top': 'toggle', 'padding-bottom': 'toggle', opacity: 'toggle' }, 'slow');
});

$(document).on('click', '.summary', function() {
	$(this).parent().find('.content').animate({ height: 'toggle', 'padding-top': 'toggle', 'padding-bottom': 'toggle', opacity: 'toggle' }, 'slow');
});
</script>

</body>
</html>