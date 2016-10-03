<?php
class FixedSigners extends Crud {
	protected $TABLE = "fixed_signers";
	
	public function __construct($connInstance) {
		parent::__construct ( $connInstance );
	}
	
}

?>