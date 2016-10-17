<?php
require_once ("../config.php");

$signers = SignatureHelper::getSigners();
$newsigner= SignatureHelper::createModalSigner();

if(Utils::checkAjax()){
	
	
	$idp = isset($_GET['id']) ? $_GET['id'] : null;
	
	
	
	die(SignatureHelper::createModalSigner($idp));
	
	
}

$pageScripts = array("signature.js");
include_once (TEMPLATES_PATH."template.php");