<?php

class aree_progettuali extends Crud {
	
	const ID= "id";
	
	const CODICE = "codice";
	
	const DESCRIZIONE = "descrizione";
	
	const ID_FLUSSO = "id_flusso";
	
	const FLAG_DEL = "flag_del";
	
	protected $TABLE = "aree_progettuali";

	public function __construct() {
		parent::__construct ( Sistemainformativo::getInstance ()->getConnection () );
	}

}
?>