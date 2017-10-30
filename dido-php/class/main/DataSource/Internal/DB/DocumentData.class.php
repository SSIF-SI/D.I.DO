<?php

class DocumentData extends AnyDocument {

	const ID_DOCDATA = "id_docdata";

	const ID_DOC = Document::ID_DOC;

	protected $TABLE = "documents_data";

	protected $id_document_label = self::ID_DOC;

	public function __construct($connInstance) {
		parent::__construct ( $connInstance );
	}
}

?>