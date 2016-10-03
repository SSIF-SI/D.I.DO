<?php
class Signature extends AnyDocument {
	
	protected $VIEW = "signers_view";
	
	public function __construct($connInstance) {
		parent::__construct ( $connInstance );
	}
	
	public function getSigners($id_md) {
		Utils::printr ( $id_md );
		$sigle = array ();
		$result = array ();
		$fixedSigners = new FixedSigners ( Connector::getInstance () );
		$signatures = $fixedSigners->getAll ();
		foreach ( $signatures as $k => $v ) {
			$result [$v ['sigla']] = array (
					'pkey' => $v ['pkey'],
					'descrizione' => $v ['descrizione'],
					'email' => $v ['email'] 
			);
		}
		$variableSignersRoles = new VariableSignersRoles ( Connector::getInstance () );
		
		$sigle = $variableSignersRoles->getRoleDescription ();
		Utils::printr ( $sigle );
		$masterDocumentData = new MasterdocumentData ( Connector::getInstance () );
		$signatures = $masterDocumentData->searchByKeys ( $sigle, $id_md );
		foreach ( $signatures as $k => $v ) {
			$publickeys [$v ['key']] = $v ['value'];
		}
		$publickeys = join ( ',', $publickeys );
		$signatures = $this->getBy ( 'pkey', $publickeys, 'sigla' );
		foreach ( $signatures as $k => $v ) {
			$result [$v ['sigla']] = array (
					'pkey' => $v ['pkey'],
					'descrizione' => array_search ( $v ['key'], $sigle ),
					'email' => $v ['email'] 
			);
		}
		Utils::printr ( $result );
		return $result;
	}
}

?>