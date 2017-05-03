<?php 
class UserSignature{
	private static $UID = "id_persona";
	private static $OUID = "id_delegato";
	
	private $_signature;
	private $_signatureRoles;
	
	public function __construct($user_id){
		$signersObj = new Signers(Connector::getInstance());
		$mySignature = Utils::getListfromField($signersObj->getBy(self::$UID, $user_id), 'pkey',self::$UID);
		
		$signatureObj = new Signature(Connector::getInstance());
		$signatures = $signatureObj->getAll('sigla','id_item');
		
		$signer = array_merge(
				Utils::filterList($signatures,self::$UID, $user_id),
				Utils::filterList($signatures,self::$OUID, $user_id));
		
		$this->_signature = reset($mySignature);
		$this->_signatureRoles = Utils::getListfromField($signer,null,'sigla');
	}
	
	public function getSignature(){
		return $this->_signature;
	}

	public function getSignatureRoles(){
		return $this->_signatureRoles;
	}
}
?>