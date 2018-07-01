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

/** Ignore Form Data */
// Allow the script to prevent a form from using data from another.
$ignoreFormData = false;

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

$isExternalLogin = $_Oli->isExternalLogin();
$isLocalLogin = $_Oli->isLocalLogin();
$isLoggedIn = $_Oli->isLoggedIn();

/** Is Script State Allowed */
/** EDIT PASSWORD: */
	$isEditPasswordAllowed = ($isLoggedIn OR !$isLocalLogin);
/** LOGGED IN (independent) */
	// $isLoggedAllowed = $isLoggedIn;
/** ACTIVATE (?) */
	$isActivateAllowed = (!$isLocalLogin AND $_Oli->config['account_activation'] AND $_Oli->config['allow_register']);
/** RECOVER (explicit) */
	// $isRecoverAllowed = !$isLocalLogin;
/** UNLOCK (independent) */
	// $isUnlockAllowed = !$isLocalLogin; 
/** REGISTER: */
	$isRegisterAllowed = (!$isLocalLogin AND $_Oli->config['allow_register']);
/** REGISTER AS ROOT: */
	if($isLocalLogin) $isRootRegisterAllowed = empty($_Oli->getLocalRootInfos());
	else $isRootRegisterAllowed = !$_Oli->isExistAccountInfos('ACCOUNTS', array('user_right' => 'ROOT'), false);
	// else if($_Oli->config['allow_register']) $isRootRegisterAllowed = !$_Oli->isExistAccountInfos('ACCOUNTS', array('user_right' => $_Oli->translateUserRight('ROOT')), false);
	// else $isRootRegisterAllowed = false;
/** LOGIN: */
	$isLoginAllowed = ($isLocalLogin OR $_Oli->config['allow_login']);

/** --- */

/** Background cleanup process */
if(!empty($_) AND !$isLocalLogin) {
	$_Oli->runQueryMySQL('DELETE FROM `' . $_Oli->translateAccountsTableCode('REQUESTS') . '` WHERE expire_date < now()');
	$_Oli->runQueryMySQL('DELETE FROM `' . $_Oli->translateAccountsTableCode('SESSIONS') . '` WHERE expire_date < now() OR (expire_date IS NULL AND update_date < date_sub(now(), INTERVAL 7 DAY))');
}

/** --- */

/** Login handled by an external website */
if($isExternalLogin) header('Location: ' . $_Oli->getLoginUrl());

