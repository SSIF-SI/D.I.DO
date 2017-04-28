<?php
class XMLBrowser{
	private static $_instance = null;
	private $_xmlTree = array();
	private $_PermissionHelper;
	
	const FILE_REGEX = "^([A-Za-z_àèéìòù\s]{1,})(\.v[0-9]{1,}){0,1}(\.xml)$";
	
	public static function getInstance(){
		if(is_null(self::$_instance)){
			self::$_instance = new self();
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
		$this->_PermissionHelper = PermissionHelper::getInstance();
	}
	
	private function __clone(){}
	private function __wakeup(){}
	
	public function setPermissionHelper($ph){
		$this->_PermissionHelper = $ph;
	}
	
	public function getXmlTree($onlyOwner = false){
		if(!$onlyOwner)
			return $this->_xmlTree;
		
		$filtered = $this->_xmlTree;
		
		foreach($filtered as $catName=>$data){
			foreach($data['documenti'] as $tipoDocumento=>$versioni){
				foreach($versioni as $numVersione=>$metadata){
					if(!$this->_PermissionHelper->isGestore($metadata['owner'])){
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
	
	public function getXmlList($dividedBycategories = true, $services = null){
		$list = array();
		foreach($this->_xmlTree as $categoria=>$xmlData){
			foreach($xmlData['documenti'] as $tipoDocumento=>$versioni){
				foreach($versioni as $numVersione=>$metadata){
					if(!is_null($services)){
						if(!in_array((string)$metadata['owner'],$services)){
						
							$isMDVisible =
								$this->_PermissionHelper->isGestore($metadata['owner']) ||
								$this->_PermissionHelper->isConsultatore($metadata['owner']);
						
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
							}
						}
					} else $isMDVisible = true;
					
					if($isMDVisible){
						if($dividedBycategories) 
							$list[$categoria][$xmlData['path'].$metadata['file']] = $metadata['xml'];
						else
							$list[$xmlData['path'].$metadata['file']] = $metadata['xml'];
					}
					
				}
			}
		}
		return $list;
	}
	
	public function getXmlFromNameAndData($name, $data){
		foreach($this->_xmlTree as $categoria=>$xmlData){
			foreach($xmlData['documenti'] as $tipoDocumento=>$versioni){
				
				if($tipoDocumento != $name) continue;
				foreach($versioni as $numVersione=>$metadata){
					$xml = $metadata['xml'];
					if(isset($xml['validEnd']) && (string($xml['validEnd'])) < $data) continue;
					return array(
						'xml_filename' => $xmlData['path'].$metadata['file'],
						'xml' => $this->getSingleXml($xmlData['path'].$metadata['file'])
					);
				}
			}
		}
		return null;
	}
	
	public function getSingleXml($xmlFilename, $services = null){
		$list = $this->getXmlList(false, $services);
		return array_key_exists($xmlFilename, $list) ? $list[$xmlFilename] : null;  
	}
	
	public function getXmlListByOwner($owner = null){
		$list = array();
		foreach($this->_xmlTree as $categoria=>$xmlData){
			foreach($xmlData['documenti'] as $tipoDocumento=>$versioni){
				foreach($versioni as $numVersione=>$metadata){
					if(is_array($owner) and count($owner)){ 
						foreach($owner as $o){
							if((string)$metadata['owner'] == $o){
								$list[$categoria][] = $xmlTree['path'].$metadata['file'];
								break;
							}
						}
					} else { 
						$list[$categoria][] = $xmlTree['path'].$metadata['file'];
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
					"xml" => $xml, 
					"owner" => $xml['owner'],
					"from" => $xml['from'],
					"validEnd" => isset($xml['validEnd']) ? $xml['validEnd'] : null,
					"visibleFor" => $xml['visibleFor'],						
					"hiddenFor" => $xml['hiddenFor']					
				); 
		}
		
		
		return $tree;
	} 
	
}
?>