<?php 
class User implements IUserData{
	private $_uid;
	private $_nome;
	private $_cognome;
	private $_codiceFiscale;
	private $_email;
	private $_gruppi;
	private $_progetti;
	
	public function __construct(ASourceUserData $sourceData){
		$this->_uid = $sourceData->getUid();
		$this->_nome = $sourceData->getNome();
		$this->_cognome = $sourceData->getCognome();
		$this->_codiceFiscale = $sourceData->getCodiceFiscale();
		$this->_email = $sourceData->getEmail();
		$this->_gruppi = $sourceData->getGruppi();
		$this->_progetti = $sourceData->getProgetti();
	}
	
	public function getUid(){
		return $this->_uid;
	}
	
	public function getNome(){
		return $this->_nome;
	}
	
	public function getCognome(){
		return $this->_cognome;
	}
	
	public function getEmail(){
		return $this->_email;
	}
	
	public function getCodiceFiscale(){
		return $this->_codiceFiscale;
	}
	
	public function getGruppi(){
		return $this->_gruppi;
	}
	
	public function getprogetti(){
		return $this->_progetti;
	}
}
?>