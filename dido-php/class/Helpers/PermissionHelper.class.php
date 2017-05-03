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
		$signersObj = new Signers(Connector::getInstance());
		$mySignature = Utils::getListfromField($signersObj->getBy('id_persona', $user_id), 'pkey','id_persona');
		$signatureObj = new Signature(Connector::getInstance());
		$signatures = $signatureObj->getAll('sigla','id_item');
		
		$signer = array_merge(
					Utils::filterList($signatures,"id_persona",$user_id ),
					Utils::filterList($signatures,"id_delegato",$user_id ));
		
		$this->_sign = array(
			'mySignature' => reset($mySignature), 
			'signRoles' => Utils::getListfromField($signer,null,'sigla')
		);	
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
	
	public function getUserId(){
		return $this->_user['idPersona'];
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
	
	public function isGestore($service = null){
		if(is_null($service)) return $this->_role == self::RUOLO_GESTORE;
		
		if($this->_role == self::RUOLO_AMMINISTRATORE) return true;
		
		if(in_array($service, $this->_user['gruppi']) || is_null($service)){
			return true && $this->_role == self::RUOLO_GESTORE;
		} 
		
		return false;
		
	}
	
	public function isConsultatore($service=null){
		if(is_null($service)) return $this->_role == self::RUOLO_CONSULTATORE;
		
		if($this->_role == self::RUOLO_AMMINISTRATORE || $this->_role == self::RUOLO_GESTORE) return true;
		
		if(in_array($service, $this->_user['gruppi']) || is_null($service)){
			return true && $this->_role == self::RUOLO_CONSULTATORE;
		}

		return false;
	}
	
	public function isSigner(){
		return !empty($this->_sign);
	}
}