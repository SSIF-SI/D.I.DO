<?php 
class Geko{
	private static $_instance = null;
	private $_connection;
	private static $tablesToRead = array("geco_missioni_dido","geco_ordini_dido");
	
	private function __construct(){}

	public static function getInstance() {
		if (self::$_instance == null) {
			self::$_instance = new Geko ();
		}
		return self::$_instance;
	}
	
	public function import(){
		
	}
}
?>