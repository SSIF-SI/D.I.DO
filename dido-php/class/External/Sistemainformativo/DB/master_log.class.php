<?php 
class master_log extends Crud{
	const CODICE_FLUSSO = "dido";

	protected $TABLE = "master_log";
	private $_idFlusso = null;

	public function __construct(){
		parent::__construct(Sistemainformativo::getInstance()->getConnection());
	}

	public function updateMasterLog($operation = null, $tables = null, $esito = "OK"){
		if(is_null($this->_idFlusso)){
			if(is_null($operation) || is_null($tables)) throw new Exception(__CLASS__.__METHOD__.': Missing $operation and $tables parameters');
			$this->_connInstance
				->query("INSERT INTO 
							master_log (codice_flusso, tabelle, operazione, data_inizio_oper) 
						 VALUES 
							('".self::CODICE_FLUSSO."', '$tables', '$operation', now())",
						"flusso_seq");
			$this->_idFlusso = $this->_connInstance->getLastInsertId();
		} else {
			$this->_connInstance
			->query("UPDATE 
						master_log
					 SET
						data_fine_oper = now(),
						esito_oper = '$esito'
					 WHERE
						id_flusso = $this->_idFlusso");
			$this->_idFlusso = null;
		}
		
	}
}
?>