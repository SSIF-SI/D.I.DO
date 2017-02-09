<?php
class DocumentData extends AnyDocument{
	protected $TABLE = "documents_data";
	protected $id_document_label = "id_doc";
	
	public function __construct($connInstance){
		parent::__construct($connInstance);
	}
}

?>