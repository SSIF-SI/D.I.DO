<?php 
class master_log extends Crud{
	const CODICE_FLUSSO = "dido";
	const SEQ_ID_FLUSSO = "flusso_seq";
	
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
						{$this->TABLE} (codice_flusso, tabelle, operazione, data_inizio_oper) 
						 VALUES 
							('".self::CODICE_FLUSSO."', '$tables', '$operation', now())",
						self::SEQ_ID_FLUSSO);
			$this->_idFlusso = $this->_connInstance->getLastInsertId();
		} else {
			$this->_connInstance
			->query("UPDATE 
						{$this->TABLE}
					 SET
						data_fine_oper = now(),
						esito_oper = '$esito'
					 WHERE
						id_flusso = $this->_idFlusso");
			$this->_idFlusso = null;
		}
		
	}
	
	public function getLastIdFlussoOk($table){
		$this->_connInstance
			->query("SELECT 
						id_flusso
					 FROM
						{$this->TABLE}
					 WHERE
						codice_flusso = '".self::CODICE_FLUSSO."'
					 AND
						operazione = 'L'
					 AND
						esito_oper = 'OK'
					 AND
						tabelle like '%$table%'
					 ORDER BY
						id_flusso DESC
					 LIMIT 1");
		$row = $this->_connInstance->getRow();
		return isset($row['id_flusso']) ? $row['id_flusso'] : 0;
	}
}
?>