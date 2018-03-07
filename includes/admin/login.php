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
|*|  - (?) Support for more than two container for the switcher
|*|  - Add captcha for registering.
|*|  
|*|  Stuff to do on Oli:
|*|  - Add support and config for login limits.
\*/

$config = array(
	'maxUserIdAttempts' => 3,
	'maxUserIPAttempts' => 5,
	'maxUsernameAttempts' => 8
);

/** Login management is disabled by the current config */
// if(!$_Oli->config['user_management'] OR !$_Oli->config['allow_login']) header('Location: ' . $_Oli->getUrlParam(0));

/** Login Method (LOCAL LOGIN or LOGIN WITH DATABASE) */
if(!$_Oli->isSetupMySQL() OR !$_Oli->isAccountsManagement) {
	$localLogin = true;
	if(!file_exists(CONTENTPATH . '.oliauth') OR empty($adminUserInfos = json_decode(file_get_contents(CONTENTPATH . '.oliauth'), true))) $allowRootRegister = true;
	else $allowRootRegister = false;
} else {
	$localLogin = false;
	if(!$_Oli->isExistAccountInfos('ACCOUNTS', array('user_right' => $_Oli->translateUserRight('ROOT')), false)) $allowRootRegister = true;
	else $allowRootRegister = false;
}
var_dump($localLogin); echo '<br />';
var_dump($allowRootRegister); echo '<br />';

$mailHeaders = 'From: Noreply ' . $_Oli->getSetting('name') . ' <noreply@' . $_Oli->getUrlParam('domain') . '>' . "\r\n";
$mailHeaders .= 'MIME-Version: 1.0' . "\r\n";
$mailHeaders .= 'Content-type: text/html; charset=utf-8';

