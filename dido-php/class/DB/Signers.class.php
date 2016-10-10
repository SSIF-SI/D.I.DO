<?php
class Signers extends Crud {
	
	protected $TABLE = "variable_signers_roles";
	
	public function __construct($connInstance) {
		parent::__construct ( $connInstance );
	}
}

?>