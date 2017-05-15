<?php
require_once ("config.php");

$tbi = $Application->getApplicationPart(Application::IMPORT)->getSavedDataToBeImported();

$data = [
	'Proposte da Geco'	=> [
		'color'			=> 'red',
		'icon-class'	=> 'fa-sign-in fa-rotate-90',
		Common::N_TOT	=> $tbi[Common::N_TOT],
		'href'			=> 'documentToImport/?from=geco'
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