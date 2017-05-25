<?php
require_once ("../config.php");
IF (! Utils::checkAjax ()) {
	Common::redirect();
}
$ARP = new AjaxResultParser();
$er = new ErrorHandler(false);

if (count ( $_FILES )) {
	foreach ( $_FILES as $inputKey => $file ) {
		if ($file ['error']){
			$er->setErrors("Errore nel caricamento del file {$file['name']}");
			break;
		}
		if(!is_uploaded_file($file['tmp_name'])){
			$er->setErrors($file['tmp_name']. " non è stato caricato da voi");
			break;
		}
		$destination = FILES_PATH.basename($file['tmp_name']);
		
		if(!move_uploaded_file($file['tmp_name'], $destination)){
			$er->setErrors("Impossibile caricate ".$file['tmp_name']);
			break;
		}
		
		$er->setOtherData('upFilePath', basename($destination));
	}
} else{
	$er->setErrors("Nessun file");
}
$ARP->encode($er->getErrors(true));
?>