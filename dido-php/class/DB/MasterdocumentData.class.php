<?php
class MasterdocumentData extends AnyDocument {
	
	protected $TABLE = "master_documents_data";
	protected $FIELD_ID = "id_masterdata";
	protected $SQL_SEARCH = "SELECT * FROM %s WHERE %s";
	
	public function __construct($connInstance) {
		parent::__construct ( $connInstance );
	}
	
}

?>