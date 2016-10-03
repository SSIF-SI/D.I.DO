<?php 
class Masterdocument extends Crud{
	protected $TABLE = "master_documents";
	
	public function __construct($connInstance){
		parent::__construct($connInstance);
	}
}

?>