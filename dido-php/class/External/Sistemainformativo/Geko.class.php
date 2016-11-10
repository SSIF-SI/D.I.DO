<?php 
class Geko{
	private static $_instance = null;
	private $_connection;
	
	private function __construct(){}

	public static function getInstance() {
		if (self::$_instance == null) {
			self::$_instance = new Geko ();
		}
		return self::$_instance;
	}
	
	public function show(){
		$ml = new master_log();
		Utils::printr($ml->getAll(null,null,100));
	}
}
?>