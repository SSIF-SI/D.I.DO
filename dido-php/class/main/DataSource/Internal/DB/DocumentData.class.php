<?php

class DocumentData extends AnyDocument {

	const ID_DOCDATA = "id_docdata";

	const ID_DOC = "id_doc";

	const KEY = "key";

	const VALUE = "value";

	protected $TABLE = "documents_data";

	protected $id_document_label = "id_doc";

	public function __construct($connInstance) {
		parent::__construct ( $connInstance );
	}
}

?>