/** Account Password Edit */
else if(in_array($_Oli->getUrlParam(2), ['edit-password', 'change-password']) AND $isEditPasswordAllowed) {
	/** Direct Password Edit */
	if($isLoggedIn) {
		$scriptState = 'edit-password';
		if(!empty($_)) {
			if(empty($_['password'])) $resultCode = 'E:Please enter your current password.';
			else if(empty($_['newPassword'])) $resultCode = 'E:Please enter the new password you want to set.';
			else {
				if(!$_Oli->verifyLogin($_Oli->getLoggedUser(), $_['password'])) $resultCode = 'E:The current password is incorrect.';
				else if(empty($hashedPassword = $_Oli->hashPassword($_['newPassword']))) $resultCode = 'E:The new password couldn\'t be hashed.';
				else if($isLocalLogin) {
					$handle = fopen(CONTENTPATH . '.oliauth', 'w');
					if(fwrite($handle, json_encode($_Oli->getLocalRootInfos(), array('password' => $hashedPassword), JSON_FORCE_OBJECT))) {
						$_Oli->logoutAccount();
						$scriptState = 'login';
						$ignoreFormData = true;
						$resultCode = 'S:Your password has been successfully updated.';
					} else $resultCode = 'E:An error occurred when updating your password.';
					fclose($handle);
				} else if($_Oli->updateAccountInfos('ACCOUNTS', array('password' => $hashedPassword), $_Oli->getLoggedUser())) $resultCode = 'S:Your password has been successfully updated.';
				else $resultCode = 'E:An error occurred when updating your password.';
			}
		}
	
	/** Complete Account Recovery */
	} else if(!$isLocalLogin) {
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
					$scriptState = 'login';
					$ignoreFormData = true;
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
			else if(!$username = $_Oli->getAccountInfos('ACCOUNTS', 'username', array('email' => trim($_['email'])), false)) $resultCode = 'E:Sorry, no account is associated with the email you entered.';
			else if($requestInfos = $_Oli->getAccountLines('REQUESTS', array('username' => $username, 'action' => 'change-password')) AND time() <= strtotime($requestInfos['expire_date'])) $resultCode = 'E:Sorry, a change-password request already exists for that account, please check your mail inbox.';
			else if($activateKey = $_Oli->createRequest($username, 'change-password')) {
				$subject = 'One more step to change your password';
				$message .= '<p><b>Hi ' . $username . '</b>!</p>';
				$message .= '<p>A new request has been created for changing your account password. <br />';
				$message .= 'To set your new password, just click on <a href="' . $_Oli->getUrlParam(0) . 'change-password/' . $activateKey . '">this link</a> and follow the instructions. <br />';
				$message .= 'This request will expire after ' . floor($expireDelay = $_Oli->getRequestsExpireDelay() /3600 /24) . ' ' . ($expireDelay > 1 ? 'days' : 'day') . '. After that, the link will be desactivated and the request deleted.</p>';
				$message .= '<p>If you can\'t open the link, just copy this in your browser: ' . $_Oli->getUrlParam(0)  . $_Oli->getUrlParam(1) . '/change-password/' . $activateKey . '.</p>';
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
				else if(($usernameAttempts = $_Oli->runQueryMySQL('SELECT COUNT(1) as attempts FROM `' . $_Oli->translateAccountsTableCode('LOG_LIMITS') . '` WHERE action = \'login\' AND username = \'' . $username . '\' AND last_trigger >= date_sub(now(), INTERVAL 1 HOUR)')[0]['attempts'] ?: 0) < 1) $resultCode = 'E:Sorry, no failed login attempts has been recorded for this account.';
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
		else if(!$isLocalLogin AND ($userIdAttempts = $_Oli->runQueryMySQL('SELECT COUNT(1) as attempts FROM `' . $_Oli->translateAccountsTableCode('LOG_LIMITS') . '` WHERE action = \'login\' AND user_id = \'' . $_Oli->getAuthID() . '\' AND last_trigger >= date_sub(now(), INTERVAL 1 HOUR)')[0]['attempts'] ?: 0) >= $config['maxUserIdAttempts']) $resultCode = 'E:<b>Anti brute-force</b> – Due to too many login attempts (' . $userIdAttempts . '), your user ID has been blocked and therefore you cannot login. Please try again later, or <a href="' . $_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/unlock' . '">unlock your account</a>.';
		else if(!$isLocalLogin AND ($userIPAttempts = $_Oli->runQueryMySQL('SELECT COUNT(1) as attempts FROM `' . $_Oli->translateAccountsTableCode('LOG_LIMITS') . '` WHERE action = \'login\' AND ip_address = \'' . $_Oli->getUserIP() . '\' AND last_trigger >= date_sub(now(), INTERVAL 1 HOUR)')[0]['attempts'] ?: 0) >= $config['maxUserIPAttempts']) $resultCode = 'E:<b>Anti brute-force</b> – Due to too many login attempts (' . $userIPAttempts . '), your IP address has been blocked and therefore you cannot login. Please try again later, or <a href="' . $_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/unlock' . '">unlock your account</a>.';
		else if(empty($_['password'])) $resultCode = 'E:Please enter your password.';
		else {
			$uid = !$isLocalLogin ? $_Oli->getAccountInfos('ACCOUNTS', 'uid', array('uid' => $_['logid'], 'username' => $_['logid'], 'email' => $_['logid']), array('where_or' => true), false) : false;
			$localRoot = !empty($_Oli->getLocalRootInfos()) ? true : false;
			
			if(!$uid AND !$localRoot) $resultCode = 'E:Sorry, no account is associated with the login ID you used.';
			else if(!$isLocalLogin AND $_Oli->getUserRightLevel($uid, false) == $_Oli->translateUserRight('NEW-USER')) $resultCode = 'E:Sorry, the account associated with that login ID is not yet activated.';
			else if(!$isLocalLogin AND $_Oli->getUserRightLevel($uid, false) == $_Oli->translateUserRight('BANNED')) $resultCode = 'E:Sorry, the account associated with that login ID is banned and is not allowed to log in.';
			else if(!$isLocalLogin AND $_Oli->getUserRightLevel($uid, false) < $_Oli->translateUserRight('USER')) $resultCode = 'E:Sorry, the account associated with that login ID is not allowed to log in.';
			
			else if(!$isLocalLogin AND ($usernameAttempts = $_Oli->runQueryMySQL('SELECT COUNT(1) as attempts FROM `' . $_Oli->translateAccountsTableCode('LOG_LIMITS') . '` WHERE action = \'login\' AND uid = \'' . $uid . '\' AND last_trigger >= date_sub(now(), INTERVAL 1 HOUR)')[0]['attempts'] ?: 0) >= $config['maxUsernameAttempts']) $resultCode = 'E:<b>Anti brute-force</b> – Due to too many login attempts (' . $usernameAttempts . '), this account has been blocked and therefore you cannot login. Please try again later, or <a href="' . $_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/unlock' . '">unlock your account</a>.';
			
			else if($_Oli->verifyLogin($_['logid'], $_['password'])) {
				$loginDuration = $_['rememberMe'] ? $_Oli->config['extended_session_duration'] : $_Oli->config['default_session_duration'];
				if($_Oli->loginAccount($_['logid'], $_['password'], $loginDuration)) {
					if(!empty($_Oli->config['associated_websites']) AND preg_match('/^(https?:\/\/)?([-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6})\b([-a-zA-Z0-9@:%_\+.~#?&\/=]*)$/', $_Oli->config['associated_websites'][0], $matches)) {
						$url = ($matches[1] ?: 'http://') . $matches[2] . (substr($matches[3], -1) == '/' ? $matches[3] : '/') . 'request.php';
						header('Location: ' . $url . '?' . http_build_query(array('action' => 'setLoginInfos', 'userID' => $_Oli->getAuthID(), 'authKey' => $_Oli->getAuthKey(), 'extendedDelay' => $_['rememberMe'], 'next' => array_slice($_Oli->config['associated_websites'], 1), 'callback' => $_['referer'] ?: $_Oli->getFullUrl())));
					} else if(!empty($_['referer']) AND !strstr($_['referer'], '/' . $_Oli->getUrlParam(1))) header('Location: ' . $_['referer']);
					// else header('Location: ' . $_Oli->getUrlParam(0));
					
					$scriptState = 'logged';
					$isLoggedIn = true;
					$resultCode = 'S:You are now succesfully logged in.';
				} else $resultCode = 'E:An error occurred while logging you in.';
			} else {
				if(!$isLocalLogin) $_Oli->insertAccountLine('LOG_LIMITS', array('id' => $_Oli->getLastAccountInfo('LOG_LIMITS', 'id') + 1, 'uid' => $uid, 'user_id' => $_Oli->getAuthID(), 'ip_address' => $_Oli->getUserIP(), 'action' => 'login', 'last_trigger' => date('Y-m-d H:i:s')));
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
#module .form form { margin: 0 }
#module .form *:last-child { margin-bottom: 0 }
#module .form h2, #module .form p, #module .form ul, #module .form button { margin: 0 0 20px }
#module .form h2 { color: #4080c0; font-size: 18px; font-weight: 400; line-height: 1 }
#module .form ul { padding-left: 20px }
#module .form .help-block { margin: 0 0 20px; padding: 10px 15px; color: #808080; border-left: 2px solid #c9c9c9 }
#module .form input { display: block; width: 100%; margin: 0 0 20px; padding: 10px 15px; font-size: 14px; font-weight: 400; border: 1px solid #e0e0e0; box-sizing: border-box; outline: none; -webkit-transition: border .3s ease; -moz-transition: border .3s ease; -o-transition: border .3s ease; transition: border .3s ease }
#module .form .checkbox, #module .form .radio { display: block; margin: 0 0 20px; padding: 0 10px; font-weight: 300; -webkit-transition: border .3s ease; -moz-transition: border .3s ease; -o-transition: border .3s ease; transition: border .3s ease }
#module .form .checkbox > label, #module .form .radio > label { cursor: pointer }
#module .form .checkbox > label > input[type=checkbox], #module .form .radio > label > input[type=radio] { display: initial; width: 14px; height: 14px; margin: 0 }
#module .form input:focus { border: 1px solid #4080c0; color: #303030 }
#module .form button { background: #4080c0; width: 100%; padding: 10px 15px; color: #fff; font-size: 14px; cursor: pointer; border: 0; -webkit-transition: background .3s ease; -moz-transition: background .3s ease; -o-transition: background .3s ease; transition: background .3s ease }
#module .form button:hover, #module button:focus { background: #306090 }
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

<!-- ScriptState: <?php var_dump($scriptState); ?> -->
<div id="header">
	<h1><a href="<?php echo $_Oli->getUrlParam(0); ?>"><?php echo $_Oli->getSetting('name'); ?></a></h1>
	<p class="description"><?php echo $_Oli->getSetting('description'); ?></p>
	<?php if($isLocalLogin) { ?><p><b>Local login</b> (restricted to the root user)</p><?php } ?>
</div>

<?php if(!$isLocalLogin) { ?>
	<div class="message">
		<div class="summary">Anti brute-force system</div>
		<div class="content">
			Your login attempts in the last hour: <br />
			- by user id: <b><?=$userIdAttempts ?: ($_Oli->runQueryMySQL('SELECT COUNT(1) as attempts FROM `' . $_Oli->translateAccountsTableCode('LOG_LIMITS') . '` WHERE action = \'login\' AND user_id = \'' . $_Oli->getAuthID() . '\' AND last_trigger >= date_sub(now(), INTERVAL 1 HOUR)')[0]['attempts'] ?: 0)?></b> (max. <?=$config['maxUserIdAttempts']?>) <br />
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

<?php if($ignoreFormData) $_ = []; ?>

<div id="module">
	<div class="toggle" style="display: none">
		<i class="fas"></i>
		<div class="tooltip"></div>
	</div>
	
	<?php //if(($_Oli->config['allow_recover'] AND $_Oli->getUrlParam(2) == 'recover') OR ($_Oli->getUrlParam(2) == 'edit-password' AND !$hideChangePasswordUI) OR $scriptState == 'logged' OR $isLoggedIn) { ?>
	<?php if(in_array($scriptState, ['logged', 'recover', 'edit-password', 'recover-password'])) { ?>
		<?php if($scriptState == 'logged' OR $isLoggedIn) { ?>
			<?php /*<div class="form" data-icon="fa-sign-out-alt" data-text="Logout" style="display:<?php if($_Oli->getUrlParam(2) != 'change-password') { ?>block<?php } else { ?>none<?php } ?>">*/ ?>
			<div class="form" data-icon="fa-sign-out-alt" data-text="Logout" style="display:<?php if($scriptState == 'logged') { ?>block<?php } else { ?>none<?php } ?>">
				<h2>You are logged in</h2>
				<form action="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/logout'?>" method="post">
					<p>You can tap on the top-right icon to change your password. You can also click on one of those links to navigate on the website.</p>
					<ul>
						<?php if(!empty($_SERVER['HTTP_REFERER']) AND !strstr($_SERVER['HTTP_REFERER'], '/' . $_Oli->getUrlParam(1))) { ?><li><a href="<?=$_SERVER['HTTP_REFERER']?>">&laquo; Go back</a> (<?=$_SERVER['HTTP_REFERER']?>).</li><?php } ?>
						<li><a href="<?=$_Oli->getUrlParam(0)?>">Access the website home page</a>.</li>
						<?php if($_Oli->getUserRightLevel() >= $_Oli->translateUserRight('ROOT')) { ?><li><a href="<?=$_Oli->getUrlParam(0) . ($_Oli->config['admin_alias'] ?: 'oli-admin/')?>">Access the Oli Admin panel</a>.</li><?php } ?>
					</ul>
					<button type="submit">Logout</button>
					
					<p>By using this website, you agree that we're using a cookie to keep you logged in.</p>
				</form>
			</div>
		<?php //if($_Oli->config['allow_recover'] AND $_Oli->getUrlParam(2) == 'recover') { ?>
		<?php } else if($scriptState == 'recover' OR !$isLocalLogin) { ?>
			<?php /*<div class="form" data-icon="fa-refresh" data-text="Logout" style="display: <?php if($_Oli->getUrlParam(2) == 'recover' AND !$hideRecoverUI) { ?>block<?php } else { ?>none<?php } ?>;">*/ ?>
			<div class="form" data-icon="fa-refresh" data-text="Recover" style="display: <?php if($scriptState == 'recover') { ?>block<?php } else { ?>none<?php } ?>;">
				<h2>Recover your account</h2>
				<?php if(!$isLocalLogin) { ?>
					<form action="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/recover'?>" method="post">
						<input type="email" name="email" value="<?=$_['email']?>" placeholder="Email address" />
						<button type="submit">Recover</button>
					</form>
				<?php } else { ?>
					<p>Sorry, but you <span class="text-error">can't</span> recover a local account.</p>
					<p>If you are the owner of the website, delete the <code>/content/.oliauth</code> file and <span class="text-info">create a new local root account</span>.</p>
				<?php } ?>
			</div>
		<?php //} else if($scriptState == 'logged' OR $isLoggedIn) { ?>
		<?php } ?>
		
		<?php //if(!$isLocalLogin OR $scriptState == 'logged') { ?>
			<?php /*<div class="form" data-icon="fa-edit" data-text="Password Update" style="display:<?php if(($scriptState != 'recover' AND $scriptState != 'logged') OR $scriptState == 'edit-password') { ?>block<?php } else { ?>none<?php } ?>">*/ ?>
		<?php if($isEditPasswordAllowed) { ?>
			<div class="form" data-icon="fa-edit" data-text="Password Edit" style="display:<?php if(in_array($scriptState, ['edit-password', 'recover-password'])) { ?>block<?php } else { ?>none<?php } ?>">
				<h2>Edit your pasword</h2>
				<?php if(!$isLocalLogin) $requestInfos = $_Oli->getAccountLines('REQUESTS', array('activate_key' => hash('sha512', $_Oli->getUrlParam(3) ?: $_['activateKey']))); ?>
				
				<form action="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/change-password'?><?php if(!empty($requestInfos)) { ?>&activateKey=<?=urlencode($_Oli->getUrlParam(3) ?: $_['activateKey'])?><?php } ?>" method="post">
					<input type="text" name="uid" value="<?=!empty($requestInfos) ? $requestInfos['uid'] : ($_Oli->getLoggedUser() ?: 'root')?>" placeholder="Username" disabled />
					<?php if($isLoggedIn) { ?>
						<input type="password" name="password" value="<?php //=$_['password'] ?>" placeholder="Current password" />
					<?php } else if(!$isLocalLogin) { ?>
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
			<div class="cta"><a href="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1)?>/login">Login to your account</a></div>
		<?php } ?>
	<?php //} else if($_Oli->getUrlParam(2) == 'unlock' AND !$hideUnlockUI) { ?>
	<?php } else if(in_array($scriptState, ['unlock', 'unlock-submit'])) { ?>
		<div class="form" data-icon="fa-key" data-text="Generate Unlock Key" style="display:<?php if($scriptState == 'unlock') { ?>block<?php } else { ?>none<?php } ?>">
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
		
		<div class="cta"><a href="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1)?>/login">Login to your account</a></div>
	<?php } else if(in_array($scriptState, ['login', 'register', 'activate', 'root-register'])) { ?>
		<?php if($isLoginAllowed) { ?>
			<?php /*<div class="form" data-icon="fa-sign-in-alt" data-text="Login" style="display:<?php if((!$_Oli->config['allow_register'] OR $_Oli->getUrlParam(2) != 'register') AND (!$isRootRegisterAllowed OR $_Oli->getUrlParam(2) != 'root')) { ?>block<?php } else { ?>none<?php } ?>">*/ ?>
			<div class="form" data-icon="fa-sign-in-alt" data-text="Login" style="display:<?php if($scriptState == 'login') { ?>block<?php } else { ?>none<?php } ?>">
				<h2>Login to your account</h2>
				<form action="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/'?>" method="post">
					<?php if(!empty($_['referer']) OR !empty($_SERVER['HTTP_REFERER'])) { ?>
						<input type="hidden" name="referer" value="<?=$_['referer'] ?: $_SERVER['HTTP_REFERER']?>" />
					<?php } ?>
					
					<p>Log in using <b>your email</b>, your user ID, or your username (if set).</p>
					<?php if(!$isLocalLogin) { ?><input type="text" name="logid" value="<?=$_['logid']?>" placeholder="Login ID" /><?php } ?>
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
			<?php /*<div class="form" data-icon="fa-unlock" data-text="Root Register" style="display: <?php if($_Oli->getUrlParam(2) == 'root') { ?>block<?php } else { ?>none<?php } ?>;">*/ ?>
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
		
		<?php //if($_Oli->config['allow_recover']) { ?>
		<?php if(!$isLocalLogin) { ?>
			<div class="cta"><a href="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1)?>/recover">Forgot your password?</a></div>
		<?php } ?>
		<?php if($userIdAttempts >= $config['maxUserIdAttempts'] OR $userIPAttempts >= $config['maxUserIPAttempts'] OR $usernameAttempts >= $config['maxUsernameAttempts']) { ?>
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
	<?php /*<p>About Oli</p>*/ ?>
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
		
		if(setup) $('#fontawesome').ready(function() { $('.toggle').children('[data-fa-i2svg]').addClass($('.form').eq(nextIndex).attr('data-icon')); }); 
		else $('.toggle').children('[data-fa-i2svg]').removeClass($('.form').eq(nextIndex).attr('data-icon')).addClass($('.form').eq(futureIndex).attr('data-icon'));
		// $('.toggle').children('.tooltip').text((index +1) + "/" + length + " — " + $('.form').eq(setup ? nextIndex : futureIndex).attr('data-text'));
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