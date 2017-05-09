<?php 
class XMLDataSource{
	const FILE_REGEX 		= "^([A-Za-z_àèéìòù\s]{1,})(\.v[0-9]{1,}){0,1}(\.xml)$";
	
	const LABEL_FILE 		= "file";
	const LABEL_VERSIONE 	= "versione";
	const LABEL_XML 		= "xml";
	
	private $_xmlTree = [];
	private $_filtered = [];
	private $_XMLParser;
	
	public function __construct($XMLParser = null){
		
		$this->_XMLParser = is_null($XMLParser) ? new XMLParser() : $XMLParser;
		
		$catlist_full = glob(XML_MD_PATH."*",GLOB_ONLYDIR);
		foreach($catlist_full as $cat){
			$catName = basename($cat);
			$xmlList = glob($cat."/*.xml");
			$documenti = $this->_createDocTree($catName, $xmlList);
			$this->_xmlTree[$catName] = /*['path' => $catName."/", 'documenti' => $documenti];*/ $documenti;
		}
		$this->_filtered = $this->_xmlTree;
	}
	
	
	public function getXmlTree($onlyFilelist = false){
		if(!$onlyFilelist){
			$filtered = $this->_filtered;
			$this->resetFilters();
			return $filtered;
		}
			
		
		$xmlList = [];
		foreach($this->_filtered as $catName=>$data){
			foreach($data as $tipoDocumento=>$versioni){
				foreach($versioni as $xml);
					array_push($xmlList,$xml[self::LABEL_FILE]);
			}
		}
		return $xmlList;
	}
		
	public function filter(IXMLFilter $filter){
		$filter->setXMLParser($this->_XMLParser);
		$filter->apply($this->_filtered);
		return $this;
	}
	
	private function resetFilters(){
		$this->_filtered = $this->_xmlTree;
	}
	
	public function getSingleXml($xmlFilename){
		
	}
	
	private function _createDocTree($catName, $xmlList){
		$tree = array();
		$ownerTree = array();
		foreach($xmlList as $xmlFile){
			$this->_XMLParser->load($xmlFile);
			
			$fileName = basename($xmlFile);
			preg_match("/".self::FILE_REGEX."/", $fileName,$fileInfo);
			
			$fileInfo[2] = !empty($fileInfo[2]) ? ("versione ".ltrim($fileInfo[2],".v")) : null;
			
			$tree[$fileInfo[1]][$fileInfo[2]] = 
				array(
					self::LABEL_FILE 		=> $catName.DIRECTORY_SEPARATOR.$fileName, 
					self::LABEL_VERSIONE 	=> $fileInfo[2],
					self::LABEL_VERSIONE 	=> $this->_XMLParser->getXMLSource()
				); 
		}
		
		
		return $tree;
	} 

}
?>