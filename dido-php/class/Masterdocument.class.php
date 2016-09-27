<?php 
class MasterDocument extends Crud{
	protected $TABLE = "masterdocument";
	
	public function __construct($connInstance){
		parent::__construct($connInstance);
	}
}

?>