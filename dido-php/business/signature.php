<?php
require_once ("../config.php");

if (Utils::checkAjax ()) {
	if (count( $_POST ) != 0) {
		echo $_POST ["persona"];
		echo $_POST ["pkey"];
		die ( print_r($_POST,1) );
	} else {
		$id = isset ( $_GET ['id'] ) ? $_GET ['id'] : null;
		$list=isset ( $_GET ['list'] ) ? $_GET ['list'] : 'all';
		switch($list){
			case 'all':
				die ( SignatureHelper::createModalSigner ( $id ) );
				break;
			case 'fixed':
				die ( SignatureHelper::createModalFixedSigner ( $id ) );
				break;
			case 'variable':
				die ( SignatureHelper::createModalVariableSigner ( $id ) );
				break;
		}
	}
}

$signers = SignatureHelper::getSigners ();

$pageScripts = array (
		"signature.js" 
);
include_once (TEMPLATES_PATH . "template.php");