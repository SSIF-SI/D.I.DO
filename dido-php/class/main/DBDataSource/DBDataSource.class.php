<?php 
class DBDataSource{
	private $_connector;

	public function __construct(IConnector $connector = null){
		$this->_connector = is_null($connector) ? Connector::getInstance() : $connector;
	}

	public function getConnector(){
		return $this->_connector;
	}
}
?>