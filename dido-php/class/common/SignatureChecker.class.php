<?php 
class SignatureChecker{
	private $_ftpDataSource;
	private $_PDFParser;
	private $_signaturesOnDocument = null;
	
	public function __construct(IFTPDataSource $ftpDataSource){
		$this->_ftpDataSource = $ftpDataSource;
		$this->_PDFParser = new PDFParser();
	}
	
	/*
	 * Questa non dovrebbe essere qui
	 * ma in un componente che "gestisce le firme"
	 */
	
	public function load($filename){
		$tmpPDF = $this->_ftpDataSource->getTempFile ( $filename );
		
		$this->_PDFParser->loadPDF ( $tmpPDF );
		$this->_signaturesOnDocument = $this->_PDFParser->getSignatures ();
		unlink ( $tmpPDF );
		
		return $this;
	}
	
	public function checkSignature($signature) {
		
		if (count ( $this->_signaturesOnDocument )) {
			foreach ( $signaturesOnDocument as $sod ) {
			// Utils::printr($sod);
				if ($sod->publicKey == $signature)
				return true;
			}
		} else
			return false;
	}
}
?>