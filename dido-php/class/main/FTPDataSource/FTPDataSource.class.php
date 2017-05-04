<?php 
class FTPDataSource{
	private $_connector;
	
	public function __construct(IFTPConnector $connector = null){
		$this->_connector = is_null($connector) ? new FTPConnector() : $connector;
	}
}

?>