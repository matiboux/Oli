<?php
if(!$_Oli->getAccountsManagementStatus()) header('Location: ' . $_Oli->getUrlParam(0));
if($_Oli->getUrlParam(2) == 'logout' AND !empty($_Oli->getAuthKey())) {
	$_Oli->logoutAccount();
	$resultCode = 'LOGOUT_OK';
}
else if($_Oli->getUrlParam(2) == 'change-password' AND !empty($_Oli->getUrlParam(3)) AND $_Oli->isExistAccountInfos('REQUESTS', array('activate_key' => $_Oli->getUrlParam(3))) AND $_Oli->issetPostVars()) {
	if(empty($_Oli->getPostVars('newPassword'))) $resultCode = 'PASSWORD_EMPTY';
	else if($_Oli->isExistAccountInfos('REQUESTS', array('activate_key' => $_Oli->getUrlParam(3)))
	AND $_Oli->getAccountInfos('REQUESTS', 'action', array('activate_key' => $_Oli->getUrlParam(3))) == 'change-password') {
		if(strtotime($_Oli->getAccountInfos('REQUESTS', 'expire_date', array('activate_key' => $_Oli->getUrlParam(3)))) >= time()) {
			$_Oli->updateAccountInfos('ACCOUNTS', array('password' => $_Oli->hashPassword($_Oli->getPostVars('newPassword'))), array('username' => $_Oli->getAccountInfos('REQUESTS', 'username', array('activate_key' => $_Oli->getUrlParam(3)))));
			$_Oli->deleteAccountLines('SESSIONS', array('username' => $_Oli->getAccountInfos('REQUESTS', 'username', array('activate_key' => $_Oli->getUrlParam(3)))));
			$_Oli->deleteAccountLines('REQUESTS', array('activate_key' => $_Oli->getUrlParam(3)));
			$resultCode = 'CHANGE_PASSWORD_OK';
			$hideChangePasswordUI = true;
		}
		else $resultCode = 'CHANGE_PASSWORD_EXPIRED';
	}
	else $resultCode = 'CHANGE_PASSWORD_FAILED';
}
else if($_Oli->verifyAuthKey() AND $_Oli->getUserRightLevel(array('username' => $_Oli->getAuthKeyOwner())) >= $_Oli->translateUserRight('USER')) header('Location: ' . $_Oli->getUrlParam(0));
else if($_Oli->getUrlParam(2) == 'activate' AND !empty($_Oli->getUrlParam(3))) {
	if($_Oli->isExistAccountInfos('REQUESTS', array('activate_key' => $_Oli->getUrlParam(3)))
	AND $_Oli->getAccountInfos('REQUESTS', 'action', array('activate_key' => $_Oli->getUrlParam(3))) == 'activate') {
		if(strtotime($_Oli->getAccountInfos('REQUESTS', 'expire_date', array('activate_key' => $_Oli->getUrlParam(3)))) >= time()) {
			$_Oli->updateUserRight('USER', array('username' => $_Oli->getAccountInfos('REQUESTS', 'username', array('activate_key' => $_Oli->getUrlParam(3)))));
			$_Oli->deleteAccountLines('REQUESTS', array('activate_key' => $_Oli->getUrlParam(3)));
			$resultCode = 'ACTIVATE_OK';
		}
		else $resultCode = 'ACTIVATE_EXPIRED';
	}
	else $resultCode = 'ACTIVATE_FAILED';
}
else if($_Oli->getUrlParam(2) == 'recover' AND $_Oli->issetPostVars()) {
	if(empty($_Oli->getPostVars('email'))) $resultCode = 'EMAIL_EMPTY';
	else if($_Oli->isExistAccountInfos('REQUESTS', array('username' => $_Oli->getAccountInfos('ACCOUNTS', 'username', array('email' => $_Oli->getPostVars('email'))), 'action' => 'change-password')) AND strtotime($_Oli->getAccountInfos('REQUESTS', 'expire_date', array('username' => $_Oli->getAccountInfos('ACCOUNTS', 'username', array('email' => $_Oli->getPostVars('email'))), 'action' => 'change-password'))) >= time())
		$resultCode = 'REQUEST_ALREADY_EXIST';
	else {
		$activateKey = $_Oli->createRequest($_Oli->getAccountInfos('ACCOUNTS', 'username', array('email' => $_Oli->getPostVars('email'))), 'change-password');
		
		$email = $_Oli->getPostVars('email');
		$subject = 'Changez votre mot de passe';
		$message = 'Bonjour ' . $_Oli->getAccountInfos('ACCOUNTS', 'username', array('email' => $_Oli->getPostVars('email'))) . ', <br />';
		$message .= 'Une requête de changement de mot de passe a été créée pour votre compte <br /> <br />';
		$message .= 'Rendez-vous sur ce lien pour choisir votre nouveau mot de passe : <br />';
		$message .= '<a href="' . $_Oli->getShortcutLink('login') . '/change-password/' . $activateKey . '">' . $_Oli->getShortcutLink('login') . '/change-password/' . $activateKey . '</a> <br /> <br />';
		$message .= 'Vous avez jusqu\'au ' . date('d/m/Y', strtotime($_Oli->getAccountInfos('REQUESTS', 'expire_date', array('username' => $_Oli->getAccountInfos('ACCOUNTS', 'username', array('email' => $_Oli->getPostVars('email'))), 'action' => 'change-password'))) + $_Oli->getRequestsExpireDelay()) . ', <br />';
		$message .= 'Une fois cette date passée, le code d\'activation ne sera plus valide <br /> <br />';
		$message .= 'Si vous n\'avez pas demandé ce changement de mot de passe, veuillez ignorer ce message <br />';
		$message .= 'Si vous avez l\'occasion de vous connecter sur le site, vous pouvez, depuis le panel, annuler cette requête';
		$headers = 'From: noreply@' . $_Oli->getSetting('domain') . "\r\n";
		$headers .= 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		$mailStatus = mail($email, $subject, utf8_decode($message), $headers);
		
		if($mailStatus) {
			$resultCode = 'RECOVER_OK';
			$hideRecoverUI = true;
		}
		else {
			$_Oli->deleteAccountLines('REQUESTS', array('activate_key' => $activateKey));
			$resultCode = 'RECOVER_FAILED';
		}
	}
}
else if($_Oli->issetPostVars()) {
	if(empty($_Oli->getPostVars('username'))) $resultCode = 'USERNAME_EMPTY';
	else if(empty($_Oli->getPostVars('password'))) $resultCode = 'PASSWORD_EMPTY';
	else if(!$_Oli->isExistAccountInfos('ACCOUNTS', array('username' => $_Oli->getPostVars('username')), false) AND $_Oli->issetPostVars('email')) {
		if(empty($_Oli->getPostVars('email'))) $resultCode = 'EMAIL_EMPTY';
		else if($_Oli->isProhibitedUsername($_Oli->getPostVars('username'))) $resultCode = 'PROHIBITED_USERNAME';
		else if($_Oli->registerAccount(trim($_Oli->getPostVars('username')), $_Oli->getPostVars('password'), strtolower(trim($_Oli->getPostVars('email'))))) {
			if($_Oli->getRegisterVerificationStatus()) $resultCode = 'REGISTER_CHECK_MAIL';
			else $resultCode = 'REGISTER_OK';
		}
		else $resultCode = 'REGISTER_FAILED';
	}
	else {
		if(!$_Oli->isExistAccountInfos('ACCOUNTS', array('username' => $_Oli->getPostVars('username')), false)) $resultCode = 'UNKNOWN_USER';
		else if($_Oli->getUserRightLevel(array('username' => $_Oli->getPostVars('username'))) == $_Oli->translateUserRight('NEW-USER')) $resultCode = 'NOT_ACTIVATED';
		else if($_Oli->getUserRightLevel(array('username' => $_Oli->getPostVars('username'))) == $_Oli->translateUserRight('BANNED')) $resultCode = 'BANNED_USER';
		else if($_Oli->verifyLogin($_Oli->getPostVars('username'), $_Oli->getPostVars('password'))) {
			$loginDuration = ($_Oli->getPostVars('rememberMe')) ? 15*24*3600 : 24*3600; // 15 days : 1 day
			if($_Oli->loginAccount($_Oli->getPostVars('username'), $_Oli->getPostVars('password'), $loginDuration)) {
				$resultCode = 'LOGIN_OK';
				
				if(!empty($_Oli->getPostVars('referer'))) header('Location: ' . $_Oli->getPostVars('referer'));
				else header('Location: ' . $_Oli->getUrlParam(0));
			}
			else $resultCode = 'LOGIN_FAILED';
		}
		else $resultCode = 'LOGIN_FAILED';
	}
}

