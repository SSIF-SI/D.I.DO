<?php 
class Sistemainformativo{
	
	private static $_instance = null;
	private static $dbData = array(
// 		'DB_ENGINE' 	=> 'pgsql',
// 		'HOST' 			=> 'sistemainformativo-dev.isti.cnr.it',
// 		'ROOT_DATABASE' => 'sistemainformativo',
// 		'ROOT_USER' 	=> 'dido',
// 		'ROOT_PASSWORD' => '!dido2k_16!'
		'DB_ENGINE' 	=> 'pgsql',
		'HOST' 			=> 'localhost',
		'ROOT_DATABASE' => 'testImport',
		'ROOT_USER' 	=> 'dido',
		'ROOT_PASSWORD' => 'emaP4ss!'
	);
	
	private $_connection;
	
	private function __construct(){
		$this->_connection = Connector::getInstance("sistemainformativo", self::$dbData);	
	}
	
	public static function getInstance() {
		if (self::$_instance == null) {
			self::$_instance = new Sistemainformativo ();
		}
		return self::$_instance;
	}
	
	public function getConnection(){
		return $this->_connection;
	}

}
?>