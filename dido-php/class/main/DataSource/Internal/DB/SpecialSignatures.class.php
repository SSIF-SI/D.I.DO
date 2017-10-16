<?php

class SpecialSignatures extends Crud {

	const ID_SPECIAL_SIGNATURE		= "id_special_signature";
	
	const ID_SPECIAL_SIGNATURE_TYPE = "id_special_signature_type";
	
	const PKEY 						= "pkey";
	
	protected $TABLE = "special_signatures";

	protected $SEQ_NAME = "special_signatures_id_special_signatures_seq";
	
	public function __construct($connInstance) {
		parent::__construct ( $connInstance );
	}
}

?>