<?php
class UsersRoles extends Crud{
	protected $TABLE = "users_roles";
	protected $VIEW = "users_roles_view";
	
	public function __construct($connInstance){
		parent::__construct($connInstance);
	}
}

?>