?>

<!DOCTYPE html>
<html>
<head>

<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="description" content="Login Page for Oli Framework" />
<meta name="keywords" content="Login,Oli,Framework,PHP" />

<style>html,body,div,span,applet,object,iframe,h1,h2,h3,h4,h5,h6,p,blockquote,pre,a,abbr,acronym,address,big,cite,code,del,dfn,em,img,ins,kbd,q,s,samp,small,strike,strong,sub,sup,tt,var,b,u,i,center,dl,dt,dd,ol,ul,li,fieldset,form,label,legend,table,caption,tbody,tfoot,thead,tr,th,td,article,aside,canvas,details,embed,figure,figcaption,footer,header,hgroup,menu,nav,output,ruby,section,summary,time,mark,audio,video{margin:0;padding:0;border:0;font-size:100%;font:inherit;vertical-align:baseline}article,aside,details,figcaption,figure,footer,header,hgroup,menu,nav,section{display:block}body{line-height:1}ol,ul{list-style:none}blockquote,q{quotes:none}blockquote:before,blockquote:after,q:before,q:after{content:'';content:none}table{border-collapse:collapse;border-spacing:0}</style>
<link rel='stylesheet prefetch' href='http://fonts.googleapis.com/css?family=Roboto:400,100,300,500,700,900|RobotoDraft:400,100,300,500,700,900'>
<link rel='stylesheet prefetch' href='http://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css'>
<style>body{background:#e9e9e9;color:#666;font-family:'RobotoDraft', 'Roboto', sans-serif;font-size:14px;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale}.header{padding:50px 0;text-align:center;letter-spacing:2px}.header h1{margin:0 0 20px;font-size:48px;font-weight:400}.header h1 a{color:#0084b4;font-weight:400;text-decoration:none}.header span{font-size:12px}.header span .fa{color:#0084b4}.header span a{color:#0084b4;font-weight:600;text-decoration:none}.message,.form-module{position:relative;background:#fff;max-width:320px;width:100%;margin:0 auto 30px;border-top:5px solid #0084b4;box-shadow:0 0 3px rgba(0, 0, 0, 0.1)}.message.message-error{border-top:5px solid #d9534f}.message .content{padding:20px 40px}.message h2{color:#555;font-size:16px;font-weight:400;line-height:1}.form-module .toggle{cursor:pointer;position:absolute;top:-0;right:-0;background:#0084b4;width:30px;height:30px;margin:-5px 0 0;color:#fff;font-size:12px;line-height:30px;text-align:center}.form-module .toggle .tooltip{position:absolute;top:8px;right:40px;display:block;background:rgba(0, 0, 0, 0.6);width:auto;padding:5px;font-size:10px;line-height:1;text-transform:uppercase}.form-module .toggle .tooltip:before{content:'';position:absolute;top:5px;right:-5px;display:block;border-top:5px solid transparent;border-bottom:5px solid transparent;border-left:5px solid rgba(0, 0, 0, 0.6)}.form-module .form{display:none;padding:40px}.form-module .form:first-child,.form-module .form:nth-child(2){display:block}.form-module h2{margin:0 0 20px;color:#0084b4;font-size:18px;font-weight:400;line-height:1}.form-module input{outline:none;display:block;width:100%;border:1px solid #d9d9d9;margin:0 0 20px;padding:10px 15px;box-sizing:border-box;font-weight:400;-webkit-transition:.3s ease;transition:.3s ease}.form-module .checkbox{display:block;margin:0 0 20px;padding:0 10px;font-weight:300;-webkit-transition:.3s ease;transition:.3s ease}.form-module .checkbox > label{cursor:pointer}.form-module .checkbox > label > input[type=checkbox]{display:initial;width:auto;margin:0;margin-top:1px\9;line-height:normal}.form-module input:focus{border:1px solid #0084b4;color:#333}.form-module button{cursor:pointer;background:#0084b4;width:100%;border:0;padding:10px 15px;color:#fff;-webkit-transition:.3s ease;transition:.3s ease}.form-module button:hover{background:#178ab4}.form-module .cta{background:#f2f2f2;width:100%;padding:15px 40px;box-sizing:border-box;color:#666;font-size:12px;text-align:center}.form-module .cta a{color:#333;text-decoration:none}.footer{text-align:center;letter-spacing:2px}.footer span{font-size:12px}.footer span .fa{color:#0084b4}.footer span a{color:#0084b4;font-weight:600;text-decoration:none}</style>

<title>Login - <?php echo $_Oli->getSetting('name'); ?></title>

</head>
<body>

<div class="header">
	<h1><a href="<?php echo $_Oli->getUrlParam(0); ?>"><?php echo $_Oli->getSetting('name'); ?></a></h1>
	<span>Powered by Oli Framework</span>
</div>

<?php if($resultCode == 'USERNAME_EMPTY') { ?>
	<div class="message message-error">
		<div class="content">
			<h2>L'identifiant est vide</h2>
		</div>
	</div>
<?php } else if($resultCode == 'PASSWORD_EMPTY') { ?>
	<div class="message message-error">
		<div class="content">
			<h2>Le mot de passe est vide</h2>
		</div>
	</div>
<?php } else if($resultCode == 'EMAIL_EMPTY') { ?>
	<div class="message message-error">
		<div class="content">
			<h2>L'email est vide</h2>
		</div>
	</div>
<?php } else if($resultCode == 'REGISTER_OK') { ?>
	<div class="message">
		<div class="content">
			<h2>Votre compte a été correctement créé, vous pouvez maintenant vous connecter</h2>
		</div>
	</div>
<?php } else if($resultCode == 'REGISTER_CHECK_MAIL') { ?>
	<div class="message">
		<div class="content">
			<h2>Votre compte a été créé mais doit être activé, vérifier vos mails et rendez vous sur le lien d'activation pour terminer votre inscription</h2>
		</div>
	</div>
<?php } else if($resultCode == 'PROHIBITED_USERNAME') { ?>
	<div class="message message-error">
		<div class="content">
			<h2>Le nom d'utilisateur que vous avez choisi est interdit</h2>
		</div>
	</div>
<?php } else if($resultCode == 'REGISTER_FAILED') { ?>
	<div class="message message-error">
		<div class="content">
			<h2>L'inscription n'a pas aboutie, l'utilisateur existe peut être déjà</h2>
		</div>
	</div>
<?php } else if($resultCode == 'ACTIVATE_OK') { ?>
	<div class="message">
		<div class="content">
			<h2>Votre compte a été correctement activé, vous pouvez maintenant l'utiliser</h2>
		</div>
	</div>
<?php } else if($resultCode == 'ACTIVATE_EXPIRED') { ?>
	<div class="message message-error">
		<div class="content">
			<h2>L'activation n'a pas aboutie, le code d'activation a déjà expiré, réinscrivez-vous pour en avoir un nouveau</h2>
		</div>
	</div>
<?php } else if($resultCode == 'ACTIVATE_FAILED') { ?>
	<div class="message message-error">
		<div class="content">
			<h2>L'activation n'a pas aboutie, le code d'activation n'est pas valide</h2>
		</div>
	</div>
<?php } else if($resultCode == 'UNKNOWN_USER') { ?>
	<div class="message message-error">
		<div class="content">
			<h2>Vous avez tenter de vous connecter à un compte qui n'existe pas, vérifiez le nom d'utilisateur et réessayez</h2>
		</div>
	</div>
<?php } else if($resultCode == 'NOT_ACTIVATED') { ?>
	<div class="message message-error">
		<div class="content">
			<h2>Vous ne pouvez pas vous connecter à un compte qui n'a pas été activé, vérifiez votre boîte mail et activez-le</h2>
		</div>
	</div>
<?php } else if($resultCode == 'BANNED_USER') { ?>
	<div class="message message-error">
		<div class="content">
			<h2>Vous ne pouvez pas vous connecter à un compte banni ! Contactez éventuellement un administrateur.</h2>
		</div>
	</div>
<?php } else if($resultCode == 'LOGIN_OK') { ?>
	<div class="message">
		<div class="content">
			<h2>Vous êtes maintenant connecté</h2>
		</div>
	</div>
<?php } else if($resultCode == 'LOGIN_FAILED') { ?>
	<div class="message message-error">
		<div class="content">
			<h2>La connexion n'a pas aboutie, vérifiez votre identifiant et votre mot de passe</h2>
		</div>
	</div>
<?php } else if($resultCode == 'LOGOUT_OK') { ?>
	<div class="message">
		<div class="content">
			<h2>Vous avez été correctement déconnecté</h2>
		</div>
	</div>
<?php } else if($resultCode == 'RECOVER_OK') { ?>
	<div class="message">
		<div class="content">
			<h2>Un email de récupération vous a été envoyé</h2>
		</div>
	</div>
<?php } else if($resultCode == 'REQUEST_ALREADY_EXIST') { ?>
	<div class="message message-error">
		<div class="content">
			<h2>Une requête semblable existe déjà et empêche la création d'une nouvelle</h2>
		</div>
	</div>
<?php } else if($resultCode == 'RECOVER_FAILED') { ?>
	<div class="message message-error">
		<div class="content">
			<h2>L'envoi du mail de récupération a échoué</h2>
		</div>
	</div>
<?php } else if($resultCode == 'CHANGE_PASSWORD_OK') { ?>
	<div class="message">
		<div class="content">
			<h2>Votre mot de passe a été changé</h2>
		</div>
	</div>
<?php } else if($resultCode == 'CHANGE_PASSWORD_EXPIRED') { ?>
	<div class="message message-error">
		<div class="content">
			<h2>Le changement de mot de passe n'a pas abouti, le code d'activation a déjà expiré</h2>
		</div>
	</div>
<?php } else if($resultCode == 'CHANGE_PASSWORD_FAILED') { ?>
	<div class="message message-error">
		<div class="content">
			<h2>Le changement de votre mot de passe a échoué</h2>
		</div>
	</div>
<?php } ?>

<div class="module form-module">
	<?php if($_Oli->getUrlParam(2) == 'recover' AND !$hideRecoverUI) { ?>
		<div class="form">
			<h2>Recover your account</h2>
			<form action="<?php echo $_Oli->getUrlParam(0); ?>form.php?callback=<?php echo urlencode($_Oli->getUrlParam(0) . 'login/recover'); ?>" method="post">
				<input type="email" name="email" placeholder="Email Address" />
				<button type="submit">Recover</button>
			</form>
		</div>
		<div class="cta"><a href="<?php echo $_Oli->getUrlParam(0); ?>form.php?callback=<?php echo urlencode($_Oli->getUrlParam(0) . 'login/'); ?>">Login to your account</a></div>
	<?php } else if($_Oli->getUrlParam(2) == 'change-password' AND $_Oli->isExistAccountInfos('REQUESTS', array('activate_key' => $_Oli->getUrlParam(3))) AND !$hideChangePasswordUI) { ?>
		<div class="form">
			<h2>Change your pasword</h2>
			<form action="<?php echo $_Oli->getUrlParam(0); ?>form.php?callback=<?php echo urlencode($_Oli->getUrlParam(0) . 'login/change-password/' . $_Oli->getUrlParam(3)); ?>" method="post">
				<input type="text" name="username" value="<?php echo $_Oli->getAccountInfos('REQUESTS', 'username', array('activate_key' => $_Oli->getUrlParam(3))); ?>" disabled />
				<input type="text" name="activateKey" value="<?php echo $_Oli->getUrlParam(3); ?>" disabled />
				<input type="password" name="newPassword" placeholder="New Password" />
				<button type="submit">Change</button>
			</form>
		</div>
		<div class="cta"><a href="<?php echo $_Oli->getUrlParam(0); ?>login/">Login to your account</a></div>
	<?php } else { ?>
		<div class="toggle"><i class="fa fa-times fa-pencil"></i>
			<div class="tooltip">Register</div>
		</div>
		<div class="form">
			<h2>Login to your account</h2>
			<form action="<?php echo $_Oli->getUrlParam(0); ?>form.php?callback=<?php echo urlencode($_Oli->getUrlParam(0) . 'login/'); ?>" method="post">
				<input type="text" name="username" placeholder="Username" />
				<input type="password" name="password" placeholder="Password" />
				<?php if(!empty($_Oli->getPostVars('referer')) OR !empty($_SERVER['HTTP_REFERER'])) { ?>
					<input type="hidden" name="referer" value="<?php echo (!empty($_Oli->getPostVars('referer'))) ? $_Oli->getPostVars('referer') : $_SERVER['HTTP_REFERER']; ?>" />
				<?php } ?>
				<div class="checkbox"><label><input type="checkbox" name="rememberMe" checked /> "Run clever boy, and remember me"</label></div>
				<button type="submit">Login</button>
			</form>
		</div>
		<div class="form">
			<h2>Create an account</h2>
			<form action="<?php echo $_Oli->getUrlParam(0); ?>form.php?callback=<?php echo urlencode($_Oli->getUrlParam(0) . 'login/register'); ?>" method="post">
				<input type="text" name="username" placeholder="Username" />
				<input type="password" name="password" placeholder="Password" />
				<input type="email" name="email" placeholder="Email Address" />
				<button type="submit">Register</button>
			</form>
		</div>
		<div class="cta"><a href="<?php echo $_Oli->getUrlParam(0); ?>login/recover">Forgot your password?</a></div>
	<?php } ?>
</div>

<div class="footer">
	<span><i class="fa fa-paint-brush"></i> Template by Andy Tran</span> - <span><i class="fa fa-code"></i> by Matiboux</span>
</div>

<script src='http://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.4/jquery.min.js'></script>
<script>$(".toggle").click(function(){$(this).children("i").toggleClass("fa-pencil");if($(this).children(".tooltip").text() == "Register"){$(this).children(".tooltip").text("Login");}else{$(this).children(".tooltip").text("Register");}$(".form").animate({height:"toggle","padding-top":"toggle","padding-bottom":"toggle",opacity:"toggle"},"slow")});</script>

</body>
</html>