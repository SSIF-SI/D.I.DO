<?php 
class XMLFilterDocumentType extends AXMLFilter implements IXMLFilter{
	public function apply(&$list){
		$this->init();
		
		foreach($list as $catName=>$data){
			foreach($data as $tipoDocumento=>$versioni){
				if(!in_array($tipoDocumento, $this->_filters))
					unset($list[$catName][$tipoDocumento]);
			}
			if(empty($list[$catName]))
				unset($list[$catName]);
				
		}
		
	}
}