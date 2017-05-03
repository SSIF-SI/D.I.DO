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
	private $_FTP_Data_Source;
	private $_DB_Data_Source;
	private $_XML_Data_Source;
	
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
		$this->_FTP_Data_Source = new FTPDataSource();
		$this->_UserManager = new UserManager();
		$this->_ImportManager = new ImportManager();
	}
	
	public function saveDataToBeImported(){
		$this->_ImportManager->saveDataToBeImported();
	}
	
	public function import($data){
		$this->_ImportManager->import($data);
	}
	
}
?>