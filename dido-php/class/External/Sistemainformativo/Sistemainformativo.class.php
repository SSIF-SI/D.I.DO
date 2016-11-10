<?php 
class Sistemainformativo{
	
	private static $_instance = null;
	private static $dbData = array(
		'DB_ENGINE' 	=> 'pgsql',
		'HOST' 			=> 'sistemainformativo-dev.isti.cnr.it',
		'ROOT_DATABASE' => 'sistemainformativo',
		'ROOT_USER' 	=> 'personale',
		'ROOT_PASSWORD' => 'personale123'
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