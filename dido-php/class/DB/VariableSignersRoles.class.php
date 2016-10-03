<?php
class VariableSignersRoles extends Crud {
	
	protected $TABLE = "variable_signers_roles";
	protected $SQL_GET_PROJECTIONS 		= "SELECT %s FROM %s";
	
	public function __construct($connInstance) {
		parent::__construct ( $connInstance );
	}
	
	public function getRoleDescription(){
		$sql = sprintf($this->SQL_GET_PROJECTIONS,"sigla, descrizione",$this->TABLE );
		$this->_connInstance->query($sql);
		$list = $this->_connInstance->allResults();
		foreach($list as $k=>$v){
			$newList[$v['descrizione']] = $v['sigla'];
		}
		return $newList;
	}
}

?>