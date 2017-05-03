<?php 
class FTPDataSource{
	private $_connector;
	
	public function __construct(IFTPConnector $connector){
		$this->_connector = $connector;
	}
}

?>