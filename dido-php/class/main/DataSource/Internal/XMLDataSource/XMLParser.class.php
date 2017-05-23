<?php

class XMLParser implements IXMLParser {
	// MD Params
	const MD_NAME = "name";

	const FROM = "from";

	const OWNER = "owner";

	const VISIBLEFOR = "visibleFor";

	const HIDDENFOR = "hiddenFor";

	const VALIDEND = "validEnd";
	
	// DOC Params
	const DOC_NAME = "name";

	const LOAD = "load";

	const ONLYIFTYPE = "onlyIfType";

	const MIN_OCCUR = "minOccur";
	
	const MAX_OCCUR = "maxOccur";
	
	// Signature params 
	const ROLE = "role";

	const ALT = "alt";
	
	// Input Params
	const TYPE = "type";
	
	const TRANSFORM = "transform";
	
	const KEY = "key";
	
	const VALUES = "values";
	
	const SHORTWIEW = "shortView";
	
	const EDITABLE = "editable";
	
	// Common Params
	const MANDATORY = "mandatory";

	
	private $_xml = null;

	public function __construct($xml = null, $md_type = null) {
		if (! is_null ( $xml ))
			$this->setXMLSource ( $xml, $md_type );
	}

	public function load($filename){
		$this->_xml = simplexml_load_file($filename);
	}
	
	public function setXMLSource($xml, $md_type = null) {
		if ($xml instanceof SimpleXMLElement)
			$this->_xml = $xml;
		else
			$this->load ( XML_MD_PATH . $xml );
		
		if (! is_null ( $md_type )) { // Se il master document è di un certo tipo
		                              // filtro i documenti associati
			$this->_filter ( $md_type );
		}
	}

	public function getXmlSource() {
		return $this->_xml;
	}

	private function _filter($mdType) {
		for($i = 0, $k = 0; $k < count ( $this->_xml->list->document ); $k ++) {
			
			$onlyIfType = $this->_xml->list->document [$k] [self::ONLYIFTYPE];
			
			if (! is_null ( $onlyIfType )) {
				$onlyIfType = ( string ) $onlyIfType;
				if ($onlyIfType != $mdType) {
					$key = $k - $i ++;
					unset ( $this->_xml->list->document [$key] );
				}
			}
		}
	}

	public function getMasterDocumentInputs() {
		return $this->_xml->inputs->input;
	}

	public function getDocumentSignatures($docname){
		$document = $this->getDocByName($docname);
		if(!$document) return false;
		
		return $document->signatures->signature;
	}
	
	public function getDocumentInputs($docname) {
		foreach ( $this->getDocList () as $document ) {
			$this->checkIfMustBeLoaded ( $document );
			
			if ($document [self::DOC_NAME] == $docname) {
				return $document->inputs->input;
			}
		}
		return false;
	}

	public function getDocList() {
		return $this->_xml->list->document;
	}

	public function getDocTypes() {
		return ( array ) $this->_xml->types;
	}

	public function getSource() {
		return $this->_xml [self::FROM];
	}

	public function getOwner() {
		return $this->_xml [self::OWNER];
	}

	public function isOwner(array $services) {
		return in_array ( $this->getOwner (), $services );
	}

	public function isSigner(array $sigRoles) {
		$signatures = [];
		
		foreach ( $this->getDocList () as $document ) {
			$this->checkIfMustBeLoaded ( $document );
			$docName = (string)$document[self::DOC_NAME];
			
			$docSignatures = $this->getDocumentSignatures($docName);
			
			if (!$docSignatures) 
				continue;

			foreach ( $docSignatures as $signature ) {
				if( in_array ( $signature [self::ROLE], $sigRoles )){
					
					if(!isset($signatures[(string)$signature [self::ROLE]]))
						$signatures[(string)$signature [self::ROLE]] = [];
					
					array_push($signatures[(string)$signature [self::ROLE]], $docName);
				}
				if( in_array ( $signature [self::ALT], $sigRoles )){
					
					if(!isset($signatures[(string)$signature [self::ALT]]))
						$signatures[(string)$signature [self::ALT]] = [];
					
					array_push($signatures[(string)$signature [self::ALT]], $docName);
					
				}
			}
			
		}
		
		
		return (empty($signatures) ? false : $signatures);
	}

	public function isVisible(array $services) {
		if ($this->isOwner ( $services ))
			return true;
		
		if (! is_null ( $this->_xml [self::VISIBLEFOR] )) {
			$list = split ( ",", ( string ) $this->_xml [self::VISIBLEFOR] );
			foreach ( $services as $service ) {
				if (in_array ( $service, $list )) {
					return true;
				}
			}
		}
		
		if (! is_null ( $this->_xml [self::HIDDENFOR] )) {
			$list = split ( ",", ( string ) $this->_xml [self::HIDDENFOR] );
			foreach ( $services as $service ) {
				if (in_array ( $service, $list )) {
					return false;
				}
			}
		}
		
		return false;
	}

	public function isValid($date = null) {
		if (empty ( $this->_xml [self::VALIDEND] ))
			return true;
		if (is_null ( $date ))
			$date = date ( 'Y-m-d' );
		return $date > $this->_xml [self::VALIDEND];
	}

	public function getDocByName($docname) {
		foreach ( $this->_xml->list->document as $document ) {
			$this->checkIfMustBeLoaded ( $document );
			if (( string ) $document [self::DOC_NAME] == $docname)
				return $document;
		}
		return null;
	}

	public function checkIfMustBeLoaded(&$document) {
		if (! is_null ( $document [self::LOAD] )) {
			$defaultXml = XML_STD_PATH . ( string ) $document [self::LOAD];
			$document = simplexml_load_file ( $defaultXml );
		}
	}

	public function generateData($data, $inputs) {
		foreach ( $inputs as $input ) {
			$data_key = FormHelper::fieldFromLabel ( ( string ) $input );
			$mandatory = isset ( $input [XMLParser::MANDATORY] ) ? ( bool ) ( string ) $input [XMLParser::MANDATORY] : true;
			
			if (empty ( $data [$data_key] ) && $mandatory) 
				return false;

			$value = $data [$data_key];
			
			unset ( $data [$data_key], $input [XMLParser::MANDATORY] );
			$data [( string ) $input] = $value;
		}
		
		return $data;
	}
}

class XMLParserException extends Exception {
}
?>