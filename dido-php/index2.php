<?php
require_once ("config.php");

$tbi = $Application
	->getApplicationPart(Application::IMPORT)
	->getSavedDataToBeImported(GecoDataSource::DATA_SOURCE_LABEL, IExternalDataSource::FILE_EXTENSION_TO_BE_IMPORTED);

$data = [
	'Proposte da Geco'	=> [
		'color'			=> 'red',
		'icon-class'	=> 'fa-sign-in fa-rotate-90',
		Common::N_TOT	=> $tbi[Common::N_TOT],
		'href'			=> BUSINESS_HTTP_PATH.'documentToImport.php?from=geco'
	]	
];

/*
$fakepost=[
		ImportManager::LABEL_IMPORT_FILENAME=>'geco-import/acquisti/acquisto_fuori mepa_124.tobeimported',
		ImportManager::LABEL_MD_NOME=>'acquisto',
		ImportManager::LABEL_MD_TYPE=>'fuori mepa'
		];
var_dump($a->import($fakepost)->getErrors());
*/

include_once (TEMPLATES_PATH."template.php");
?>