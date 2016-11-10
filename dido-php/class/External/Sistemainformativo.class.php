<?php 
class SistemaInformativo{
	
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
			self::$_instance = new SistemaInformativo ();
		}
		return self::$_instance;
	}
	
	public function getConnection(){
		return $this->_connection;
	}
	
}

class master_log extends Crud{
	const CODICE_FLUSSO = "dido";
	
	protected $TABLE = "master_log";
	private $_idFlusso = null;
	
	public function __construct($connInstance){
		parent::__construct($connInstance);
	}
	
	public function updateMasterLog($operation = null, $tables = null, $verbose = true){
		if(is_null($this->_idFlusso)){
			if(is_null($operation) || is_null($tables)) throw new Exception(__CLASS__.__METHOD__.': Missing $operation and $tables parameters');
			$stub = Utils::stubFill($this->getStub(), array(null,self::CODICE_FLUSSO,$tables,$operation,"now()",null,null));
			$this->idFlusso = $this->_connInstance->getLastInsertId();
		} else {
			$stub = array("id_flusso" => $this->_idFlusso,"data_fine_oper" => "now()", "esito_oper" => "OK");
			$this->_idFlusso = null;
		}
		$this->save($stub);
	}
}
?>