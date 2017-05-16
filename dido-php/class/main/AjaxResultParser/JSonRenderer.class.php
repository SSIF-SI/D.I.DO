<?php 
class JSonRenderer implements IORenderer{
	public function encode($object){
		die ( json_encode($object) );	
	}
	
	public function decode($object){
		die ( json_decode($object) );
	}
	
}?>