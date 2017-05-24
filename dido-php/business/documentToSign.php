<?php 
require_once ("../config.php");

if(isset($_GET[Masterdocument::ID_MD]) && isset($_GET[Document::ID_DOC])){
	$Application_DocumentBrowser = $Application->getApplicationPart(Application::DOCUMENTBROWSER);
	include("documentDetailToSign.php");
} else {
	$list = $Application
	->getApplicationPart(Application::DOCUMENTBROWSER)
	->getAllMyPendingDocumentsToSign();
	
	$list[Application_DocumentBrowser::LABEL_MD] = Common::categorize($list[Application_DocumentBrowser::LABEL_MD]);
		
	$XMLDataSource = $Application->getXMLDataSource();
}
$pageScripts = array("MyModal.js","locationHash.js");
include_once (TEMPLATES_PATH."template.php");
?>