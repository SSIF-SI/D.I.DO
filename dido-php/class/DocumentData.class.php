<?php
class Document extends Crud{
	protected $TABLE = "documents_data";
	protected $FIELD_ID = "id_docdata";

	public function __construct($connInstance){
		parent::__construct($connInstance);
	}
}

?>