<?php 
class Document extends Crud{
	const ID_DOC 				= "id_doc";
	const ID_MD 				= "id_md";
	const NOME 					= "nome";
	const CLOSED 				= "closed";
	const EXTENSION 			= "extension";
	const IMPORTED_FILE_NAME	= "imported_file_name";
	
	protected $TABLE 	= "documents";
	protected $SEQ_NAME = "documents_id_doc_seq";
	
	public function __construct($connInstance){
		parent::__construct($connInstance);
	}
}

?>