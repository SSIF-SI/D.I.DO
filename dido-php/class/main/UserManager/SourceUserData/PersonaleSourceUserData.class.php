<?php

class PersonaleSourceUserData extends ASourceUserData {
	const USER_DATA = "userdata";
	
	private $_data;

	public function __construct() {
		if(!Session::getInstance ()->exists ( self::USER_DATA )){
			Session::getInstance ()->set ( self::USER_DATA, Personale::getInstance ()->getPersonabyEmail ( Session::getInstance ()->get ( AUTH_USER ) ) );
			Session::getInstance ()->setKeyDuration( self::USER_DATA, 1200 );
		}
		$this->_data =
			Session::getInstance ()->get ( self::USER_DATA );
		if (! $this->_data)
			throw new Exception ( "User data not Found" );
	}

	public function getUid() {
		return $this->_data [Personale::ID_PERSONA];
	}

	public function getNome() {
		return $this->_data [Personale::NOME];
	}

	public function getCognome() {
		return $this->_data [Personale::COGNOME];
	}

	public function getEmail() {
		return $this->_data [Personale::EMAIL];
	}

	public function getCodiceFiscale() {
		return $this->_data [Personale::CODICE_FISCALE];
	}

	public function getGruppi() {
		return $this->_data [Personale::GRUPPI];
	}

	public function getprogetti() {
		return $this->_data [Personale::PROGETTI];
	}
	
	public function reload(){
		Session::getInstance()->delete(self::USER_DATA);
	}
}
?>