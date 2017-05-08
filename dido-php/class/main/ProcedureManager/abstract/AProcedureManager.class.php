<?php 
abstract class AProcedureManager{
	const OPEN = 0;
	const CLOSED = 1;
	const INCOMPLETE = -1;
	
	private $_dbConnector = null;
	
	public function __construct(IDBConnector $dbConnector){
		$this->_dbConnector = $dbConnector;
	}
	
	abstract public function create($main, $data, $uploadPath);
	abstract public function update($data, $uploadPath = null);
	abstract public function delete($main);
}
?>