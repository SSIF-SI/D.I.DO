<?php 
$id_md = $_GET['md'];

$Application_Detail = $Application->getApplicationPart("Detail");

$md = $Application_DocumentBrowser->get($id_md);

if(Utils::checkAjax ()){
	// Ho effettuato una richiesta ajax
	$ARP = new AjaxResultParser();
	
	if(!$md){
		$eh = new ErrorHandler("Master document inesistente");
		$ARP->encode($eh->getErrors(true));
	}
	
	if(count($_POST)){
		// Ho appena postato qualcosa, quindi devo agire di conseguenza
		$eh = new ErrorHandler(false);
		$ARP->encode($eh->getErrors(true));
	}
	
	// Se sono qui ho richieste in GET
	extract($md);
	
	$XMLParser = new XMLParser($md[Masterdocument::XML], $md[Masterdocument::TYPE]);
	$docInfo = $Application_Detail->createDocumentInfoPanel($XMLParser->getDocumentInputs($documents[$_GET['d']][Document::NOME]), $documents_data[$_GET['d']], false);
	echo("<form method='POST'>$docInfo</form>");
	Utils::includeScript(SCRIPTS_PATH, "datepicker.js");
	die();
}

if(!$md || empty($md[Application_DocumentBrowser::LABEL_MD]))
	Common::redirect();

if(isset($_GET['download'])){
	extract($md);
	
	$filename =
		$md[Masterdocument::FTP_FOLDER] .
		Common::getFolderNameFromMasterdocument($md) .
		DIRECTORY_SEPARATOR .
		Common::getFilenameFromDocument($documents[$_GET['d']]);

	$Application->getFTPDataSource()->download($filename);
}

$Application_Detail->createDetail($md);
extract($md);

$view = basename(__FILE__);
?>