<?php 
abstract class AProcedureManager{
	private $_dbConnector = null;
	
	public function __construct(IDBConnector $dbConnector){
		$this->_dbConnector = $dbConnector;
	}
	
	abstract public function create($main, $data, $uploadPath);
	abstract public function update($data, $uploadPath = null);
	abstract public function delete($main);
}
?>