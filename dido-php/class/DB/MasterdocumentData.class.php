<?php
class MasterdocumentData extends AnyDocument {
	const ID_MASTERDATA = "id_masterdata";
	const ID_MD 		= "id_md";
	const KEY 			= "key";
	const VALUE 		= "value";
	
	protected $TABLE 				= "master_documents_data";
	protected $id_document_label 	= "id_md";
			  
	public function __construct($connInstance) {
		parent::__construct ( $connInstance );
	}
}

?>