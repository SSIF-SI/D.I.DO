<?php
class Signature extends AnyDocument {
	protected $VIEW = "signers_view";
	protected $TABLE = "signers";
	
	public function __construct($connInstance) {
		parent::__construct ( $connInstance );
	}
	public function getSigners($id_md) {
		$sigle = array ();
		$result = array ();
		
		$fixedSigners = new FixedSigners ( Connector::getInstance () );
		$id_fs = Utils::getListfromField($fixedSigners->getAll(),'id_persona');
		
		$SignersRoles = new SignersRoles ( Connector::getInstance () );
		$sigle = $SignersRoles->getRoleDescription ();
		
		$masterDocumentData = new MasterdocumentData ( Connector::getInstance () );
		$id_vs = $masterDocumentData->searchByKeys ( array_keys ( $sigle ), $id_md );
		$id_vs = Utils::getListfromField ( $id_vs, 'value' );
		
		//$signatures = $this->getBy ( 'id_persona', array_merge($id_fs,$id_vs), 'sigla' );
		$id_s = array_merge($id_fs,$id_vs);
		$id_s = join(", ",array_map("Utils::apici",$id_s));
		$signers = Utils::getListfromField($this->getBy('id_persona', $id_s),null,'sigla');
		
		//utils::printr($signers);
		return $signers;
	}
}

?>