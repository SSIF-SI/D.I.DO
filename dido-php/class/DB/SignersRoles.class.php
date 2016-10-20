<?php
class SignersRoles extends Crud {
	
	protected $TABLE = "signers_roles";
	protected $SQL_GET_PROJECTIONS 		= "SELECT %s FROM %s";
	
	public function __construct($connInstance) {
		parent::__construct ( $connInstance );
	}
	
	public function getRoleDescription(){
		$sql = sprintf($this->SQL_GET_PROJECTIONS,"sigla, descrizione",$this->TABLE );
		$this->_connInstance->query($sql);
		$list = $this->_connInstance->allResults();
		foreach($list as $k=>$v){
			$newList[$v['sigla']] = $v['descrizione'];
		}
		return $newList;
	}
}

?>