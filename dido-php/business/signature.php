<?php
require_once ("../config.php");

if (Utils::checkAjax ()) {
	if (count( $_POST ) != 0) {
		echo $_POST ["persona"];
		echo $_POST ["pkey"];
		die ( print_r($_POST,1) );
	} else {
		$idp = isset ( $_GET ['id'] ) ? $_GET ['id'] : null;
		$list=isset ( $_GET ['list'] ) ? $_GET ['list'] : 'all';
		if($list=='all'){
			die ( SignatureHelper::createModalSigner ( $idp ) );
		}elseif ($list=='fixed'){
			die ( SignatureHelper::createModalFixedSigner ( $idp ) );
	}
		
	}
}

$signers = SignatureHelper::getSigners ();

$pageScripts = array (
		"signature.js" 
);
include_once (TEMPLATES_PATH . "template.php");