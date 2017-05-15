<?php 
class Sezione{
	private $_label;
	private $_numBadge;
	private $_contents;
	
	public function __construct($label = null){
		$this->setLabel($label);
	}
	
	public function getLabel(){
		return $this->_label;
	}
	
	public function setLabel($label){
		$this->_label = $label;
	}
	
	public function getNumBadge(){
		return $this->_numBadge;
	}
	
	public function setNumBadge($numBadge){
		$this->_numBadge = $numBadge;
	}
	
	public function getContents($contents){
		return $this->_contents;
	}
	
	public function setContents($contents){
		$this->_contents = $contents;
	}
}
?>