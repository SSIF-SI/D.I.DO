<?php

abstract class AnyDocumentData extends Crud {
	const KEY = "key";

	const VALUE = "value";

	protected $SQL_SEARCH = "SELECT * FROM %s WHERE %s";

	protected $id_document_label = null;

	protected function __construct($connInstance) {
		parent::__construct ( $connInstance );
	}

	public function searchByKeyValue($key_value, $id = null, $link = 'AND') {
		$where = array ();
		if (! is_null ( $id ) && ! is_null ( $this->id_document_label ))
			$where [] = "{$this->id_document_label} = '$id'";
		
		if (! empty ( $key_value )) {
			foreach ( $key_value as $key => $value ) {
				if (is_null ( $key ) || is_null ( $value ))
					continue;
				
				if (is_array ( $value )) {
					$value = array_map ( 'Utils::apici', $value );
					$where [] = "(".self::KEY."='{$key}' AND ".self::VALUE." IN (" . join ( ",", $value ) . "))";
				} else {
					$where [] = "(".self::KEY."='{$key}' AND ".self::VALUE."='{$value}')";
				}
			}
		}
		$where = join ( " $link ", $where );
		$sql = sprintf ( $this->SQL_SEARCH, $this->_canIUseView() ? $this->VIEW : $this->TABLE, $where );
		$this->_connInstance->query ( $sql );
		return $this->_connInstance->allResults ();
	}

	public function searchByKeys($keys, $id = null, $link = "AND") {
		if (empty ( $keys ) || ! is_array ( $keys ))
			return $this->getAll ();
		
		$where = array ();
		
		if (! is_null ( $id ) && ! is_null ( $this->id_document_label ))
			$where [] = "{$this->id_document_label} = '$id'";
		
		$keys = join ( ",", array_map ( "Utils::apici", $keys ) );
		$where [] = self::KEY . " in ($keys)";
		
		$where = join ( " {$link} ", $where );
		
		$sql = sprintf ( $this->SQL_SEARCH, $this->_canIUseView() ? $this->VIEW : $this->TABLE, $where );
		$this->_connInstance->query ( $sql );
		return $this->_connInstance->allResults ();
	}

	public function saveInfo($data, $id_parent/*,$docInputs*/){
		$this->useView(false);
		$existents_input = Utils::getListfromField ( $this->searchByKeys ( array_keys ( $data ), $id_parent ), null, self::KEY );
		$this->useView(true);
		
		$this->_connInstance->begin ();
		
		foreach ( $data as $key => $value ) {
			$result = new ErrorHandler ( false );
			
			if (isset ( $existents_input [$key] ) && empty ( $value )) {
				$pkFields = $existents_input [$key];
				unset ( $pkFields [$this->id_document_label], $pkFields [self::KEY], $pkFields [self::VALUE] );
				$result = $this->delete ( $pkFields );
			} elseif (! isset ( $existents_input [$key] ) || $value != $existents_input [$key] [self::VALUE]) {
				if (! empty ( $value )) {
					$existents_input [$key] [self::KEY] = $key;
					$existents_input [$key] [self::VALUE] = $value;
					$existents_input [$key] [$this->id_document_label] = $id_parent;
					
					$object = Utils::stubFill ( $this->_stub, $existents_input [$key] );
					
					$result = $this->save ( $object, null );
				}
			}
			
			if ($result->getErrors () !== false) {
				$this->_connInstance->rollback ();
				return $result;
			}
		}
		$this->_connInstance->commit ();
		
		return $result;
	}
}
?>