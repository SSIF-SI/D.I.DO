<?php
require_once("../config.php");

if(!Utils::checkAjax()) {
	header("location: ".HTTP_ROOT);
	die();
}
$PDFSigner = new PDFSigner();
Utils::printr($_POST);
die(Utils::printr($_FILES));
if(count($_FILES)){
	foreach($_FILES as $inputKey => $file){
		if($file['error'])
			die(json_encode(array("error" => "Errore nel caricamento del file {$file['name']}")));
		if(isset($_POST['pdf'])){
			$PDFSigner->loadPDF($file['tmp_name']);
			Utils::printr($file);
				
		}
		if(isset($_POST['keystore'])){
			$PDFSigner->setKeystore($file['tmp_name']);
			Utils::printr($file);
				
		}
	}
	if(isset($_POST['pwd'])){
		$PDFSigner->setPassword($_POST['pwd']);
		
	}
} else die(json_encode(array("error" => "Nessun file.")));
?>