<?php
require_once ("config.php");

$a = new Application();
$tbi = $a->getSavedDataToBeImported(GecoDataSource::DATA_SOURCE_LABEL, IExternalDataSource::FILE_EXTENSION_TO_BE_IMPORTED);

$fakepost=[
		ImportManager::LABEL_IMPORT_FILENAME=>'geco-import/acquisti/acquisto_fuori mepa_124.tobeimported',
		ImportManager::LABEL_MD_NOME=>'acquisto',
		ImportManager::LABEL_MD_TYPE=>'fuori mepa'
		];
var_dump($a->import($fakepost)->getErrors());



?>
??