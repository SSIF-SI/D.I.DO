<?php
class ImportManager{
	private $_externalDataSourceRepository = [];
	
	public function __construct(){
		$this->_ImportManager->registerImporter('geco', new GecoDataSource);
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
	
	public function getDataToBeImported(){
		$toBeImported = [];
		foreach($this->_externalDataSourceRepository as $label => $externalDataSource){
			$dataToBeImported = array_merge($dataToBeImported, $externalDataSource->getDataToBeImported());
		}
		return $toBeImported;
	}
}
?>