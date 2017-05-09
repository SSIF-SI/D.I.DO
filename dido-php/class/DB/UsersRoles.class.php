<?php
class UsersRoles extends Crud{
	const ID_PERSONA 	= "id_persona";
	const RUOLO 		= Roles::RUOLO;
	const ID_RUOLO 		= "id_ruolo";
	
	protected $TABLE 	= "users_roles";
	protected $VIEW 	= "users_roles_view";
	
	public function __construct($connInstance){
		parent::__construct($connInstance);
	}
}

?>