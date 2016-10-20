<?php
class VariableSigners extends Crud {
	
	protected $TABLE = "variable_signers";
	
	public function __construct($connInstance) {
		parent::__construct ( $connInstance );
	}
	
}

?>