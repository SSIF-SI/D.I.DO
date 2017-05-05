<?php 
class FTPDataSource{
	private $_connector;
	
	public function __construct(IFTPConnector $connector = null){
		$this->_connector = is_null($connector) ? new FTPConnector() : $connector;
	}
	
	public function getNewPathFromXml($xml){
		return dirname($xml).DIRECTORY_SEPARATOR.date("Y").DIRECTORY_SEPARATOR.date("m");
	}
	
	
}

?>