<?php

class PersonaleSourceUserData extends ASourceUserData {

	private $_data;

	public function __construct() {
		$this->_data = Personale::getInstance ()->getPersonabyEmail ( Session::getInstance ()->get ( AUTH_USER ) );
		if (! $this->_data)
			throw new Exception ( "User data not Found" );
	}

	public function getUid() {
		return $this->_data ['idPersona'];
	}

	public function getNome() {
		return $this->_data ['nome'];
	}

	public function getCognome() {
		return $this->_data ['cognome'];
	}

	public function getEmail() {
		return $this->_data ['email'];
	}

	public function getCodiceFiscale() {
		return $this->_data ['codiceFiscale'];
	}

	public function getGruppi() {
		return $this->_data ['gruppi'];
	}

	public function getprogetti() {
		return $this->_data ['progetti'];
	}
}
?>