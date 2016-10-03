<?php
class VariableSigners extends Crud {
	
	protected $TABLE = "variable_signer";
	
	public function __construct($connInstance) {
		parent::__construct ( $connInstance );
	}
	
}

?>