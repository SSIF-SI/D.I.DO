<?php
class SignatureDispatcher{
	
	const SIGNED_PREFIX = "_signed";
	
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
		
		Utils::printr($basePath);
		
		var_dump(mkdir($basePath, 0775));
		
		$fileName = $this->generateFilename($pathParts, "fromFtpToServer");
		
		$tmpFilename = $this->_ftpDataSource->getTempFile($pathFile, $basePath);
		rename($tmpFilename, $fileName);
	}
	
	private function generateFilename($pathParts, $transformCallback){
		return $this->$transformCallback($pathParts);
	}
	
	private function fromFtpToServer($pathParts){
		
		$suffix = join("|", $pathParts);
		
		$docFilename = array_pop($pathParts);

		$fileParts = pathinfo($docFilename);
		$docFilenameExt = $fileParts['extension'];
		
		$newFilename = str_replace(".".$docFilenameExt,"",$docFilename);
		$newFilename .= "______|" .$suffix;
		
		return $newFilename;
	}
	
	private function fromServerToFtp($pathParts){
		return $newPath;
	}
	
	public function copy($source, $destination){
		
	}
}