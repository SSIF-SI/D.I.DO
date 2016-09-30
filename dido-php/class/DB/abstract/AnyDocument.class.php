<?php 
class AnyDocument extends Crud{
	
	protected function __construct($connInstance){
		parent::__construct($connInstance);
	}
	
	protected function searchByKeyValue(array $key_value) {
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
		return $this->_connInstance->allResults();
	}
}
?>