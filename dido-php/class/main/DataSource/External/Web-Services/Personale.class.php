<?php

class Personale {

	const ID_PERSONA = "idPersona";
	
	const NOME = "nome";
	
	const COGNOME = "cognome";
	
	const EMAIL = "email";
	
	const CODICE_FISCALE = "codiceFiscale";
	
	const PERSONALE = "personale";
	
	const GRUPPI = "gruppi";
	
	const PROGETTI = "progetti";
	
	const SESSIONKEY_PERSONALE = "ws_personale";
	
	private static $_instance = null;

	private $_persone;

	private $_cfId;

	private $_gruppi;
	
	private $_progetti;

	private $_email;

	private function __construct() {
		if(!Session::getInstance()->exists(self::SESSIONKEY_PERSONALE)){
			
			ini_set ( "soap.wsdl_cache_enabled", "0" );
			$wsdl_url = "http://pimpa.isti.cnr.it/PERSONALE/web-services/dido/dido.wsdl";
			$client = new SoapClient ( $wsdl_url );
			$personale = json_decode ( json_encode ( $client->personale () ), true );
			$gruppi = json_decode ( json_encode ( $client->gruppi () ), true );
			$progetti = json_decode ( json_encode ( $client->progetti () ), true );
			Session::getInstance()->set(self::SESSIONKEY_PERSONALE,[self::PERSONALE => $personale, self::GRUPPI => $gruppi, self::PROGETTI => $progetti]);
			Session::getInstance()->setKeyDuration(self::SESSIONKEY_PERSONALE, 3600);
		}
		
		//Utils::printr(Session::getInstance()->getKeyDuration(self::SESSIONKEY_PERSONALE));
		
		$sessionPersonale = Session::getInstance()->get(self::SESSIONKEY_PERSONALE);
		
		$this->_persone = Utils::getListfromField ( $sessionPersonale[self::PERSONALE], null, self::ID_PERSONA );
		$this->_cfId = Utils::getListfromField ( $sessionPersonale[self::PERSONALE], self::ID_PERSONA, self::CODICE_FISCALE );
		$this->_email = Utils::getListfromField ( $sessionPersonale[self::PERSONALE], self::ID_PERSONA, self::EMAIL );
		
		$this->_gruppi = Utils::getListfromField ( $sessionPersonale[self::GRUPPI], null, "sigla" );
		
		$this->_progetti = Utils::getListfromField ( $sessionPersonale[self::PROGETTI], null, "id" );
	}

	private function __clone() {
	}

	private function __wakeup() {
	}

	public static function getInstance() {
		if (self::$_instance == null) {
			self::$_instance = new self ();
		}
		return self::$_instance;
	}

	public function getPersone() {
		return $this->_persone;
	}

	public function getGruppi() {
		return $this->_gruppi;
	}

	public function getProgetti() {
		return $this->_progetti;
	}

	public function getPersona($id) {
		return $this->_persone [$id];
	}

	public function getNominativo($id) {
		if (empty ( $id ))
			return false;
		$result = $this->getPersona ( $id )[self::COGNOME] . " " . $this->getPersona ( $id )[self::NOME];
		$result = trim ( $result );
		return empty ( $result ) ? null : $result;
	}
	
	public function getPersonabyCf($cf) {
		return isset ( $this->_cfId [$cf] ) ? $this->_persone [$this->_cfId [$cf]] : false;
	}

	public function getPersonabyEmail($email) {
		return isset ( $this->_email [$email] ) ? $this->_persone [$this->_email [$email]] : false;
	}

	public function getGruppo($sigla) {
		return $this->_gruppi [$sigla];
	}

	public function getPeopleByGroupType($type) {
		$list = array ();
		foreach ( $this->_persone as $id => $datiPersona ) {
			foreach ( $datiPersona [self::GRUPPI] as $sigla ) {
				if ($this->_gruppi [$sigla] ['tipo'] == $type)
					$list [$id] = $datiPersona;
			}
		}
		return $list;
	}
	
	public function reload(){
		Session::getInstance()->delete(self::SESSIONKEY_PERSONALE);
	}
}
?>