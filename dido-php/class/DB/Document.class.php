<?php 
class Document extends Crud{
	protected $TABLE = "documents";
	
	public function __construct($connInstance){
		parent::__construct($connInstance);
	}
}

?>