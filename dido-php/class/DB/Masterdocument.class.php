<?php 
class Masterdocument extends Crud{
	protected $TABLE = "master_documents";
	protected $SEQ_NAME = "master_documents_id_md_seq";
	
	public function __construct($connInstance){
		parent::__construct($connInstance);
	}
}

?>