<?php
class UsersRoles extends Crud{
	protected $TABLE = "users_roles";

	public function __construct($connInstance){
		parent::__construct($connInstance);
	}
}

?>