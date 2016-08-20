<?php
/*\
|*|
|*|  GRAVATAR ADDON
|*|    (for Oli Beta 1.7.0)
|*|
|*|  Use Gravatar Avatars & Profiles 
|*|
|*|  Creatied the 7 June 2016
|*|    by Matiboux (http://matiboux.com/)
|*|
|*|  Releases date:
|*|    * [version 1.0]: 7 June 2016
|*|
\*/

namespace Gravatar {

class Gravatar {
	
	/** ----------- */
	/**  Variables  */
	/** ----------- */
	
	private $_Oli;
	
	private $defaultAvatarUrl = null;
	private $defaultSize = '80';
	
	/** --------------- */
	/**  Magic Methods  */
	/** --------------- */
	
	/** Construct */
	public function __construct() {
		global $_Oli;
		if(!isset($_Oli)) trigger_error('Oli Object ($_Oli) must be defined!', E_USER_ERROR);
		$this->_Oli = &$_Oli;
	}
	public function __destruct() {}
	
	public function __toString() {
		return 'GRAVATAR ADDON for Oli';
	}
	
	/** *** *** */
	
	/** --------------- */
	/**  Config Loader  */
	/** --------------- */
		
	/** Load Config */
	public function loadConfig($config) {
		foreach($config as $eachConfig => $eachValue) {
			$eachValue = $this->_Oli->decodeConfigValues($eachValue);
			
			if($eachConfig == 'default_avatar')
				$this->setDefaultAvatarUrl($eachValue);
			else if($eachConfig == 'default_size')
				$this->setDefaultSize($eachValue);
		}
	}
	
	/** *** *** */
	
	/** --------------- */
	/**  Configuration  */
	/** --------------- */
	
	/** Default Avatar */
	public function setDefaultAvatarUrl($url) {
		$this->defaultAvatarUrl = $url;
	}
	public function getDefaultAvatar() {
		return $this->defaultAvatarUrl;
	}
	
	/** Default Size */
	public function setDefaultSize($url) {
		$this->defaultSize = $url;
	}
	public function getDefaultSize() {
		return $this->defaultSize;
	}
	
	/** *** *** */
	
	/** ---------- */
	/**  Gravatar  */
	/** ---------- */
	
	/** Get Gravatar */
	public function getGravatar($email = null, $size = null, $username = null) {
		$email = (!isset($email)) ? $this->_Oli->getAccountInfos('ACCOUNTS', 'email', array('username' => $this->_Oli->getAuthKeyOwner())) : $email;
		$size = (!isset($size)) ? $this->defaultSize : $size;
		return 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($email))) .'?s=' . $size . '&d=' . $this->defaultAvatarUrl;
	}
	public function getGravatarCode($email = null, $class = null, $alt = null, $size = null) {
		echo '<img src="' . $this->getGravatar($email, $size) . '" ' . (isset($class) ? 'class="' . $class . '" ' : '') . (isset($alt) ? 'alt="' . $alt . '" ' : '') . '/>';
	}
	
	/** ------------------- */
	/**  Gravatar Profiles  */
	/** ------------------- */
}

}