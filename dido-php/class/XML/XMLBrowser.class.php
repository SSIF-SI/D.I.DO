<?php
class XMLBrowser{
	private static $_instance = null;
	private $_xmlTree = array();
	
	const FILE_REGEX = "^([A-Za-z_\s]{1,})(\.v[0-9]{1,}){0,1}(\.xml)$";
	
	public static function getInstance(){
		if(is_null(self::$_instance)){
			self::$_instance = new XMLBrowser();
		}
		return self::$_instance;
	}
	
	private function __construct(){
		$catlist_full = glob(XML_MD_PATH."*",GLOB_ONLYDIR);
		foreach($catlist_full as $cat){
			$catName = basename($cat);
			$xmlList = array_map('basename',glob($cat."/*.xml"));
			$documenti = self::_createDocTree($xmlList);
			$this->_xmlTree[$catName] = array('path' => $catName."/", 'documenti' => $documenti);
		}
	}
	
	public function getXmlTree(){
		return $this->_xmlTree;
	}
	
	public function getXmlCategories(){
		return array_keys($this->_xmlTree);
	}
	
	private function _createDocTree($xmlList){
		$tree = array();
		foreach($xmlList as $xmlFile){
			$fileName = basename($xmlFile);
			preg_match("/".self::FILE_REGEX."/", $fileName,$fileInfo);
			if(!empty($fileInfo[2])) 
				$fileInfo[2] = "versione ".ltrim($fileInfo[2],".v");
			$tree[$fileInfo[1]][$fileInfo[2]] = $fileInfo[0]; 
		}
		
		return $tree;
	} 
	
}
?>

