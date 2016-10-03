<?php 
class AnyDocument extends Crud{
	
	protected $SQL_SEARCH = "SELECT * FROM %s WHERE %s";
	protected $id_document_label = null;
	
	protected function __construct($connInstance){
		parent::__construct($connInstance);
	}
	
	protected function searchByKeyValue(array $key_value, $id = null) {
		if (empty ( $key_value ))
			return $this->getAll ();
		$where = array ();
		
		if(!is_null($id) && !is_null($this->id_document_label))
			$where[] = "{$this->id_document_label} = '$id'";
		
		Utils::printr($key_value);
		
		foreach ( $key_value as $key => $value ) {
			if (is_null ( $key ) || is_null ( $value ))
				continue;
			
			if(is_array($value)){
				$value = array_map('Utils::apici', $value);
				$where [] = "key='{$key}' AND value IN (".join(",",$value).")";
			} else {
				$where [] = "key='{$key}' AND value={$value}";
			}
		}
		$where = join ( " AND ", $where );
		$sql = sprintf ( $this->SQL_SEARCH, isset ( $this->VIEW ) ? $this->VIEW : $this->TABLE, $where );
		$this->_connInstance->query ( $sql );
		return $this->_connInstance->allResults();
	}
	
	protected function searchByKeys($keys, $id = null){
		if (empty ( $keys ) || !is_array($keys))
			return $this->getAll ();
		
		$where = array();
		
		if(!is_null($id) && !is_null($this->id_document_label))
			$where[] = "{$this->id_document_label} = '$id'";
		
		$keys = join(",", array_map("Utils::apici",$keys));
		$where[] = "key in ($keys)";
		
		$where = join(" AND ", $where);
		
		$sql = sprintf ( $this->SQL_SEARCH, isset ( $this->VIEW ) ? $this->VIEW : $this->TABLE, $where );
		$this->_connInstance->query ( $sql );
		return $this->_connInstance->allResults();
		
	}
}
?>