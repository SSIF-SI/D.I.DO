<?php 
$id_md = $_GET[Masterdocument::ID_MD];
$Application_Detail = $Application->getApplicationPart("Detail");
$md = $Application_DocumentBrowser->get($id_md);

if(isset($_GET['download'])){
	
	if(!$md || empty($md[Application_DocumentBrowser::LABEL_MD]))
		Common::redirect();
	
	extract($md);

	$filename =
		$md[Masterdocument::FTP_FOLDER] .
		Common::getFolderNameFromMasterdocument($md) .
		DIRECTORY_SEPARATOR .
		Common::getFilenameFromDocument($documents[$_GET[Document::ID_DOC]]);

	$Application->getFTPDataSource()->download($filename);
}

if(Utils::checkAjax ()){
	
	if(isset($_GET['upload'])){
		$Application_Detail->upload($md);
	}
	
	// Ho effettuato una richiesta ajax
	$ARP = new AjaxResultParser();
	
	if(!$md){
		$eh = new ErrorHandler("Master document inesistente.");
		$ARP->encode($eh->getErrors(true));
	}
	
	extract($md);
	$XMLParser = new XMLParser($md[Masterdocument::XML], $md[Masterdocument::TYPE]);
	$docInputs = $XMLParser->getDocumentInputs($documents[$_GET[Document::ID_DOC]][Document::NOME]);
	
	if(count($_POST)){
		
		$ARP->encode(
			$Application_Detail->updateDocumentData(
				$_GET[Document::ID_DOC], 
				$docInputs,	
				$_POST)
			->getErrors(true));
	}
	
	// Se sono qui ho richieste in GET
	$Application_Detail->editInfo($docInputs, $documents_data[$_GET[Document::ID_DOC]]);
}

if(!$md || empty($md[Application_DocumentBrowser::LABEL_MD]))
	Common::redirect();

$Application_Detail->createDetail($md);
extract($md);

$view = basename(__FILE__);
?>