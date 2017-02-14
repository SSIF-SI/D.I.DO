<?php
require_once ("../config.php");

if (! Utils::checkAjax ()) {
	header ( "location: " . HTTP_ROOT );
	die ();
}
$PDFSigner = new PDFSigner ();

if (count ( $_FILES )) {
	foreach ( $_FILES as $inputKey => $file ) {
		if ($file ['error'])
			die ( json_encode ( array (
					"error" => "Errore nel caricamento del file {$file['name']}" 
			) ) );
		if ($inputKey == 'pdfDaFirmare') {
			$tmp_name = $file ["tmp_name"];
			$PDFSigner->loadPDF ( $tmp_name );
			$pdfname = str_replace ( ".pdf", "_signed.pdf", FILES_PATH . $file ["name"] );
			$pdfbasename = str_replace ( ".pdf", "_signed.pdf", basename ( $file ["name"] ) );
		}
		if ($inputKey == 'keystore') {
			$tmp_key = $file ["tmp_name"];
			$PDFSigner->setKeystore ( $tmp_key );
		}
	}
	if (isset ( $_POST ['pwd'] )) {
		$PDFSigner->setPassword ( $_POST ['pwd'] );
	}
} else
	die ( json_encode ( array ("error" => "Nessun file." ) ) );

$PDFSigner->signPDF ( $PDFSigner->getPdf (), $pdfname, $tmp_key, $_POST ['pwd'] );
$fsize = filesize ( $pdfname );
header ( 'Content-Length: ' . filesize ( $pdfname ) );
header ( "Content-type: application/pdf" ); // add here more headers for diff. extensions
header ( "Content-Disposition: attachment; filename=\"" . $pdfbasename . "\"" ); // use 'attachment' to force a download
header ( "Content-length: $fsize" );
header ( "Cache-control: private" ); // use this to open files directly
header ("Expires: 0");

readfile ( $pdfname );
// ob_clean ();
// flush ();
unlink ( $pdfname );
die ( json_encode ( array ("error" => "" ) ) );
?>