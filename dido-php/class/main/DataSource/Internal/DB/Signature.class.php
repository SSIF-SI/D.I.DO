<?php

class Signature extends AnyDocument {

	const ID_ITEM = "id_item";

	const SIGLA = SignersRoles::SIGLA;

	const DESCRIZIONE = SignersRoles::DESCRIZIONE;

	const ID_PERSONA = Signers::ID_PERSONA;

	const PKEY = Signers::PKEY;

	const ID_DELEGATO = "id_delegato";

	const PKEY_DELEGATO = "pkey_delegato";

	const FIXED_ROLE = SignersRoles::FIXED_ROLE;

	protected $VIEW = "signers_view";

	protected $TABLE = "signers";

	public function __construct($connInstance) {
		parent::__construct ( $connInstance );
	}

	public function getSigners($id_md, $md_inputs) {
		$signesr = array ();
		$sigle = array ();
		$result = array ();
		
		$fixedSigners = new FixedSigners ( DBConnector::getInstance () );
		$id_fs = array_unique ( Utils::getListfromField ( $fixedSigners->getAll (), FixedSigners::ID_PERSONA ) );
		$id_fs = join ( ", ", array_map ( "Utils::apici", $id_fs ) );
		$fixed = Utils::filterList ( $this->getBy ( FixedSigners::ID_PERSONA, $id_fs ), self::FIXED_ROLE, 1 );
		$signers = Utils::getListfromField ( $fixed, null, self::SIGLA );
		
		$SignersRoles = new SignersRoles ( DBConnector::getInstance () );
		$sigle = $SignersRoles->getRoleDescription ();
		
		/*
		 * $masterDocumentData = new MasterdocumentData (
		 * DBConnector::getInstance () );
		 * $id_vs = $masterDocumentData->searchByKeys ( array_keys ( $sigle ),
		 * $id_md );
		 * $id_vs = Utils::getListfromField ( $id_vs, 'value' );
		 */
		
		$id_vs = array ();
		if(empty($md_inputs))
			return array();
		
		foreach ( $md_inputs as $key => $value ) {
			if (in_array ( $key, $sigle ))
				array_push ( $id_vs, $value );
		}

		
		// $signatures = $this->getBy ( 'id_persona',
		// array_merge($id_fs,$id_vs), 'sigla' );
		// $id_s = array_unique(array_merge($id_fs,$id_vs));
		$id_vs = join ( ", ", array_map ( "Utils::apici", $id_vs ) );
		
		$signers = array_merge ( $signers, Utils::getListfromField ( $this->getBy ( self::ID_PERSONA, $id_vs ), null, self::SIGLA ) );
		return $signers;
	}
}

?>