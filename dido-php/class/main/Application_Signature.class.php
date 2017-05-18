<?php
class Application_Signature {
	const FIXED = "fixed";
	const VARIABLE = "variable";

	/*
	 * Connettore al DB, verrà utilizzato da svariate classi
	 */
	private $_dbConnector;
	
	/*
	 * Gestione dati dell'utente collegato
	 */
	private $_userManager;
	private $_fixedSigners;
	private $_variableSigners;
	private $_signersRoles;
	private $_signers;
	
	public function __construct(IDBConnector $dbConnector, UserManager $UserManager) {
		$this->_dbConnector = $dbConnector;
		$this->_userManager = $UserManager;
		
		$this->_fixedSigners = new FixedSigners ( $dbConnector );
		$this->_variableSigners = new VariableSigners ( $dbConnector );
		$this->_signersRoles = new SignersRoles ( $dbConnector );
		$this->_Signers = new Signers ( $dbConnector );
	}
	//da Sistemare.
	public function getSigner($id = null, $type = "", $idfield = "") {
		$instanceName = checkType ( $type );
		if ($instanceName)
			return is_null ( $id ) ? $this->$instanceName->getStub () : $this->$instanceName->get ( array (
					$idfield => id 
			) );
		else
			return false;
	}
	
	public function getSigners($withoutId = null) {
		$signers = array_keys ( 	$this->_Signers->getAll ( null, 'id_persona' ) );
		//recupera la lista delle persone
		$listPersone=Personale::getInstance()->getPersone();
		foreach ( $signers as $id_persona ) {
			if (array_key_exists ( $id_persona, $listPersone ) && $id_persona != $withoutId)
				unset ( $listPersone [$id_persona] );
		}
		
	}
	
	public function getSignersRoles($fixed,$assignable = false) {
		$signer_roles = Utils::getListfromField ( Utils::filterList ( $this->_signersRoles->getAll ( 'sigla', 'id_sr' ), 'fixed_role', $fixed ), 'descrizione' );
		if($assignable && $fixed==1)
			return array_diff_key ( $signer_roles, Utils::getListfromField ( $this->_fixedSigners->getAll (), null, 'id_sr' ) );
		else
			return $signer_roles;
	}
	
	public function saveSigner($signer, $type = "") {
		$instanceName = checkType ( $type );
		if (!empty($instanceName)) {
			$result = $this->$instanceName->save ( $signer );
			if ($result->getErrors () !== false) {
				return false;
			} else
				return true;
		} else
			false;
	}
	public function deleteSigner($signer, $type = "") {
		$instanceName = checkType ( $type );
		if (!empty($instanceName)) {
			$result = $this->$instanceName->delete ( $signer );
			if ($result->getErrors () !== false) {
				return false;
			} else
				return true;
		} else
			false;
	}
	private function checkType($type) {
		$instanceName = "_" . $type . "Signers";
		if (! isset ( $this->$instanceName ))
			return null;
		else
			return $istanceName;
	}
}
?>