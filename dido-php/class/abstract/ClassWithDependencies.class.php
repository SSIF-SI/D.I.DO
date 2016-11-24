<?php 
abstract class ClassWithDependencies{
	public function setDependency($key,$value){
		$this->__set($key,$value);
	}
	
	public function __set($key, $value){
		if(property_exists($this, $key))
			$this->$key = $value;
	}
}
?>