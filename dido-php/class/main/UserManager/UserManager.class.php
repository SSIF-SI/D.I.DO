<?php

class UserManager {
	// Se aggiungiamo/Modifichiamo ruoli nella tabella roles, dobbiano
	// aggiornare questa classe
	const RUOLO_AMMINISTRATORE = 'Amministratore';

	const RUOLO_GESTORE = 'Gestore Dati';

	const RUOLO_CONSULTATORE = 'Consultatore';

	private $_fieldToWriteOnDb;

	private $_user;

	private $_role;

	private $_signature;
	
	public function __construct(IDBConnector $connector) {
		// Ad oggi i dati dell'utente li peschiamo dal Web service del personale
		$sourceUserData = new PersonaleSourceUserData ();
		$this->_user = new User ( $sourceUserData );
		
		// Setto il campo da scrivere nel DB che mi identifica l'utente
		$this->_fieldToWriteOnDb = $this->_user->getUid ();
		
		// Ora setto il ruolo
		$user_roleObj = new UsersRoles ( $connector );
		$user_role = Utils::getListfromField ( $user_roleObj->getBy ( UsersRoles::ID_PERSONA, $this->_fieldToWriteOnDb ), UsersRoles::RUOLO, UsersRoles::ID_PERSONA );
		$this->_role = isset ( $user_role [$this->_fieldToWriteOnDb] ) ? $user_role [$this->_fieldToWriteOnDb] : null;
		
		$this->_signature = new UserSignature ( $this->_fieldToWriteOnDb );

	}

	public function getUser() {
		return $this->_user;
	}

	public function getFieldToWriteOnDb() {
		return $this->_fieldToWriteOnDb;
	}

	public function isSigner() {
		return ! is_null ( $this->_signature->getSignature () );
	}

	public function hasSignRole($signRole) {
		return array_key_exists ( $signRole, $this->_signature->getSignatureRoles () );
	}

	public function getUserRole() {
		return $this->_role;
	}

	public function getUserSign() {
		return $this->_sign;
	}

	public function isAdmin() {
		return $this->_role == self::RUOLO_AMMINISTRATORE;
	}

	public function isGestore($strict = false) {
		return $strict ? $this->_role == self::RUOLO_GESTORE : $this->_role == self::RUOLO_AMMINISTRATORE || $this->_role == self::RUOLO_GESTORE;
	}

	public function isConsultatore($strict = false) {
		return $strict ? $this->_role == self::RUOLO_CONSULTATORE : $this->_role == self::RUOLO_AMMINISTRATORE || $this->_role == self::RUOLO_GESTORE || $this->_role == self::RUOLO_CONSULTATORE;
	}

}
?>