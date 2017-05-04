<?php 
class XMLParser implements IXMLParser{
	private $_xml = null;
	
	public function __construct($xml = null, $md_type = null){
		if(!is_null($xml))
			$this->setXMLSource($xml, $md_type);
	}
	
	public function load($xmlFilename){
		$this->_xml = simplexml_load_file($xmlFilename);
	}
	
	public function setXMLSource($xml, $md_type = null){
		$this->_xml = $xml;
		
		if(!is_null($md_type)){// Se il master document è di un certo tipo filtro i documenti associati
			$this->_filter($md_type);
		}
	}
	
	public function getXmlSource(){
		return $this->_xml;
	}
	
	private function _filter($mdType){
		for($i=0,$k=0; $k<count($this->_xml->list->document); $k++){
			
			$onlyIfType = $this->_xml->list->document[$k]['onlyIfType'];
			
			if(!is_null($onlyIfType)){
				$onlyIfType = (string)$onlyIfType;
				if($onlyIfType != $mdType){
					$key = $k-$i++;
					unset($this->_xml->list->document[$key]);
				}
			}
							
		}
	}
	
	public function getMasterDocumentInputs(){
		return $this->_xml->inputs->input;
	}
	
	public function getDocList(){
		return $this->_xml->list->document;
	}
	
	public function getDocTypes(){
		return (array)$this->_xml->types;
	}
	
	public function getSource(){
		return $this->_xml['from'];
	}
	
	public function getOwner(){
		return $this->_xml['owner'];
	}
	
	public function isOwner(array $services){
		return in_array($this->getOwner(), $services);
	}
	
	public function isSigner(array $sigRoles){
		foreach($this->getDocList() as $document){
			$this->checkIfMustBeLoaded($document);
			
			if(!is_null($document->signatures->signature)){
				foreach($document->signatures->signature as $signature){
					if(in_array($signature['role'],$sigRoles) || in_array($signature['alt'],$sigRoles)) return true;
				}
			}
		}
	}
	
	public function isVisible(array $services){
		if($this->isOwner($services)) return true;
		
		if(!is_null($this->_xml['visibleFor'])){
			$list = split(",", (string)$this->_xml['visibleFor']);
			foreach($services as $service){
				if(in_array($service, $list)){
					return true;
				}
			}
		}
		
		if(!is_null($this->_xml['hiddenFor'])){
			$list = split(",", (string)$this->_xml['hiddenFor']);
			foreach($services as $service){
				if(in_array($service, $list)){
					return false;
				}
			}
		}
		
		return false;
	}
	
	public function isValid($date = null){
		if(empty($this->_xml['validEnd'])) return true;
		if(is_null($date)) $date = date('Y-m-d');
		return $date > $this->_xml['validEnd'];
	}
	
	public function getDocByName($docname){
		foreach($this->_xml->list->document as $document){
			$this->checkIfMustBeLoaded($document);
			if((string)$document['name'] == $docname) return $document;
		}
		return null;
	}
	
	public function checkIfMustBeLoaded(&$document){
		if(!is_null($document['load'])){
			$defaultXml = XML_STD_PATH . (string)$document['load'];
			$document = simplexml_load_file($defaultXml);
		}
	}
}


class XMLParserException extends Exception{}
?>