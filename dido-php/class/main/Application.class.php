<?php 
class Application{
	/*
	 * La classe principale di DIDO
	 * 
	 * Dido gestisce i dati di 3 sorgenti principali:
	 * 
	 * - Repository FTP
	 * - Database 
	 * - Lista di XML
	 * 
	 * Dido Importa i dati da sorgenti esterne, attualmente solo da GECO
	 * 
	 *
	 * L'accesso a Dido è riservato agli utenti ISTI con vari livelli di permessi.
	 * 
	 */
	
	/*
	 * Sorgenti di dati
	 */
	private $_FTPDataSource;
	private $_DBDataSource;
	private $_XMLDataSource;
	
	/*
	 * Per gestire i dati importati/Da importare
	 */	
	
	private $_ImportManager;
	/*
	 * 
	 * Gestione dati dell'utente collegato
	 */
	private $_UserManager;
	
	public function __construct(){
		
		$this->_FTPDataSource = new FTPDataSource();
		$this->_DBDataSource = new DBDataSource();
		$this->_XMLDataSource = new XMLDataSource();
		
		$this->_ImportManager = new ImportManager();
		$this->_UserManager = new UserManager();
		
	}
	
	
	/*
	 * Sezione di Import dei dati
	 */
	public function saveDataToBeImported(){
		// Funzione eseguita in cron
		$this->_ImportManager->saveDataToBeImported();
	}
	
	public function getSavedDataToBeImported($from = null){
		$owners = $this->_UserManager->isAdmin() ? [] : $this->_UserManager->getUser()->getGruppi();
		
		$xmlList = $this->_XMLDataSource
							->filter(new XMLFilterOwner($owners))
							->filter(new XMLFilterValidity(date("Y-m-d")))
							->getXmlTree();
		
		$catlist = array_keys($xmlList);
		return $this->_ImportManager->getSavedDataToBeImported($owners, $catlist, $from);
	}
	
	public function import($from, $data){
		return $this->_ImportManager->import($from, $data);
	}
	
}
?>