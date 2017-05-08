<?php
class Signature extends AnyDocument {
	protected $VIEW = "signers_view";
	protected $TABLE = "signers";
	
	public function __construct($connInstance) {
		parent::__construct ( $connInstance );
	}
	public function getSigners($id_md, $md_inputs) {
		$signesr = array();
		$sigle = array ();
		$result = array ();
		
		$fixedSigners = new FixedSigners ( DBConnector::getInstance () );
		$id_fs = array_unique(Utils::getListfromField($fixedSigners->getAll(),'id_persona'));
		$id_fs = join(", ",array_map("Utils::apici",$id_fs));
		$fixed = Utils::filterList($this->getBy('id_persona', $id_fs), "fixed_role", 1);
		$signers = Utils::getListfromField($fixed,null,'sigla');
		
		
		$SignersRoles = new SignersRoles ( DBConnector::getInstance () );
		$sigle = $SignersRoles->getRoleDescription ();
		
		/*
		$masterDocumentData = new MasterdocumentData ( DBConnector::getInstance () );
		$id_vs = $masterDocumentData->searchByKeys ( array_keys ( $sigle ), $id_md );
		$id_vs = Utils::getListfromField ( $id_vs, 'value' );
		*/
		
		$id_vs = array();
		foreach($md_inputs as $key=>$value){
			if(in_array($key,$sigle)) array_push($id_vs,$value);
		}
		
		//$signatures = $this->getBy ( 'id_persona', array_merge($id_fs,$id_vs), 'sigla' );
		//$id_s = array_unique(array_merge($id_fs,$id_vs));
		$id_vs = join(", ",array_map("Utils::apici",$id_vs));
		
		$signers = array_merge($signers, Utils::getListfromField($this->getBy('id_persona', $id_vs),null,'sigla'));
		return $signers;
	}
}

?>