<?php

class PDFParser {

	private $_signatureManagerClass;

	private $_signatures;

	private $_metadata;

	private $_annotations;

	public function __construct() {
		$this->setSignatureManagerClass ( new Java ( 'dido.pdfmanager.PdfManager' ) );
	}

	public function setSignatureManagerClass($smg) {
		$this->_signatureManagerClass = $smg;
	}

	public function loadPDF($pdf_path) {
		$this->_signatureManagerClass->loadPDF ( $pdf_path );
		$this->_signatures = json_decode ( ( string ) $this->_signatureManagerClass->getSignatures () );
		$this->_annotations = json_decode ( ( string ) $this->_signatureManagerClass->getAnnotations () );
		
		$content = json_decode ( $this->_signatureManagerClass->getXmlMetadata () );
		$xmp_data_start = strpos ( $content, '<x:xmpmeta' );
		$xmp_data_end = strpos ( $content, '</x:xmpmeta>' );
		$xmp_length = $xmp_data_end - $xmp_data_start;
		$xmp_data = substr ( $content, $xmp_data_start, $xmp_length + 12 );
		$xmp_data = str_replace ( ":", "_", $xmp_data );
		
		$this->_metadata = simplexml_load_string ( $xmp_data );
	}

	public function getSignatures() {
		return $this->_signatures;
	}

	public function getMetadata() {
		return $this->_metadata;
	}

	public function getAnnotations() {
		return $this->_annotations;
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