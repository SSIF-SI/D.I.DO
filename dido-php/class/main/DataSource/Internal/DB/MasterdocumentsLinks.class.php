<?php
class MasterdocumentsLinks extends Crud {

	const ID_LINK = "id_link";

	const ID_FATHER = "id_md_father";

	const ID_CHILD = "id_md_child";

	const NOME = SharedDocumentConstants::NOME;
	
	protected $TABLE = "master_documents_links";
	protected $VIEW = "master_documents_links_search_view";
	
	protected $SEQ_NAME = "master_documents_links_id_link_seq";
	
	public function __construct($connInstance) {
		parent::__construct ( $connInstance );
		$this->useView(false);
	}
}

?>