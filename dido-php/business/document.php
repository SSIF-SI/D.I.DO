<?php 
require_once("../config.php");
$Application_DocumentBrowser = $Application->getApplicationPart(Application::DOCUMENTBROWSER);

define (PAGE_TITLE, "Procedimenti in sospeso");

if(isset($_GET['action'])){
	$Application->manageAction($_GET['action']);
	die();
}

if(isset($_GET[Masterdocument::ID_MD])){
	include("documentDetail.php");
} else {
	$list = $Application_DocumentBrowser->getAllMyPendingDocuments();
	
	$list[Application_DocumentBrowser::LABEL_MD] = Common::categorize($list[Application_DocumentBrowser::LABEL_MD]);
	
	$XMLDataSource = $Application->getXMLDataSource();

}

$pageScripts = array("MyModal.js","locationHash.js");
include_once (TEMPLATES_PATH."template.php");
?>
