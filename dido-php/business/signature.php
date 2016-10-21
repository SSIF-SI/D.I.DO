<?php
require_once ("../config.php");

if (Utils::checkAjax ()) {
	if (count( $_POST ) != 0) {
		$classname= $_GET['list'];
		$dbconnector=new $classname(Connector::getInstance());
		die(json_encode($dbconnector->save($_POST)));
	} else {
		$id = isset ( $_GET ['id'] ) ? $_GET ['id'] : null;
		$list=isset ( $_GET ['list'] ) ? $_GET ['list'] : null;
		switch($list){
			case 'Signers':
				die ( SignatureHelper::createModalSigner ( $id ) );
				break;
			case 'FixedSigners':
				die ( SignatureHelper::createModalFixedSigner ( $id ) );
				break;
			case 'VariableSigners':
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