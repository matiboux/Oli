<?php
/*\
|*|  ----------------------------
|*|  --- [  Oli Login page  ] ---
|*|  ----------------------------
|*|
|*|  Oli Github repository: https://github.com/matiboux/Oli/
|*|
|*|  For Oli version BETA-1.8.0 and prior versions,
|*|  the source code of the Oli Login page was hosted on a standalone Github repository.
|*|  → https://github.com/matiboux/Oli-Login-Page
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
// if(!$_OliConfig['user_management'] OR !$_OliConfig['allow_login']) header('Location: ' . $_Oli->getUrlParam(0));

/** *** *** */

$config = [
	'maxUserIdAttempts' => 3,
	'maxUserIPAttempts' => 5,
	'maxUidAttempts' => 8
];

const STATE_DISABLED = 'DISABLED';
const STATE_LOGIN = 'LOGIN';
const STATE_REGISTER = 'REGISTER';
const STATE_ROOT_REGISTER = 'ROOT_REGISTER';
const STATE_LOGGED = 'LOGGED';
const STATE_RECOVER = 'RECOVER';
const STATE_SET_USERNAME = 'SET_USERNAME';
const STATE_EDIT_PASSWORD = 'EDIT_PASSWORD';
const STATE_RECOVER_PASSWORD = 'RECOVER_PASSWORD';
const STATE_UPDATE_AVATAR = 'UPDATE_AVATAR';
const STATE_CONFIG_2FA = 'CONFIG_2FA';
const STATE_DELETE_ACCOUNT = 'DELETE_ACCOUNT';
const STATE_ACCOUNT_SETTINGS = 'ACCOUNT_SETTINGS';
const STATE_UNLOCK = 'UNLOCK';
const STATE_UNLOCK_SUBMIT = 'UNLOCK_SUBMIT';
const STATE_ACTIVATE = 'ACTIVATE';

/** @var int[] Enum of the login form script states */
$STATE = array_flip([
	STATE_DISABLED,
	STATE_LOGIN,
	STATE_REGISTER,
	STATE_ROOT_REGISTER,
	STATE_LOGGED,
	STATE_RECOVER,
	STATE_SET_USERNAME,
	STATE_EDIT_PASSWORD,
	STATE_RECOVER_PASSWORD,
	STATE_UPDATE_AVATAR,
	STATE_CONFIG_2FA,
	STATE_DELETE_ACCOUNT,
	STATE_ACCOUNT_SETTINGS,
	STATE_UNLOCK,
	STATE_UNLOCK_SUBMIT,
	STATE_ACTIVATE,
]);

/** @var int Script state */
$scriptState = $STATE[STATE_DISABLED];

/** @var \Oli\OliCore $_Oli */
/** @var \Oli\AccountsManager Accounts Manager */
$_AM = $_Oli->getAccountsManager();

if ($_AM !== null)
{

$isExternalLogin = $_AM->isExternalLogin();
$isLocalLogin = $_AM->isLocalLogin();
$isLoggedIn = $_AM->isLoggedIn();

$ignoreFormData = false; // Ignore Form Data - Allow the script to prevent a form from using data from another.
$showAntiBruteForce = false; // Display the Anti Brute Force stats.

/** LIST OF VALUES [$scriptState] - But sometimes uppercased. */
/** And [Is Script State Allowed?] */
// - 'LOGIN' Log into your account.
	$isLoginAllowed = ($isLocalLogin OR $_OliConfig['allow_login']);
// - 'LOGGED' Logged in.
	// $isLoggedAllowed = $isLoggedIn;
// - 'REGISTER' Create an account.
	$isRegisterAllowed = (!$isLocalLogin AND $_OliConfig['allow_register']);
// .. 'registered' Account created. (?)
// - 'ROOT-REGISTER' Create a root account.
	if($isLocalLogin) $isRootRegisterAllowed = empty($_AM->getLocalRootInfos());
	else $isRootRegisterAllowed = !$_AM->isExistAccountInfos('ACCOUNTS', array('user_right' => 'ROOT'), false);
// .. 'root-registered' >> 'login' Root account created.
// - 'ACTIVATE' Activate your account.
	$isActivateAllowed = (!$isLocalLogin AND $_OliConfig['account_activation'] AND $_OliConfig['allow_register']);
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
	$_AM->db->runQuerySQL('DELETE FROM `' . $_AM->translateAccountsTableCode('REQUESTS') . '` WHERE expire_date < now()');
	$_AM->db->runQuerySQL('DELETE FROM `' . $_AM->translateAccountsTableCode('SESSIONS') . '` WHERE expire_date < now() OR (expire_date IS NULL AND update_date < date_sub(now(), INTERVAL 7 DAY))');
}

/** --- */

/** Login handled by an external instance of Oli, or an external script. */
if($isExternalLogin) header('Location: ' . $_Oli->getLoginUrl());

