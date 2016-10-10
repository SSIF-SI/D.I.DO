<?php 
class XMLParser{
	private static $_instance = null;
	private $_xml = null;
	
	public static function getInstance(){
		if(is_null(self::$_instance)){
			self::$_instance = new XMLParser();
		}
		return self::$_instance;
	}
	
	private function __construct(){}
	
	public function setXMLSource($xml, $md_type = null){
		$this->_xml = simplexml_load_file($xml);
		
		if(!is_null($md_type)){// Se il master document è di un certo tipo filtro i documenti associati
			$this->_filter($md_type);
		}
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
}

class XMLParserException extends Exception{}
?>