<?php
require_once ("../../config.php");

if(!$Application->getUserManager()->isAdmin())
	Common::redirect();

$PermissionManager = new PermissionManager($Application->getDBConnector());

if(Utils::checkAjax()){
	$result = $PermissionManager->processAjax();
	if($result === null)
		$PermissionManager->createModal();
	else{
		$ARP = new AjaxResultParser();
		$ARP->encode($result->getErrors(true));
	}
}

$list = $PermissionManager->getUsersRoles();

$metadata = HTMLHelper::createMetadata ( $list, ADMIN_BUSINESS_PATH . basename ( $_SERVER ['PHP_SELF'] ), [
		UsersRoles::ID_PERSONA,
		UsersRoles::ID_RUOLO
], [UsersRoles::ID_PERSONA => function($id){return Personale::getInstance()->getNominativo($id);}] );

$userRolesTable = HTMLHelper::editTable ( $list, $metadata ['buttons'], $metadata ['substitutes'], null, [UsersRoles::ID_RUOLO]);

$pageScripts = ["MyModal.js", "userRolesModal.js"];
include_once (TEMPLATES_PATH . "template.php");

?>