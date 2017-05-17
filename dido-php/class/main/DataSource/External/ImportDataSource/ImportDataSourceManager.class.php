<?php

class ImportDataSourceManager {

	private $_externalDataSourceRepository = [ ];

	public function __construct() {
		$this->register ( GecoDataSource::DATA_SOURCE_LABEL, new GecoDataSource () );
	}

	public function register($labelDataSource, IExternalDataSource $externalDataSource) {
		if (! is_object ( $externalDataSource ))
			throw new Exception ( "Can't register a non object" );
		
		$this->_externalDataSourceRepository [$labelDataSource] = $externalDataSource;
	}

	public function isRegistered($labelDataSource) {
		return array_key_exists ( $labelDataSource, $this->_externalDataSourceRepository );
	}

	public function getSource($labelDataSource = null) {
		return is_null ( $labelDataSource ) ? $this->_externalDataSourceRepository : ($this->isRegistered ( $labelDataSource ) ? $this->_externalDataSourceRepository [$labelDataSource] : false);
	}
}
?>