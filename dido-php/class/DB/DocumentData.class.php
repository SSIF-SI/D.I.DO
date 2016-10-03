<?php
class Document extends AnyDocument{
	protected $TABLE = "documents_data";
	
	public function __construct($connInstance){
		parent::__construct($connInstance);
	}
}

?>