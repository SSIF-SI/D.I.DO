<?php
class MasterdocumentData extends Crud {
	
	protected $TABLE = "master_documents_data";
	protected $FIELD_ID = "id_masterdata";
	protected $SQL_SEARCH = "SELECT * FROM %s WHERE %s";
	
	public function __construct($connInstance) {
		parent::__construct ( $connInstance );
	}
	
	public function searchByKeyValue(array $key_value) {
		if (empty ( $key_value ))
			return $this->getAll ();
		$where = array ();
		foreach ( $key_value as $key => $value ) {
			if (is_null ( $key ) || is_null ( $value ))
				continue;
			if(is_array($value)){
				array_map('Utils::apici()', $value);
				$where [] = "key={$key} AND value IN (".join(",",$value).")";
			} else 
				$where [] = "key={$key} AND value={$value}";
		}
		$where = join ( " AND ", $where );
		$sql = sprintf ( $this->$SQL_SEARCH, isset ( $this->VIEW ) ? $this->VIEW : $this->TABLE, $where );
		$this->_connInstance->query ( $sql );
	}
	
	
}

?>