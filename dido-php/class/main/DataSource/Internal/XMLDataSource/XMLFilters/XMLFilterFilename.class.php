<?php 
class XMLFilterFilename extends AXMLFilter implements IXMLFilter {

	public function apply(&$list) {
		if (! $this->init ())
			return;

		foreach ( $list as $catName => $data ) {
			foreach ( $data as $tipoDocumento => $versioni ) {
				foreach ( $versioni as $nv => $xmlData ) {
					if (! in_array($xmlData[XMLDataSource::LABEL_FILE], $this->_filters ))
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
?>