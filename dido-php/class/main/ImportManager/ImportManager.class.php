<?php
class ImportManager{
	private $_externalDataSourceRepository = [];
	
	public function __construct(){
		$this->register('geco', new GecoDataSource());
	}
	
	public function register($label, IExternalDataSource $externalDataSource){
		if(!is_object($externalDataSource)) 
			throw new Exception("Can't register a non object");
		
		$this->_externalDataSourceRepository[$label] = $externalDataSource;
	}
	
	public function saveDataToBeImported(){
		foreach($this->_externalDataSourceRepository as $label => $externalDataSource){
			$dataToBeImported = array_merge($dataToBeImported, $externalDataSource->saveDataToBeImported());
		}
	}
	
	public function getSavedDataToBeImported(){
		$toBeImported = [];
		foreach($this->_externalDataSourceRepository as $label => $externalDataSource){
			$dataToBeImported = array_merge($dataToBeImported, $externalDataSource->getSavedDataToBeImported());
		}
		return $toBeImported;
	}
	
	public function import($data){
		
	}
}
?>