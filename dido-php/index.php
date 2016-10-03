<?php 

require_once ("config.php");

/*
$signature = new Java('dido.signature.SignatureManager');
if($signature->loadPDF("/var/lib/tomcat7/webapps/dido-php-test/richiesta_delega_2016_DMT_signed.pdf")){
	$list = json_decode($signature->getSignatures());
	echo"<pre>".print_r($list,1)."</pre>";
}
*/
/*
$ftp = FTPConnector::getInstance();
$contents = $ftp->getContents("/CAMPUS");
Utils::printr(Utils::filterList($contents['contents'],'isPDF',1));
*/

FlowChecker::getInstance()->checkMasterDocument(array('id_md' => 1));

//$md = new Masterdocument(Connector::getInstance());
