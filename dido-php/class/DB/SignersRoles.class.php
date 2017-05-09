<?php
class SignersRoles extends Crud {
	const ID_SR 		= "id_sr";
	const SIGLA 		= "sigla";
	const DESCRIZIONE 	= "descrizione";
	const FIXED_ROLE 	= "fixed_role";
	
	protected $TABLE 					= "signers_roles";
	protected $SQL_GET_PROJECTIONS 		= "SELECT %s FROM %s";
	
	public function __construct($connInstance) {
		parent::__construct ( $connInstance );
	}
	
	public function getRoleDescription(){
		$sql = sprintf($this->SQL_GET_PROJECTIONS,self::SIGLA.", ".self::DESCRIZIONE,$this->TABLE );
		$this->_connInstance->query($sql);
		$list = $this->_connInstance->allResults();
		foreach($list as $k=>$v){
			$newList[$v[self::SIGLA]] = $v[self::DESCRIZIONE];
		}
		return $newList;
	}
}

?>