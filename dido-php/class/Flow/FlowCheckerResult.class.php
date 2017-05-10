<?php

class FlowCheckerResult {

	public $documentName = null;

	public $inputs = null;

	public $defaultInputs = null;

	public $found = null;

	public $signatures = array ();

	public $limit = null;

	public $mandatory = null;

	public $errors = array ();

	function __set($property, $value) {
		if (property_exists ( $this, $property )) {
			$this->$property = $value;
			return true;
		}
		return false;
	}
}
?>