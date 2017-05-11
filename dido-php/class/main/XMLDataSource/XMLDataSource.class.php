<?php

class XMLDataSource {

	const FILE_REGEX = "^([A-Za-z_àèéìòù\s]{1,})(\.v[0-9]{1,}){0,1}(\.xml)$";

	const LABEL_FILE = "file";

	const LABEL_VERSIONE = "versione";

	const LABEL_XML = "xml";

	private $_xmlTree = [ ];

	private $_filtered = [ ];

	private $_XMLParser;

	private $_XMLTreeCreated = false;
	
	public function __construct($XMLParser = null) {
		$this->_XMLParser = is_null ( $XMLParser ) ? new XMLParser () : $XMLParser;
	}
	
	private function _createXMLTree(){
		$catlist_full = glob ( XML_MD_PATH . "*", GLOB_ONLYDIR );
		foreach ( $catlist_full as $cat ) {
			$catName = basename ( $cat );
			$xmlList = array_map(function($el){ return str_replace(XML_MD_PATH,"",$el);}, glob ( $cat . "/*.xml" ));
			$documenti = $this->_createDocTree ( $catName, $xmlList );
			$this->_xmlTree [$catName] = $documenti;
		}
		$this->_filtered = $this->_xmlTree;
		$this->_XMLTreeCreated = true;
	}

	public function getXmlTree($onlyFilelist = false) {
		if(!$this->_XMLTreeCreated)
			$this->_createXMLTree();
		
		if (! $onlyFilelist) {
			$filtered = $this->_filtered;
			$this->resetFilters ();
			return $filtered;
		}
		
		$xmlList = [ ];
		foreach ( $this->_filtered as $catName => $data ) {
			foreach ( $data as $tipoDocumento => $versioni ) {
				foreach ( $versioni as $xml )
					;
				array_push ( $xmlList, $xml [self::LABEL_FILE] );
			}
		}
		return $xmlList;
	}

	public function filter(IXMLFilter $filter) {
		$filter->setXMLParser ( $this->_XMLParser );
		$filter->apply ( $this->_filtered );
		return $this;
	}

	private function resetFilters() {
		$this->_filtered = $this->_xmlTree;
	}

	public function getSingleXmlByFilename($xmlFilename) {
		foreach($this->_xmlTree as $categorie => $categoria){
			foreach($categoria as $nome => $versioni){
				foreach($versioni as $versione=> $dato){
					if($dato[self::LABEL_FILE] == $xmlFilename)
						return $dato;
				}
			}
		}
		return null;
	}
	
	private function _createDocTree($catName, $xmlList) {
		$tree = array ();
		$ownerTree = array ();
		foreach ( $xmlList as $xmlFile ) {
			$this->_XMLParser->setXMLSource( $xmlFile );
			
			$fileName = basename ( $xmlFile );
			preg_match ( "/" . self::FILE_REGEX . "/", $fileName, $fileInfo );
			
			$fileInfo [2] = ! empty ( $fileInfo [2] ) ? ("versione " . ltrim ( $fileInfo [2], ".v" )) : null;
			
			$tree [$fileInfo [1]] [$fileInfo [2]] = array (
					self::LABEL_FILE => /*$catName . DIRECTORY_SEPARATOR . $fileName*/ $xmlFile,
					self::LABEL_VERSIONE => $fileInfo [2],
					self::LABEL_XML => $this->_XMLParser->getXMLSource () 
			);
		}
		
		return $tree;
	}
}
?>