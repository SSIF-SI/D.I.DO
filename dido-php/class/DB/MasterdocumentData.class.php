<?php
class MasterdocumentData extends AnyDocument {
	protected $TABLE = "master_documents_data";
	protected $id_document_label = "id_md";
			  
	public function __construct($connInstance) {
		parent::__construct ( $connInstance );
	}
}

?>