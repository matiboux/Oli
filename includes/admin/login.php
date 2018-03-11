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

$_ = array_merge($_GET, $_POST);

$config = array(
	'maxUserIdAttempts' => 3,
	'maxUserIPAttempts' => 5,
	'maxUsernameAttempts' => 8
);

/** Script State Variable */
$scriptState = null; // Default value
// $scriptState values:
//x - 'login' LOG INTO YOUR ACCOUNT
//x - 'logged' LOGGED IN
//x - 'register' CREATE AN ACCOUNT
//x ... 'login' << 'registered' ACCOUNT CREATED (?)
//x - 'root-register' CREATE A ROOT ACCOUNT
//x ... 'login' << 'root-registered' ROOT ACCOUNT CREATED (?)
// - 'activate' ACTIVATE YOUR ACCOUNT
//x - 'recover' RECOVER YOUR ACCOUNT
//x - 'edit-password' PASSWORD EDIT (DIRECT)
//x - 'recover-password' PASSWORD EDIT (RECOVER)
//x ... 'login' << 'edited-password' PASSWORD CHANGED
//x - 'unlock' UNLOCK YOUR ACCOUNT
// - 'unlock-submit' UNLOCK YOUR ACCOUNT
// ... 'login' << 'unlock-submited' UNLOCK YOUR ACCOUNT

/** --- */

/** Login management is disabled by the current config */
// if(!$_Oli->config['user_management'] OR !$_Oli->config['allow_login']) header('Location: ' . $_Oli->getUrlParam(0));

$isLoginLocal = $_Oli->isLoginLocal();
$isLoggedIn = $_Oli->verifyAuthKey();

/** Is Script State Allowed */
/** EDIT PASSWORD: */
	$isEditPasswordAllowed = $isLoggedIn OR !$isLoginLocal;
/** LOGGED IN (independent) */
	// $isLoggedAllowed = $isLoggedIn;
/** ACTIVATE (will be independent) */
	// $isActivateAllowed = $_Oli->config['account_activation'] AND !$isLoginLocal;
/** RECOVER: */
	$isRecoverAllowed = !$isLoginLocal;
/** UNLOCK (independent) */
	// $isUnlockAllowed = !$isLoginLocal;
/** REGISTER: */
	$isRegisterAllowed = $_Oli->config['allow_register'] AND !$isLoginLocal;
/** REGISTER AS ROOT: */
	if($isLoginLocal) {
		if(empty($_Oli->getLocalRootInfos())) $isRootRegisterAllowed = true;
		else $isRootRegisterAllowed = false;
	} else {
		if(!$_Oli->isExistAccountInfos('ACCOUNTS', array('user_right' => $_Oli->translateUserRight('ROOT')), false)) $isRootRegisterAllowed = true;
		else $isRootRegisterAllowed = false;
	}
/** LOGIN: */
	$isLoginAllowed = $isLoginLocal OR $_Oli->config['allow_login'];

/** --- */

$mailHeaders = 'From: Noreply ' . $_Oli->getSetting('name') . ' <noreply@' . $_Oli->getUrlParam('domain') . '>' . "\r\n";
$mailHeaders .= 'MIME-Version: 1.0' . "\r\n";
$mailHeaders .= 'Content-type: text/html; charset=utf-8';

/** --- */

/** Background cleanup process */
if(!empty($_) AND !$isLoginLocal) {
	$_Oli->runQueryMySQL('DELETE FROM `' . $_Oli->translateAccountsTableCode('REQUESTS') . '` WHERE expire_date < now()');
	$_Oli->runQueryMySQL('DELETE FROM `' . $_Oli->translateAccountsTableCode('SESSIONS') . '` WHERE expire_date < now() OR (expire_date IS NULL AND update_date < date_sub(now(), INTERVAL 7 DAY))');
}

/** --- */

/** Account Password Edit */
// WIP / Add support for direct password edit if logged in, but not local login
if(in_array($_Oli->getUrlParam(2), ['edit-password', 'change-password']) AND $isEditPasswordAllowed) {
	/** Direct Password Edit */
	if($isLoggedIn) {
		$scriptState = 'edit-password';
		if(!empty($_)) {
			if(empty($_['oldPassword'])) $resultCode = 'E:Please enter your current password.';
			else if(empty($_['newPassword'])) $resultCode = 'E:Please enter the new password you want to set.';
			else {
				$rootUserInfos = $_Oli->getLocalRootInfos();
				if(!password_verify($_['oldPassword'], $rootUserInfos['password'])) $resultCode = 'E:The current password is incorrect.';
				else if(empty($hashedPassword = $_Oli->hashPassword($_['password']))) $resultCode = 'E:The new password couldn\'t be hashed.';
				else if($isLoginLocal) {
					$handle = fopen(CONTENTPATH . '.oliauth', 'w');
					if(fwrite($handle, json_encode($rootUserInfos, array('password' => $hashedPassword), JSON_FORCE_OBJECT))) {
						$_Oli->logoutAccount();
						$scriptState = 'login';
						$resultCode = 'S:Your password has been successfully updated.';
					} else $resultCode = 'E:An error occurred when updating your password.';
					fclose($handle);
				} else {
					$resultCode = 'E:Direct Password Edit unavailable yet for non local login.';
				}
			}
		}
	
	/** Complete Account Recovery */
	} else if(!$isLoginLocal) {
		$scriptState = 'recover-password';
		if(!empty($_)) {
			if(empty($_['activateKey'])) $resultCode = 'E:The Activate Key is missing.';
			else if(!$requestInfos = $_Oli->getAccountLines('REQUESTS', array('activate_key' => hash('sha512', $_['activateKey'])))) $resultCode = 'E:Sorry, the request you asked for does not exist.';
			else if($requestInfos['action'] != 'change-password') $resultCode = 'E:The request you triggered does not allow you to change your password.';
			else if(time() > strtotime($requestInfos['expire_date'])) $resultCode = 'E:Sorry, the request you triggered has expired.';
			else {
				/** Logout the user if they're logged in */
				if($isLoggedIn) $_Oli->logoutAccount();
				
				/** Deletes all the user sessions, change the user password and delete the request */
				if($_Oli->deleteAccountLines('SESSIONS', $requestInfos['username']) AND $_Oli->updateAccountInfos('ACCOUNTS', array('password' => $_Oli->hashPassword($_['newPassword'])), $requestInfos['username']) AND $_Oli->deleteAccountLines('REQUESTS', array('activate_key' => hash('sha512', $_['activateKey'])))) {
					$hideChangePasswordUI = true;
					$resultCode = 'S:Your password has been successfully changed!';
				} else $resultCode = 'E:An error occurred while changing your password.';
			}
		}
	
	/** An error occurred..? */
	} else $resultCode = 'E:An error occurred..?';
}

