<?php
require_once("../config.php");

if(!Utils::checkAjax()) {
	header("location: ".HTTP_ROOT);
	die();
}
if(count($_FILES)){
	foreach($_FILES as $inputKey => $file){
		Utils::printr($file);
		if($file['error'])
			die(json_encode(array("error" => "Errore nel caricamento del file {$file['name']}")));
		$PDFSigner = new PDFSigner();
		$PDFSigner->loadPDF($file['tmp_name']);
// 		if(isset($_POST['getOnlySignatures'])){
// 			$signatures = $PDFParser->getSignatures();
// 			if(count($signatures) == 0)
// 				die(json_encode(array("error" => "Nel file {$file['name']} non sono presenti firme digitali")));
// 			else
// 				die(json_encode(array("signatures" => $signatures)));
// 		}

	}
} else die(json_encode(array("error" => "Nessun file.")));
?>