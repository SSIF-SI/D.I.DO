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

	public function getNewPathFromXml($xml) {
		return dirname ( $xml ) . DIRECTORY_SEPARATOR . date ( "Y" ) . DIRECTORY_SEPARATOR . date ( "m" ) . DIRECTORY_SEPARATOR;
	}

	public function getFolderNameFromMasterdocument($md) {
		return Common::fieldFromLabel ( $md[Masterdocument::NOME] . " " . $md[Masterdocument::ID_MD]);
	}

	public function getFilenameFromDocument($document) {
		return Common::fieldFromLabel ( $document [Document::NOME] . " " . $document [Document::ID_DOC] . "." . $document [Document::EXTENSION] );
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
	
}

?>