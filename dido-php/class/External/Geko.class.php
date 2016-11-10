<?php 
class Geko{
	private static $_instance = null;
	private $_connection;
	
	private function __construct(){
		$this->_connection = Sistemainformativo::getInstance()->getConnection();
	}

	public static function getInstance() {
		if (self::$_instance == null) {
			self::$_instance = new Geko ();
		}
		return self::$_instance;
	}
	
	public function show(){
		$ml = new master_log($this->_connection);
		Utils::printr($ml->getAll(null,null,100));
	}
}

class geco_missioni_dido extends Crud{
	protected $TABLE = "geco_missioni_dido";

	public function __construct($connInstance){
		parent::__construct($connInstance);
	}
}

class geco_ordini_dido extends Crud{
	protected $TABLE = "geco_ordini_dido";

	public function __construct($connInstance){
		parent::__construct($connInstance);
	}
}

?>