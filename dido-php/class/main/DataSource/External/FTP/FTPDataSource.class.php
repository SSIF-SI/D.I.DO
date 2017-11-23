<?php

class FTPDataSource implements IFTPDataSource{

	private $_ftpConnector;

	public function __construct(IFTPConnector $ftpConnector = null) {
		$this->_ftpConnector = is_null ( $ftpConnector ) ? new FTPConnector() : $ftpConnector;
	}

	public function createFolder($folder) {
		return $this->_ftpConnector->mksubdirs ( $folder );
	}

	public function deleteFolder($folder) {
		return $this->_ftpConnector->deleteFolder ( $folder );
	}

	public function deleteFile($filePath) {
		return $this->_ftpConnector->delete ( $filePath );
	}
	
	public function deleteFolderRecursively($folder){
		return $this->_ftpConnector->deleteFolderRecursively($folder);
		
	}
	
	public function getTempFile($file, $tmpPath = FILES_PATH) {
		return $this->_ftpConnector->getTempFile($file, $tmpPath);
	}
	
	public function upload($source, $destination) {
		$result = $this->_ftpConnector->upload($source, $destination);
		unlink($source);
		return $result;
	}
	
	public function download($filename){
		$this->_ftpConnector->download($filename);
	}
	
	public function rename($oldPath,$newPath){
		$this->_ftpConnector->rename($oldPath, $newPath);
	}
	
}

?>