/** Logged user */
else if($isLoggedIn) {
	$scriptState = 'logged';
	
	/** Disconnect the user */
	if($_Oli->getUrlParam(2) == 'logout') {
		if($_Oli->logoutAccount()) {
			if(!empty($_Oli->config['associated_websites']) AND preg_match('/^(https?:\/\/)?([-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6})\b([-a-zA-Z0-9@:%_\+.~#?&\/=]*)$/', $_Oli->config['associated_websites'][0], $matches)) {
				$url = ($matches[1] ?: 'http://') . $matches[2] . (substr($matches[3], -1) == '/' ? $matches[3] : '/') . 'request.php';
				header('Location: ' . $url . '?' . http_build_query(array('action' => 'removeLoginInfos', 'next' => array_slice($_Oli->config['associated_websites'], 1), 'callback' => $_Oli->getFullUrl())));
			} else {
				$scriptState = 'login';
				$resultCode = 'S:You have been successfully disconnected.';
			}
		} else $resultCode = 'E:An error occurred while disconnecting you.';
	
	// } else if(in_array($_Oli->getUrlParam(2), ['edit-password', 'change-password'])) {
	// This shouldn't be able to get this far. 
	
	} else {
		/** Redirect the user */
		if(!empty($_SERVER['HTTP_REFERER']) AND !strstr($_SERVER['HTTP_REFERER'], '/' . $_Oli->getUrlParam(1))) header('Location: ' . $_SERVER['HTTP_REFERER']);
		
		/** Notice the user */
		else $resultCode = 'I:You\'re already logged in, ' . $_Oli->getAuthKeyOwner() . '.';
	}

/** Activate an account */
// WIP / Add a display, update the script
// } else if($_Oli->getUrlParam(2) == 'activate' AND $_Oli->config['account_activation'] AND !$isLoginLocal) {
} else if($_Oli->getUrlParam(2) == 'activate' AND !empty($_Oli->getUrlParam(3)) AND $_Oli->config['account_activation'] AND !$isLoginLocal) {
	if(!$requestInfos = $_Oli->getAccountLines('REQUESTS', array('activate_key' => hash('sha512', $_Oli->getUrlParam(3))))) $resultCode = 'E:Sorry, the request you asked for does not exist.';
	else if($requestInfos['action'] != 'activate') $resultCode = 'E:The request you triggered does not allow you to activate any account.';
	else if(time() > strtotime($requestInfos['expire_date'])) $resultCode = 'E:Sorry, the request you triggered has expired.';
	else if($_Oli->deleteAccountLines('REQUESTS', array('activate_key' => hash('sha512', $_Oli->getUrlParam(3)))) AND $_Oli->updateUserRight('USER', $requestInfos['username'])) {
		$scriptState = 'login';
		$resultCode = 'S:Your account has been successfully activated!';
	} else $resultCode = 'E:An error occurred while activating your account.';

/** Recover an account */
} else if($_Oli->getUrlParam(2) == 'recover' AND $isRecoverAllowed) {
	$scriptState = 'recover';
	if(!empty($_)) {
		if(empty($_['email'])) $resultCode = 'E:Please enter your email.';
		else if(!$username = $_Oli->getAccountInfos('ACCOUNTS', 'username', array('email' => trim($_['email'])), false)) $resultCode = 'E:Sorry, no account is associated with the email you entered.';
		else if($requestInfos = $_Oli->getAccountLines('REQUESTS', array('username' => $username, 'action' => 'change-password')) AND time() <= strtotime($requestInfos['expire_date'])) $resultCode = 'E:Sorry, a change-password request already exists for that account, please check your mail inbox.';
		else if($activateKey = $_Oli->createRequest($username, 'change-password')) {
			$email = $_['email'];
			$subject = 'One more step to change your password';
			/** This message will need to be reviewed in a future release */
			$message = nl2br('Hi ' . $username . '!
A change-password request has been created for your account.
To set your new password, you just need to click on <a href="' . $_Oli->getShortcutLink('login') . 'change-password/' . $activateKey . '">this link</a> and follow the instructions.
This request will stay valid for ' . $expireDelay = $_Oli->getRequestsExpireDelay() /3600 /24 . ' ' . ($expireDelay > 1 ? 'days' : 'day') . '. Once it has expired, the link will be desactivated.

If you can\'t open the link, just copy it in your browser: ' . $_Oli->getUrlParam(0)  . $_Oli->getUrlParam(1) . '/change-password/' . $activateKey . '.

If you didn\'t want to change your password or didn\'t ask for this request, please just ignore this mail.
Also, if possible, please take time to cancel the request from your account settings.');
			
			if(mail($email, $subject, $message, $mailHeaders)) {
				$hideRecoverUI = true;
				$resultCode = 'S:The request has been successfully created and a mail has been sent to you.';
			} else {
				$_Oli->deleteAccountLines('REQUESTS', array('activate_key' => $activateKey));
				$resultCode = 'D:An error occurred while sending the mail to you.';
			}
		} else $resultCode = 'E:An error occurred while creating the change-password request.';
	
	}

/** Unlock an account */
} else if($_Oli->getUrlParam(2) == 'unlock' AND !$isLoginLocal) {
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
				else if(($usernameAttempts = $_Oli->runQueryMySQL('SELECT COUNT(1) as attempts FROM `' . $_Oli->translateAccountsTableCode('LOG_LIMITS') . '` WHERE action = \'login\' AND username = \'' . $username . '\' AND last_trigger >= date_sub(now(), INTERVAL 1 HOUR)')[0]['attempts'] ?: 0) < 1) $resultCode = 'E:Sorry, no failed login attempts has been recorded for this account.';
				else if($requestInfos = $_Oli->getAccountLines('REQUESTS', array('username' => $username, 'action' => 'unlock')) AND time() <= strtotime($requestInfos['expire_date'])) $resultCode = 'E:Sorry, an unlock request already exists for that account, please check your mail inbox.';
				else if($activateKey = $_Oli->createRequest($username, 'unlock')) {
					$email = $email ?: $_Oli->getAccountInfos('ACCOUNTS', 'email', $username, false);
					$subject = 'One more step to unlock your account';
					/** This message will need to be reviewed in a future release */
					$message = nl2br('Hello, ' . $username . '!
Last step to unlock your account, click on <a href="' . $_Oli->getUrlParam(0)  . $_Oli->getUrlParam(1) . 'unlock/' . $activateKey . '">this link</a>.
This request will stay valid for ' . $expireDelay = $_Oli->getRequestsExpireDelay() /3600 /24 . ' ' . ($expireDelay > 1 ? 'days' : 'day') . '. Once it has expired, the link will be desactivated.

Link: ' . $_Oli->getUrlParam(0)  . $_Oli->getUrlParam(1) . '/unlock/' . $activateKey . '.');
					
					if(mail($email, $subject, $message, $mailHeaders)) {
						$hideUnlockUI = true;
						$scriptState = 'unlock-submit';
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
		else if($requestInfos = $_Oli->getAccountLines('REQUESTS', array('activate_key' => hash('sha512', $_Oli->getUrlParam(3))))) {
			if($requestInfos['action'] != 'unlock') $resultCode = 'E:The request you triggered does not allow you to unlock your account.';
			else if(time() > strtotime($requestInfos['expire_date'])) $resultCode = 'E:Sorry, the request you triggered has expired.';
			else {
				/** Deletes all the account log limits and delete the request */
				if($_Oli->deleteAccountLines('LOG_LIMITS', $requestInfos['username']) AND $_Oli->deleteAccountLines('REQUESTS', array('activate_key' => hash('sha512', $_['activateKey'])))) {
					$hideUnlockUI = true;
					$scriptState = 'login';
					$resultCode = 'S:Your account has been successfully unlocked!';
				} else $resultCode = 'E:An error occurred while changing your password.';
			}
		}
	}
	
/** Create a new account */
// WIP / Fix the allowed vars?
} else if($isRegisterState = ($_Oli->getUrlParam(2) == 'register' AND $isRegisterAllowed) OR $isRootRegisterState = (in_array($_Oli->getUrlParam(2), ['root-register', 'root']) AND $isRootRegisterAllowed)) {
	if($isRegisterState) $scriptState = 'register';
	else if($isRootRegisterState) $scriptState = 'root-register';
	
	if(!empty($_)) {
		if(empty($_['username'] = trim($_['username']))) $resultCode = 'E:Please enter an username.';
		else if(!preg_match('/^[_0-9a-zA-Z]+$/', $_['username'])) $resultCode = 'E:The username is incorrect. Please only use letters, numbers and underscores.';
		else if($_Oli->isProhibitedUsername($_['username'])) $resultCode = 'E:Sorry, you\'re not allowed to use that username.';
		else if(empty($_['password'])) $resultCode = 'E:Please enter a password.';
		
		/** Local Root Register */
		// else if($isRegisterState AND isset($_['olisc'])) {
		else if($isRegisterState AND isset($_['olisc'])) {
			if(empty($_['olisc'])) $resultCode = 'E:Please enter the Oli Security Code.';
			else if($_['olisc'] != $_Oli->getOliSecurityCode()) $resultCode = 'E:The Oli Security Code is incorrect.';
			else if($isLoginLocal) {
				if(empty($hashedPassword = $_Oli->hashPassword($_['password']))) $resultCode = 'E:Your password couldn\'t be hashed.';
				else {
					$handle = fopen(CONTENTPATH . '.oliauth', 'w');
					fwrite($handle, json_encode(array('username' => $_['username'], 'password' => $hashedPassword), JSON_FORCE_OBJECT));
					fclose($handle);
					
					$scriptState = 'login';
					$isRootRegisterAllowed = false;
					$resultCode = 'S:Your account has been successfully created as a root and local account.';
				}
			} else $resultCode = 'W:Root register with database is not yet supported.';
		
		// else if($isRootRegisterAllowed AND isset($_['olisc']) AND empty($_['olisc'])) $resultCode = 'E:Please enter the Oli Security Code.';
		// else if($isRootRegisterAllowed AND isset($_['olisc']) AND $_['olisc'] != $_Oli->getOliSecurityCode()) $resultCode = 'E:The Oli Security Code is incorrect.';
		
		/** Classic Register */
		// } else if($isRootRegisterState AND isset($_['email'])) {
		} else if($isRootRegisterState) {
			if(empty($_['email'] = strtolower(trim($_['email'])))) $resultCode = 'E:Please enter your email.';
			else if(!preg_match('/^[-_a-zA-Z0-9]+(?:\.?[-_a-zA-Z0-9]+)*@[^\s]+(?:\.[a-z]+)$/', $_['email'])) $resultCode = 'E:The email is incorrect. Make sure you only use letters, numbers, hyphens, underscores or periods.';
			else if($_Oli->isExistAccountInfos('ACCOUNTS', $_['username'], false)) $resultCode = 'E:Sorry, the username you choose is already associated with an existing account.';
			else if($_Oli->isExistAccountInfos('ACCOUNTS', array('email' => $_['email']), false)) $resultCode = 'E:Sorry, the email you entered is already associated with an existing account.';
			else if($_Oli->registerAccount($_['username'], $_['password'], $_['email'], array('headers' => $mailHeaders))) {
				if($_Oli->config['account_activation']) $resultCode = 'S:Your account has been successfully created and a mail has been sent to <b>' . $_['email'] . '</b>.';
				else $resultCode = 'S:Your account has been successfully created; you can now log into it.';
				$scriptState = 'login';
			} else $resultCode = 'E:An error occurred while creating your account.';
		
		/** An error occurred..? */
		} else $resultCode = 'E:An error occurred..?';
	}

/** Login */
} else if($isLoginAllowed) {
	$scriptState = 'login';
	
	/** Want to log out, but not logged in */
	if($_Oli->getUrlParam(2) == 'logout') $resultCode = 'I:You are already disconnected.';
	
	/** Login */
	else if(!empty($_)) {
		if($_Oli->isSetupMySQL()) $_Oli->deleteAccountLines('LOG_LIMITS', 'action = \'login\' AND last_trigger < date_sub(now(), INTERVAL 1 HOUR)');
		
		if(empty($_['username'] = trim($_['username']))) $resultCode = 'E:Please enter your username or your email.';
		else if($_Oli->isSetupMySQL() AND ($userIdAttempts = $_Oli->runQueryMySQL('SELECT COUNT(1) as attempts FROM `' . $_Oli->translateAccountsTableCode('LOG_LIMITS') . '` WHERE action = \'login\' AND user_id = \'' . $_Oli->getUserID() . '\' AND last_trigger >= date_sub(now(), INTERVAL 1 HOUR)')[0]['attempts'] ?: 0) >= $config['maxUserIdAttempts']) $resultCode = 'E:<b>Anti brute-force</b> – Due to too many login attempts (' . $userIdAttempts . '), your user ID has been blocked and therefore you cannot login. Please try again later, or <a href="' . $_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . (!empty($_['username']) ? '/unlock/' . $_['username'] : '/unlock') . '">unlock your account</a>.';
		else if($_Oli->isSetupMySQL() AND ($userIPAttempts = $_Oli->runQueryMySQL('SELECT COUNT(1) as attempts FROM `' . $_Oli->translateAccountsTableCode('LOG_LIMITS') . '` WHERE action = \'login\' AND ip_address = \'' . $_Oli->getUserIP() . '\' AND last_trigger >= date_sub(now(), INTERVAL 1 HOUR)')[0]['attempts'] ?: 0) >= $config['maxUserIPAttempts']) $resultCode = 'E:<b>Anti brute-force</b> – Due to too many login attempts (' . $userIPAttempts . '), your IP address has been blocked and therefore you cannot login. Please try again later, or <a href="' . $_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . (!empty($_['username']) ? '/unlock/' . $_['username'] : '/unlock') . '">unlock your account</a>.';
		else if($_Oli->isSetupMySQL() AND ($usernameAttempts = $_Oli->runQueryMySQL('SELECT COUNT(1) as attempts FROM `' . $_Oli->translateAccountsTableCode('LOG_LIMITS') . '` WHERE action = \'login\' AND username = \'' . $_['username'] . '\' AND last_trigger >= date_sub(now(), INTERVAL 1 HOUR)')[0]['attempts'] ?: 0) >= $config['maxUsernameAttempts']) $resultCode = 'E:<b>Anti brute-force</b> – Due to too many login attempts (' . $usernameAttempts . '), this username has been blocked and therefore you cannot login. Please try again later, or <a href="' . $_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . (!empty($_['username']) ? '/unlock/' . $_['username'] : '/unlock') . '">unlock your account</a>.';
		else if(empty($_['password'])) $resultCode = 'E:Please enter your password.';
		else {
			if(!$isLoginLocal) {
				$isExistByUsername = $_Oli->isExistAccountInfos('ACCOUNTS', $_['username'], false);
				$isExistByEmail = $_Oli->isExistAccountInfos('ACCOUNTS', array('email' => $_['username']), false);
			} else {
				if(file_exists(CONTENTPATH . '.oliauth')) $rootUserInfos = json_decode(file_get_contents(CONTENTPATH . '.oliauth'), true);
				$isExistByUsername = $_['username'] == strtolower($rootUserInfos['username']) OR $_['username'] == $rootUserInfos['email'];
			}
			
			if(!$isExistByUsername AND ($isLoginLocal OR !$isExistByEmail)) $resultCode = 'E:Sorry, no account is associated with the username or email you entered.';
			else if(!$isLoginLocal AND (($isExistByUsername AND $_Oli->getUserRightLevel($_['username'], false) == $_Oli->translateUserRight('NEW-USER')) OR ($isExistByEmail AND $_Oli->getUserRightLevel(array('email' => $_['username']), false) == $_Oli->translateUserRight('NEW-USER')))) $resultCode = 'E:Sorry, the account associated with that username or email is not yet activated.';
			else if(!$isLoginLocal AND (($isExistByUsername AND $_Oli->getUserRightLevel($_['username'], false) == $_Oli->translateUserRight('BANNED')) OR ($isExistByEmail AND $_Oli->getUserRightLevel(array('email' => $_['username']), false) == $_Oli->translateUserRight('BANNED')))) $resultCode = 'E:Sorry, the account associated with that username or email is banned and is not allowed to log in.';
			else if(!$isLoginLocal AND (($isExistByUsername AND $_Oli->getUserRightLevel($_['username'], false) < $_Oli->translateUserRight('USER')) OR ($isExistByEmail AND $_Oli->getUserRightLevel(array('email' => $_['username']), false) < $_Oli->translateUserRight('USER')))) $resultCode = 'E:Sorry, the account associated with that username or email is not allowed to log in.';
			
			else if($_Oli->verifyLogin($_['username'], $_['password'])) {
				$loginDuration = $_['rememberMe'] ? $_Oli->config['extended_session_duration'] : $_Oli->config['default_session_duration'];
				if($authKey = $_Oli->loginAccount($_['username'], $_['password'], $loginDuration)) {
					if(!empty($_Oli->config['associated_websites']) AND preg_match('/^(https?:\/\/)?([-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6})\b([-a-zA-Z0-9@:%_\+.~#?&\/=]*)$/', $_Oli->config['associated_websites'][0], $matches)) {
						$url = ($matches[1] ?: 'http://') . $matches[2] . (substr($matches[3], -1) == '/' ? $matches[3] : '/') . 'request.php';
						header('Location: ' . $url . '?' . http_build_query(array('action' => 'setLoginInfos', 'userID' => $_Oli->getUserID(), 'authKey' => $authKey, 'extendedDelay' => $_['rememberMe'], 'next' => array_slice($_Oli->config['associated_websites'], 1), 'callback' => $_['referer'] ?: $_Oli->getFullUrl())));
					} else if(!empty($_['referer']) AND !strstr($_['referer'], '/' . $_Oli->getUrlParam(1))) header('Location: ' . $_['referer']);
					// else header('Location: ' . $_Oli->getUrlParam(0));
					$resultCode = 'S:You are now succesfully logged in.';
					$scriptState = 'logged';
				} else $resultCode = 'E:An error occurred while logging you in.';
			} else {
				if($_Oli->isSetupMySQL()) $_Oli->insertAccountLine('LOG_LIMITS', array('id' => $_Oli->getLastAccountInfo('LOG_LIMITS', 'id') + 1, 'username' => $_['username'], 'user_id' => $_Oli->getUserID(), 'ip_address' => $_Oli->getUserIP(), 'action' => 'login', 'last_trigger' => date('Y-m-d H:i:s')));
				$resultCode = 'E:Sorry, the password you used is wrong.';
			}
		}
	}
}

/** Nothing's happening */
else $resultCode = 'E:It seems you are not allowed to do anything here.';
?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="description" content="<?=$_Oli->getSetting('name')?> login page" />
<meta name="keywords" content="oli,login,page,PHP,framework,official,Mathieu,Guérin,Mati,Matiboux" />
<meta name="author" content="Matiboux" />
<title>Login - <?php echo $_Oli->getSetting('name'); ?></title>

<script id="fontawesome" defer src="https://use.fontawesome.com/releases/v5.0.8/js/all.js"></script>
<style>
@import url("https://fonts.googleapis.com/css?family=Roboto:300,400,700");
html { position: relative; min-height: 100% }
body { font-family: 'Roboto', sans-serif; background: #f8f8f8; height: 100%; margin: 0; color: #808080; font-size: 14px; overflow-x: hidden }
@media (max-width: 420px) { body { font-size: 12px } }

#header { margin: 50px 30px; text-align: center; color: #303030; letter-spacing: 1px }
#header h1 { margin: 0 0 20px; font-size: 40px; font-weight: 400 }
#header p { font-size: 12px }
#header p.description { font-size: 14px }
#header a, .message a { color: #4080c0; text-decoration: none }
#header h1 a { background: #4080c0; display: inline-block; padding: 5px 10px; color: #fff; font-weight: bold; border-radius: 10px }
@media (max-width: 420px) {
	#header { margin: 30px 10px }
	#header h1 { font-size: 32px }
	#header p { font-size: 10px; }
	#header p.description { font-size: 12px; } }

.message, #module { position: relative; background: #fff; max-width: 320px; width: 100%; min-height: 20px; margin: 30px auto; border-top: 5px solid #808080; box-shadow: 0 0 10px rgba(0, 0, 0, .2) }
.message.message-info, #module { border-top-color: #4080c0 }
.message.message-success { border-top-color: #40c040 }
.message.message-error { border-top-color: #c04040 }
.message .summary { padding: 5px; text-align: center; font-size: 14px; cursor: pointer }
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

#module .toggle { position: absolute; top: 0; right: 0; background: #4080c0; width: 30px; height: 30px; margin: -5px 0 0; color: #fff; font-size: 14px; text-align: center; cursor: pointer }
#module .toggle [data-fa-i2svg] { padding: 8px 0 }
#module .toggle .tooltip { position: absolute; display: block; background: #808080; top: 8px; right: 40px; width: auto; min-height: 10px; padding: 5px; font-size: 10px; line-height: 1; text-transform: uppercase; white-space: nowrap }
#module .toggle .tooltip:before { content: ''; position: absolute; display: block; top: 5px; right: -5px; border-top: 5px solid transparent; border-bottom: 5px solid transparent; border-left: 5px solid #808080 }
#module .form { display: block; padding: 40px }
#module .form ~ .form { display: none }
#module form { margin: 0 }
#module h2 { margin: 0 0 20px; color: #4080c0; font-size: 18px; font-weight: 400; line-height: 1 }
#module p { margin: 0 0 20px }
#module input { display: block; width: 100%; margin: 0 0 20px; padding: 10px 15px; font-size: 14px; font-weight: 400; border: 1px solid #e0e0e0; box-sizing: border-box; outline: none; -webkit-transition: border .3s ease; -moz-transition: border .3s ease; -o-transition: border .3s ease; transition: border .3s ease }
#module .checkbox, #module .radio { display: block; margin: 0 0 20px; padding: 0 10px; font-weight: 300; -webkit-transition: border .3s ease; -moz-transition: border .3s ease; -o-transition: border .3s ease; transition: border .3s ease }
#module .checkbox > label, #module .radio > label { cursor: pointer }
#module .checkbox > label > input[type=checkbox],
#module .radio > label > input[type=radio] { display: initial; width: 14px; height: 14px; margin: 0 }
#module input:focus { border: 1px solid #4080c0; color: #303030 }
#module button { background: #4080c0; width: 100%; padding: 10px 15px; color: #fff; font-size: 14px; cursor: pointer; border: 0; -webkit-transition: background .3s ease; -moz-transition: background .3s ease; -o-transition: background .3s ease; transition: background .3s ease }
#module button:hover, #module button:focus { background: #306090 }
#module .help-block { margin: 0 0 20px; padding: 10px 15px; color: #808080; border-left: 2px solid #c9c9c9 }
#module .cta { background: #f0f0f0; width: 100%; color: #c0c0c0; font-size: 12px; text-align: center }
#module .cta:nth-child(odd) { background: #e8e8e8 } 
#module .cta a, #module .cta span { display: block; padding: 15px 40px; color: #808080; font-size: 12px; text-align: center }
#module .cta a { text-decoration: none }
#module .cta a:hover, #module .cta a:focus { color: #303030 }
@media (max-width: 420px) {
	#module .toggle { width: 25px; height: 25px; font-size: 12px }
	#module .toggle [data-fa-i2svg] { padding: 6.5px 0 }
	#module .toggle .tooltip { top: 7px; right: 32px; padding: 3px 4px; font-size: 9px }
	#module .toggle .tooltip:before { top: 4px; right: -4px; border-width: 4px }
	#module .form { padding: 30px }
	#module h2 { margin: 0 0 15px; font-size: 16px }
	#module p { margin: 0 0 15px }
	#module input { margin: 0 0 15px; font-size: 12px }
	#module .checkbox, #module .radio { margin: 0 0 15px }
	#module button { padding: 10px 15px; font-size: 12px } }

#footer { margin: 30px 10px; text-align: center; letter-spacing: 1px }
#footer p { font-size: 12px }
#footer p .fa { color: #4080c0 }
#footer p a { color: #4080c0; font-weight: bold; text-decoration: none }
@media (max-width: 420px) {
	#footer { margin-top: 20px; margin-bottom: 20px }
	#footer p { font-size: 10px } }

.text-info { color: #4080c0 }
.text-success { color: #40c040 }
.text-error { color: #c04040 }
</style>

</head>
<body>

<div id="header">
	<h1><a href="<?php echo $_Oli->getUrlParam(0); ?>"><?php echo $_Oli->getSetting('name'); ?></a></h1>
	<p class="description"><?php echo $_Oli->getSetting('description'); ?></p>
	<?php if($isLoginLocal) { ?><p><b>Local login</b> (restricted to the root user)</p><?php } ?>
	<p><?php var_dump($scriptState); ?></p>
</div>

<?php if(!empty($_)) { ?>
	<div class="message">
		<div class="summary">Form data we received</div>
		<div class="content">
			It's better to be transparent about the data we receive from you.
			<ul>
				<?php foreach($_ as $eachParam => $eachValue) { ?>
					<li><?=$eachParam?> → <?=$eachValue ? '"' . $eachValue . '"' : '<i><s>empty</s></i>'?></li>
				<?php } ?>
			</ul>
		</div>
	</div>
<?php } ?>

<?php if(!$isLoginLocal) { ?>
	<div class="message">
		<div class="summary">Anti brute-force system</div>
		<div class="content">
			Your login attempts in the last hour: <br />
			- by user id: <b><?=$userIdAttempts ?: ($_Oli->runQueryMySQL('SELECT COUNT(1) as attempts FROM `' . $_Oli->translateAccountsTableCode('LOG_LIMITS') . '` WHERE action = \'login\' AND user_id = \'' . $_Oli->getUserID() . '\' AND last_trigger >= date_sub(now(), INTERVAL 1 HOUR)')[0]['attempts'] ?: 0)?></b> (max. <?=$config['maxUserIdAttempts']?>) <br />
			- by IP address: <b><?=$userIPAttempts ?: ($_Oli->runQueryMySQL('SELECT COUNT(1) as attempts FROM `' . $_Oli->translateAccountsTableCode('LOG_LIMITS') . '` WHERE action = \'login\' AND ip_address = \'' . $_Oli->getUserIP() . '\' AND last_trigger >= date_sub(now(), INTERVAL 1 HOUR)')[0]['attempts'] ?: 0)?></b> (max. <?=$config['maxUserIPAttempts']?>) <br />
			<?php if(!empty($_['username'])) { ?>- by username (<?=$_['username']?>): <b><?=$usernameAttempts ?: ($_Oli->runQueryMySQL('SELECT COUNT(1) as attempts FROM `' . $_Oli->translateAccountsTableCode('LOG_LIMITS') . '` WHERE action = \'login\' AND username = \'' . $_['username'] . '\' AND last_trigger >= date_sub(now(), INTERVAL 1 HOUR)')[0]['attempts'] ?: 0)?></b> (max. <?=$config['maxUsernameAttempts']?>)<?php } ?>
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

<div id="module">
	<div class="toggle" style="display: none">
		<i class="fas"></i>
		<div class="tooltip"></div>
	</div>
	
	<?php //if(($_Oli->config['allow_recover'] AND $_Oli->getUrlParam(2) == 'recover') OR ($_Oli->getUrlParam(2) == 'edit-password' AND !$hideChangePasswordUI) OR $scriptState == 'logged' OR $isLoggedIn) { ?>
	<?php if(in_array($scriptState, ['recover', 'logged', 'edit-password', 'recover-password'])) { ?>
		<?php if($isLoggedIn) { ?>
			<?php /*<div class="form" data-icon="fa-sign-out-alt" data-text="Logout" style="display:<?php if($_Oli->getUrlParam(2) != 'change-password') { ?>block<?php } else { ?>none<?php } ?>">*/ ?>
			<div class="form" data-icon="fa-sign-out-alt" data-text="Logout" style="display:<?php if($scriptState == 'logged') { ?>block<?php } else { ?>none<?php } ?>">
				<h2>Logout from your account</h2>
				<form action="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/logout'?>" method="post">
					<button type="submit">Logout</button>
				</form>
			</div>
		<?php //if($_Oli->config['allow_recover'] AND $_Oli->getUrlParam(2) == 'recover') { ?>
		<?php } else if($isRecoverAllowed) { ?>
			<?php /*<div class="form" data-icon="fa-refresh" data-text="Logout" style="display: <?php if($_Oli->getUrlParam(2) == 'recover' AND !$hideRecoverUI) { ?>block<?php } else { ?>none<?php } ?>;">*/ ?>
			<div class="form" data-icon="fa-refresh" data-text="Recover" style="display: <?php if($scriptState == 'recover') { ?>block<?php } else { ?>none<?php } ?>;">
				<h2>Recover your account</h2>
				<form action="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/recover'?>" method="post">
					<input type="email" name="email" value="<?=$_['email']?>" placeholder="Email address" />
					<button type="submit">Recover</button>
				</form>
			</div>
		<?php //} else if($scriptState == 'logged' OR $isLoggedIn) { ?>
		<?php } ?>
		
		<?php //if(!$isLoginLocal OR $scriptState == 'logged') { ?>
			<?php /*<div class="form" data-icon="fa-edit" data-text="Password Update" style="display:<?php if(($scriptState != 'recover' AND $scriptState != 'logged') OR $scriptState == 'edit-password') { ?>block<?php } else { ?>none<?php } ?>">*/ ?>
		<?php if($isEditPasswordAllowed) { ?>
			<div class="form" data-icon="fa-edit" data-text="Password Edit" style="display:<?php if(!in_array($scriptState, ['logged', 'recover'])) { ?>block<?php } else { ?>none<?php } ?>">
				<h2>Edit your pasword</h2>
				<?php if(!$isLoginLocal) $requestInfos = $_Oli->getAccountLines('REQUESTS', array('activate_key' => hash('sha512', $_Oli->getUrlParam(3) ?: $_['activateKey']))); ?>
				<form action="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/change-password'?><?php if(!empty($requestInfos)) { ?>&activateKey=<?=urlencode($_Oli->getUrlParam(3) ?: $_['activateKey'])?><?php } ?>" method="post">
					<?php $username = !empty($requestInfos) ? $requestInfos['username'] : $_Oli->getLocalRootInfos('username'); ?>
					
					<input type="text" name="username" value="<?=$username?>" placeholder="Username" disabled />
					<?php if($isLoggedIn) { ?>
						<input type="password" name="oldPassword" value="<?php //=$_['oldPassword'] ?>" placeholder="Current password" />
					<?php } else if(!$isLoginLocal) { ?>
						<input type="text" name="activateKey" value="<?=$_Oli->getUrlParam(3) ?: $_['activateKey']?>" placeholder="Activation key" <?php if($requestInfos) { ?>disabled<?php } ?> />
					<?php } else { ?>
						<p>An error occurred..</p>
					<?php } ?>
					<input type="password" name="newPassword" value="<?php //=$_['newPassword'] ?>" placeholder="New password" />
					<button type="submit">Update Password</button>
				</form>
			</div>
		<?php } ?>
		
		<?php if(!$isLoggedIn) { ?>
			<div class="cta"><a href="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1)?>/">Login to your account</a></div>
		<?php } ?>
	<?php //} else if($_Oli->getUrlParam(2) == 'unlock' AND !$hideUnlockUI) { ?>
	<?php } else if($scriptState == 'unlock' OR $scriptState == 'unlock-submit') { ?>
		<div class="form" data-icon="fa-key" data-text="Generate Unlock Key" style="display:<?php if($scriptState != 'unlock-submit') { ?>block<?php } else { ?>none<?php } ?>">
			<h2>Generate an unlock key</h2>
			<p>In order to unlock your account, an unlock key will be generated and send to you by email.</p>
			<form action="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/unlock'?>" method="post">
				<input type="text" name="username" value="<?=$_['username'] ?: $_Oli->getUrlParam(3)?>" placeholder="Username" />
				<button type="submit">Generate</button>
			</form>
		</div>
		<div class="form" data-icon="fa-unlock" data-text="Submit Unlock Key" style="display:<?php if($scriptState == 'unlock-submit') { ?>block<?php } else { ?>none<?php } ?>">
			<h2>Unlock your account</h2>
			<form action="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/unlock'?>" method="post">
				<input type="text" name="unlockKey" value="<?=$_['unlockKey'] ?: $_Oli->getUrlParam(3)?>" placeholder="Unlock Key" />
				<button type="submit">Unlock your account</button>
			</form>
		</div>
		
		<div class="cta"><a href="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1)?>/">Login to your account</a></div>
	<?php } else if($scriptState == 'login' OR $scriptState == 'register' OR $scriptState == 'root-register') { ?>
		<?php if($isLoginAllowed) { ?>
			<?php /*<div class="form" data-icon="fa-sign-in-alt" data-text="Login" style="display:<?php if((!$_Oli->config['allow_register'] OR $_Oli->getUrlParam(2) != 'register') AND (!$isRootRegisterAllowed OR $_Oli->getUrlParam(2) != 'root')) { ?>block<?php } else { ?>none<?php } ?>">*/ ?>
			<div class="form" data-icon="fa-sign-in-alt" data-text="Login" style="display:<?php if($scriptState != 'register' AND $scriptState != 'root-register') { ?>block<?php } else { ?>none<?php } ?>">
				<h2>Login to your account</h2>
				<form action="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/login'?>" method="post">
					<?php if(!empty($_['referer']) OR !empty($_SERVER['HTTP_REFERER'])) { ?>
						<input type="hidden" name="referer" value="<?=$_['referer'] ?: $_SERVER['HTTP_REFERER']?>" />
					<?php } ?>
					
					<input type="text" name="username" value="<?=$_['username']?>" placeholder="Username" />
					<input type="password" name="password" value="<?=$_['password']?>" placeholder="Password" />
					<div class="checkbox"><label><input type="checkbox" name="rememberMe" <?php if(!isset($_['rememberMe']) OR $_['rememberMe']) { ?>checked<?php } ?> /> « Run clever boy, and remember me »</label></div>
					<button type="submit">Login</button>
				</form>
			</div>
		<?php } ?>
		
		<?php //if($_Oli->config['allow_register']) { ?>
		<?php if($isRegisterAllowed) { ?>
			<?php /*<div class="form" data-icon="fa-pencil-alt" data-text="Register" style="display: <?php if($_Oli->getUrlParam(2) == 'register') { ?>block<?php } else { ?>none<?php } ?>;">*/ ?>
			<div class="form" data-icon="fa-pencil-alt" data-text="Register" style="display: <?php if($scriptState == 'register') { ?>block<?php } else { ?>none<?php } ?>;">
				<h2>Create a new account</h2>
				<form action="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/register'?>" method="post">
					<input type="text" name="username" value="<?=$_['username']?>" placeholder="Username" />
					<input type="password" name="password" value="<?=$_['password']?>" placeholder="Password" />
					<input type="email" name="email" value="<?=$_['email']?>" placeholder="Email address" />
					
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
		<?php if($isRootRegisterAllowed) { ?>
			<?php /*<div class="form" data-icon="fa-unlock" data-text="Root Register" style="display: <?php if($_Oli->getUrlParam(2) == 'root') { ?>block<?php } else { ?>none<?php } ?>;">*/ ?>
			<div class="form" data-icon="fa-unlock" data-text="Root Register" style="display: <?php if($scriptState == 'root-register') { ?>block<?php } else { ?>none<?php } ?>;">
				<h2>Create a root account</h2>
				<p>Be <span class="text-error">careful</span>. Only the owner of the website should use this form. <br />
				<span class="text-info">Verify your identity</span> by typing the <?php if($_Oli->refreshOliSecurityCode()) { ?>new<?php } ?> security code generated in the <code>/.olisc</code> file.</p>
				<form action="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/root'?>" method="post">
					<input type="text" name="username" value="<?=$_['username']?>" placeholder="Username" />
					<input type="password" name="password" value="<?=$_['password']?>" placeholder="Password" />
					<input type="text" name="olisc" value="<?=$_['olisc']?>" placeholder="Oli Security Code" />
					<button type="submit">Register as Root</button>
				</form>
			</div>
		<?php } ?>
		
		<?php //if($_Oli->config['allow_recover']) { ?>
		<?php if($_Oli->getUrlParam(2) == 'recover' AND !$isLoginLocal) { ?>
			<div class="cta"><a href="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1)?>/recover">Forgot your password?</a></div>
		<?php } ?>
		<?php if($userIdAttempts >= $config['maxUserIdAttempts'] OR $userIPAttempts >= $config['maxUserIPAttempts'] OR $usernameAttempts >= $config['maxUsernameAttempts']) { ?>
			<div class="cta"><a href="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1)?>/unlock<?php if(!empty($username)) { ?>/<?=$username?><?php } ?>">Unlock your account <?php if(!empty($username)) { ?>(<?=$username?>)<?php } ?></a></div>
		<?php } ?>
	<?php } else { ?>
		<div class="form" data-icon="fa-times" data-text="Error">
			<h2>An error occurred</h2>
			<p>Either you're not allowed to do anything on this page or an error occurred. Please report it to the admin.</p>
			<p>For debug purposes, here's the script state value: <?=!empty($scriptState) ? $scriptState : var_dump($scriptState)?></p>
		</div>
		<div class="form" data-icon="fa-phone" data-text="Contact" >
			<h2>Contact an admin</h2>
			<p>Later maybe.</p>
		</div>
	<?php } ?>
</div>

<div id="footer">
	<p>Powered by <a href="https://github.com/OliFramework/Oli">Oli</a>, an open source PHP framework</p>
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
		
		if(setup) $('#fontawesome').ready(function() { $('.toggle').children('[data-fa-i2svg]').addClass($('.form').eq(nextIndex).attr('data-icon')); }); 
		else $('.toggle').children('[data-fa-i2svg]').removeClass($('.form').eq(nextIndex).attr('data-icon')).addClass($('.form').eq(futureIndex).attr('data-icon'));
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