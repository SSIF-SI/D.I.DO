<?php
class PermissionHelper{
	const RUOLO_AMMINISTRATORE = 'Amministratore';
	const RUOLO_GESTORE = 'Gestore Dati';
	const RUOLO_CONSULTATORE = 'Consultatore';
	
	private static $_instance = null;
	private $_user;
	private $_role;
	private $_sign;
	
	private function __construct(){
		// Set User
		$this->_user = Personale::getInstance()->getPersonabyEmail(Session::getInstance()->get("AUTH_USER"));
		$user_id = $this->_user['idPersona'];
		
		// Set User Role
		$user_roleObj = new UsersRoles(Connector::getInstance());
		$user_role = Utils::getListfromField($user_roleObj->getBy("id_persona", $user_id),"ruolo","id_persona");
		$this->_role = isset($user_role[$user_id]) ? $user_role[$user_id] : null;
		
		// Set user Signature, if exists
		$signerObj = new Signers(Connector::getInstance());
		$signer = Utils::getListfromField($signerObj->getBy("id_persona",$user_id ),"pkey","id_persona");
		$this->_sign = isset($signer[$user_id]) ? $signer[$user_id] : null;	
	}
	
	public static function getInstance() {
		if (self::$_instance == null) {
			self::$_instance = new PermissionHelper ();
		}
		return self::$_instance;
	}
	
	public function getUser(){
		return $this->_user;
	}
	
	public function getUserField($field){
		if(!isset($this->_user[$field])) Throw new Exception(__CLASS__.":".__METHOD__." User has no field $field");
		return $this->_user[$field];
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
	
	public function isGestore(){
		if($this->_role == self::RUOLO_AMMINISTRATORE) return true;
		
		$services = func_get_args();
		if(count($services) == 0)
			return $this->_role == self::RUOLO_GESTORE;
		
		foreach($services as $service){
			if(in_array($service, $this->_user['gruppi'])){
				return true && $this->_role == self::RUOLO_GESTORE;
			}
		}
	}
	
	public function isConsultatore(){
		if($this->_role == self::RUOLO_AMMINISTRATORE || $this->_role == self::RUOLO_GESTORE) return true;
		
		// TODO controlli
	}
	
	public function isSigner(){
		return !is_null($this->_sign);
	}
}