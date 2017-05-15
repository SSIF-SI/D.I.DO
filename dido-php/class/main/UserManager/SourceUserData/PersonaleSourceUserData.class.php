<?php

class PersonaleSourceUserData extends ASourceUserData {
	const USER_DATA = "userdata";
	private $_data;

	public function __construct() {
		if(!Session::getInstance ()->exists ( self::USER_DATA ))
			Session::getInstance ()->set ( self::USER_DATA, Personale::getInstance ()->getPersonabyEmail ( Session::getInstance ()->get ( AUTH_USER ) ) );
		
		$this->_data =
			Session::getInstance ()->get ( self::USER_DATA );
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