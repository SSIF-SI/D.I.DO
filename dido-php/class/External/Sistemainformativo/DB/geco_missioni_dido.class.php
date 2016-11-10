<?php 
class geco_missioni_dido extends Crud{
	protected $TABLE = "geco_missioni_dido";

	public function __construct(){
		parent::__construct(Sistemainformativo::getInstance()->getConnection());
	}
}

?>