if($_Oli->getUrlParam(2) == 'change-password' AND !empty($_Oli->getPostVars('activateKey'))) {
	if($_Oli->isEmptyPostVars('newPassword')) $resultCode = 'E:Please enter the new password you want to set.';
	else if(!$requestInfos = $_Oli->getAccountLines('REQUESTS', array('activate_key' => hash('sha512', $_Oli->getPostVars('activateKey'))))) $resultCode = 'E:Sorry, the request you asked for does not exist.';
	else if($requestInfos['action'] != 'change-password') $resultCode = 'E:The request you triggered does not allow you to change your password.';
	else if(time() > strtotime($requestInfos['expire_date'])) $resultCode = 'E:Sorry, the request you triggered has expired.';
	else {
		/** Logout the user if they're logged in */
		if($_Oli->verifyAuthKey()) $_Oli->logoutAccount();
		
		/** Deletes all the user sessions, change the user password and delete the request */
		if($_Oli->deleteAccountLines('SESSIONS', $requestInfos['username']) AND $_Oli->updateAccountInfos('ACCOUNTS', array('password' => $_Oli->hashPassword($_Oli->getPostVars('newPassword'))), $requestInfos['username']) AND $_Oli->deleteAccountLines('REQUESTS', array('activate_key' => hash('sha512', $_Oli->getPostVars('activateKey'))))) {
			$hideChangePasswordUI = true;
			$resultCode = 'S:Your password has been successfully changed!';
		} else $resultCode = 'E:An error occurred while changing your password.';
	}
}
else if(!$_Oli->issetPostVars() AND $_Oli->getUrlParam(2) == 'unlock' AND $requestInfos = $_Oli->getAccountLines('REQUESTS', array('activate_key' => hash('sha512', $_Oli->getUrlParam(3))))) {
	if($requestInfos['action'] != 'unlock') $resultCode = 'E:The request you triggered does not allow you to unlock your account.';
	else if(time() > strtotime($requestInfos['expire_date'])) $resultCode = 'E:Sorry, the request you triggered has expired.';
	else {
		/** Deletes all the account log limits and delete the request */
		if($_Oli->deleteAccountLines('LOG_LIMITS', $requestInfos['username']) AND $_Oli->deleteAccountLines('REQUESTS', array('activate_key' => hash('sha512', $_Oli->getPostVars('activateKey'))))) {
			$hideUnlockUI = true;
			$resultCode = 'S:Your account has been successfully unlocked!';
		} else $resultCode = 'E:An error occurred while changing your password.';
	}
}
else if($_Oli->verifyAuthKey()) {
	if($_Oli->getUrlParam(2) == 'logout') {
		if($_Oli->logoutAccount()) {
			if(!empty($_Oli->config['associated_websites']) AND preg_match('/^(https?:\/\/)?([-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6})\b([-a-zA-Z0-9@:%_\+.~#?&\/=]*)$/', $_Oli->config['associated_websites'][0], $matches)) {
				$url = ($matches[1] ?: 'http://') . $matches[2] . (substr($matches[3], -1) == '/' ? $matches[3] : '/') . 'request.php';
				header('Location: ' . $url . '?' . http_build_query(array('action' => 'removeLoginInfos', 'next' => array_slice($_Oli->config['associated_websites'], 1), 'callback' => $_Oli->getFullUrl())));
			} else $resultCode = 'S:You have been successfully disconnected.';
		}
		else $resultCode = 'E:An error occurred while disconnecting you.';
	} else if($_Oli->getUrlParam(2) != 'change-password') header('Location: ' . $_Oli->getUrlParam(0));	
}
/** At this point, the user cannot be logged in */
else if($_Oli->getUrlParam(2) == 'logout') { $resultCode = 'I:You are disconnected.'; }
else if($_Oli->config['account_activation'] AND $_Oli->getUrlParam(2) == 'activate' AND !empty($_Oli->getUrlParam(3))) {
	if(!$requestInfos = $_Oli->getAccountLines('REQUESTS', array('activate_key' => hash('sha512', $_Oli->getUrlParam(3))))) $resultCode = 'E:Sorry, the request you asked for does not exist.';
	else if($requestInfos['action'] != 'activate') $resultCode = 'E:The request you triggered does not allow you to activate any account.';
	else if(time() > strtotime($requestInfos['expire_date'])) $resultCode = 'E:Sorry, the request you triggered has expired.';
	else if($_Oli->deleteAccountLines('REQUESTS', array('activate_key' => hash('sha512', $_Oli->getUrlParam(3)))) AND $_Oli->updateUserRight('USER', $requestInfos['username'])) $resultCode = 'S:Your account has been successfully activated!';
	else $resultCode = 'E:An error occurred while activating your account.';
}
else if($_Oli->issetPostVars()) {
	$_Oli->runQueryMySQL('DELETE FROM `' . $_Oli->translateAccountsTableCode('REQUESTS') . '` WHERE expire_date < now()');
	$_Oli->runQueryMySQL('DELETE FROM `' . $_Oli->translateAccountsTableCode('SESSIONS') . '` WHERE expire_date < now() OR (expire_date IS NULL AND update_date < date_sub(now(), INTERVAL 7 DAY))');
	
	if($_Oli->config['allow_recover'] AND $_Oli->getUrlParam(2) == 'recover') {
		if($_Oli->isEmptyPostVars('email')) $resultCode = 'E:Please enter your email.';
		else if(!$username = $_Oli->getAccountInfos('ACCOUNTS', 'username', array('email' => trim($_Oli->getPostVars('email'))), false)) $resultCode = 'E:Sorry, no account is associated with the email you entered.';
		else if($requestInfos = $_Oli->getAccountLines('REQUESTS', array('username' => $username, 'action' => 'change-password')) AND time() <= strtotime($requestInfos['expire_date'])) $resultCode = 'E:Sorry, a change-password request already exists for that account, please check your mail inbox.';
		else if($activateKey = $_Oli->createRequest($username, 'change-password')) {
			$email = $_Oli->getPostVars('email');
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
	} else if($_Oli->getUrlParam(2) == 'unlock') {
		$username = trim($_Oli->getPostVars('username'));
		
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
					$resultCode = 'S:The request has been successfully created and a mail has been sent to you.';
				} else {
					$_Oli->deleteAccountLines('REQUESTS', array('activate_key' => $activateKey));
					$resultCode = 'D:An error occurred while sending the mail to you.';
				}
			} else $resultCode = 'E:An error occurred while creating the unlock request.';
		}
	} else if($_Oli->config['allow_register'] AND $_Oli->issetPostVars('email')) {
		if($_Oli->isEmptyPostVars('username')) $resultCode = 'E:Please enter an username.';
		else {
			$username = trim($_Oli->getPostVars('username'));
			if($_Oli->isExistAccountInfos('ACCOUNTS', $username, false)) $resultCode = 'E:Sorry, the username you choose is already associated with an existing account.';
			else if($_Oli->isProhibitedUsername($username)) $resultCode = 'E:Sorry, the username you choose is prohibited.';
			else if($_Oli->isEmptyPostVars('password')) $resultCode = 'E:Please enter an password.';
			else if($_Oli->isEmptyPostVars('email')) $resultCode = 'E:Please enter your email.';
			else {
				$email = strtolower(trim($_Oli->getPostVars('email')));
				if($_Oli->isExistAccountInfos('ACCOUNTS', array('email' => $email), false)) $resultCode = 'E:Sorry, the email you entered is already associated with an existing account.';
				else if($_Oli->registerAccount($username, $_Oli->getPostVars('password'), $email, array('headers' => $mailHeaders))) {
					if($_Oli->config['account_activation']) $resultCode = 'S:Your account has been successfully created and a mail has been sent to <b>' . $email . '</b>.';
					else $resultCode = 'S:Your account has been successfully created; you can now log into it.';
				} else $resultCode = 'E:An error occurred while creating your account.';
			}
		}
	} else if($_Oli->config['allow_login']) {
		$_Oli->deleteAccountLines('LOG_LIMITS', 'action = \'login\' AND last_trigger < date_sub(now(), INTERVAL 1 HOUR)');
		$username = trim($_Oli->getPostVars('username'));
		
		if(($userIdAttempts = $_Oli->runQueryMySQL('SELECT COUNT(1) as attempts FROM `' . $_Oli->translateAccountsTableCode('LOG_LIMITS') . '` WHERE action = \'login\' AND user_id = \'' . $_Oli->getUserID() . '\' AND last_trigger >= date_sub(now(), INTERVAL 1 HOUR)')[0]['attempts'] ?: 0) >= $config['maxUserIdAttempts']) $resultCode = 'E:<b>Anti brute-force</b> – Due to too many login attempts (' . $userIdAttempts . '), your user ID has been blocked and therefore you cannot login. Please try again later, or <a href="' . $_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . (!empty($username) ? '/unlock/' . $username : '/unlock') . '">unlock your account</a>.';
		else if(($userIPAttempts = $_Oli->runQueryMySQL('SELECT COUNT(1) as attempts FROM `' . $_Oli->translateAccountsTableCode('LOG_LIMITS') . '` WHERE action = \'login\' AND ip_address = \'' . $_Oli->getUserIP() . '\' AND last_trigger >= date_sub(now(), INTERVAL 1 HOUR)')[0]['attempts'] ?: 0) >= $config['maxUserIPAttempts']) $resultCode = 'E:<b>Anti brute-force</b> – Due to too many login attempts (' . $userIPAttempts . '), your IP address has been blocked and therefore you cannot login. Please try again later, or <a href="' . $_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . (!empty($username) ? '/unlock/' . $username : '/unlock') . '">unlock your account</a>.';
		else if(empty($username)) $resultCode = 'E:Please enter your username or your email.';
		else if(($usernameAttempts = $_Oli->runQueryMySQL('SELECT COUNT(1) as attempts FROM `' . $_Oli->translateAccountsTableCode('LOG_LIMITS') . '` WHERE action = \'login\' AND username = \'' . $username . '\' AND last_trigger >= date_sub(now(), INTERVAL 1 HOUR)')[0]['attempts'] ?: 0) >= $config['maxUsernameAttempts']) $resultCode = 'E:<b>Anti brute-force</b> – Due to too many login attempts (' . $usernameAttempts . '), this username has been blocked and therefore you cannot login. Please try again later, or <a href="' . $_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . (!empty($username) ? '/unlock/' . $username : '/unlock') . '">unlock your account</a>.';
		else if($_Oli->isEmptyPostVars('password')) $resultCode = 'E:Please enter your password.';
		else {
			$isExistByUsername = $_Oli->isExistAccountInfos('ACCOUNTS', $username, false);
			$isExistByEmail = $_Oli->isExistAccountInfos('ACCOUNTS', array('email' => $username), false);
			
			if(!$isExistByUsername AND !$isExistByEmail) $resultCode = 'E:Sorry, no account is associated with the username or email you entered.';
			else if(($isExistByUsername AND $_Oli->getUserRightLevel($username, false) == $_Oli->translateUserRight('NEW-USER')) OR ($isExistByEmail AND $_Oli->getUserRightLevel(array('email' => $username), false) == $_Oli->translateUserRight('NEW-USER'))) $resultCode = 'E:Sorry, the account associated with that username or email is not yet activated.';
			else if(($isExistByUsername AND $_Oli->getUserRightLevel($username, false) == $_Oli->translateUserRight('BANNED')) OR ($isExistByEmail AND $_Oli->getUserRightLevel(array('email' => $username), false) == $_Oli->translateUserRight('BANNED'))) $resultCode = 'E:Sorry, the account associated with that username or email is banned and is not allowed to log in.';
			else if(($isExistByUsername AND $_Oli->getUserRightLevel($username, false) < $_Oli->translateUserRight('USER')) OR ($isExistByEmail AND $_Oli->getUserRightLevel(array('email' => $username), false) < $_Oli->translateUserRight('USER'))) $resultCode = 'E:Sorry, the account associated with that username or email is not allowed to log in.';
			
			else if($_Oli->verifyLogin($username, $_Oli->getPostVars('password'))) {
				$loginDuration = $_Oli->getPostVars('rememberMe') ? $_Oli->config['extended_session_duration'] : $_Oli->config['default_session_duration'];
				if($authKey = $_Oli->loginAccount($username, $_Oli->getPostVars('password'), $loginDuration)) {
					if(!empty($_Oli->config['associated_websites']) AND preg_match('/^(https?:\/\/)?([-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6})\b([-a-zA-Z0-9@:%_\+.~#?&\/=]*)$/', $_Oli->config['associated_websites'][0], $matches)) {
						$url = ($matches[1] ?: 'http://') . $matches[2] . (substr($matches[3], -1) == '/' ? $matches[3] : '/') . 'request.php';
						header('Location: ' . $url . '?' . http_build_query(array('action' => 'setLoginInfos', 'userID' => $_Oli->getUserID(), 'authKey' => $authKey, 'extendedDelay' => $_Oli->getPostVars('rememberMe'), 'next' => array_slice($_Oli->config['associated_websites'], 1), 'callback' => $_Oli->getPostVars('referer') ?: $_Oli->getFullUrl())));
					} else if(!empty($_Oli->getPostVars('referer'))) header('Location: ' . $_Oli->getPostVars('referer'));
					else header('Location: ' . $_Oli->getUrlParam(0));
				} else $resultCode = 'E:An error occurred while logging you in.';
			} else {
				$_Oli->insertAccountLine('LOG_LIMITS', array('id' => $_Oli->getLastAccountInfo('LOG_LIMITS', 'id') + 1, 'username' => $username, 'user_id' => $_Oli->getUserID(), 'ip_address' => $_Oli->getUserIP(), 'action' => 'login', 'last_trigger' => date('Y-m-d H:i:s')));
				$resultCode = 'E:Sorry, the password you entered seems to be wrong.';
			}
		}
	}
}
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

