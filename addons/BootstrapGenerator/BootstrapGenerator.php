<?php
/*\
|*|
|*|  BOOTSTRAP GENERATOR
|*|    (Addon for Oli Framework)
|*|
|*|  Easy way to create a Bootstrap website
|*|
|*|  Created: 4th April 2016
|*|  Developper: Matiboux (http://twitter.com/Matiboux)
|*|
\*/

namespace BootstrapGenerator {

// use \BootstrapGenerator\HeaderCreator;

class BootstrapGenerator {
	/** Externals Class */
	private $_Oli;
	
	private $defaultCharset = 'utf-8';
	private $authorizedWebsites = null;
	private $apiUrl = '';
	
	
	/** ------- */
	/**  SETUP  */
	/** ------- */
	
	/** Construct */
	public function __construct() {
		global $_Oli;
		if(empty($_Oli))
			trigger_error('L\'objet du Framework Oli ($_Oli) n\'est pas défini', E_USER_ERROR);
		
		$this->_Oli = &$_Oli;
	}
	
	/** *** *** */
	
	/** --------------- */
	/**  Configuration  */
	/** --------------- */
	
	/** Default Charset Config */
	public function setDefaultCharset($charset = 'utf-8') {
		$this->defaultCharset = $charset;
		return true;
	}
	
	/** *** *** */
	
	/** Setup API */
	public function setupAPI($contentType) {
		if($this->authorizedWebsites == '*')
			header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
		else {
			foreach($this->authorizedWebsites as $eachWebsite) {
				if($_SERVER['HTTP_ORIGIN'] == $eachWebsite) {
					header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
					break;
				}
			}
		}
		
		$this->APIContentType = $contentType;
		$this->_Oli->setContentType($contentType, $this->defaultCharset, true);
		return true;
	}
	
	/** -------------------------------- */
	/**  POST/GET Parameters Management  */
	/** -------------------------------- */
	
	/** Get POST/GET Parameters */
	public function getPostParameters(&$parameters) {
		if(!empty($_POST)) {
			foreach($_POST as $eachKey => $eachValue) {
				$parameters[$eachKey] = $eachValue;
			}
			
			if(!empty($parameters))
				return true;
			else
				return false;
		}
		else
			return false;
	}
	public function getGetParameters(&$parameters) {
		if(!empty($_GET)) {
			foreach($_GET as $eachKey => $eachValue) {
				$parameters[$eachKey] = $eachValue;
			}
			
			if(!empty($parameters))
				return true;
			else
				return false;
		}
		else
			return false;
	}
	public function getParameters(&$parameters, $priority = 'POST') {
		if($priority == 'POST') {
			if(!empty($_POST)) {
				$this->getPostParameters($parameters);
				return true;
			}
			else if(!empty($_GET)) {
				$this->getGetParameters($parameters);
				return true;
			}
			else
				return false;
		}
		else if($priority == 'GET') {
			if(!empty($_GET)) {
				$this->getGetParameters($parameters);
				return true;
			}
			else if(!empty($_POST)) {
				$this->getPostParameters($parameters);
				return true;
			}
			else
				return false;
		}
		else
			return false;
	}
	
	/** -------------------- */
	/**  API Url Management  */
	/** -------------------- */
	
	/** Get API Url */
	public function getAPIUrl() {
		return $this->apiUrl;
	}
	
	/** ---------------------------- */
	/**  Response Management (JSON)  */
	/** ---------------------------- */
	
	/** Set Response */
	public function setResponse($key, $value) {
		$this->responseArray[$key] = $value;
		return true;
	}
	
	/** Get Response */
	public function getResponse($key = null) {
		if(!empty($key))
			return $this->responseArray[$key];
		else
			return $this->responseArray;
	}
	
	/** Encode Response */
	public function encodeResponse($options = null) {
		if(!empty($options))
			return json_encode($this->responseArray, $options);
		else
			return json_encode($this->responseArray);
	}
}

}