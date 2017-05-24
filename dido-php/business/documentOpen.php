<?php 
require_once("../config.php");

$Application_DocumentBrowser = $Application->getApplicationPart(Application::DOCUMENTBROWSER);

if(isset($_GET['md'])){
	include("documentDetail.php");
} else {

	$list = $Application_DocumentBrowser->getAllMyPendingsDocument();
	
	$list[Application_DocumentBrowser::LABEL_MD] = Common::categorize($list[Application_DocumentBrowser::LABEL_MD]);
	
	$XMLDataSource = $Application->getXMLDataSource();

}
$pageScripts = array("MyModal.js","locationHash.js");
include_once (TEMPLATES_PATH."template.php");
?>
