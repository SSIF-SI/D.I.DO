<?php 
class SignatureChecker{
	public static function checkSignatures($filename, $document){
		$checkResult = array();
		// 1-Scarico il pdf da FTP
		$tmpPDF = FTPConnector::getInstance()->getTempFile($filename);
			
		// 2-Lo passo alla classe Java per recuperare le firme
		$sigClass = new Java('dido.signature.SignatureManager');
		$sigClass->loadPDF($tmpPDF);
		$signaturesOnDocument = json_decode((string)$sigClass->getSignatures());
		Utils::printr($signaturesOnDocument);
		// 3-cancello il file temporaneo
		unlink($tmpPDF);
		
		// 4-confronto le firme trovate con quelle attese..
		foreach($document->signatures->signature as $signature){
			$who = (string)$signature['role'];
			if($who == "REQ") {
				$checkResult[$who] = 'skipped';
				continue;
			}
			
			$pKey = self::_getSignerPkey($who);
			$checkResult[$who] = 'ok';
		}
		return $checkResult;
	}
	
	static function _getSignerPkey($role){
		// mi connetto al db 
		// verifico se $role è un firmatario fisso
		// altrimenti recupero dalla tabella variable_signers_roles la chiave 
		// con la quale richiedo, sempre al db, la mail del firmatario e quindi su 
		// variable_signers ho la chiave
	}
}
?>