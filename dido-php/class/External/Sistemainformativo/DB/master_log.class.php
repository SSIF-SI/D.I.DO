<?php 
class master_log extends Crud{
	const CODICE_FLUSSO = "dido";

	protected $TABLE = "master_log";
	private $_idFlusso = null;

	public function __construct(){
		parent::__construct(Sistemainformativo::getInstance()->getConnection());
	}

	public function updateMasterLog($operation = null, $tables = null, $verbose = true){
		if(is_null($this->_idFlusso)){
			if(is_null($operation) || is_null($tables)) throw new Exception(__CLASS__.__METHOD__.': Missing $operation and $tables parameters');
			$stub = Utils::stubFill($this->getStub(), array(null,self::CODICE_FLUSSO,$tables,$operation,"now()",null,null));
			$this->_idFlusso = $this->_connInstance->getLastInsertId();
		} else {
			$stub = array("id_flusso" => $this->_idFlusso,"data_fine_oper" => "now()", "esito_oper" => "OK");
			$this->_idFlusso = null;
		}
		$this->save($stub);
	}
}
?>