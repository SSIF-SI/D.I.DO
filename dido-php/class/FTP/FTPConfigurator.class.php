<?php 
class FTPConfigurator implements IFTPConfiguration{
	private $_FTPConfiguratorSource;
	
	public function __construct(AFTPConfigurationSource $source){
		$this->_FTPConfiguratorSource = $source;
	}
	
	public function getHost(){
		return $this->_FTPConfiguratorSource->getHost();
	}
	
	public function getUsername(){
		return $this->_FTPConfiguratorSource->getUsername();
	}
	
	public function getPassword(){
		return $this->_FTPConfiguratorSource->getPassword();
	}
	
	public function getBasedir(){
		return $this->_FTPConfiguratorSource->getBasedir();
	}
	
	public function isActive(){
		return $this->_FTPConfiguratorSource->isActive();
	}
	
}
?>