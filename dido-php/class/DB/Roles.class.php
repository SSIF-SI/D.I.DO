<?php
class Roles extends Crud{
	protected $TABLE = "roles";

	public function __construct($connInstance){
		parent::__construct($connInstance);
	}
}

?>