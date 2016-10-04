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
		$sigle = array ();
		$result = array ();
		
		$fixedSigners = new FixedSigners ( Connector::getInstance () );
		$signatures = $fixedSigners->getAll ();
		
		foreach ( $signatures as $k => $v ) {
			$result [$v ['sigla']] = array (
					'pkey' => $v ['pkey'],
					'descrizione' => $v ['descrizione'] ,
					'email'=> $v ['email']
			);
		}
		$variableSignersRoles = new VariableSignersRoles ( Connector::getInstance () );
		$sigle = $variableSignersRoles->getRoleDescription ();
		
		$masterDocumentData = new MasterdocumentData ( Connector::getInstance () );
		$signatures = $masterDocumentData->searchByKeys ( array_keys($sigle), $id_md );
		$signatures = Utils::getListfromField($signatures, 'value');
		
		$publickeys=join(',', array_map("Utils::apici",$signatures));

		$signatures = $this->getBy('pkey',$publickeys,'sigla' ) ;
		
		foreach ( $signatures as $k => $v ) {
			$result [$v ['sigla']] = array (
					'pkey' => $v ['pkey'],
					'descrizione' => isset($sigle[$v ['sigla']]) ? $sigle[$v ['sigla']] : null ,
					'email'=> $v ['email']
			);
		}
		
		return $result;
	}
}

?>