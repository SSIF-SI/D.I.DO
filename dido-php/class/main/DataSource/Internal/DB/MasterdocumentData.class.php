<?php

class MasterdocumentData extends AnyDocument {

	const ID_MASTERDATA = "id_masterdata";

	const ID_MD = Masterdocument::ID_MD;

	const KEY = "key";

	const VALUE = "value";

	protected $TABLE = "master_documents_data";

	protected $id_document_label = self::ID_MD;

	public function __construct($connInstance) {
		parent::__construct ( $connInstance );
	}
}

?>