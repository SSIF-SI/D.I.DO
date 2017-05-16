<?php
require_once ("config.php");

if($Application->getUserManager()->isGestore()){
	$tbi = $Application
		->getApplicationPart(Application::IMPORT)
		->getSavedDataToBeImported(GecoDataSource::DATA_SOURCE_LABEL, IExternalDataSource::FILE_EXTENSION_TO_BE_IMPORTED);
	
	$count_tbi = 0;
	
	if(count($tbi)) foreach($tbi as $cat=>$subCat){
		$count_tbi += Common::countMultipleMultiArrayItems($subCat, array_keys($subCat));
	}
	
	$data = [
		'Proposte da Geco'	=> [
			'color'			=> 'red',
			'icon-class'	=> 'fa-sign-in fa-rotate-90',
			Common::N_TOT	=> $count_tbi,
			'href'			=> BUSINESS_HTTP_PATH.'documentToImport.php?from=geco'
		]	
	];
}

include_once (TEMPLATES_PATH."template.php");
?>