/** Account Password Edit */
else if(in_array($_Oli->getUrlParam(2), ['edit-password', 'change-password']) AND $isEditPasswordAllowed) {
	/**	Password Edit (is Logged In) */
	if($isLoggedIn) {
		$scriptState = $STATE[STATE_EDIT_PASSWORD];
		if(!empty($_)) {
			if(empty($_['password'])) $resultCode = 'E:Please enter your current password.';
			else if(empty($_['newPassword'])) $resultCode = 'E:Please enter the new password you want to set.';
			else if(!$_AM->verifyLogin($_AM->getLoggedUser(), $_['password'])) $resultCode = 'E:The current password is incorrect.';
			else if(empty($hashedPassword = $_AM->hashPassword($_['newPassword']))) $resultCode = 'E:The new password couldn\'t be hashed.';
			else if($isLocalLogin) {
				$handle = fopen(CONTENTPATH . '.oliauth', 'w');
				if(fwrite($handle, json_encode(array_merge($_AM->getLocalRootInfos(), array('password' => $hashedPassword)), JSON_FORCE_OBJECT))) {
					$_AM->logoutAllAccount(); // Log out all sessions
					$scriptState = $STATE[STATE_LOGIN];
					$ignoreFormData = true;
					$resultCode = 'S:Your password has been successfully updated.';
				} else $resultCode = 'E:An error occurred when updating your password.';
				fclose($handle);
			} else if($_AM->updateAccountInfos('ACCOUNTS', array('password' => $hashedPassword), $_AM->getLoggedUser())) {
				$_AM->logoutAllAccount(); // Log out all sessions
				$resultCode = 'S:Your password has been successfully updated.';
			} else $resultCode = 'E:An error occurred when updating your password.';
		}

	/** Account Recovery (not Logged In) */
	} else if(!$isLocalLogin) {
		$scriptState = $STATE[STATE_RECOVER_PASSWORD];

		if(!empty($_)) $activateKey = $_['activateKey'];
		else $activateKey = $_Oli->getUrlParam(3) ?: null;

		if(!empty($activateKey)) $requestInfos = $_AM->getAccountLines('REQUESTS', array('activate_key' => hash('sha512', $activateKey)));

		if(!empty($_)) {
			if(empty($_['activateKey'])) $resultCode = 'E:The Activate Key is missing.';
			else if(empty($requestInfos)) $resultCode = 'E:Sorry, the request you asked for does not exist.';
			else if($requestInfos['action'] != 'change-password') $resultCode = 'E:The request you triggered does not allow you to change your password.';
			else if(time() > strtotime($requestInfos['expire_date'])) $resultCode = 'E:Sorry, the request you triggered has expired.';
			else {
				/** Deletes all the user sessions, change the user password and delete the request */
				if(!$_AM->logoutAllAccount($requestInfos['uid'])) $resultCode = 'E:An error occurred while changing your password (#1).';
				else if(!$_AM->updateAccountInfos('ACCOUNTS', array('password' => $_AM->hashPassword($_['newPassword'])), $requestInfos['uid'])) $resultCode = 'E:An error occurred while changing your password (#2).';
				else if(!$_AM->deleteAccountLines('REQUESTS', array('activate_key' => hash('sha512', $_['activateKey'])))) $resultCode = 'E:An error occurred while changing your password (#3).';
				else {
					$scriptState = $STATE[STATE_LOGIN];
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
	$scriptState = $STATE[STATE_LOGGED];

	/** Disconnect the user */
	if($_Oli->getUrlParam(2) == 'logout') {
		if($_AM->logoutAccount()) {
			// if(!empty($_OliConfig['associated_websites']) AND preg_match('/^(https?:\/\/)?([-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6})\b([-a-zA-Z0-9@:%_\+.~#?&\/=]*)$/', $_OliConfig['associated_websites'][0], $matches)) {
				// $url = ($matches[1] ?: 'http://') . $matches[2] . (substr($matches[3], -1) == '/' ? $matches[3] : '/') . 'request.php';
				// header('Location: ' . $url . '?' . http_build_query(array('action' => 'removeLoginInfos', 'next' => array_slice($_OliConfig['associated_websites'], 1), 'callback' => $_Oli->getFullUrl())));
			// } else {
				$scriptState = $STATE[STATE_LOGIN];
				$resultCode = 'S:You have been successfully disconnected.';
			// }
		} else $resultCode = 'E:An error occurred while disconnecting you.';

	// } else if(in_array($_Oli->getUrlParam(2), ['edit-password', 'change-password'])) {
	// This shouldn't be able to get this far.

	/** Account Settings */
	} else if($_Oli->getUrlParam(2) == 'account-settings') {
		$scriptState = $STATE[STATE_ACCOUNT_SETTINGS];

	/** Account Username Edit */
	} else if($_Oli->getUrlParam(2) == 'set-username' AND $isSetUsernameAllowed) {
		$scriptState = $STATE[STATE_SET_USERNAME];
		if(!empty($_)) {
			if($_AM->isProhibitedUsername($_['username']) === false) $resultCode = 'E:You\'re not allowed to use this username.';
			else if(!empty($_['username']) AND $_AM->isExistAccountInfos('ACCOUNTS', array('username' => $_['username']))) $resultCode = 'E:This username is already used.';
			else if($_AM->updateAccountInfos('ACCOUNTS', array('username' => $_['username']), $_AM->getLoggedUser())) {
				$scriptState = $STATE[STATE_LOGGED];
				$ignoreFormData = true;
				$resultCode = 'S:Your username has been successfully set.';
			} else $resultCode = 'E:An error occurred when updating your username.';
		}

	/** Account Avatar Update */
	} else if($_Oli->getUrlParam(2) == 'update-avatar') {
		$scriptState = $STATE[STATE_UPDATE_AVATAR];
		if(!empty($_)) {
			if(empty($_['method'])) $resultCode = 'E:Please select a method.';
			else {
				$_['method'] = strtolower(trim($_['method']));
				if(!in_array($_['method'], ['gravatar', 'custom', 'default'], true)) $resultCode = 'E:You selected an invalid method.';
				else if($_AM->updateAccountInfos('ACCOUNTS', array('avatar_method' => $_['method']))) {
					if($_['method'] == 'custom' AND !empty($_['custom']) AND $_['custom']['error'] !== UPLOAD_ERR_NO_FILE) {
						if($_['custom']['error'] !== UPLOAD_ERR_OK) $resultCode = 'E:An error occurred when uploading your new custom avatar.';
						else {
							$imagesize = getimagesize($_['custom']['tmp_name']);
							if($imagesize === false) $resultCode = 'E:You uploaded a file that is not an image.';
							else if($_['custom']['size'] > 1024**2) $resultCode = 'E:You uploaded a file that is big.';
							else if($imagesize[0] > 400 OR $imagesize[1] > 400) $resultCode = 'E:You uploaded an image that is too large.';
							else if($_AM->saveUserAvatar($_['custom']['tmp_name'], array_pop(explode('.', $_['custom']['name'])))) $resultCode = 'S:Your new avatar has been successfully saved.';
							else $resultCode = 'E:An error occurred when saving your new avatar.';
						}
					} else {
						$status = $_['delete-custom'] ? $_AM->deleteUserAvatar() : null;

						if($status === true) $resultCode = 'S:The avatar method has been successfully updated and your custom avatar has been successfully deleted.';
						if($status === false) $resultCode = 'S:The avatar method has been successfully updated but an error occurred when deleting your custom avatar.';
						else $resultCode = 'S:The avatar method has been successfully updated.';
					}
				} else $resultCode = 'E:An error occurred when saving the method you chose.';
			}
		}

	/** Configure Account 2FA */
	} else if($_Oli->getUrlParam(2) == 'config-2fa') {
		$scriptState = $STATE[STATE_CONFIG_2FA];
		// if(!empty($_)) {

		// }

	/** Delete Your Account */
	} else if($_Oli->getUrlParam(2) == 'delete-account') {
		$scriptState = $STATE[STATE_DELETE_ACCOUNT];
		if(!empty($_) AND $_['confirm']) {
			if($_AM->deleteFullAccount($_AM->getLoggedUser())) {
				$scriptState = $STATE[STATE_LOGIN];
				$ignoreFormData = true;
				$resultCode = 'S:Your account has been successfully deleted.';
			} else $resultCode = 'E:An error occurred while deleting your account.';
		}

	} else {
		/** Redirect the user */
		// if(!empty($_SERVER['HTTP_REFERER']) AND !strstr($_SERVER['HTTP_REFERER'], '/' . $_Oli->getUrlParam(1))) header('Location: ' . $_SERVER['HTTP_REFERER']);

		/** Notice the user */
		$resultCode = 'I:You\'re already logged in, ' . $_AM->getLoggedName() . '.';
	}

/** Activate an account */
// } else if($_Oli->getUrlParam(2) == 'activate' AND $_OliConfig['account_activation'] AND !$isLocalLogin) {
} else if($_Oli->getUrlParam(2) == 'activate' AND $isActivateAllowed) {
	$scriptState = $STATE[STATE_ACTIVATE];
	$activateKey = $_['activateKey'] ?: $_Oli->getUrlParam(3);
	if(empty($activateKey)) $resultCode = 'E:Please enter your activate key.';
	else if(!$requestInfos = $_AM->getAccountLines('REQUESTS', array('activate_key' => hash('sha512', $activateKey)))) $resultCode = 'E:Sorry, the request you asked for does not exist.';
	else if($requestInfos['action'] != 'activate') $resultCode = 'E:The request you triggered does not allow you to activate any account.';
	else if(time() > strtotime($requestInfos['expire_date'])) $resultCode = 'E:Sorry, the request you triggered has expired.';
	else if($_AM->updateUserRight('USER', $requestInfos['uid']) AND $_AM->deleteAccountLines('REQUESTS', array('activate_key' => hash('sha512', $activateKey)))) {
		$scriptState = $STATE[STATE_LOGIN];
		$resultCode = 'S:Your account has been successfully activated!';
	} else $resultCode = 'E:An error occurred while activating your account.';

/** Recover an account */
} else if($_Oli->getUrlParam(2) == 'recover') {
	if($isRootRegisterAllowed) $scriptState = $STATE[STATE_ROOT_REGISTER];
	// else if($isLocalLogin) {
		// $resultCode = 'I:You cannot recover a root local account. If you\'re the website owner, you can delete the <code>/content/.oliauth</code> file and create another account.';
		// $scriptState = $STATE[STATE_LOGIN];
	// } else {
	else {
		$scriptState = $STATE[STATE_RECOVER];
		if(!empty($_) AND !$isLocalLogin) {
			if(empty($_['email'])) $resultCode = 'E:Please enter your email.';
			else if(!$uid = $_AM->getAccountInfos('ACCOUNTS', 'uid', array('email' => trim($_['email'])))) $resultCode = 'E:Sorry, no account is associated with the email you entered.';
			else if($requestInfos = $_AM->getAccountLines('REQUESTS', array('uid' => $uid, 'action' => 'change-password')) AND time() <= strtotime($requestInfos['expire_date'])) $resultCode = 'E:Sorry, a change-password request already exists for that account, please check your mail inbox.';
			else if($activateKey = $_AM->createRequest($uid, 'change-password')) {
				$subject = 'One more step to change your password';
				$message .= '<p><b>Hi ' . $_AM->getName($uid) . '</b>!</p>';
				$message .= '<p>A new request has been created for changing your account password. <br />';
				$message .= 'To set your new password, just click on <a href="' . $_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/change-password/' . $activateKey . '">this link</a> and follow the instructions. <br />';
				$message .= 'This request will expire after ' . floor($expireDelay = $_AM->getRequestsExpireDelay() /3600 /24) . ' ' . ($expireDelay > 1 ? 'days' : 'day') . '. After that, the link will be desactivated and the request deleted.</p>';
				$message .= '<p>If you can\'t open the link, just copy this in your browser: ' . $_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/change-password/' . $activateKey . '.</p>';
				$message .= '<p>If you didn\'t want to change your password or didn\'t ask for this request, please just ignore this mail.</p>';

				if(mail($_['email'], $subject, $_Oli->getTemplate('mail', array('__URL__' => $_Oli->getUrlParam(0), '__NAME__' => $_Oli->getSetting('name') ?: 'Oli Mailling Service', '__SUBJECT__' => $subject, '__CONTENT__' => $message)), $_Oli->getDefaultMailHeaders(true))) {
					$scriptState = $STATE[STATE_RECOVER_PASSWORD];
					$ignoreFormData = true;
					$resultCode = 'S:The request has been successfully created and a mail has been sent to you.';
				} else {
					$_AM->deleteAccountLines('REQUESTS', array('activate_key' => $activateKey));
					$resultCode = 'D:An error occurred while sending the mail to you.';
				}
			} else $resultCode = 'E:An error occurred while creating the change-password request.';
		}
	}

/** Unlock an account */
} else if($_Oli->getUrlParam(2) == 'unlock' AND !$isLocalLogin) {
	/** Ask the unlock code */
	if(($_['unlockKey'] ?: $_Oli->getUrlParam(3)) === null) {
		$scriptState = $STATE[STATE_UNLOCK];
		if(!empty($_)) {
			$username = trim($_['username']);

			if(empty($username)) $resultCode = 'E:Please enter your username or your email.';
			else {
				$isExistByUsername = $_AM->isExistAccountInfos('ACCOUNTS', $username, false);
				$emailOwner = $_AM->getAccountInfos('ACCOUNTS', 'username', array('email' => $username), false);
				if(!$isExistByUsername AND $emailOwner) {
					$email = $username;
					$username = $emailOwner;
				}

				if(!$isExistByUsername AND !$emailOwner) $resultCode = 'E:Sorry, no account is associated with the username or email you entered.';
				else if(($uidAttempts = $_AM->db->runQuerySQL('SELECT COUNT(1) as attempts FROM `' . $_AM->translateAccountsTableCode('LOG_LIMITS') . '` WHERE action = \'login\' AND username = \'' . $username . '\' AND last_trigger >= date_sub(now(), INTERVAL 1 HOUR)')[0]['attempts'] ?: 0) < 1) $resultCode = 'E:Sorry, no failed login attempts has been recorded for this account.';
				else if($requestInfos = $_AM->getAccountLines('REQUESTS', array('username' => $username, 'action' => 'unlock')) AND time() <= strtotime($requestInfos['expire_date'])) $resultCode = 'E:Sorry, an unlock request already exists for that account, please check your mail inbox.';
				else if($activateKey = $_AM->createRequest($username, 'unlock')) {
					$subject = 'One more step to unlock your account';
					$message .= '<p><b>Hello ' . $username . '</b></p>';
					$message .= '<p>Just one last step to unlock your account. If you tried logging in and got blocked after multiple attempts, just click on <a href="' . $_Oli->getUrlParam(0)  . $_Oli->getUrlParam(1) . '/unlock/' . $activateKey . '">this link</a> to unlock your account. <br />';
					$message .= 'This request will expire after ' . floor($expireDelay = $_AM->getRequestsExpireDelay() /3600 /24) . ' ' . ($expireDelay > 1 ? 'days' : 'day') . '. After that, the link will be desactivated and the request deleted.</p>';
					$message .= '<p>If you can\'t open the link, just copy this in your browser: ' . $_Oli->getUrlParam(0)  . $_Oli->getUrlParam(1) . '/unlock/' . $activateKey . '.</p>';

					if(mail($email ?: $_AM->getAccountInfos('ACCOUNTS', 'email', $username, false), $subject, $_Oli->getTemplate('mail', array('__URL__' => $_Oli->getUrlParam(0), '__NAME__' => $_Oli->getSetting('name') ?: 'Oli Mailling Service', '__SUBJECT__' => $subject, '__CONTENT__' => $message)), $_Oli->getDefaultMailHeaders(true))) {
						$scriptState = $STATE[STATE_UNLOCK_SUBMIT];
						$ignoreFormData = true;
						$resultCode = 'S:The request has been successfully created and a mail has been sent to you.';
					} else {
						$_AM->deleteAccountLines('REQUESTS', array('activate_key' => $activateKey));
						$resultCode = 'D:An error occurred while sending the mail to you.';
					}
				} else $resultCode = 'E:An error occurred while creating the unlock request.';
			}
		}

	/** Submit the unlock code */
	} else {
		$scriptState = $STATE[STATE_UNLOCK_SUBMIT];

		if(empty($_['unlockKey'] ?: $_Oli->getUrlParam(3))) $resultCode = 'E:Please enter the unlock key.';
		else if(!$requestInfos = $_AM->getAccountLines('REQUESTS', array('activate_key' => hash('sha512', $_['unlockKey'] ?: $_Oli->getUrlParam(3))))) $resultCode = 'E:No request with this unlock key has been found.';
		else if($requestInfos['action'] != 'unlock') $resultCode = 'E:The request you triggered does not allow you to unlock your account.';
		else if(time() > strtotime($requestInfos['expire_date'])) $resultCode = 'E:Sorry, the request you triggered has expired.';
		else {
			/** Deletes all the account log limits and delete the request */
			if($_AM->deleteAccountLines('LOG_LIMITS', $requestInfos['username']) AND $_AM->deleteAccountLines('REQUESTS', array('activate_key' => hash('sha512', $_['unlockKey'] ?: $_Oli->getUrlParam(3))))) {
				$scriptState = $STATE[STATE_LOGIN];
				$ignoreFormData = true;
				// $hideUnlockUI = true;
				$resultCode = 'S:Your account has been successfully unlocked!';
			} else $resultCode = 'E:An error occurred while changing your password.';
		}
	}

/** Create a new account */
// WIP / Fix the allowed vars?
} else if($isRegisterState = ($_Oli->getUrlParam(2) == 'register' AND $isRegisterAllowed) OR $isRootRegisterState = (in_array($_Oli->getUrlParam(2), ['root-register', 'root']) AND $isRootRegisterAllowed)) {
	if($isRegisterState) $scriptState = $STATE[STATE_REGISTER];
	else if($isRootRegisterState) $scriptState = $STATE[STATE_ROOT_REGISTER];

	if(!empty($_)) {
		/** Password Checks */
		if(empty($_['password'])) $resultCode = 'E:Please enter a password.';
		else if(strlen($_['password']) < 6) $resultCode = 'E:Your password must be at least 6 characters long.';

		/** Root Register Checks */
		else if($isRootRegisterState AND empty($_['olisc'])) $resultCode = 'E:Please enter the Oli Security Code.';
		else if($isRootRegisterState AND $_['olisc'] != $_Oli->getSecurityCode()) $resultCode = 'E:The Oli Security Code is incorrect.';

		/** Not Local Login Checks */
		else if(!$isLocalLogin AND empty($_['email'] = strtolower(trim($_['email'])))) $resultCode = 'E:Please enter your email.';
		else if(!$isLocalLogin AND !preg_match('/^[-_a-z0-9]+(?:\.?[-_a-z0-9]+)*@[^\s]+(?:\.[a-z]+)$/i', $_['email'])) $resultCode = 'E:The email is incorrect. Make sure you only use letters, numbers, hyphens, underscores or periods.';
		else if(!$isLocalLogin AND $_AM->isExistAccountInfos('ACCOUNTS', array('email' => $_['email']), false)) $resultCode = 'E:Sorry, this email is already associated with an existing account.';
		else if(!$isLocalLogin AND $isRootRegisterState AND $_AM->isExistAccountInfos('ACCOUNTS', array('user_right' => 'ROOT'), false)) $resultCode = 'E:Sorry, there is already an existing root account.';

		/** Global Register */
		else if($_AM->registerAccount(!$isLocalLogin ? $_['email'] : null, $_['password'], $isRootRegisterState ? $_['olisc'] : null)) {
			$scriptState = $STATE[STATE_LOGIN];
			$ignoreFormData = true;

			if($isRootRegisterState) {
				$isRootRegisterAllowed = false;
				$resultCode = 'S:Your account has been successfully created as a root account.';
			} else if($_OliConfig['account_activation']) $resultCode = 'S:Your account has been successfully created. You received an email to activate your account.';
			else $resultCode = 'S:Your account has been successfully created. You can now log in.';
		} else $resultCode = 'E:An error occurred while creating your account..';
	}

/** Login */
} else if($isLoginAllowed) {
	$scriptState = $STATE[STATE_LOGIN];

	$userIdAttempts = 0;
	$userIPAttempts = 0;
	$uidAttempts = 0;

	/** Want to log out, but not logged in */
	if($_Oli->getUrlParam(2) == 'logout') $resultCode = 'I:You are already disconnected.';

	/** Login */
	else if(!empty($_)) {
		if(!$isLocalLogin) $_AM->deleteAccountLines('LOG_LIMITS', 'action = \'login\' AND last_trigger < date_sub(now(), INTERVAL 1 HOUR)');

		$_['logid'] = @$_['logid'] ? trim($_['logid']) : null;
		if(!$isLocalLogin AND empty($_['logid'])) $resultCode = 'E:Please enter your login ID.';
		// else if(!$isLocalLogin AND ($userIdAttempts = $_AM->db->runQuerySQL('SELECT COUNT(1) as attempts FROM `' . $_AM->translateAccountsTableCode('LOG_LIMITS') . '` WHERE action = \'login\' AND user_id = \'' . $_AM->getAuthID() . '\' AND last_trigger >= date_sub(now(), INTERVAL 1 HOUR)')[0]['attempts'] ?: 0) >= $config['maxUserIdAttempts']) $resultCode = 'E:<b>Anti brute-force</b> – Due to too many login attempts (' . $userIdAttempts . '), your user ID has been blocked and therefore you cannot login. Please try again later, or <a href="' . $_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/unlock' . '">unlock your account</a>.';
		else if(!$isLocalLogin AND ($userIPAttempts = $_AM->db->runQuerySQL('SELECT COUNT(1) as attempts FROM `' . $_AM->translateAccountsTableCode('LOG_LIMITS') . '` WHERE action = \'login\' AND ip_address = \'' . $_Oli->getUserIP() . '\' AND last_trigger >= date_sub(now(), INTERVAL 1 HOUR)')[0]['attempts'] ?: 0) >= $config['maxUserIPAttempts']) $resultCode = 'E:<b>Anti brute-force</b> – Due to too many login attempts (' . $userIPAttempts . '), your IP address has been blocked and therefore you cannot login. Please try again later, or <a href="' . $_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/unlock' . '">unlock your account</a>.';
		else if(empty($_['password'])) $resultCode = 'E:Please enter your password.';
		else {
			$uid = !$isLocalLogin ? $_AM->getAccountInfos('ACCOUNTS', 'uid', array('uid' => $_['logid'], 'username' => $_['logid'], 'email' => $_['logid']), array('where_or' => true), false) : false;
			$localRoot = !empty($_AM->getLocalRootInfos()) ? true : false;

			if(!$uid AND !$localRoot) $resultCode = 'E:Sorry, no account is associated with the login ID you used.';
			else if(!$isLocalLogin AND $_AM->getUserRightLevel($uid, false) == $_AM->translateUserRight('NEW-USER')) $resultCode = 'E:Sorry, the account associated with that login ID is not yet activated.';
			else if(!$isLocalLogin AND $_AM->getUserRightLevel($uid, false) == $_AM->translateUserRight('BANNED')) $resultCode = 'E:Sorry, the account associated with that login ID is banned and is not allowed to log in.';
			else if(!$isLocalLogin AND $_AM->getUserRightLevel($uid, false) < $_AM->translateUserRight('USER')) $resultCode = 'E:Sorry, the account associated with that login ID is not allowed to log in.';

			else if(!$isLocalLogin AND ($uidAttempts = $_AM->db->runQuerySQL('SELECT COUNT(1) as attempts FROM `' . $_AM->translateAccountsTableCode('LOG_LIMITS') . '` WHERE action = \'login\' AND uid = \'' . $uid . '\' AND last_trigger >= date_sub(now(), INTERVAL 1 HOUR)')[0]['attempts'] ?: 0) >= $config['maxUidAttempts']) $resultCode = 'E:<b>Anti brute-force</b> – Due to too many login attempts (' . $uidAttempts . '), this account has been blocked and therefore you cannot login. Please try again later, or <a href="' . $_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/unlock' . '">unlock your account</a>.';

			else if($_AM->verifyLogin($_['logid'], $_['password'])) {
				$loginDuration = $_['rememberMe'] ? $_OliConfig['extended_session_duration'] : $_OliConfig['default_session_duration'];
				if($_AM->loginAccount($_['logid'], $_['password'], $loginDuration)) {
					if(!empty($_OliConfig['associated_websites']) AND preg_match('/^(https?:\/\/)?([-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6})\b([-a-zA-Z0-9@:%_\+.~#?&\/=]*)$/', $_OliConfig['associated_websites'][0], $matches)) {
						$url = ($matches[1] ?: 'http://') . $matches[2] . (substr($matches[3], -1) == '/' ? $matches[3] : '/') . 'request.php';
						header('Location: ' . $url . '?' . http_build_query(array('action' => 'setLoginInfos', 'authKey' => $_AM->getAuthKey(), 'extendedDelay' => $_['rememberMe'] ? true : false, 'next' => array_slice($_OliConfig['associated_websites'], 1), 'callback' => $_['referer'] ?: $_Oli->getFullUrl())));
					} else if(!empty($_['referer']) AND !strstr($_['referer'], '/' . $_Oli->getUrlParam(1))) header('Location: ' . $_['referer']);
					// else header('Location: ' . $_Oli->getUrlParam(0));

					$scriptState = $STATE[STATE_LOGGED];
					$showAntiBruteForce = true;
					$isLoggedIn = true;
					$resultCode = 'S:You are now succesfully logged in.';
				} else $resultCode = 'E:An error occurred while logging you in.';
			} else {
				if(!$isLocalLogin) $_AM->insertAccountLine('LOG_LIMITS', array('id' => $_AM->getLastAccountInfo('LOG_LIMITS', 'id') + 1, 'uid' => $uid, 'ip_address' => $_Oli->getUserIP(), 'action' => 'login', 'last_trigger' => date('Y-m-d H:i:s')));
				$resultCode = 'E:Sorry, the password you used is wrong.';
			}
		}
	}
}

/** Nothing's happening because this is an error */
else $resultCode = 'E:It seems you are not allowed to do anything here.';

}
?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="description" content="<?=$_Oli->getSetting('name')?> login page" />
<meta name="keywords" content="<?=$_Oli->getSetting('name')?>,Oli,Login,Page" />
<meta name="author" content="Matiboux" />
<title>Login - <?php echo $_Oli->getSetting('name') ?: 'Oli'; ?></title>

<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.1.0/css/all.css" integrity="sha384-lKuwvrZot6UHsBSfcMvOkWwlCMgc0TaWr+30HWe3a4ltaBwTZhyTEggF5tJv8tbt" crossorigin="anonymous" />
<link rel="stylesheet" href="<?=$_Oli->getLoginUrl()?>oli-login.css" />
<link rel="stylesheet" href="<?=$_Oli->getAssetsUrl()?>oli-login.css" />

</head>
<body>

<!-- ScriptState: <?php var_dump($scriptState); ?> -->

<div id="header">
	<h1><a href="<?php echo $_Oli->getUrlParam(0); ?>"><?php echo $_Oli->getSetting('name'); ?></a></h1>
	<p class="description"><?php echo $_Oli->getSetting('description'); ?></p>
	<?php if($scriptState !== $STATE[STATE_DISABLED] && $isLocalLogin) { ?>
		<p><b>Local login</b> (restricted to the root user)</p>
	<?php } ?>
</div>

<?php if($scriptState !== $STATE[STATE_DISABLED]) { ?>

	<?php if($showAntiBruteForce) { ?>
		<div class="message">
			<div class="summary">Anti brute-force system</div>
			<div class="content">
				Your login attempts in the last hour: <br />
				<?php /*- by user id: <b><?=$userIdAttempts ?: ($_AM->db->runQuerySQL('SELECT COUNT(1) as attempts FROM `' . $_AM->translateAccountsTableCode('LOG_LIMITS') . '` WHERE action = \'login\' AND user_id = \'' . $_AM->getAuthID() . '\' AND last_trigger >= date_sub(now(), INTERVAL 1 HOUR)')[0]['attempts'] ?: 0)?></b> (max. <?=$config['maxUserIdAttempts']?>) <br />*/ ?>
				- by IP address: <b><?=$userIPAttempts ?: ($_AM->db->runQuerySQL('SELECT COUNT(1) as attempts FROM `' . $_AM->translateAccountsTableCode('LOG_LIMITS') . '` WHERE action = \'login\' AND ip_address = \'' . $_Oli->getUserIP() . '\' AND last_trigger >= date_sub(now(), INTERVAL 1 HOUR)')[0]['attempts'] ?: 0)?></b> (max. <?=$config['maxUserIPAttempts']?>) <br />
				<?php if(!empty($_['uid'])) { ?>- by uid (<?=$_['uid']?>): <b><?=$uidAttempts ?: ($_AM->db->runQuerySQL('SELECT COUNT(1) as attempts FROM `' . $_AM->translateAccountsTableCode('LOG_LIMITS') . '` WHERE action = \'login\' AND uid = \'' . $_['uid'] . '\' AND last_trigger >= date_sub(now(), INTERVAL 1 HOUR)')[0]['attempts'] ?: 0)?></b> (max. <?=$config['maxUidAttempts']?>)<?php } ?>
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

		<?php if(in_array($scriptState, [$STATE[STATE_LOGGED],
		                                 $STATE[STATE_RECOVER],
		                                 $STATE[STATE_SET_USERNAME],
		                                 $STATE[STATE_EDIT_PASSWORD],
		                                 $STATE[STATE_RECOVER_PASSWORD],
		                                 $STATE[STATE_UPDATE_AVATAR],
		                                 $STATE[STATE_CONFIG_2FA],
		                                 $STATE[STATE_DELETE_ACCOUNT],
		                                 $STATE[STATE_ACCOUNT_SETTINGS],
		                                ], true)) { ?>
			<?php $showLoggedLinksCTA = true; ?>
			<?php if($scriptState === $STATE[STATE_RECOVER]) { ?>
				<div class="form" data-icon="fa-sync-alt" data-text="Recover" style="display: <?php if($scriptState === $STATE[STATE_RECOVER]) { ?>block<?php } else { ?>none<?php } ?>">
					<h2>Recover your account</h2>
					<?php if(!$isLocalLogin) { ?>
						<form action="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/recover'?>" method="post">
							<input type="email" name="email" value="<?=@$_['email']?>" placeholder="Email address" autocomplete="email" aria-label="Email address" />
							<button type="submit">Recover</button>

							<p>An email will be sent to you with instructions to continue.</p>
						</form>
					<?php } else { ?>
						<p>Sorry, but you <span class="text-error">can't</span> recover a local account.</p>
						<p>If you are the owner of the website, delete the <code>/content/.oliauth</code> file and <span class="text-info">create a new local root account</span>.</p>
					<?php } ?>
				</div>
			<?php } ?>

			<?php if($scriptState === $STATE[STATE_SET_USERNAME]) { ?>
				<div class="form" data-icon="fa-address-card" data-text="Set Username" style="display: <?php if($scriptState === $STATE[STATE_SET_USERNAME]) { ?>block<?php } else { ?>none<?php } ?>">
					<h2>Set your username</h2>
					<form action="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/set-username'?>" method="post">
						<?php if(empty($username = $_AM->getLoggedUsername() ?: '')) { ?>
							<p>Make your account publicly visible by setting a username.
							Of course, you can remove or change your username at any time.</p>
						<?php } else { ?>
							<p>Be careful, changing or removing your username will allow others to use it, and will make links using it outdated.</p>
						<?php } ?>

						<input type="text" name="username" value="<?=$username?>" placeholder="Username" autocomplete="new-username" aria-label="Username" />
						<?php if(empty($username)) { ?>
							<button type="submit">Set Username</button>
						<?php } else { ?>
							<button type="submit">Update Username</button>
						<?php } ?>

						<p>Your username represents your public identity on the platform.</p>
					</form>
				</div>

			<?php } else if(in_array($scriptState, [$STATE[STATE_RECOVER],
			                                        $STATE[STATE_EDIT_PASSWORD],
			                                        $STATE[STATE_RECOVER_PASSWORD],
			                                       ], true)) { ?>
				<div class="form" data-icon="fa-edit" data-text="Password Edit" style="display: <?php if(in_array($scriptState, [$STATE[STATE_EDIT_PASSWORD], $STATE[STATE_RECOVER_PASSWORD]], true)) { ?>block<?php } else { ?>none<?php } ?>">
					<h2>Edit your password</h2>

					<form action="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/change-password'?><?php if(!empty($requestInfos)) { ?>?activateKey=<?=urlencode($_Oli->getUrlParam(3) ?: @$_['activateKey'])?><?php } ?>" method="post">
						<?php if($isLoggedIn) { ?>
							<p>You are logged in as <b><?=$_AM->getLoggedName() ?: 'unknown user'?></b>.</p>
							<input type="password" name="password" placeholder="Current password" autocomplete="current-password" aria-label="Current password" />

						<?php } else if(!$isLocalLogin) { ?>
							<?php if(!empty($requestInfos)) { ?>
								<p>Request for <b><?=$_AM->getName($requestInfos['uid'])?></b>.</p>
							<?php } ?>
							<input type="text" name="activateKey" value="<?=$_Oli->getUrlParam(3) ?: @$_['activateKey']?>" placeholder="Activation Key" <?php if(!empty($requestInfos)) { ?>disabled<?php } ?> autocomplete="off" />

						<?php } else { ?>
							<p>Something went wrong..</p>
						<?php } ?>

						<input type="password" name="newPassword" placeholder="New password" autocomplete="new-password" aria-label="New password" />
						<button type="submit">Update Password</button>

						<p>You'll be disconnected from all your devices.</p>
					</form>
				</div>

			<?php } else if($scriptState === $STATE[STATE_UPDATE_AVATAR]) { // https://www.gravatar.com/avatar/ ?>
				<div class="form" data-icon="fa-user-circle" data-text="Avatar Updated" style="display: <?php if($scriptState === $STATE[STATE_UPDATE_AVATAR]) { ?>block<?php } else { ?>none<?php } ?>">
					<h2>Update your avatar</h2>
					<form action="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/update-avatar'?>" method="post" enctype="multipart/form-data">
						<p>Choose the avatar you want to use between the default avatar, your avatar from <a href="https://gravatar.com/" target="__blank">Gravatar</a>, or a custom one you uploaded or will do.</p>

						<?php $currentMethod = $_AM->getLoggedAvatarMethod(); ?>
						<div class="radio mt-1"><label><input type="radio" name="method" value="default" src="<?=$_AM->getLoggedAvatar('default')?>" <?php if($currentMethod == 'default') { ?>checked<?php } ?> /> Use the default avatar</label></div>
						<div class="radio mt-1"><label><input type="radio" name="method" value="gravatar" src="<?=$_AM->getLoggedAvatar('gravatar', 200)?>" <?php if($currentMethod == 'gravatar') { ?>checked<?php } ?> /> Use your Gravatar</label></div>
						<div class="radio mt-1"><label><input type="radio" name="method" value="custom" src="<?=$_AM->getLoggedAvatar('custom')?>" <?php if($currentMethod == 'custom') { ?>checked<?php } ?> /> Use a custom avatar</label></div>

						<p>Here's how your avatar will look like:</p>
						<img id="preview" class="avatar mt-1" src="<?=$_AM->getLoggedAvatar()?>" alt="Preview of your new avatar" />

						<div class="custom-info custom" <?php if($currentMethod != 'custom') { ?>style="display: none"<?php } ?>>
							<p>You can upload a new custom avatar if you feel like changing it! <br />
							<b>Max Size</b>: 1 MB, 400x400 pixels.</p>
							<input type="file" name="custom" class="mt-1" />
						</div>

						<div class="custom-info not-custom" <?php if($currentMethod == 'custom') { ?>style="display: none"<?php } ?>>
							<div class="checkbox"><label><input type="checkbox" name="delete-custom" /> <i class="fas fa-exclamation-triangle fa-fw"></i> Delete your custom avatar (if you have uploaded one)</label></div>
						</div>

							<hr />
						<button type="submit">Update Avatar</button>
					</form>
				</div>

			<?php } else if($scriptState === $STATE[STATE_CONFIG_2FA]) { ?>
				<div class="form" data-icon="fa-key" data-text="2FA Config" style="display: <?php if($scriptState === $STATE[STATE_CONFIG_2FA]) { ?>block<?php } else { ?>none<?php } ?>">
					<h2>Configure 2FA</h2>
					<form action="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/config-2fa'?>" method="post">
						<p>Configure 2FA to secure your account! You can choose to receive the 2FA code either via email, or via Telegram for more ease.</p>
						<div class="radio mt-1"><label><input type="radio" name="method" value="none" <?php if(true) { ?>checked<?php } ?> /> Disable 2FA</label></div>
						<div class="radio mt-1"><label><input type="radio" name="method" value="email" <?php if(false) { ?>checked<?php } ?> /> Use 2FA with your email</label></div>
						<?php if(!empty($_OliConfig['telegram_bot_token'])) { ?>
							<div class="radio mt-1"><label><input type="radio" name="method" value="telegram" <?php if(false) { ?>checked<?php } ?> /> Use 2FA with Telegram!</label></div>
							<p class="mt-1">If Telegram is down for any reason, you'll be notified and the code will be sent via email.</p>
						<?php } ?>

						<button type="submit">Configure 2FA</button>
					</form>
				</div>

			<?php } else if($scriptState === $STATE[STATE_DELETE_ACCOUNT]) { ?>
				<div class="form" data-icon="fa-trash" data-text="Delete your Account" style="display: <?php if($scriptState === $STATE[STATE_DELETE_ACCOUNT]) { ?>block<?php } else { ?>none<?php } ?>">
					<h2>Delete your Account</h2>
					<p><b>Are you sure about this, <?=$_AM->getLoggedName()?>? There is not no going back after this.</b></p>
					<p class="mt-1">Personal data associated with your account will be <b>permanently deleted</b>.</p>
					<p class="mt-1">Once this account is deleted, you may register again using your email, and people may use your username (if you had set one).</p>
					<hr />

					<form action="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/delete-account'?>" method="post">
						<input type="hidden" name="confirm" value="true" />
						<p><b>Are you sure</b> you want to continue?</p>
						<a href="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1)?>" class="btn btn-warning mt-1">No, don't delete anything!</a>
						<button class="btn-danger btn-sm mt-1" type="submit">Yes, delete my account</button>
					</form>
				</div>

			<?php } else { ?>
				<?php $showLoggedLinksCTA = false; ?>
				<div class="form" data-icon="fa-sign-out-alt" data-text="Logout & Links" style="display:<?php if($scriptState === $STATE[STATE_LOGGED]) { ?>block<?php } else { ?>none<?php } ?>">
					<h2>You are logged in</h2>
					<p>You can tap on the top-right icon to change your password. You can also click on one of those links to navigate on the website.</p>

					<?php if(!empty($_SERVER['HTTP_REFERER']) AND !strstr($_SERVER['HTTP_REFERER'], '/' . $_Oli->getUrlParam(1))) { ?>
						<a href="<?=$_SERVER['HTTP_REFERER']?>" class="btn">&laquo; Go back</a>
						<p class="mt-1">&rsaquo; Go back to <?=$_SERVER['HTTP_REFERER']?></p>
					<?php } ?>
					<a href="<?=$_Oli->getUrlParam(0)?>" class="btn">Website home page</a>
					<?php if($_AM->getUserRightLevel() >= $_AM->translateUserRight('ROOT')) { ?>
						<a href="<?=$_Oli->getOliAdminUrl()?>" class="btn mt-1">Oli Admin panel</a>
					<?php } ?> <hr />

					<a href="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/logout'?>" class="btn">Log out</a>

					<p>By using this website, you agree that we're using a cookie to keep you logged in. Otherwise, please log out.</p>
				</div>
			<?php } ?>

			<?php if($scriptState !== $STATE[STATE_RECOVER]) { ?>
				<div class="form" data-icon="fa-cog" data-text="Account Settings" style="display: <?php if($scriptState === $STATE[STATE_ACCOUNT_SETTINGS]) { ?>block<?php } else { ?>none<?php } ?>">
					<div class="profile">
						<img class="avatar" src="<?=$_AM->getLoggedAvatar()?>" />
						<div class="infos">
							<span>Welcome,</span>
							<p><b><?=$_AM->getLoggedName()?></b>!</p>
						</div>
					</div>

					<h2>Account Settings</h2>
					<p>Manage your basic account settings.</p>

					<a href="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/set-username'?>" class="btn">Set Username</a>
					<a href="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/edit-password'?>" class="btn mt-1">Edit Password</a>
					<a href="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/update-avatar'?>" class="btn mt-1">Update Avatar</a>
					<a href="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/config-2fa'?>" class="btn disabled mt-1">Configure 2FA</a>

					<a href="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/delete-account'?>" class="btn btn-danger">Delete your Account</a>
				</div>
			<?php } ?>

			<?php if(!$isLoggedIn) { ?>
				<div class="cta"><a href="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1)?>/">Log into your account</a></div>
			<?php } else if($showLoggedLinksCTA) { ?>
				<div class="cta"><a href="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1)?>/">Logout & Links</a></div>
			<?php } ?>

		<?php } else if(in_array($scriptState, [$STATE[STATE_UNLOCK], $STATE[STATE_UNLOCK_SUBMIT]], true)) { ?>
			<div class="form" data-icon="fa-key" data-text="Generate Unlock Key" style="display: <?php if($scriptState === $STATE[STATE_UNLOCK]) { ?>block<?php } else { ?>none<?php } ?>">
				<h2>Generate an unlock key</h2>
				<p>In order to unlock your account, an unlock key will be generated and sent to you by email.</p>
				<form action="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/unlock'?>" method="post">
					<input type="text" name="username" value="<?=@$_['username'] ?: $_Oli->getUrlParam(3)?>" placeholder="Username" autocomplete="username" aria-label="Username" />
					<button type="submit">Generate</button>

					<p>An email will be sent to you with instructions to continue.</p>
				</form>
			</div>

			<div class="form" data-icon="fa-unlock" data-text="Submit Unlock Key" style="display:<?php if($scriptState === $STATE[STATE_UNLOCK_SUBMIT]) { ?>block<?php } else { ?>none<?php } ?>">
				<h2>Unlock your account</h2>
				<form action="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/unlock'?>" method="post">
					<input type="text" name="unlockKey" value="<?=@$_['unlockKey'] ?: $_Oli->getUrlParam(3)?>" placeholder="Unlock Key" autocomplete="off" aria-label="Unlock Key" />
					<button type="submit">Unlock your account</button>

					<p>This will reset the Anti-BruteForce stats.</p>
				</form>
			</div>

			<div class="cta">
				<a href="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1)?>/">Log into your account</a>
			</div>

		<?php } else if(in_array($scriptState, [$STATE[STATE_LOGIN], $STATE[STATE_REGISTER], $STATE[STATE_ACTIVATE], $STATE[STATE_ROOT_REGISTER]], true)) { ?>
			<?php if($isLoginAllowed) { ?>
				<div class="form" data-icon="fa-sign-in-alt" data-text="Login" style="display:<?php if($scriptState === $STATE[STATE_LOGIN]) { ?>block<?php } else { ?>none<?php } ?>">
					<h2>Log into your account</h2>
					<form action="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/'?>" method="post">
						<?php if(!empty($_['referer']) OR !empty($_SERVER['HTTP_REFERER'])) { ?>
							<input type="hidden" name="referer" value="<?=@$_['referer'] ?: $_SERVER['HTTP_REFERER']?>" />
						<?php } ?>

						<?php if(!$isLocalLogin) { ?>
							<p>Log in using <b>your email</b>, your user ID, or your username (if set).</p>
							<input type="text" name="logid" value="<?=@$_['logid']?>" placeholder="Login ID" autocomplete="username" aria-label="Login ID" />
						<?php } else { ?>
							<p>Log in using <b>the root password</b> (if set).</p>
							<input type="text" name="logid" value="root" placeholder="Login ID" autocomplete="username" aria-label="Login ID" disabled />
						<?php } ?>
						<input type="password" name="password" value="<?=@$_['password']?>" placeholder="Password" autocomplete="current-password" aria-label="Password" />
						<div class="checkbox"><label><input type="checkbox" name="rememberMe" <?php if(!isset($_['rememberMe']) OR $_['rememberMe']) { ?>checked<?php } ?> /> « Run you clever boy, and remember me »</label></div>
						<button type="submit">Login</button>

						<p>A cookie will be created to keep you logged in to your account.</p>
					</form>
				</div>
			<?php } ?>

			<?php if($isRegisterAllowed) { ?>
				<div class="form" data-icon="fa-user-plus" data-text="Register" style="display: <?php if($scriptState === $STATE[STATE_REGISTER]) { ?>block<?php } else { ?>none<?php } ?>;">
					<h2>Create a new account</h2>
					<form action="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/register'?>" method="post">
						<input type="email" name="email" value="<?=@$_['email']?>" placeholder="Email address" autocomplete="email" aria-label="Email address" />
						<input type="password" name="password" value="<?=@$_['password']?>" placeholder="Password" autocomplete="new-password" aria-label="Password" />

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
				<div class="form" data-icon="fa-unlock" data-text="Activate" style="display: <?php if($scriptState === $STATE[STATE_ACTIVATE]) { ?>block<?php } else { ?>none<?php } ?>;">
					<h2>Activate your account</h2>
					<form action="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/activate'?>" method="post">
						<input type="text" name="activateKey" value="<?=@$_['activateKey']?>" placeholder="Activate Key" autocomplete="off" aria-label="Activate Key" />
						<button type="submit">Activate</button>
					</form>
				</div>
			<?php } ?>

			<?php if($isRootRegisterAllowed) { ?>
				<div class="form" data-icon="fa-star" data-text="Root Register" style="display: <?php if($scriptState === $STATE[STATE_ROOT_REGISTER]) { ?>block<?php } else { ?>none<?php } ?>;">
					<h2>Create a root account</h2>
					<p>Be <span class="text-error">careful</span>. Only the owner of the website should use this form. <br />
					<span class="text-info">Verify your identity</span> by typing the <?php if($_Oli->refreshSecurityCode()) { ?>new<?php } ?> security code generated in the <code>/.olisc</code> file.</p>
					<form action="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/root'?>" method="post">
						<?php /*<input type="text" name="username" value="<?=@$_['username']?>" placeholder="Username" autocomplete="username" aria-label="Username" /> */ ?>
						<?php if(!$isLocalLogin) { ?><input type="email" name="email" value="<?=@$_['email']?>" placeholder="Email address" autocomplete="email" aria-label="Email address" /><?php } ?>
						<input type="password" name="password" value="<?=@$_['password']?>" placeholder="Password" autocomplete="new-password" aria-label="Password" />
						<input type="text" name="olisc" value="<?=@$_['olisc']?>" placeholder="Oli Security Code" autocomplete="off" aria-label="Oli Security Code" />
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
			<div class="form" data-icon="fa-exclamation-triangle" data-text="Error">
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

<?php } else { ?>

	<div id="module">
		<div class="form" data-icon="fa-exclamation-triangle" data-text="Error">
			<h2>Accounts management disabled</h2>
			<p>Sorry about that, but accounts management is disabled on this website.</p>
		</div>
		<div class="cta"><a href="<?=$_Oli->getUrlParam(0)?>">Website home page</a></div>
	</div>

<?php } ?>

<div id="footer">
	<p><?=$_Oli?></p>
</div>

<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
<script src="<?=$_Oli->getLoginUrl()?>oli-login.js"></script>
<?php if($scriptState === $STATE[STATE_UPDATE_AVATAR]) { ?>
<script>
$('input[type="radio"][name="method"]').change(function() {
	$('#preview').attr('src', $(this).attr('src'));
	if(this.value == 'custom') {
		$('.custom-info.custom').slideDown();
		$('.custom-info.not-custom').slideUp();
		$('.custom-info.custom input').prop('disabled', false);
		$('.custom-info.not-custom input').prop('disabled', true);
	} else {
		$('.custom-info.custom').slideUp();
		$('.custom-info.not-custom').slideDown();
		$('.custom-info.custom input').prop('disabled', true);
		$('.custom-info.not-custom input').prop('disabled', false);
	}
});
</script>
<?php } ?>

</body>
</html>
