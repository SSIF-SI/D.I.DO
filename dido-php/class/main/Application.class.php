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
	 * ImportManager per gestire i dati importati/Da importare
	 */	
	
	private $_ImportManager;
	/*
	 * 
	 * Gestione dati dell'utente collegato
	 */
	private $_UserManager;
	
	public function __construct(){
		
		$this->_FTPDataSource = new FTPDataSource();
		$this->_UserManager = new UserManager();
		$this->_XMLDataSource = new XMLDataSource($XMLParser);
		$this->_ImportManager = new ImportManager($this->_UserManager->getUserRole(), $this->_XMLDataSource->getXMLList);
	}
	
	public function saveDataToBeImported(){
		// Funzione eseguita in cron
		$this->_ImportManager->saveDataToBeImported();
	}
	
	public function getSavedDataToBeImported(){
		$this->_ImportManager->getSavedDataToBeImported();
	}
	
	public function import($data){
		$this->_ImportManager->import($data);
	}
	
}
?>