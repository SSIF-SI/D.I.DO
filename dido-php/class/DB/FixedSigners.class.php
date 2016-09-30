<?php
class FixedSigners extends Crud {
	
	protected $TABLE = "fixed_signers";
	protected $FIELD_ID = "id_fs";
	
	public function __construct($connInstance) {
		parent::__construct ( $connInstance );
	}
	
}

?>