<script defer src="https://use.fontawesome.com/releases/v5.0.8/js/all.js"></script>
<style>
@import url("https://fonts.googleapis.com/css?family=Roboto:300,400,700");
html { position: relative; min-height: 100% }
body { font-family: 'Roboto', sans-serif; background: #f8f8f8; height: 100%; margin: 0; color: #808080; font-size: 14px; overflow-x: hidden }

#header { margin: 50px 0; text-align: center; color: #303030; letter-spacing: 1px }
#header h1 { margin: 0 0 20px; font-size: 40px; font-weight: 400 }
#header p { font-size: 12px }
#header p.description { font-size: 14px }
@media (max-width: 520px) {
	#header h1 { font-size: 32px }
	#header p { font-size: 10px; }
	#header p.description { font-size: 12px; } }
#header a, #message a { color: #4080c0; text-decoration: none }
#header a { font-weight: bold }

.message, #module { position: relative; background: #fff; max-width: 320px; width: 100%; min-height: 30px; margin: 30px auto; border-top: 5px solid #808080; box-shadow: 0 0 10px rgba(0, 0, 0, .2) }
.message.message-info, #module { border-top-color: #4080c0 }
.message.message-success { border-top-color: #40c040 }
.message.message-error { border-top-color: #c04040 }
.message .content { padding: 20px 40px }
.message h2 { color: #555; font-size: 16px; font-weight: 400; line-height: 1 }
.message a:hover { text-decoration: underline }

#module .toggle { cursor: pointer; position: absolute; top: 0; right: 0; background: #4080c0; width: 30px; height: 30px; margin: -5px 0 0; color: #fff; font-size: 14px; line-height: 30px; text-align: center }
#module .toggle [data-fa-i2svg] { padding: 8px }
#module .toggle .tooltip { position: absolute; top: 8px; right: 40px; display: block; background: #808080; width: auto; padding: 5px; font-size: 10px; line-height: 1; text-transform: uppercase; white-space: nowrap }
#module .toggle .tooltip:before { content: ''; position: absolute; top: 5px; right: -5px; display: block; border-top: 5px solid transparent; border-bottom: 5px solid transparent; border-left: 5px solid #808080 }
#module .form { display: none; padding: 40px }
#module .form:first-child, #module .form:nth-child(2) { display: block }
#module form { margin: 0 }
#module h2 { margin: 0 0 20px; color: #4080c0; font-size: 18px; font-weight: 400; line-height: 1 }
#module input { outline: none; display: block; width: 100%; border: 1px solid #e0e0e0; margin: 0 0 20px; padding: 10px 15px; box-sizing: border-box; font-weight: 400; -webkit-transition: .3s ease; transition: .3s ease }
#module .checkbox, #module .radio { display: block; margin: 0 0 20px; padding: 0 10px; font-weight: 300; -webkit-transition: .3s ease; transition: .3s ease }
#module .checkbox > label, #module .radio > label { cursor: pointer }
#module .checkbox > label > input[type=checkbox] { display: initial; width: auto; margin: 0 }
#module .radio > label > input[type=radio] { display: initial; width: auto; margin: 0 }
#module input:focus { border: 1px solid #4080c0; color: #303030 }
#module button { cursor: pointer; background: #4080c0; width: 100%; border: 0; padding: 10px 15px; color: #fff; -webkit-transition: .3s ease; transition: .3s ease }
#module button:hover, #module button:focus { background: #306090 }
#module .help-block { margin: 0 0 20px; padding: 10px 15px; color: #808080; border-left: 2px solid #c9c9c9 }
#module .cta { background: #f0f0f0; width: 100%; color: #c0c0c0; font-size: 12px; text-align: center }
#module .cta:nth-child(odd) { background: #e8e8e8 } 
#module .cta a, #module .cta span { display: block; padding: 15px 40px; color: #808080; font-size: 12px; text-align: center }
#module .cta a { text-decoration: none }
#module .cta a:hover, #module .cta a:focus { color: #303030 }

#footer { margin: 30px 0; text-align: center; letter-spacing: 1px }
#footer p { font-size: 12px }
@media (max-width: 520px) { #footer p { font-size: 10px; } }
#footer p .fa { color: #4080c0 }
#footer p a { color: #4080c0; font-weight: bold; text-decoration: none }
</style>

</head>
<body>

<div id="header">
	<h1><a href="<?php echo $_Oli->getUrlParam(0); ?>"><?php echo $_Oli->getSetting('name'); ?></a></h1>
	<p class="description"><?php echo $_Oli->getSetting('description'); ?></p>
	<?php if($localLogin) { ?><p class="method"><b>Local login</b> (restricted to the root user)</p><?php } ?>
</div>
<?php /*
<div class="message">
	<div class="content">
		<b>New!</b> Anti brute-force system. <br /> <br />
		
		Login attempts in the last hour... <br />
		- by user id: <b><?=$userIdAttempts ?: ($_Oli->runQueryMySQL('SELECT COUNT(1) as attempts FROM `' . $_Oli->translateAccountsTableCode('LOG_LIMITS') . '` WHERE action = \'login\' AND user_id = \'' . $_Oli->getUserID() . '\' AND last_trigger >= date_sub(now(), INTERVAL 1 HOUR)')[0]['attempts'] ?: 0)?></b> (max. <?=$config['maxUserIdAttempts']?>) <br />
		- by IP address: <b><?=$userIPAttempts ?: ($_Oli->runQueryMySQL('SELECT COUNT(1) as attempts FROM `' . $_Oli->translateAccountsTableCode('LOG_LIMITS') . '` WHERE action = \'login\' AND ip_address = \'' . $_Oli->getUserIP() . '\' AND last_trigger >= date_sub(now(), INTERVAL 1 HOUR)')[0]['attempts'] ?: 0)?></b> (max. <?=$config['maxUserIPAttempts']?>) <br />
		<?php if(!empty($username)) { ?>- by username (<?=$username?>): <b><?=$usernameAttempts ?: ($_Oli->runQueryMySQL('SELECT COUNT(1) as attempts FROM `' . $_Oli->translateAccountsTableCode('LOG_LIMITS') . '` WHERE action = \'login\' AND username = \'' . $username . '\' AND last_trigger >= date_sub(now(), INTERVAL 1 HOUR)')[0]['attempts'] ?: 0)?></b> (max. <?=$config['maxUsernameAttempts']?>)<?php } ?>
	</div>
</div>*/ ?>

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
	<?php if(($_Oli->config['allow_recover'] AND $_Oli->getUrlParam(2) == 'recover') OR ($_Oli->getUrlParam(2) == 'change-password' AND !$hideChangePasswordUI)) { ?>
		<?php if($_Oli->config['allow_recover']) { ?>
			<div class="toggle">
				<i class="fa <?php if($_Oli->getUrlParam(2) != 'change-password') { ?>fa-unlock-alt" placeholder="fa-refresh<?php } else { ?>fa-refresh" placeholder="fa-unlock-alt<?php } ?>"></i>
				<div class="tooltip" placeholder="<?php if($_Oli->getUrlParam(2) != 'change-password') { ?>Recover">Change password<?php } else { ?>Change password">Recover<?php } ?></div>
			</div>
			
			<div class="form" style="display: <?php if($_Oli->getUrlParam(2) == 'recover' AND !$hideRecoverUI) { ?>block<?php } else { ?>none<?php } ?>;">
				<h2>Recover your account</h2>
				<form action="<?=$_Oli->getUrlParam(0)?>form.php?callback=<?=urlencode($_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/recover')?>" method="post">
					<input type="email" name="email" value="<?=$_Oli->getPostVars('email')?>" placeholder="Email address" />
					<button type="submit">Recover</button>
				</form>
			</div>
		<?php } ?>
	
		<div class="form" style="display:<?php if(!$_Oli->config['allow_recover'] OR $_Oli->getUrlParam(2) == 'change-password' OR $hideRecoverUI) { ?>block<?php } else { ?>none<?php } ?>">
			<h2>Change your pasword</h2>
			<form action="<?=$_Oli->getUrlParam(0)?>form.php?callback=<?=urlencode($_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/change-password')?><?php if($requestInfos = $_Oli->getAccountLines('REQUESTS', array('activate_key' => hash('sha512', $_Oli->getUrlParam(3) ?: $_Oli->getPostVars('activateKey'))))) { ?>&activateKey=<?=urlencode($_Oli->getUrlParam(3) ?: $_Oli->getPostVars('activateKey'))?><?php } ?>" method="post">
				<?php if($requestInfos) { ?><input type="text" name="username" value="<?=$requestInfos['username']?>" placeholder="Username" disabled /><?php } ?>
				<input type="text" name="activateKey" value="<?=$_Oli->getUrlParam(3) ?: $_Oli->getPostVars('activateKey')?>" placeholder="Activation key" <?php if($requestInfos) { ?>disabled<?php } ?> />
				<input type="password" name="newPassword" value="<?=$_Oli->getPostVars('newPassword')?>" placeholder="New password" />
				<button type="submit">Update</button>
			</form>
		</div>
		
		<div class="cta"><a href="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1)?>/">Login to your account</a></div>
	<?php } else if($_Oli->getUrlParam(2) == 'unlock' AND !$hideUnlockUI) { ?>
		<div class="form" style="display: block">
			<h2>Unlock your account</h2>
			<form action="<?=$_Oli->getUrlParam(0)?>form.php?callback=<?=urlencode($_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/unlock')?>" method="post">
				<input type="text" name="username" value="<?=$_Oli->getPostVars('username') ?: $_Oli->getUrlParam(3)?>" placeholder="Username" />
				<button type="submit">Unlock</button>
				<?php /*<p class="help-block">Introducing help blocks! Yay. Totally not inspired from Bootstrap CSS. uwu</p>*/ ?>
			</form>
		</div>
		
		<div class="cta"><a href="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1)?>/">Login to your account</a></div>
	<?php } else { ?>
		<?php if($_Oli->config['allow_register']) { ?>
			<div class="toggle">
				<i class="fas <?php if($_Oli->getUrlParam(2) != 'register') { ?>fa-pencil-alt" placeholder="fa-sign-in<?php } else { ?>fa-sign-in-alt" placeholder="fa-pencil<?php } ?>"></i>
				<div class="tooltip" placeholder="<?php if($_Oli->getUrlParam(2) != 'register') { ?>Login">Register<?php } else { ?>Register">Login<?php } ?></div>
			</div>
		<?php } ?>
		
		<?php // $allowRootRegister ?>
		<div class="form" data-icon="fas fa-sign-in" data-text="Login" style="display:<?php if(!$_Oli->config['allow_register'] OR $_Oli->getUrlParam(2) != 'register') { ?>block<?php } else { ?>none<?php } ?>">
			<h2>Login to your account</h2>
			<form action="<?=$_Oli->getUrlParam(0)?>form.php?callback=<?=urlencode($_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/login')?>" method="post">
				<?php if(!empty($_Oli->getPostVars('referer')) OR !empty($_SERVER['HTTP_REFERER'])) { ?>
					<input type="hidden" name="referer" value="<?=$_Oli->getPostVars('referer') ?: $_SERVER['HTTP_REFERER']?>" />
				<?php } ?>
				
				<input type="text" name="username" value="<?=$_Oli->getPostVars('username')?>" placeholder="Username" />
				<input type="password" name="password" value="<?=$_Oli->getPostVars('password')?>" placeholder="Password" />
				<div class="checkbox"><label><input type="checkbox" name="rememberMe" <?php if(!$_Oli->issetPostVars('rememberMe') OR $_Oli->getPostVars('rememberMe')) { ?>checked<?php } ?> /> « Run clever boy, and remember me »</label></div>
				<button type="submit">Login</button>
			</form>
		</div>
		<?php if($_Oli->config['allow_register']) { ?>
			<div class="form" data-icon="fas fa-pencil-alt" data-text="Register" style="display: <?php if($_Oli->getUrlParam(2) == 'register') { ?>block<?php } else { ?>none<?php } ?>;">
				<h2>Create a new account</h2>
				<form action="<?=$_Oli->getUrlParam(0)?>form.php?callback=<?=urlencode($_Oli->getUrlParam(0) . $_Oli->getUrlParam(1) . '/register')?>" method="post">
					<input type="text" name="username" value="<?=$_Oli->getPostVars('username')?>" placeholder="Username" />
					<input type="password" name="password" value="<?=$_Oli->getPostVars('password')?>" placeholder="Password" />
					<input type="email" name="email" value="<?=$_Oli->getPostVars('email')?>" placeholder="Email address" />
					
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
		
		<?php if($_Oli->config['allow_recover']) { ?>
			<div class="cta"><a href="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1)?>/recover">Forgot your password?</a></div>
		<?php } ?>
		<?php if($userIdAttempts >= $config['maxUserIdAttempts'] OR $userIPAttempts >= $config['maxUserIPAttempts'] OR $usernameAttempts >= $config['maxUsernameAttempts']) { ?>
			<div class="cta"><a href="<?=$_Oli->getUrlParam(0) . $_Oli->getUrlParam(1)?>/unlock<?php if(!empty($username)) { ?>/<?=$username?><?php } ?>">Unlock your account <?php if(!empty($username)) { ?>(<?=$username?>)<?php } ?></a></div>
		<?php } ?>
	<?php } ?>
</div>

<div id="footer">
	<p>Powered by <a href="https://github.com/OliFramework/Oli">Oli</a>, an open source PHP framework</p>
</div>

<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
<script>
$('.toggle').click(function() {
	$(this).children('i').addClass([
		'fas ' + $(this).children('i').attr('placeholder'),
		$(this).children('i').removeClass('fas'),
		$(this).children('i').attr('placeholder', $(this).children('i').attr('class')),
		$(this).children('i').removeClass()][0]);
	$(this).children('.tooltip').text([
		$(this).children('.tooltip').attr('placeholder'),
		$(this).children('.tooltip').attr('placeholder', $(this).children('.tooltip').text())][0]);
	$('.form').animate({ height: 'toggle', 'padding-top': 'toggle', 'padding-bottom': 'toggle', opacity: 'toggle' }, 'slow');
});
</script>

</body>
</html>