<?php
class UserManager{
	const RUOLO_AMMINISTRATORE 	= 'Amministratore';
	const RUOLO_GESTORE 		= 'Gestore Dati';
	const RUOLO_CONSULTATORE 	= 'Consultatore';
	
	private $_fieldToWriteOnDb;
	
	private $_user;
	private $_role;
	private $_signature;
	
	public function __construct(){
		// Ad oggi i dati dell'utente li peschiamo dal Web service del personale
		$sourceUserData = new PersonaleSourceUserData();
		$this->_user = new User($sourceUserData);
		
		// Setto il campo da scrivere nel DB che mi identifica l'utente
		$this->_fieldToWriteOnDb = $this->_user->getUid();
		
		// Ora setto il ruolo
		$user_roleObj = new UsersRoles(Connector::getInstance());
		$user_role = Utils::getListfromField($user_roleObj->getBy("id_persona", $this->_fieldToWriteOnDb), "ruolo", "id_persona");
		$this->_role = isset($user_role[$this->_fieldToWriteOnDb]) ? $user_role[$this->_fieldToWriteOnDb] : null;
				
		$this->_signature = new UserSignature($this->_fieldToWriteOnDb);
	}
	
	public function getUser(){
		return $this->_user;
	}
	
	public function getFieldToWriteOnDb(){
		return $this->_fieldToWriteOnDb;
	}
	
	public function isSigner(){
		
		return !is_null($this->_signature->getSignature());
	}
	
	public function hasSignRole($signRole){
		return array_key_exists($signRole, $this->_signature->getSignatureRoles());
	}
	
	public function getUserRole(){
		return $this->_role;
	
	}
	public function getUserSign(){
		return $this->_sign;
	}
	
	public function isAdmin(){
		return $this->_role == self::RUOLO_AMMINISTRATORE;
	}
	
	public function isGestore($service = null){
		if($this->_role == self::RUOLO_AMMINISTRATORE) return true;
	
		if(is_null($service)) return $this->_role == self::RUOLO_GESTORE;
	
		if(in_array($service, $this->_user->getGruppi()) || is_null($service)){
			return true && $this->_role == self::RUOLO_GESTORE;
		}
	
		return false;
	}
	
	public function isConsultatore($service = null){
		if($this->_role == self::RUOLO_AMMINISTRATORE || $this->_role == self::RUOLO_GESTORE) return true;

		if(is_null($service)) return $this->_role == self::RUOLO_CONSULTATORE;
		
		if(in_array($service, $this->_user->getGruppi()) || is_null($service)){
			return true && $this->_role == self::RUOLO_CONSULTATORE;
		}
	
		return false;
	}
}
?>