<?php
class OwnerRules implements IOwnerRules {
	private $_XMLpath;
	private $_OwnerRules;
	const DEFAULT_FILENAME = "ownerRules.xml";
	
	public function __construct($path = null) {
		$this->setXMLPath ( $path );
	}
	
	function getInputField() {
		if (! empty ( $this->_OwnerRules ))
			return ( array ) $this->_OwnerRules->inputField;
		else
			return array ();
	}
	
	function getXMLPath() {
		return $this->_XMLpath;
	}
	
	function setXMLPath($path) {
		if (! empty ( $path ))
			$this->_XMLpath = $path;
		else
			$this->_XMLpath = FILES_PATH . self::DEFAULT_FILENAME;
		$this->load ();
	}
	
	private function load() {
		$this->_OwnerRules = simplexml_load_file ( $this->_XMLpath );
	}
}
?>