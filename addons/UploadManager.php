<?php
/*\
|*|
|*|  UPLOAD MANAGER 2.0
|*|    (for Oli Beta 1.7.0)
|*|
|*|  Easy way to Manage Accounts
|*|  Tools to speed up your website
|*|
|*|  Created on 22 November 2015
|*|    by Matiboux (http://matiboux.com/)
|*|
|*|  Releases date:
|*|    * [version 1.0]: 22 November 2015
|*|    * [version 2.0]: X July 2016
|*|
\*/

namespace UploadManager {

class UploadManager {
	
	/** ----------- */
	/**  Variables  */
	/** ----------- */
	
	private $_Oli;
	
	/** Uploads Table & Path */
	private $uploadsTable = null;
	private $uploadsPath = null;
	private $uploadsUrl = null;
	
	/** Files Limits */
	private $maxSize = 1024 ** 2; // 1 Mio = 1024 ** 2 octets
	private $allowedTypes = null;
	private $typesList = null;
	
	/** More */
	private $keygenLength = 16;
	
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
		return 'UPLOAD MANAGER 2.0 for Oli';
	}
	
	/** *** *** */
	
	/** --------------- */
	/**  Config Loader  */
	/** --------------- */
		
	/** Load Config */
	public function loadConfig($config) {
		foreach($config as $eachConfig => $eachValue) {
			$eachValue = $this->_Oli->decodeConfigValues($eachValue);
			
			if($eachConfig == 'upload_table') $this->setUploadTable($eachValue);
			else if($eachConfig == 'upload_path') $this->setUploadPath($eachValue);
			else if($eachConfig == 'upload_url') $this->setUploadUrl($eachValue);
			else if($eachConfig == 'max_size') $this->setMaxSize($eachValue);
			else if($eachConfig == 'allowed_types') $this->setAllowedTypes($eachValue);
			else if($eachConfig == 'types_list') $this->setTypesList($eachValue);
			else if($eachConfig == 'keygen_length') $this->setKeygenLength($eachValue);
		}
	}
	
	/** *** *** */
	
	/** --------------- */
	/**  Configuration  */
	/** --------------- */
	
		/** ---------------------- */
		/**  Uploads Table & Path  */
		/** ---------------------- */
		
		/** Uploads Table */
		public function setUploadTable($table) {
			$this->uploadsTable = $table;
		}
		public function getUploadsTable() {
			return $this->uploadsTable;
		}
		
		/** Uploads Path */
		public function setUploadPath($path) {
			$this->uploadsPath = $path;
		}
		public function getUploadsPath() {
			return $this->uploadsPath;
		}
		
		/** Uploads Url */
		public function setUploadUrl($url) {
			$this->uploadsUrl = $url;
		}
		public function getUploadsUrl() {
			return $this->uploadsUrl;
		}
		
		/** -------------- */
		/**  Files Limits  */
		/** -------------- */
		
		/** Max Size */
		public function setMaxSize($size) {
			$this->maxSize = $size;
		}
		public function getMaxSize($unit = null) {
			$maxSize = $this->maxSize;
			if(isset($unit)) {
				if($unit == 'TiB' OR $unit == 'Tio') $maxSize /= (1024 ** 4);
				else if($unit == 'GiB' OR $unit == 'Gio') $maxSize /= (1024 ** 3);
				else if($unit == 'MiB' OR $unit == 'Mio') $maxSize /= (1024 ** 2);
				else if($unit == 'KiB' OR $unit == 'Kio') $maxSize /= 1024;
				
				else if($unit == 'TB' OR $unit == 'To') $maxSize /= (1000 ** 4);
				else if($unit == 'GB' OR $unit == 'Go') $maxSize /= (1000 ** 3);
				else if($unit == 'MB' OR $unit == 'Mo') $maxSize /= (1000 ** 2);
				else if($unit == 'KB' OR $unit == 'Ko') $maxSize /= 1000;
				
				else if($unit == 'Tb') $maxSize /= (1000 ** 4) * 8;
				else if($unit == 'Gb') $maxSize /= (1000 ** 3) * 8;
				else if($unit == 'Mb') $maxSize /= (1000 ** 2) * 8;
				else if($unit == 'Kb') $maxSize /= 1000 * 8;
				else if($unit == 'b') $maxSize *= 8;
			}
			return $maxSize;
		}
		
		/** Allowed types */
		public function setAllowedTypes($types) {
			$this->allowedTypes = $types;
		}
		public function getAllowedFileTypes() {
			return $this->allowedTypes;
		}
		
		/** Types List */
		public function setTypesList($types) {
			$this->typesList = $types;
		}
		public function getTypesList() {
			return $this->typesList;
		}
		
		/** ------ */
		/**  More  */
		/** ------ */
		
		/** Keygen Length */
		public function setKeygenLength($length) {
			$this->keygenLength = $length;
		}
		public function getKeygenLength() {
			return $this->keygenLength;
		}
	
	/** *** *** */
	
	/** ------------- */
	/**  Files Infos  */
	/** ------------- */
	
		/** --------- */
		/**  General  */
		/** --------- */
		
		/** Get File Lines */
		public function getFileLines($where = [], $settings = null, $caseSensitive = null, $forceArray = null, $rawResult = null) {
			return $this->_Oli->getLinesMySQL($this->uploadsTable, $where, $settings, $caseSensitive, $forceArray, $rawResult);
		}
		
