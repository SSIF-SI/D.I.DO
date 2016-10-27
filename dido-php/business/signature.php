<?php
require_once ("../config.php");

if (Utils::checkAjax ()) {
	
	
	
	$classname = $_GET ['list'];
	$dbconnector = new $classname ( Connector::getInstance () );
	
	$delete = isset ( $_GET ['delete'] ) ? true : false;
	if (isset($_GET['confirm'])){
		die ( SignatureHelper::createModalConfirm("Vuoi eliminare l'elemento dalla tabella?"));
	}
	if ($delete){
		unset($_GET['list'],$_GET['delete']);
		die ( json_encode ( $dbconnector->delete ( $_GET ) ) );
	}
	
	if (count ( $_POST ) != 0) {
		
		die ( json_encode ( $dbconnector->save ( $_POST ) ) );
	
	} else {
		$list = isset ( $_GET ['list'] ) ? $_GET ['list'] : null;
		unset($_GET['list']);
		$id = reset($_GET);
		if(!$id)
			$id=null;
		switch ($list) {
			case 'Signers' :
				die ( SignatureHelper::createModalSigner ( $id ) );
				break;
			case 'FixedSigners' :
				die ( SignatureHelper::createModalFixedSigner ( $id ) );
				break;
			case 'VariableSigners' :
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