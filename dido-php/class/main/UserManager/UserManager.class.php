<?php
class UserManager{
	private $_user;
	private $_role;
	private $_sign;
	
	public function __construct(){
		$sourceUserData = new PersonaleSourceUserData();
		$this->_user = new User($sourceData);
	}
}
?>