		/** Get File Infos */
		public function getFileInfos($whatVar, $where = [], $settings = null, $caseSensitive = null, $forceArray = null, $rawResult = null) {
			return $this->_Oli->getInfosMySQL($this->uploadsTable, $whatVar, $where, $settings, $caseSensitive, $forceArray, $rawResult);
		}
		
		/** Is Exist File Infos */
		public function isExistFileInfos($where = [], $caseSensitive = true) {
			return $this->_Oli->isExistInfosMySQL($this->uploadsTable, $where, $caseSensitive);
		}
		
		/** ----------- */
		/**  File Type  */
		/** ----------- */
		
		/** Get File Type */
		public function getFileType($where, $caseSensitive = true) {
			foreach($this->typesList as $eachType => $eachExtensions) {
				if(in_array($this->getFileInfos('file_type', $where, $caseSensitive), $eachExtensions)) {
					$output = 'text';
					break;
				}
			}
			return (isset($output)) ? $output : 'unknown';
		}
		
		/** ------------- */
		/**  Upload File  */
		/** ------------- */
		
		/** Upload File */
		public function uploadFile($file, $filePath = null, $settings = null) {
			if(is_array($filePath)) {
				$settings = isset($settings) ? $settings : $filePath;
				$filePath = null;
			}
			
			$filePathParts = explode('/', $filePath);
			if(substr($filePath, -1) == '/') $filePathParts[] = '';
			
			$pathAddon = implode('/', array_slice($filePathParts, 0, -1)) ?: '';
			$fileName = array_reverse($filePathParts)[0] ?: null;
			$fileExtension = strtolower(substr(strrchr($file['name'], '.'), 1));
			
			if($file['error'] == UPLOAD_ERR_OK AND $file['size'] <= $this->maxSize AND ($this->allowedTypes == '*' OR in_array($fileExtension, $this->allowedTypes))) {
				if(isset($fileName)) $fileKey = $fileName;
				else {
					$i = 0;
					do {
						$fileKey = $this->_Oli->keygen($this->keygenLength);
						$i++;
					} while($i < 10 AND $this->isExistFile($pathAddon . $fileKey . '.' . $fileExtension) AND $this->isExistFileInfos(array('path_addon' => $pathAddon, 'file_key' => $fileKey)));
				}
				
				if(!$this->isExistFile($pathAddon . $fileKey . '.' . $fileExtension) AND $this->isExistFileInfos(array('path_addon' => $pathAddon, 'file_key' => $fileKey)))
					$this->deleteFileInfos(array('path_addon' => $pathAddon, 'file_key' => $fileKey));
				
				if(!empty($fileKey) AND !$this->isExistFile($pathAddon . $fileKey . '.' . $fileExtension) AND !$this->isExistFileInfos(array('path_addon' => $pathAddon, 'file_key' => $fileKey))) {
					if(!file_exists($this->uploadsPath . $pathAddon)) mkdir($this->uploadsPath . $pathAddon);
					if(move_uploaded_file($file['tmp_name'], $this->uploadsPath . $pathAddon . $fileKey . '.' . $fileExtension)) {
						$uploadMatches['id'] = (isset($settings['id'])) ? $settings['id'] : $this->_Oli->getLastInfoMySQL($this->uploadsTable, 'id') + 1;
						$uploadMatches['name'] = (isset($settings['name'])) ? $settings['name'] : ($fileName ?: '');
						$uploadMatches['owner'] = (isset($settings['owner'])) ? $settings['owner'] : '';
						$uploadMatches['date'] = (isset($settings['date'])) ? $settings['date'] : date('Y-m-d H:i:s');
						$uploadMatches['path_addon'] = $pathAddon ?: '';
						$uploadMatches['file_key'] = $fileKey;
						$uploadMatches['file_type'] = $fileExtension;
						$uploadMatches['file_name'] = $fileKey . '.' . $fileExtension;
						$uploadMatches['file_size'] = $file['size'];
						$uploadMatches['file_hash'] = sha1_file($this->uploadsPath . $pathAddon . $fileKey . '.' . $fileExtension);
						$uploadMatches['file_hash_algo'] = 'sha1';
						$uploadMatches['original_file_name'] = $file['name'];
						
						if($this->_Oli->insertLineMySQL($this->uploadsTable, $uploadMatches)) return $fileKey;
						else return false;
					}
					else return false;
				}
				else return false;
			}
			else return false;
		}
			
		/** --------------------- */
		/**  Edit & Delete Infos  */
		/** --------------------- */

		/** Update File Infos */
		public function updateFileInfos($what, $where) {
			return $this->_Oli->updateInfosMySQL($this->uploadsTable, $what, $where);
		}
		
		/** Delete File Infos */
		public function deleteFileInfos($where) {
			if($this->isExistFileInfos($where)) return $this->_Oli->deleteLinesMySQL($this->uploadsTable, $where);
			else return false;
		}

	/** ---------------- */
	/**  Uploaded Files  */
	/** ---------------- */
	
	/** Is Exist File */
	public function isExistFile($filePath) {
		return !empty(glob($this->uploadsPath . $filePath));
	}
	
	/** Delete File */
	public function deleteFile($filePath) {
		$result = array_map('unlink', glob($this->uploadsPath . $filePath));
		return count($result) == 1 ? $result[0] : $result;
	}
}

}