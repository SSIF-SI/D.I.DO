<?php

abstract class AFTPConfigurationSource implements IFTPConfiguration {

	private $_host;

	private $_username;

	private $_password;

	private $_baseDir;

	private $_isActive = 0;

	public function __construct() {
		$this->loadConfiguration ();
	}

	abstract function loadConfiguration();

	public function getHost() {
		return $this->_host;
	}

	protected function setHost($host) {
		$this->_host = $host;
		return $this;
	}

	public function getUsername() {
		return $this->_username;
	}

	protected function setUsername($userName) {
		$this->_username = $userName;
		return $this;
	}

	public function getPassword() {
		return $this->_password;
	}

	protected function setPassword($password) {
		$this->_password = $password;
		return $this;
	}

	public function getBasedir() {
		return $this->_baseDir;
	}

	protected function setBasedir($baseDir) {
		$this->_baseDir = $baseDir;
		return $this;
	}

	public function isActive() {
		return $this->_isActive;
	}

	protected function setActive($isAcrive) {
		$this->_isActive = $isActive;
	}
}
?>
