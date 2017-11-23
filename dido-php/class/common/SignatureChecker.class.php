<?php 
class SignatureChecker{
	private $_ftpDataSource;
	private $_SignatureInspector;
	private $_signaturesOnDocument = null;
	
	public function __construct(IFTPDataSource $ftpDataSource){
		$this->_ftpDataSource = $ftpDataSource;
		$this->_SignatureInspector = new SignatureInspector();
	}
	
	/*
	 * Questa non dovrebbe essere qui
	 * ma in un componente che "gestisce le firme"
	 */
	
	public function load($filename){
		$tmpFile = $this->_ftpDataSource->getTempFile ( $filename );
		
		$path_parts = pathinfo($filename);
		$ext = strtoupper($path_parts['extension']);
		
		$this->_SignatureInspector->load ( $tmpFile, $ext );
		$this->_signaturesOnDocument = $this->_SignatureInspector->getSignatures ();
		unlink ( $tmpFile );
		
		return $this;
	}
	
	public function checkSignature($signature) {
		if (count ( $this->_signaturesOnDocument )) {
			foreach ( $this->_signaturesOnDocument as $sod ) {
			// Utils::printr($sod);
				if ($sod->publicKey == $signature)
				return true;
			}
		} else
			return false;
	}
	
	public static function emptySignatures($signatures){
		$signatures = (array) $signatures;
		return empty($signatures);
	}
}
?>