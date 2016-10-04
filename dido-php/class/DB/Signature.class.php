<?php
class Signature extends AnyDocument {
	
	protected $VIEW = "signers_view";
	
	public function __construct($connInstance) {
		parent::__construct ( $connInstance );
	}
	
	public function getSigners($id_md) {
		$sigle = array ();
		$result = array ();
		
		$fixedSigners = new FixedSigners ( Connector::getInstance () );
		$signatures = $fixedSigners->getAll ();
		
		foreach ( $signatures as $k => $v ) {
			$result [$v ['sigla']] = array (
					'pkey' => $v ['pkey'],
<<<<<<< HEAD
					'descrizione' => $v ['descrizione'],
					'email' => $v ['email'] 
=======
					'descrizione' => $v ['descrizione'] ,
					'email'=> $v ['email']
>>>>>>> branch 'develop' of git://github.com/liparig/D.I.DO.git
			);
		}
		$variableSignersRoles = new VariableSignersRoles ( Connector::getInstance () );
		$sigle = $variableSignersRoles->getRoleDescription ();
<<<<<<< HEAD
		Utils::printr ( $sigle );
=======
		
>>>>>>> branch 'develop' of git://github.com/liparig/D.I.DO.git
		$masterDocumentData = new MasterdocumentData ( Connector::getInstance () );
<<<<<<< HEAD
		$signatures = $masterDocumentData->searchByKeys ( $sigle, $id_md );
=======
		$signatures = $masterDocumentData->searchByKeys ( array_keys($sigle), $id_md );
		$signatures = Utils::getListfromField($signatures, 'value');
		
		$publickeys=join(',', array_map("Utils::apici",$signatures));

		$signatures = $this->getBy('pkey',$publickeys,'sigla' ) ;
		
>>>>>>> branch 'develop' of git://github.com/liparig/D.I.DO.git
		foreach ( $signatures as $k => $v ) {
<<<<<<< HEAD
			$publickeys [$v ['key']] = $v ['value'];
		}
		$publickeys = join ( ',', $publickeys );
		$signatures = $this->getBy ( 'pkey', $publickeys, 'sigla' );
		foreach ( $signatures as $k => $v ) {
			$result [$v ['sigla']] = array (
					'pkey' => $v ['pkey'],
					'descrizione' => array_search ( $v ['key'], $sigle ),
					'email' => $v ['email'] 
=======
			$result [$v ['sigla']] = array (
					'pkey' => $v ['pkey'],
					'descrizione' => isset($sigle[$v ['sigla']]) ? $sigle[$v ['sigla']] : null ,
					'email'=> $v ['email']
>>>>>>> branch 'develop' of git://github.com/liparig/D.I.DO.git
			);
		}
		
		return $result;
	}
}

?>