<?php 
class AjaxResultParser implements IORenderer{
	private $_outputRenderer;
	
	public function __construct($outputRenderer = null){
		$this->_outputRenderer = is_null($outputRenderer) ? new JSonRenderer() : $outputRenderer;
	}
	
	public function encode($object){
		return $this->_outputRenderer->encode($object);
	}

	public function decode($object){
		return $this->_outputRenderer->decode($object);
	}
}
?>