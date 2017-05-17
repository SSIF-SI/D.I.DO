<?php

abstract class AXMLFilter {

	protected $_filters;

	protected $_XMLParser;

	public function __construct($filters) {
		$this->_filters = $filters;
	}

	public function setXMLParser($XMLParser) {
		$this->_XMLParser = $XMLParser;
	}

	public function init() {
		return ! (empty ( $this->_filters ) || empty ( $this->_XMLParser ));
	}
}
?>