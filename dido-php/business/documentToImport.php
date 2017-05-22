<?php 
require_once("../config.php");

$Application_Import = $Application->getApplicationPart(Application::IMPORT);

if(!isset($_GET['from'])){
	Common::redirect();
}
	
$from = $_GET['from'];

if(Utils::checkAjax ()){
	
	
	// Ho appena postato un import singolo
	$ARP = new AjaxResultParser();
	$ARP->encode(
		$Application_Import
			->import( $from, $_POST)
			->getErrors(true)
	);

}

$list = $Application_Import
			->getSavedDataToBeImported($from, IExternalDataSource::FILE_EXTENSION_TO_BE_IMPORTED);

$pageScripts = array("MyModal.js","locationHash.js");
include_once (TEMPLATES_PATH."template.php");
?>
