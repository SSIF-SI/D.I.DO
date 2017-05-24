<?php

class UsersRoles extends Crud {

	const ID_PERSONA = "id_persona";

	const ID_RUOLO = Roles::ID_RUOLO;

	const RUOLO = Roles::RUOLO;

	protected $TABLE = "users_roles";

	protected $VIEW = "users_roles_view";

	public function __construct($connInstance) {
		parent::__construct ( $connInstance );
	}
}

?>