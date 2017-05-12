<?php

class ProcedureManager implements IProcedureManager {

	private $_dbConnector;

	private $_MDPManager;

	private $_DPManager;

	private $_FTPDataSource;

	public function __construct(IDBConnector $dbConnector, IFTPDataSource $ftpDataSource) {
		$this->_dbConnector = $dbConnector;
		$this->_MDPManager = new MasterDocumentProcedureManager ( $dbConnector );
		$this->_DPManager = new DocumentProcedureManager ( $dbConnector );
		$this->_FTPDataSource = $ftpDataSource;
	}

	public function createMasterdocument($md, $md_data) {
		// Creo cartella ftp
		$md [Masterdocument::FTP_FOLDER] = $this->_FTPDataSource->getNewPathFromXml ( $md [Masterdocument::XML] );
		if (! $this->_FTPDataSource->createFolder ( $md [Masterdocument::FTP_FOLDER] )) {
			return false;
		}
		
		// Creo il MD;
		$this->_dbConnector->begin ();
		$new_md = $this->_MDPManager->create ( $md, $data );
		if (! $new_md) {
			$this->removeMasterdocumentFolder( $md [Masterdocument::FTP_FOLDER] );
			$this->_dbConnector->rollback ();
			return false;
		}
		
		$this->_dbConnector->commit ();
		return $new_md;
	}

	public function updateMasterdocument($md_data) {
		return $this->_MDPManager->update ( $md_data );
	}

	public function deleteMasterdocument($md) {
		return $this->_MDPManager->delete ( $md );
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

	public function updateDocument($document, $data, $filePath = null, $repositoryPath = null) {
		$this->_dbConnector->begin ();
		if (! $this->_DPManager->update ( $data )) {
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
		return $new_doc;
	}

	public function deleteDocument($doc, $ftpFolder) {
		$this->_dbConnector->begin ();
		if (! $this->_DPManager->delete ( $doc, $ftpFolder )) {
			$this->_dbConnector->rollback ();
			return false;
		}
		$filePath = $ftpFolder . $this->_FTPDataSource->getFilenameFromDocument ( $doc );
		if (! $this->_FTPDataSource->deleteFile ( $filePath )) {
			$this->_dbConnector->rollback ();
			return false;
		}
		$this->_dbConnector->commit ();
		return true;
	}

	private function uploadFile($doc, $filePath, $repositoryPath) {
		$filename = $repositoryPath . $this->_FTPDataSource->getFilenameFromDocument ( $doc );
		if (! $this->_FTPDataSource->upload ( $filePath, $filename )) {
			return false;
		}
		return true;
	}
}
?>