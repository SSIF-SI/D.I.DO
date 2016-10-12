<?php
class Signers extends Crud {
	
	protected $TABLE = "signers";
	
	public function __construct($connInstance) {
		parent::__construct ( $connInstance );
	}
}

?>