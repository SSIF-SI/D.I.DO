<?php
require_once("../config.php");

if(!Utils::checkAjax()) {
	header("location: ".HTTP_ROOT);
	die();
}
$PDFSigner = new PDFSigner();
Utils::printr($_FILES);
Utils::printr($_POST);
if(count($_FILES)){
	foreach($_FILES as $inputKey => $file){
		if($file['error'])
			die(json_encode(array("error" => "Errore nel caricamento del file {$file['name']}")));
		if($inputKey=='pdfDaFirmare'){
			$PDFSigner->loadPDF($file['tmp_name']);
			$pdfname="/tmp/".$file['name']."signed.pdf";
		}
		if($inputKey=='keystore'){
			$PDFSigner->setKeystore($file['tmp_name']);
		}
	}
	if(isset($_POST['pwd'])){
		$PDFSigner->setPassword($_POST['pwd']);
	}	
} else die(json_encode(array("error" => "Nessun file.")));

$PDFSigner->signPDF($PDFSigner->getPdf(),$PDFSigner->getPdf().'signed',$PDFSigner->getKeystore(),$_POST['pwd']);
header("Content-type: text/csv");
header("Cache-Control: no-store, no-cache");
header("Content-Disposition: attachment; filename=".$pdfname);
$file = fopen('php://output','w');

die(json_encode(array("error"=>"")));
?>