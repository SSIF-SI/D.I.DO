<?php 
class Application{
	/*
	 * La classe principale di DIDO
	 * 
	 * Dido gestisce i documenti utilizzando 3 sorgenti principali:
	 * 
	 * - Repository FTP
	 * - Master Document Manager (Database) 
	 * - Lista di XML descrittivi per ogni tipologia di documento (files)
	 * 
	 * Dido Importa i documenti anche da sorgenti esterne (attualmente solo da GECO)
	 * 
	 * L'accesso a Dido è riservato agli utenti ISTI con vari livelli di permessi.
	 * 
	 */
	
	/*
	 * Connettore al DB, verrà utilizzato da svariate classi
	 */
	private $_dbConnector;
	
	/*
	 * Sorgenti di dati
	 */
	private $_FTPDataSource;
	private $_MasterdocumentManager;
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
		$this->_dbConnector = Connector::getInstance();
		
		$this->_FTPDataSource = new FTPDataSource();
		$this->_MasterdocumentManager = new MasterdocumentManager($this->_dbConnector);
		$this->_XMLDataSource = new XMLDataSource();
		
		$this->_ImportManager = new ImportManager();
		$this->_UserManager = new UserManager($this->_DBDataSource->getConnector());
		
	}
	
	public function getAllMyMasterDocuments(){
		$this->_MasterdocumentManager->getAllMyMasterDocuments();
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