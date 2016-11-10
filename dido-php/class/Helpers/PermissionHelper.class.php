<?php
class PermissionHelper{
private static $_instance = null;
private $_user;
private $_role;
private $_sign;

private function __construct(){
	
	$userEmail=Session::getInstance()->get("AUTH_USER");
	$this->_user=Personale::getInstance()->getPersonabyEmail($userEmail);
	$user=$this->_user[idPersona];
	$user_roleObj=new UsersRoles(Connector::getInstance());
	$signerObj=new Signers(Connector::getInstance());
	$user_role= $user_roleObj->getBy("id_persona", $user);
	$signer= $signerObj->getBy("id_persona",$user );
	$signer=Utils::getListfromField($signer,"pkey","id_persona");
	$user_role=Utils::getListfromField($user_role,"ruolo","id_persona");
	$this->_role=$user_role[$user];
	$this->_sign=$signer[$user];	
	
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
	return $this->_user[idPersona];
}
public function getUserRole(){
	return $this->_role;
	
}
public function getUserSign(){
	return $this->_sign;
	
}
public function isAdmin(){
	if($this->_role=='Amministratore')
		return true;
	else
		false;
}
public function isGestore(){
	if($this->_role=='Gestore Dati')
		return $this->_user[gruppi];
	else 
		return false;
}
public function isSigner(){
	return isset($this->_sign);
}
}