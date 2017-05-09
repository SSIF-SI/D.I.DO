<?php
class Signers extends Crud {
	const ID_PERSONA 	= "id_persona";
	const PKEY 			= "pkey";
	
	protected $TABLE = "signers";
	
	public function __construct($connInstance) {
		parent::__construct ( $connInstance );
	}
}

?>