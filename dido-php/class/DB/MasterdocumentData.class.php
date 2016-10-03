<?php
class MasterdocumentData extends AnyDocument {
	protected $TABLE = "master_documents_data";
	
	public function __construct($connInstance) {
		parent::__construct ( $connInstance );
	}
	
}

?>