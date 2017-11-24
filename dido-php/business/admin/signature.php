<?php
require_once ("../../config.php");

if (Utils::checkAjax ()) {
	$ARP=new AjaxResultParser();
	$classname = $_GET ['list'];
	if ($classname != 'ApplySign')
		$classObj = new $classname ( DBConnector::getInstance () );
	
	$delete = isset ( $_GET ['delete'] ) ? true : false;
	if ($delete) {
		unset ( $_GET ['list'], $_GET ['delete'] );
		
		$ARP->encode($classObj->delete ( $_GET ) ->getErrors(true)) ;
	}
	
	if (count ( $_POST ) != 0) {
		$ARP->encode($classObj->save ( $_POST )->getErrors(true));
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
			case 'SpecialSignatures' :
				die ( SignatureHelper::createModalSpecialSigner ( $id ) );
				break;
		}
	}
}

$signers = SignatureHelper::getSigners ();

$pageScripts = array (
		"MyModal.js",
		"signatureModal.js",
		"locationHash.js"
);

$view = ADMIN_VIEWS_PATH.basename($_SERVER['PHP_SELF']);

include_once (TEMPLATES_PATH . "template.php");