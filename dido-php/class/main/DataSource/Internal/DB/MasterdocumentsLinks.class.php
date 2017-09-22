<?php
class MasterdocumentsLinks extends Crud {

	const ID_LINK = "id_link";

	const ID_FATHER = "id_md_father";

	const ID_CHILD = "id_md_child";

	protected $TABLE = "master_documents_links";

	protected $SEQ_NAME = "master_documents_links_id_link_seq";
	
	public function __construct($connInstance) {
		parent::__construct ( $connInstance );
	}
}

?>