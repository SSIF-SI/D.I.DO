<?php 
require_once("../config.php");
$Application_DocumentBrowser = $Application->getApplicationPart(Application::DOCUMENTBROWSER);

$addData = [
	'open'			=> [
		'title'		=> 'Procedimenti in sospeso',
		'method'	=> 'getAllMyPendingDocuments'			
	],
	'closed'		=> [
		'title'		=> 'Procedimenti chiusi',
		'method'	=> 'getAllMyClosedDocuments',
		'filter'	=> ['field' => Masterdocument::CLOSED, 'value' => ProcedureManager::CLOSED]
	],
	'incomplete'	=> [
		'title'		=> 'Procedimenti incompleti',
		'method'	=> 'getAllMyClosedDocuments',
		'filter'	=> ['field' => Masterdocument::CLOSED, 'value' => ProcedureManager::INCOMPLETE]
	]
];

define (PAGE_TITLE, $addData[$_GET['status']]['title']);

if(isset($_GET['action'])){
	$Application->manageAction($_GET['action']);
	die();
}

if(isset($_GET[Masterdocument::ID_MD])){
	include("documentDetail.php");
} else {
	$method = $addData[$_GET['status']]['method'];
	$list = $Application_DocumentBrowser->$method();
	if(isset($addData[$_GET['status']]['filter'])){
		
		$filter = $addData[$_GET['status']]['filter'];
		$md = Utils::filterList($list[Application_DocumentBrowser::LABEL_MD], $filter['field'], $filter['value']);
		$mdToRemove = array_diff(array_keys($list[Application_DocumentBrowser::LABEL_MD]), array_keys($md));
		$list = Application_DocumentBrowser::purge($mdToRemove, $list);
	}

	$list[Application_DocumentBrowser::LABEL_MD] = Common::categorize($list[Application_DocumentBrowser::LABEL_MD]);
	
	$XMLDataSource = $Application->getXMLDataSource();
}

$pageScripts = array("MyModal.js","locationHash.js");
include_once (TEMPLATES_PATH."template.php");
?>
