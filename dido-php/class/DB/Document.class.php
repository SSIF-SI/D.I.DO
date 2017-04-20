<?php 
class Document extends Crud{
	protected $TABLE = "documents";
	protected $SEQ_NAME = "documents_id_doc_seq";
	
	public function __construct($connInstance){
		parent::__construct($connInstance);
	}
}

?>