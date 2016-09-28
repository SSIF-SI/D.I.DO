<?php 
class XMLParser{
	public static $instance = null;
	private $_xml = null;
	
	public static function getInstance(){
		if(is_null(self::$instance)){
			self::$instance = new XMLParser();
		}
		return self::$instance;
	}
	
	private function __construct(){}
	
	public function setXMLSource($xml, $md_type = null){
		$this->_xml = simplexml_load_file($xml);
		
		if(!is_null($md_type)){// Se il master document è di un certo tipo lo filtro
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
	
	public function getDocList(){
		return $this->_xml->list->document;
	}
	
	public function getDocTypes(){
		return (array)$this->_xml->types;
	}
	
}

class XMLParserException extends Exception{}
?>