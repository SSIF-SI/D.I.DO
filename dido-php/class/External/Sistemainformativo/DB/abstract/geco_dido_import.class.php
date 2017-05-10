<?php

abstract class geco_dido_import extends Crud {

	private static $SQL_GET_RECORDS_TO_IMPORT = "SELECT * FROM %s WHERE id_flusso > %d";

	public function __construct($connector = null) {
		if (is_null ( $connector ))
			$connector = Sistemainformativo::getInstance ()->getConnection ();
		parent::__construct ( $connector );
	}

	public function getRecordsToImport($idFlusso) {
		$sql = sprintf ( self::$SQL_GET_RECORDS_TO_IMPORT, $this->TABLE, $idFlusso );
		$this->_connInstance->query ( $sql );
		return $this->_connInstance->allResults ();
	}
}
?>