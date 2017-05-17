<?php

class XMLFilterCategory extends AXMLFilter implements IXMLFilter {

	public function apply(&$list) {
		if (! $this->init ())
			return;
		
		foreach ( $list as $catName => $data ) {
			if (! in_array ( $this->_filters, $list ))
				unset ( $list [$catName] );
		}
	}
}