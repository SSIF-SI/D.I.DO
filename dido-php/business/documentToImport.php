<?php 
require_once("../config.php");

$Application_Import = $Application->getApplicationPart(Application::IMPORT);

if(!isset($_GET['from'])){
	Common::redirectTo();
}
	
$from = $_GET['from'];

$list = $Application_Import
			->getSavedDataToBeImported($from, IExternalDataSource::FILE_EXTENSION_TO_BE_IMPORTED);

include_once (TEMPLATES_PATH."template.php");
?>
