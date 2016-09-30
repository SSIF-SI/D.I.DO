<?php
class Signature extends AnyDocument {
	protected $VIEW = "signers_view";
	protected $FIELD_ID = "";
	public function __construct($connInstance) {
		parent::__construct ( $connInstance );
	}
	/*
	 * $masterDocumentData = new MasterdocumentData(Connector::getInstance()); $this->_md_data = $masterDocumentData->searchByKeyValue(array( 'id_md'	=> $id, $sigObj->getDescrizioni() ));
	 */
	public function getSigners($id_md) {
		$sigle = array ();
		$result = array ();
		$fixedSigners = new FixedSigners ( Connector::getInstance () );
		$signatures = $fixedSigners->getAll ();
		foreach ( $signatures as $k => $v )
			$result [$v ['sigla']] = $v ['pkey'];
		$variableSignersRoles = new VariableSignerRoles ( Connector::getInstance () );
		$sigle = $variableSignersRoles->getRoleDescription ();
		$masterDocumentData = new MasterdocumentData ( Connector::getInstance () );
		$signatures = $masterDocumentData->searchByKeyValue ( array (
				'id_md' => $id_md,
				$sigle 
		) );
		foreach ( $signatures as $k => $v )
			$result [$v ['key']] = $v ['value'];
		return $result;
	}
}

?>