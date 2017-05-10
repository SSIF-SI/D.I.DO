<?php

class XMLFilterValidity extends AXMLFilter implements IXMLFilter {

	public function apply(&$list) {
		if (! $this->init ())
			return;
		
		foreach ( $list as $catName => $data ) {
			foreach ( $data as $tipoDocumento => $versioni ) {
				foreach ( $versioni as $nv => $xmlData ) {
					$this->_XMLParser->setXMLSource ( $xmlData [XMLDataSource::LABEL_XML] );
					if (! $this->_XMLParser->isValid ( $this->_filters ))
						unset ( $list [$catName] [$tipoDocumento] [$nv] );
				}
				if (empty ( $list [$catName] [$tipoDocumento] ))
					unset ( $list [$catName] [$tipoDocumento] );
			}
			if (empty ( $list [$catName] ))
				unset ( $list [$catName] );
		}
	}
}