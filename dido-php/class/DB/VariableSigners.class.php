<?php
class VariableSigners extends Crud {
	
	protected $TABLE = "variable_signer";
	protected $FIELD_ID = "id_vs";
	
	public function __construct($connInstance) {
		parent::__construct ( $connInstance );
	}
	
}

?>