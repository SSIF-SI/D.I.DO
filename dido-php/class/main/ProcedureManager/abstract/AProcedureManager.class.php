<?php 
abstract class AProcedureManager{
	const OPEN = 0;
	const CLOSED = 1;
	const INCOMPLETE = -1;
	
	private $_dbConnector = null;
	
	public function __construct(IDBConnector $dbConnector, IFTPConnector $ftpConnector){
		$this->_dbConnector = $dbConnector;
	}
	
	public function getDBConnector(){
		return $this->_dbConnector;
	}
	
	abstract public function create($main, $data);
	abstract public function update($data);
	abstract public function delete($main);
}
?>