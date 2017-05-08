<?php 
abstract class AProcedureManager{
	const OPEN = 0;
	const CLOSED = 1;
	const INCOMPLETE = -1;
	
	private $_dbConnector = null;
	private $_ftpConnector = null;
	
	public function __construct(IDBConnector $dbConnector, IFTPConnector $ftpConnector){
		$this->_dbConnector = $dbConnector;
		$this->_ftpConnector = $ftpConnector;
	}
	
	public function getDBConnector(){
		return $this->_dbConnector;
	}
	
	public function getFTPConnector(){
		return $this->_ftpConnector;
	}
	
	abstract public function create($main, $data, $uploadPath);
	abstract public function update($data, $uploadPath = null);
	abstract public function delete($main, $ftpFilePath);
}
?>