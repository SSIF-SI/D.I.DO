<?php

class Document extends Crud {

	const ID_DOC = "id_doc";

	const ID_MD = Masterdocument::ID_MD;

	const NOME = SharedDocumentConstants::NOME;

	const CLOSED = SharedDocumentConstants::CLOSED;

	const EXTENSION = "extension";

	const IMPORTED_FILE_NAME = "imported_file_name";

	protected $TABLE = "documents";

	protected $SEQ_NAME = "documents_id_doc_seq";

	public function __construct($connInstance) {
		parent::__construct ( $connInstance );
	}
}

?>