<?php

class UserSignature {

	private $_signature;

	private $_specialSignatures;
	
	private $_allSpecialSignatures;

	private $_signatureRoles;

	public function __construct(IDBConnector $dbConnector, $user_id) {
		$signersObj = new Signers ( $dbConnector );
		$mySignature = Utils::getListfromField ( $signersObj->getBy ( Signers::ID_PERSONA, $user_id ), Signers::PKEY, Signers::ID_PERSONA );
		
		$signatureObj = new Signature ( DBConnector::getInstance () );
		$signatures = $signatureObj->getAll ( Signature::SIGLA, Signature::ID_ITEM );
		
		$signer = array_merge ( Utils::filterList ( $signatures, Signature::ID_PERSONA, $user_id ), Utils::filterList ( $signatures, Signature::ID_DELEGATO, $user_id ) );
		
		$this->_signature = reset ( $mySignature );
		$this->_signatureRoles = Utils::getListfromField ( $signer, null, SignersRoles::SIGLA );
		
		$specialSignatures = new SpecialSignatures($dbConnector);
		$this->_allSpecialSignatures = Utils::groupListBy($specialSignatures->getAll(),SpecialSignatureTypes::TYPE);
		$this->_specialSignatures = Utils::getListfromField($specialSignatures->getBy(SpecialSignatures::ID_PERSONA, $user_id),SpecialSignatures::PKEY,SpecialSignatureTypes::TYPE);
		
	}

	public function getSignature() {
		return $this->_signature;
	}

	public function getSpecialSignatures() {
		return $this->_specialSignatures;
	}

	public function getAllSpecialSignatures() {
		return $this->_allSpecialSignatures;
	}

	public function getSignatureRoles() {
		return $this->_signatureRoles;
	}
}
?>