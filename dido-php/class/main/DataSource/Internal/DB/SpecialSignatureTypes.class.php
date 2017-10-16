<?php

class SpecialSignatureTypes extends Crud {

	const ID_SIGNATURE_TYPE = "id_signature_type";

	const TYPE = "type";

	protected $TABLE = "special_signature_types";
	
	protected $SEQ_NAME = "signature_types_id_signature_type_seq";
	
	public function __construct($connInstance) {
		parent::__construct ( $connInstance );
	}
}

?>