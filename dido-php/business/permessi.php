<?php 
require_once ("../config.php");

$userRolesObj = new UsersRoles( Connector::getInstance () );

if (Utils::checkAjax ()) {

	$delete = isset ( $_GET ['delete'] ) ? true : false;
	
	if ($delete){
		unset($_GET['delete']);
		die ( json_encode ( $userRolesObj->delete ( $_GET ) ) );
	}
	
	if (count ( $_POST ) != 0) {
		die ( json_encode ( $userRolesObj->save ( $_POST ) ) );
	} else {
		if(count($_GET) > 0)
			$user_role = $userRolesObj->get($_GET);
		else {
			$user_role = $userRolesObj->getStub();
		}
		
		$listPersone = ListHelper::listPersone();
		
		$alreadyset = array_keys($userRolesObj->getAll("id_persona",'id_persona'));
		
		foreach( $alreadyset as $id_persona){
			if(array_key_exists($id_persona, $listPersone) && $id_persona!=$user_role['id_persona'])
				unset($listPersone[$id_persona]);
		}
		die ( createModal ($user_role, $listPersone) );
	}
}

$list = $userRolesObj->getAll("id_persona","ruolo");
$metadata = HTMLHelper::createMetadata($list, basename($_SERVER['PHP_SELF']), array("id_persona"), array('id_persona'=> 'PersonaleHelper::getNominativo'));
$userRolesTable = HTMLHelper::editTable($list,$metadata['buttons'], $metadata['substitutes']);

$pageScripts = array (
		"MyModal.js","userRolesModal.js"
);

include_once (TEMPLATES_PATH . "template.php");


function createModal($user_role, $listPersone){
	$rolesObj = new Roles(Connector::getInstance());
	$listaRuoli = Utils::getListfromField($rolesObj->getAll("ruolo"),"ruolo","id_ruolo");
	$user_role['id_ruolo'] = array_search($user_role['ruolo'], $listaRuoli);
	ob_start();
	if(is_null($user_role['id_persona']) && count($listPersone) == 0): ?>
	<div class="alert alert-danger">
		Non è possibile assegnare ulteriori ruoli
	</div>
<?php else: ?>
				<form id="userRoles" name="userRoles" method="POST">
<?php 
			echo HTMLHelper::select('id_persona', "Persona", $listPersone, $user_role['id_persona']);
			echo HTMLHelper::select('id_ruolo', "Ruolo", $listaRuoli, $user_role['id_ruolo']);
?>
				</form>
<?php endif;
	return ob_get_clean();
}
?>