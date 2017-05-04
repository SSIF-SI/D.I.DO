<?php 
class XMLFilterOwner extends AXMLFilter implements IXMLFilter{
	public function apply(&$list){
		$this->init();
		
		foreach($list as $catName=>$data){
			foreach($data as $tipoDocumento=>$versioni){
				foreach($versioni as $nv=>$xmlData){
					$this->_XMLParser->setXMLSource($xmlData['xml']);
					if(!$this->_XMLParser->isOwner($this->_filters))
						unset($list[$catName][$tipoDocumento][$nv]);
				}
				if(empty($list[$catName][$tipoDocumento]))
					unset($list[$catName][$tipoDocumento]);
			}
			if(empty($list[$catName]))
				unset($list[$catName]);
		}
		
		/*foreach($list as $catName=>$data){
			foreach($data['documenti'] as $tipoDocumento=>$versioni){
				foreach($versioni as $numVersione=>$metadata){
					if(!in_array($owner, $metadata['owner'])){
						unset($list[$catName]['documenti'][$tipoDocumento][$numVersione]);
					}
				}
				if(empty($list[$catName]['documenti'][$tipoDocumento]))
					unset($list[$catName]['documenti'][$tipoDocumento]);
			}
				
			if(empty($list[$catName]['documenti'])) unset($list[$catName]);
		}
		*/
	}
}