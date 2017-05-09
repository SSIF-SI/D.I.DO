<?php
class FixedSigners extends Crud {
	const ID_FS 		= "id_fs";
	const ID_PERSONA 	= "id_persona";
	const ID_DELEGATO 	= "id_delegato";
	const ID_SR 		= "id_sr";
	
	protected $TABLE = "fixed_signers";
	
	public function __construct($connInstance) {
		parent::__construct ( $connInstance );
	}
	
}

?>