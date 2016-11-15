<?php 
class geco_missioni_dido extends geco_dido_import{
	protected $TABLE = "geco_missioni_dido";
	
	public function __construct(){
		parent::__construct(Sistemainformativo::getInstance()->getConnection());
	}
}

?>