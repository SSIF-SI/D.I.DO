<?php
class SignatureDispatcher{
	
	const SIGNED_PREFIX = "_signed";
	const PIPE = "#";
	const SAMBAGROUP = "sambadido";
	const OVERWRITE_FILE_SIGNED = "overwrite_file_signed";
	
	private $_ftpDataSource;
	
	private static $directoryMapper = [
		"DIR"	=> "direttore"
	];
	
	public function __construct(IFTPDataSource $ftpDataSource){
		$this->_ftpDataSource = $ftpDataSource;
	}
	
	public function dispatch($role, $pathFile){
		
		if(!array_key_exists($role, self::$directoryMapper)) return false;
		
		$pathParts = explode( DIRECTORY_SEPARATOR, $pathFile);
		
		$basePath = SAMBA_ROOT . self::$directoryMapper[$role] . DIRECTORY_SEPARATOR .reset($pathParts). DIRECTORY_SEPARATOR;
				
		if(@mkdir($basePath)){
			chgrp($basePath,self::SAMBAGROUP);
			chmod($basePath, 0775);
		}
		
		$fileName = $basePath . $this->generateFilename($pathParts, "fromFtpToServer");
		
		if(Session::getInstance()->exists(self::OVERWRITE_FILE_SIGNED)){
			Session::getInstance()->delete(self::OVERWRITE_FILE_SIGNED);
		} else {
			if(file_exists($fileName)){
				return true;
			}
		}
		
		$tmpFilename = $this->_ftpDataSource->getTempFile($pathFile, $basePath);

		chmod($tmpFilename, 0775);
		$result = rename($tmpFilename, $fileName);
		chgrp($fileName,self::SAMBAGROUP);
		return $result;
	}
	
	private function generateFilename($pathParts, $transformCallback){
		return $this->$transformCallback($pathParts);
	}
	
	private function fromFtpToServer($pathParts){
		
		$suffix = join(self::PIPE, $pathParts);
		
		$docFilename = array_pop($pathParts);

		$fileParts = pathinfo($docFilename);
		$docFilenameExt = $fileParts['extension'];
		
		$newFilename = str_replace(".".$docFilenameExt,"",$docFilename);
		$newFilename .= "______".self::PIPE .$suffix;
		
		return $newFilename;
	}
	
	private function fromServerToFtp($pathParts){
		
// 		atto_145______{self::PIPE}pec{self::PIPE}2017{self::PIPE}11{self::PIPE}attività_pec_114{self::PIPE}atto_145_signed.pdf
		$filename=array_shift($pathParts);
		$filename=rtrim($filename,"_");
		$docFilename = array_pop($pathParts);
		$fileParts = pathinfo($docFilename);
		$docFilenameExt = $fileParts['extension'];
		array_push($pathParts,$filename.".".$docFilenameExt);
		$newPath=join(DIRECTORY_SEPARATOR, $pathParts);
		return $newPath;
	}
	
	public function put($pathFile){
		$pathParts = explode( self::PIPE, basename($pathFile));
		$fileName = $this->generateFilename($pathParts, "fromServerToFtp");
		// Upload To ftp
		self::printLog($pathFile." -> ".$fileName);
		return $this->_ftpDataSource->upload($pathFile, $fileName);
	}
	
	public function scan(){
		self::printLog("Scanning folder(s) for signed files..");
		$signedDocs = [];
		$purgeDirs = [];
		foreach(self::$directoryMapper as $role=>$path){
			$types = glob(SAMBA_ROOT . $path . DIRECTORY_SEPARATOR . "*");
			if(count($types)){
				$purgeDirs = array_merge($purgeDirs, $types);
				foreach ($types as $type){
					self::printLog($type."...");
					$signed = glob($type. DIRECTORY_SEPARATOR. "*" . self::SIGNED_PREFIX. ".*");
					self::printLog(count($signed)." file(s) found");
					if(!empty($signed))
						$signedDocs = array_merge($signedDocs,$signed);
				}
			}
		}
		self::printLog(count($signedDocs)." total file(s) found");
		if(empty($signedDocs)){
			return;
		}
		
		foreach($signedDocs as $sd){
			self::printLog($this->put($sd) ? "OK." : "Error occurred. Please Verify.");
		}
		
		if(!count($purgeDirs))
			return;
		
		foreach($purgeDirs as $dir){
			if(!count(glob($dir.DIRECTORY_SEPARATOR."*"))){
				self::printLog("Removing folder $dir...");
				self::printLog(rmdir($dir) ? "Ok." : "Error occurred. Please Verify.");
			}
		}
	}
	
	private static function printLog($txt){
		print "[".date("Y-m-d H:i:s")."] - ".$txt.PHP_EOL;
	}
}