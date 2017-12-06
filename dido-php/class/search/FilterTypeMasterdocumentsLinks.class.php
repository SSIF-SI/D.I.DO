<?php
class FilterTypeMasterdocumentsLinks implements iFilterTypeSource{
	
	static function getTypes($ADB, $className, $types){
		return FilterTypeMasterdocument::getTypes($ADB, $className, $types);
	}		
}