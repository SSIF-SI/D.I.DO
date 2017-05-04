<?php 
class XMLFilterCategory extends AXMLFilter implements IXMLFilter{
	public function apply(&$list){
		$this->init();
		
		foreach($list as $catName=>$data){
			if(!in_array($this->_filters, $list))
				unset($list[$catName]);
		}
		
	}
}