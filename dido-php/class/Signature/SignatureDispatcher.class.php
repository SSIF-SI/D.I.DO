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
				
		mkdir($basePath, 0775);
		
		$fileName = $this->generateFilename($pathParts, "fromFtpToServer");
		
		$tmpFilename = $this->_ftpDataSource->getTempFile($pathFile, $basePath);

		chmod($tmpFilename, 0755);
		
		
		return rename($tmpFilename, $basePath.$fileName);
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
		
// 		atto_145______|pec|2017|11|attività_pec_114|atto_145_signed.pdf
		$filename=array_shift($pathParts);
		$filename=trim($filename,"_");
		$docFilename = array_pop($pathParts);
		$fileParts = pathinfo($docFilename);
		$docFilenameExt = $fileParts['extension'];
		array_push($pathParts,$filename.".".$docFilenameExt);
		$newPath=join(DIRECTORY_SEPARATOR, $pathParts);
		
		
		return $newPath;
	}
	public function test($pathFile){
		$pathParts = explode( "|", $pathFile);
		return 	$fileName = $this->generateFilename($pathParts, "fromServerToFtp");
		
	}
	public function copy($source, $destination){
		
	}
}