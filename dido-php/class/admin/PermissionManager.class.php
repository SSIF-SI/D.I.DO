<?php 
class PermissionManager{
	private $_dbConnector;
	private $_roles;
	private $_usersRoles;
	private $_Personale;
	
	public function __construct($dbConnector){
		$this->_dbConnector = $dbConnector;
		$this->_roles= new Roles($dbConnector); 
		$this->_usersRoles= new UsersRoles($dbConnector); 
		$this->_Personale = Personale::getInstance();
	}
	
	public function getUsersRoles(){
		return $this->_usersRoles->getAll(UsersRoles::RUOLO);
	}
	
	public function createModal() {
		$user_role =
			isset($_GET[UsersRoles::ID_PERSONA]) && isset($_GET[UsersRoles::ID_RUOLO]) ?
			$this->_usersRoles->get ( $_GET ) :
			$this->_usersRoles->getStub ();
		
		$listPersone = ListHelper::persone ();
		
		$alreadyset = array_keys ( $this->_usersRoles->getAll ( UsersRoles::ID_PERSONA, UsersRoles::ID_PERSONA ) );
		
		foreach ( $alreadyset as $id_persona ) {
			if (array_key_exists ( $id_persona, $listPersone ) && $id_persona != $user_role [UsersRoles::ID_PERSONA])
				unset ( $listPersone [$id_persona] );
		}
		
		$listaRuoli = Utils::getListfromField ( $this->_roles->getAll ( Roles::RUOLO ), Roles::RUOLO, Roles::ID_RUOLO );
		ob_start ();
		if (is_null ( $user_role [UsersRoles::ID_PERSONA] ) && count ( $listPersone ) == 0) :
?>
		<div class="alert alert-danger">Non è possibile assegnare ulteriori ruoli</div>
<?php 
		else: 
?>
		<form id="userRoles" name="userRoles" method="POST">
<?php
			if (isset($_GET[UsersRoles::ID_PERSONA])){
?>
			<label for="persona">Persona:</label>
			<p id="persona"><?=$this->_Personale->getNominativo ( $user_role [UsersRoles::ID_PERSONA] )?></p>
<?php 			
				echo HTMLHelper::input ( 'hidden', UsersRoles::ID_PERSONA, null, $user_role [UsersRoles::ID_PERSONA] );
			} else {
				echo HTMLHelper::select ( UsersRoles::ID_PERSONA, "Persona", $listPersone, $user_role [UsersRoles::ID_PERSONA] );
			}
			echo HTMLHelper::select ( UsersRoles::ID_RUOLO, "Ruolo", $listaRuoli, $user_role [UsersRoles::ID_RUOLO] );
?>
		</form>
<?php
		endif;
		ob_end_flush ();
		die();
	}
	
	public function processAjax(){
		$delete = isset ( $_GET ['delete'] ) ? true : false;
		
		if ($delete) {
			unset ( $_GET ['delete'] );
			return $this->_usersRoles->delete ( $_GET ) ;
		}
		if (count ( $_POST ) != 0) {
			return $this->_usersRoles->save ( $_POST ) ;
		} 
	}
}
?>