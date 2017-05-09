<?php 
class ProcedureManager{
	private $_dbConnector;
	private $_MDPManager;
	private $_DPManager;
	private $_FTPDataSource;
	
	public function __construct(IDBConnector $dbConnector, IFTPDataSource $ftpDataSource){
		$this->_dbConnector = $dbConnector;
		$this->_MDPManager = new MasterDocumentProcedureManager($dbConnector);
		$this->_DPManager = new DocumentProcedureManager($dbConnector);
		$this->_FTPDataSource = $ftpDataSource;
	}
	
	public function createMasterDocument($md, $md_data){
		// Creo cartella ftp
		$md['ftp_folder'] = $this->_FTPDataSource->getNewPathFromXml($md['xml']);
		if (!$this->_FTPDataSource->createFolder($md['ftp_folder'])){
			return false;
		}
		
		// Creo il MD;
		$this->_dbConnector->begin();
		$new_md = $this->_MDPManager->create($md, $data);
		if(!$new_md){
			$this->_FTPDataSource->deleteFolder($md['ftp_folder']);
			$this->_dbConnector->rollback();
			return false;
		}
		
		$this->_dbConnector->commit();
		return $new_md;
	}
	
	public function updateMasterDocument($md_data){
		return $this->_MDPManager->update($md_data);
	}
	
	public function deleteMasterDocument($md_data){
		return $this->_MDPManager->delete($md);
	}
	
	public function createDocument($doc, $data, $filePath, $ftpFolder){
		$this->_dbConnector->begin();
		$new_doc = $this->_DPManager->create($doc, $data); 
		if(!$new_doc){
			$this->_dbConnector->rollback();
			return false;
		}
		$filename = $ftpFolder . $this->_FTPDataSource->getFilenameFromDocument($new_doc);
		if(!$this->_FTPDataSource->upload($filePath, $filename)){
			$this->_dbConnector->rollback();
			return false;
		}
		
		$this->_dbConnector->commit();
		return $new_doc;
	}
	
	public function updateDocument($data, $filePath = null){
		$this->_dbConnector->begin();
		if(!$this->_DPManager->update($data)){
			$this->_dbConnector->rollback();
			return false;	
		}
		if(!is_null($filePath)){
			$filename = $ftpFolder . $this->_FTPDataSource->getFilenameFromDocument($new_doc);
			if(!$this->_FTPDataSource->upload($filePath, $filename)){
				$this->_dbConnector->rollback();
				return false;
			}
		}
		
		$this->_dbConnector->commit();
		return $new_doc;
	}
}
?>