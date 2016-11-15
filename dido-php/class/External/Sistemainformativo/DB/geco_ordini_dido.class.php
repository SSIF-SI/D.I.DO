<?php 
class geco_ordini_dido extends geco_dido_import{
	protected $TABLE = "geco_ordini_dido";
	
	public function __construct(){
		parent::__construct(Sistemainformativo::getInstance()->getConnection());
	}
	
}

?>