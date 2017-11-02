<?php
require_once ("config.php");

$data = [];

/*
//Se sei gestore Potresti dover importare documenti da fonti esterne
if($Application->getUserManager()->isGestore()){
	$tbi = $Application
		->getApplicationPart(Application::IMPORT)
		->getSavedDataToBeImported(GecoDataSource::DATA_SOURCE_LABEL, IExternalDataSource::FILE_EXTENSION_TO_BE_IMPORTED);
	
	$count_tbi = 0;
	
	if(count($tbi)) foreach($tbi as $cat=>$subCat){
		$count_tbi += ArrayHelper::countItems($tbi, $cat);
	}
	
	$data['Proposte da Geco'] = [
		'color'			=> 'red',
		'icon-class'	=> 'fa-sign-in fa-rotate-90',
		Common::N_TOT	=> $count_tbi,
		'href'			=> BUSINESS_HTTP_PATH.'documentToImport.php?from=geco'
	];
}
*/

$myDocs = $Application
	->getApplicationPart(Application::DOCUMENTBROWSER)
	->getAllMyPendingDocuments();

$openDocuments = count($myDocs[Application_DocumentBrowser::LABEL_MD]);

$data['Procedimenti in sospeso'] = [
		'color'			=> 'green',
		'icon-class'	=> 'fa-unlock',
		Common::N_TOT	=> $openDocuments,
		'href'			=> BUSINESS_HTTP_PATH.'document.php?status=open'
];


$closedDocuments = $Application
	->getApplicationPart(Application::DOCUMENTBROWSER)
	->getAllMyClosedDocuments();

$closed = Utils::filterList($closedDocuments[Application_DocumentBrowser::LABEL_MD], Masterdocument::CLOSED, ProcedureManager::CLOSED);

$data['Procedimenti chiusi'] = [
		'color'			=> 'red',
		'icon-class'	=> 'fa-lock',
		Common::N_TOT	=> count($closed),
		'href'			=> BUSINESS_HTTP_PATH.'document.php?status=closed'
];

$incomplete = Utils::filterList($closedDocuments[Application_DocumentBrowser::LABEL_MD], Masterdocument::CLOSED, ProcedureManager::INCOMPLETE);

$data['Procedimenti incompleti'] = [
		'color'			=> 'yellow',
		'icon-class'	=> 'fa-warning',
		Common::N_TOT	=> count($incomplete),
		'href'			=> BUSINESS_HTTP_PATH.'document.php?status=incomplete'
];


// Se sei firmatario potresti avere documenti da firmare
if($Application->getUserManager()->isSigner()){
	$toSign = 0;
	foreach($myDocs[Application_DocumentBrowser::LABEL_MD] as $md){
		$toSign += $md[Application_DocumentBrowser::DOC_TO_SIGN_INSIDE];
	}


	$data['Documenti da firmare'] = [
			'color'			=> 'primary',
			'icon-class'	=> 'fa-edit',
			Common::N_TOT	=> $toSign,
			'href'			=> BUSINESS_HTTP_PATH.'documentToSign.php'
	];
}

include_once (TEMPLATES_PATH."template.php");

?>