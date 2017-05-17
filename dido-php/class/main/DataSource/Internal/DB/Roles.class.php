<?php

class Roles extends Crud {

	const ID_RUOLO = "id_ruolo";

	const RUOLO = "ruolo";

	protected $TABLE = "roles";

	public function __construct($connInstance) {
		parent::__construct ( $connInstance );
	}
}

?>