<?php
class VariableSigners extends Crud {
	const ID_VS 		= "id_vs";
	const ID_SR 		= "id_sr";
	const ID_PERSONA	= "id_persona";
	
	protected $TABLE = "variable_signers";
	
	public function __construct($connInstance) {
		parent::__construct ( $connInstance );
	}
	
}

?>