<?php
class Signature extends AnyDocument {
	protected $VIEW = "signers_view";
	
	public function __construct($connInstance) {
		parent::__construct ( $connInstance );
	}
	/*
	 * $masterDocumentData = new MasterdocumentData(Connector::getInstance()); $this->_md_data = $masterDocumentData->searchByKeyValue(array( 'id_md'	=> $id, $sigObj->getDescrizioni() ));
	 */
	public function getSigners($id_md) {
		Utils::printr($id_md);
		$sigle = array ();
		$result = array ();
		$fixedSigners = new FixedSigners ( Connector::getInstance () );
		$signatures = $fixedSigners->getAll ();
		foreach ( $signatures as $k => $v )
			$result [$v ['sigla']] = $v ['pkey'];
		$variableSignersRoles = new VariableSignersRoles ( Connector::getInstance () );
		
		$sigle = $variableSignersRoles->getRoleDescription ();
		Utils::printr($sigle);
		
		$masterDocumentData = new MasterdocumentData ( Connector::getInstance () );
		$signatures = $masterDocumentData->searchByKeys ( $sigle , $id_md );
		
		foreach ( $signatures as $k => $v )
			$result [$v ['key']] = $v ['value'];
		Utils::printr($result);
		return $result;
	}
}

?>