<?php 

require_once ("config.php");
require_once 'Personale.class.php';


$signature = new Java('dido.signature.SignatureManager');
if($signature->loadPDF("/var/lib/tomcat7/webapps/dido-php-test/richiesta_delega_2016_DMT_signed.pdf")){
	$list = json_decode($signature->getSignatures());
	echo"<pre>".print_r($list,1)."</pre>";
	$content = json_decode($signature->getXmlMetadata());
	
	$xmp_data_start = strpos($content, '<x:xmpmeta');
	$xmp_data_end   = strpos($content, '</x:xmpmeta>');
	$xmp_length     = $xmp_data_end - $xmp_data_start;
	$xmp_data       = substr($content, $xmp_data_start, $xmp_length + 12);
	$xmp_data = str_replace(":","_",$xmp_data);
	$xmp            = simplexml_load_string($xmp_data);	
	
	echo"<pre>PDF di tipo: ".$xmp->rdf_RDF->rdf_Description['pdfaid_conformance']."</pre>";

}

/*
$ftp = FTPConnector::getInstance();
$contents = $ftp->getContents("/CAMPUS");
Utils::printr(Utils::filterList($contents['contents'],'isPDF',1));
*/
$p=Personale::getInstance();
// print_r(Personale::getInstance()->getPersonakey(),1);
//$md = new Masterdocument(Connector::getInstance());
