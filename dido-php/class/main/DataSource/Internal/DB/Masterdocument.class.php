<?php

class Masterdocument extends Crud {

	const ID_MD = "id_md";

	const NOME = "nome";

	const TYPE = "type";

	const XML = "xml";

	const FTP_FOLDER = "ftp_folder";

	const CLOSED = "closed";

	protected $TABLE = "master_documents";

	protected $SEQ_NAME = "master_documents_id_md_seq";

	public function __construct($connInstance) {
		parent::__construct ( $connInstance );
	}
}

?>