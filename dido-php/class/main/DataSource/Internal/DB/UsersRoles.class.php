<?php

class UsersRoles extends Crud {

	const ID_PERSONA = "id_persona";

	const ID_RUOLO = "id_ruolo";

	const RUOLO = Roles::RUOLO;

	protected $TABLE = "users_roles";

	protected $VIEW = "users_roles_view";

	public function __construct($connInstance) {
		parent::__construct ( $connInstance );
	}
}

?>