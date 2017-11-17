<?php

class ProcedureManager implements IProcedureManager {

	private $_dbConnector;

	private $_MDPManager;

	private $_DPManager;

	private $_FTPDataSource;
	
	const OPEN = 0;
	
	const CLOSED = 1;
	
	const INCOMPLETE = - 1;

	public function __construct(IDBConnector $dbConnector, IFTPDataSource $ftpDataSource) {
		$this->_dbConnector = $dbConnector;
		$this->_MDPManager = new MasterDocumentProcedureManager ( $dbConnector );
		$this->_DPManager = new DocumentProcedureManager ( $dbConnector );
		$this->_FTPDataSource = $ftpDataSource;
	}

	public function createMasterdocument($md, $md_data) {

		// Creo il MD;
		$this->_dbConnector->begin ();

		$md [Masterdocument::FTP_FOLDER] = Common::getNewPathFromXml ( $md [Masterdocument::XML] );
		$new_md = $this->_MDPManager->create ( $md, $md_data );
		if (! $new_md) {
			
			$this->removeMasterdocumentFolder( $md [Masterdocument::FTP_FOLDER] );
			$this->_dbConnector->rollback ();
			return false;
		}
		// Creo cartella ftp
		
		if (! $this->_FTPDataSource->createFolder ( $new_md [Masterdocument::FTP_FOLDER] 
				. Common::getFolderNameFromMasterdocument($new_md))) {
			return false;
		}

		$this->_dbConnector->commit ();
		return $new_md;
	}

	public function updateMasterdocument($md_data) {
		return $this->_MDPManager->update ( $md_data );
	}

	public function updateDocumentData($data) {
		return $this->_DPManager->update( $data );
	}

	public function closeMasterdocument($md, $status) {
		return $this->_MDPManager->close($md, $status );
	}
	
	public function closeDocument($doc) {
		return $this->_DPManager->close($doc, self::CLOSED );
	}
	
	public function deleteMasterdocument($md) {
		// Elimino il MD dal DB
		
		$this->_dbConnector->begin ();
		
		if(!$this->_MDPManager->delete ( $md)){
			$this->_dbConnector->rollback ();
			return false;
		}
		// Elimino la folder del MasterDocument dall'FTP
		if (! $this->_FTPDataSource->deleteFolderRecursively($md [Masterdocument::FTP_FOLDER]
				. Common::getFolderNameFromMasterdocument($md))) {
			$this->_dbConnector->rollback ();
			return false;
		}
		$this->_dbConnector->commit();
		return true;
	}

	public function removeMasterdocumentFolder($repositoryPath) {
		$this->_FTPDataSource->deleteFolder ( $repositoryPath );
	}

	public function createDocument($doc, $data, $filePath, $repositoryPath) {
		$this->_dbConnector->begin ();
		$new_doc = $this->_DPManager->create ( $doc, $data );
		if (! $new_doc) {
			$this->_dbConnector->rollback ();
			return false;
		}
		
		if (! $this->uploadFile( $new_doc, $filePath, $repositoryPath )) {
			$this->_dbConnector->rollback ();
			return false;
		}
		
		$this->_dbConnector->commit ();
		return $new_doc;
	}

	public function updateDocument($document, $data = null, $filePath = null, $repositoryPath = null) {
		$this->_dbConnector->begin ();
		
		if (!is_null($data) && !$this->updateDocumentData( $data )) {
			$this->_dbConnector->rollback ();
			return false;
		}
		
		if (! is_null ( $filePath ) && ! is_null ( $repositoryPath )) {
			if (! $this->uploadFile ( $document, $filePath, $repositoryPath )) {
				$this->_dbConnector->rollback ();
				return false;
			}
		}
		
		$this->_dbConnector->commit ();
		return true;
	}

	public function deleteDocument($doc, $ftpFolder) {
		//finfo(__METHOD__);
// 		flog($doc);
// 		flog($ftpFolder);
		$this->_dbConnector->begin ();
		if (! $this->_DPManager->delete ( $doc )) {
			$this->_dbConnector->rollback ();
			return false;
		}
		$filePath = $ftpFolder . Common::getFilenameFromDocument ( $doc );
// 		flog("filePath: %s",$filePath);
		if (! $this->_FTPDataSource->deleteFile ( $filePath )) {
			$this->_dbConnector->rollback ();
			return false;
		}
		$this->_dbConnector->commit ();
		return true;
	}

	public function checkIfMDMustBeClosed($md){
		
	}
	
	public function checkIfDocMustBeClosed($doc){
		
	}

	private function uploadFile($doc, $filePath, $repositoryPath) {
		$filename = $repositoryPath . Common::getFilenameFromDocument ( $doc );
		$result = $this->_FTPDataSource->upload ( $filePath, $filename );
		return $result;
	}
}
?>