<?php 
class Document extends Crud{
	protected $TABLE = "documents";
	protected $FIELD_ID = "id_doc";
	
	public function __construct($connInstance){
		parent::__construct($connInstance);
	}
}

?>