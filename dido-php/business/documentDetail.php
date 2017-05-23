<?php 
$id_md = $_GET['md'];

$Application_Detail = $Application->getApplicationPart("Detail");

$md = $Application_DocumentBrowser->get($id_md);

if(!$md)
	Common::redirect();

//Utils::printr($md);

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