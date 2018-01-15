<?php

class SignatureInspector{
	private $_signatureManagerClass;
	
	private $_signatures;
	
	private $_metadata = null;
	
	private $_annotations;

	public function __construct() {
		$this->setSignatureManagerClass ( new Java ( 'dido.signatureinspector.SignatureInspector' ) );
	}

	public function setSignatureManagerClass($smg) {
		$this->_signatureManagerClass = $smg;
	}

	public function load($file_path, $ext = null) {
		if(is_null($ext)){
			$path_parts = pathinfo($file_path);
			$ext = strtoupper($path_parts['extension']);
		}
		$method = "load$ext";
		//finfo("%s(%s) Called", $method,$file_path);
		$this->_signatureManagerClass->$method ( $file_path );
		$this->_signatures = json_decode ( ( string ) $this->_signatureManagerClass->getSignatures () );
		//finfo("getSignatures Called");
		$this->_annotations = json_decode ( ( string ) $this->_signatureManagerClass->getAnnotations () );
		//finfo("getAnnotations Called");
		
		if(strtolower($ext) == 'pdf'){
			$content = json_decode ( $this->_signatureManagerClass->getXmlMetadata () );
			$xmp_data_start = strpos ( $content, '<x:xmpmeta' );
			$xmp_data_end = strpos ( $content, '</x:xmpmeta>' );
			$xmp_length = $xmp_data_end - $xmp_data_start;
			$xmp_data = substr ( $content, $xmp_data_start, $xmp_length + 12 );
			$xmp_data = str_replace ( ":", "_", $xmp_data );
			
			$this->_metadata = simplexml_load_string ( $xmp_data );
		}
		
	}

	public function getAnnotations() {
		return $this->_annotations;
	}

	public function getSignatures() {
		return $this->_signatures;
	}

	public function getPDFDescription($attribute = null) {
		return is_null ( $attribute ) ? $this->_metadata->rdf_RDF->rdf_Description : $this->_metadata->rdf_RDF->rdf_Description [$attribute];
	}

	public function isPDFA() {
		$pdfConformance = $this->getPDFDescription ( 'pdfaid_conformance' );
		if (is_null ( $pdfConformance ))
			return false;
		return substr ( strtoupper ( ( string ) $this->_metadata->rdf_RDF->rdf_Description ['pdfaid_conformance'] ), 0, 1 ) == "A";
	}

	public function hasAnnotations() {
		return is_array ( $this->_annotations ) && count ( $this->_annotations ) > 0;
	}
}

?>