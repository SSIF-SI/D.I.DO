<?php 
class XMLParser{
	private $_xml = null;
	
	public function __construct($xml, $md_type = null){
		$this->_xml = simplexml_load_file($xml);
		
		if(!is_null($md_type)){
			$this->_filter($md_type);
		} 
	}

	private function _filter($mdType){
		Utils::printr("N.docs:".count($this->_xml->list->document));
		Utils::printr("Filtro: $mdType");
		$toBeRemoved = array();
		for($k=0; $k<count($this->_xml->list->document); $k++){
			$onlyIfType = $this->_xml->list->document[$k]['onlyIfType'];
			
			if(is_null($onlyIfType))
				Utils::printr("Non trovato in {$this->_xml->list->document[$k]['name']}");
			else {
				$onlyIfType = (string)$onlyIfType;
				if($onlyIfType == $mdType){
					Utils::printr("Doc {$this->_xml->list->document[$k]['name']} Ok");
				} else {
					Utils::printr("Doc {$this->_xml->list->document[$k]['name']} NON Ok");
					Utils::printr($k);
					$toBeRemoved[] = $k;
				}
			}				
		}
		
		foreach($toBeRemoved as $key){
			Utils::printr($key);
			Utils::printr($this->_xml->list->document[$key]['name']);
			unset($this->_xml->list->document[$key]);
		}
		
		Utils::printr("N.docs:".count($this->_xml->list->document));
		
		Utils::printr($this->_xml->list);
		/*
		for($k=0; $k<count($this->_xml->list->document); $k++){
			if(!is_null($this->_xml->list->document[$k]['onlyIfType']) && (string)($this->_xml->list->document[$k]['onlyIfType']) != $mdType)
				unset($this->_xml->list->document[$k]); 
		}
		*/
			
	}
	
	public function getDocList(){
		return $this->_xml->list;
	}
}

class XMLParserException extends Exception{}
?>