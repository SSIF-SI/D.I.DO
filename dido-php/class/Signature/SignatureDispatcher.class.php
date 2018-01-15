<?php
class SignatureDispatcher{
	
	const SIGNED_PREFIX = "_signed";
	
	private static $directoryMapper = [
		"DIR"	=> "direttore"
	];
	
	public function __construct(IFTPDataSource $ftpDataSource){
		$this->_ftpDataSource = $ftpDataSource;
	}
	
	private function generateFilename($sourceFilename, $transformCallback){
		
	}
	
	private function fromFtpToServer($path){
		return $newPath;
	}
	
	private function fromServerToFtp($path){
		return $newPath;
	}
	
	public function copy($source, $destination){
		
	}
}