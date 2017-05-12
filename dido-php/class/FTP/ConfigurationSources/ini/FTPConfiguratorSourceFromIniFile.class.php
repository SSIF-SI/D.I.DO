<?php

class FTPConfiguratorSourceFromIniFile extends AFTPConfigurationSource {
	const DEFAULT_INI_FILE = "config.ini";
	
	private $_iniFile = "";
	
	public function __construct($iniFile = null){
		$this->setIniFile(is_null ($iniFile) ? self::DEFAULT_INI_FILE : $iniFile);
	}
	public function setIniFile($iniFile){
		$this->_iniFile = $iniFile;
		$this->loadConfiguration();
	}
	
	public function loadConfiguration() {
		$config = parse_ini_file ( $this->_iniFile );
		$this->setHost ( $config ['FTP_SERVER'] )->setUsername ( $config ['FTP_USER_NAME'] )->setPassword ( $config ['FTP_USER_PASS'] )->setBasedir ( $config ['FTP_BASEDIR'] )->setActive ( $config ['FTP_ACTIVE'] );
	}
}