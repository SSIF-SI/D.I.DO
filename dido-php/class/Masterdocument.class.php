<?php 
class Masterdocument extends Crud{
	protected $TABLE = "masterdocument";
	protected $FIELD_ID = "id_md";
	
	public function __construct($connInstance){
		parent::__construct($connInstance);
	}
}

?>