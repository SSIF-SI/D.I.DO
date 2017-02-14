<?php
class PDFSigner{
	private $_signatureManagerClass;
	private $_keystore;
	private $_password;
	private $_outputStream;
	private $_pdfpath;
	
	public function __construct(){
		$this->setSignatureManagerClass(new Java('dido.pdfmanager.PdfManager'));
	}
	
	public function setSignatureManagerClass($smg){
		$this->_signatureManagerClass = $smg;
	}
	public function loadPDF($pdf_path){
		$this->_pdfpath=$pdf_path;		
	}
	public function setPassword($password){
		$this->_password=$password;
		
	}
	public function setOutputStream($outputStream){
		$this->_outputStream=$outputStream;
		
	}
	public function setKeystore($keystore){
		$this->_keystore=$keystore;
	}
	public function getPdf(){
		return $this->_pdfpath;
	}
	public function getKeystore(){
		return $this->_keystore;
	}
	
	public function signPDF($pdf_path=null,$outputStream=null,$keystore=null,$password=null){
		if($pdf_path!=null)
			$this->loadPDF($pdf_path);
		if($outputStream!=null)
			$this->setOutputStream($outputStream);
		if($keystore!=null)
			$this->setKeystore($keystore);
		if($password!=null)
			$this->setPassword($password);
		$this->_signatureManagerClass->sign($this->_pdfpath,$this->_outputStream,$this->_keystore,$this->_password);
	}
}
?>