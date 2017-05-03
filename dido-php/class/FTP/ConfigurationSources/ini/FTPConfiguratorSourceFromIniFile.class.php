<?php 
class FTPConfiguratorSourceFromIniFile extends AFTPConfigurationSource{
	const CONFIG_FILENAME = "config.ini";

	public function loadConfiguration(){
		$config = parse_ini_file(self::CONFIG_FILENAME);
		
		$this->setHost($config['FTP_SERVER'])
			->setUsername($config['FTP_USER_NAME'])
			->setPassword($config['FTP_USER_PASS'])
			->setBasedir($config['FTP_BASEDIR'])
			->setActive($config['FTP_ACTIVE']);
	}
}