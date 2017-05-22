<?php
require_once ("config.php");

$data = [];

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

$myDocs = $Application
	->getApplicationPart(Application::DOCUMENTBROWSER)
	->getAllMyPendingsDocument();

$openDocuments = count($myDocs[Application_DocumentBrowser::LABEL_MD]);

$toSign = 0;
foreach($myDocs[Application_DocumentBrowser::LABEL_MD] as $md){
	$toSign += $md[Application_DocumentBrowser::DOC_TO_SIGN_INSIDE];
}

$data['Procedimenti in sospeso'] = [
	'color'			=> 'yellow',
	'icon-class'	=> 'fa-file-text',
	Common::N_TOT	=> $openDocuments,
	'href'			=> BUSINESS_HTTP_PATH.'documentOpen.php'
];

$data['Documenti da firmare'] = [
		'color'			=> 'green',
		'icon-class'	=> 'fa-edit',
		Common::N_TOT	=> $toSign,
		'href'			=> BUSINESS_HTTP_PATH.'documentToSign.php'
];


include_once (TEMPLATES_PATH."template.php");

?>