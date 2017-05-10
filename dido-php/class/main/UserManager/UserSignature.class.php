<?php

class UserSignature {

	private $_signature;

	private $_signatureRoles;

	public function __construct($user_id) {
		$signersObj = new Signers ( DBConnector::getInstance () );
		$mySignature = Utils::getListfromField ( $signersObj->getBy ( Signers::ID_PERSONA, $user_id ), Signers::PKEY, Signers::ID_PERSONA );
		
		$signatureObj = new Signature ( DBConnector::getInstance () );
		$signatures = $signatureObj->getAll ( Signature::SIGLA, Signature::ID_ITEM );
		
		$signer = array_merge ( Utils::filterList ( $signatures, Signature::ID_PERSONA, $user_id ), Utils::filterList ( $signatures, Signature::ID_DELEGATO, $user_id ) );
		
		$this->_signature = reset ( $mySignature );
		$this->_signatureRoles = Utils::getListfromField ( $signer, null, SignersRoles::SIGLA );
	}

	public function getSignature() {
		return $this->_signature;
	}

	public function getSignatureRoles() {
		return $this->_signatureRoles;
	}
}
?>