<?php
require_once ("../config.php");

if (Utils::checkAjax ()) {
	$classname = $_GET ['list'];
	if ($classname != 'ApplySign')
		$userRolesObj = new $classname ( DBConnector::getInstance () );
	
	$delete = isset ( $_GET ['delete'] ) ? true : false;
	if ($delete) {
		unset ( $_GET ['list'], $_GET ['delete'] );
		die ( json_encode ( $userRolesObj->delete ( $_GET ) ) );
	}
	
	if (count ( $_POST ) != 0) {
		
		die ( json_encode ( $userRolesObj->save ( $_POST ) ) );
	} else {
		$list = isset ( $_GET ['list'] ) ? $_GET ['list'] : null;
		unset ( $_GET ['list'] );
		$id = reset ( $_GET );
		if (! $id)
			$id = null;
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
			case 'ApplySign' :
				die ( SignatureHelper::createModalApplySign () );
				break;
			case 'SpecialSigners' :
					die ( SignatureHelper::createModalSpecialSigner ( $id ) );
					break;
		}
	}
}

define (PAGE_TITLE, "Gestione Firme");

$signers = SignatureHelper::getSigners ();

$pageScripts = array (
		"MyModal.js",
		"signatureModal.js" 
);
include_once (TEMPLATES_PATH . "template.php");