<?php 
require_once("../config.php");

if(!isset($_GET['from'])){
	Common::redirectTo();
}
	
$from = $_GET['from'];

$list = $Application
			->getApplicationPart(Application::IMPORT)
			->getSavedDataToBeImported($from, IExternalDataSource::FILE_EXTENSION_TO_BE_IMPORTED);

unset($tbi[Common::N_TOT]);

include_once (TEMPLATES_PATH."template.php");
?>