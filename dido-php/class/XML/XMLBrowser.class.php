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
			$xmlList = glob($cat."/*.xml");
			$documenti = self::_createDocTree($xmlList);
			$this->_xmlTree[$catName] = array('path' => $catName."/", 'documenti' => $documenti);
		}
	}
	
	public function getXmlTree($onlyOwner = false){
		if(!$onlyOwner)
			return $this->_xmlTree;
		
		$filtered = $this->_xmlTree;
		
		foreach($filtered as $catName=>$data){
			foreach($data['documenti'] as $tipoDocumento=>$versioni){
				foreach($versioni as $numVersione=>$metadata){
					if(!in_array((string)$metadata['owner'],PermissionHelper::getInstance()->getUserField('gruppi'))){
						unset($filtered[$catName]['documenti'][$tipoDocumento][$numVersione]);
					}
				}
				if(empty($filtered[$catName]['documenti'][$tipoDocumento]))
					unset($filtered[$catName]['documenti'][$tipoDocumento]);
			}
			
			if(empty($filtered[$catName]['documenti'])) unset($filtered[$catName]);
		}
		
		return $filtered;
	}
	
	public function getXmlList($dividedBycategories = true){
		$list = array();
		foreach($this->_xmlTree as $categoria=>$xmlData){
			foreach($xmlData['documenti'] as $tipoDocumento=>$versioni){
				foreach($versioni as $numVersione=>$metadata){
					if($dividedBycategories) 
						$list[$categoria][] = $xmlTree['path'].$metadata['file'];
					else
						$list[] = $xmlTree['path'].$metadata['file'];
				}
			}
		}
		return $list;
	}
	
	public function getXmlListByOwner($owner = null){
		$list = array();
		foreach($this->_xmlTree as $categoria=>$xmlData){
			foreach($xmlData['documenti'] as $tipoDocumento=>$versioni){
				foreach($versioni as $numVersione=>$metadata){
					if(!is_null($owner)){ 
						if((string)$metadata['owner'] == $owner)
							$list[$owner][] = $xmlTree['path'].$metadata['file'];
					} else {
						$list[(string)$metadata['owner']][] = $xmlTree['path'].$metadata['file'];
					}
				}
			}
		}
		return $list;
	}
	
	public function getXmlCategories(){
		return array_keys($this->_xmlTree);
	}
	
	private function _createDocTree($xmlList){
		$tree = array();
		$ownerTree = array();
		foreach($xmlList as $xmlFile){
			$xml = simplexml_load_file($xmlFile);
			$fileName = basename($xmlFile);
			preg_match("/".self::FILE_REGEX."/", $fileName,$fileInfo);
			if(!empty($fileInfo[2])) 
				$fileInfo[2] = "versione ".ltrim($fileInfo[2],".v");
			$tree[$fileInfo[1]][$fileInfo[2]] = 
				array(
					"file" => $fileInfo[0], 
					"owner" => $xml['owner'],
					"visibleFor" => $xml['visibleFor'],						
					"hiddenFor" => $xml['hiddenFor']					
				); 
		}
		
		
		return $tree;
	} 
	
	public function filterXmlByServices($services = null){
		if(is_null($services) || !is_array($services) || PermissionHelper::getInstance()->isAdmin()) return;
		
		foreach($this->_xmlTree as $catName=>$data){
			foreach($data['documenti'] as $tipoDocumento=>$versioni){
				foreach($versioni as $numVersione=>$metadata){
					
					if(!in_array((string)$metadata['owner'],$services)){
						
						$isMDVisible = 
							PermissionHelper::getInstance()->isGestore() || 
							PermissionHelper::getInstance()->isConsultatore() || 
							PermissionHelper::getInstance()->isSigner();
						
						if($isMDVisible){
							if(!is_null($metadata['visibleFor'])){
								$isMDVisible = false;
								$list = split(",", (string)$metadata['visibleFor']);
								foreach($services as $service){
									if(in_array($service, $list)){
										$isMDVisible = true;
										break;
									}
								}
							} 
							
							if(!is_null($metadata['hiddenFor'])){
								$list = split(",", (string)$metadata['hiddenFor']);
								foreach($services as $service){
									if(in_array($service, $list)){
										$isMDVisible = false;
										break;
									}
								}
							}
						
							if(!$isMDVisible){
								unset($this->_xmlTree[$catName]['documenti'][$tipoDocumento][$numVersione]);					
							}
						} else 
							unset($this->_xmlTree[$catName]['documenti'][$tipoDocumento][$numVersione]);
					}
				}
				if(empty($this->_xmlTree[$catName]['documenti'][$tipoDocumento]))
					unset($this->_xmlTree[$catName]['documenti'][$tipoDocumento]);
			}
			if(empty($this->_xmlTree[$catName]['documenti'])) unset($this->_xmlTree[$catName]);
		}
	}
}
?>