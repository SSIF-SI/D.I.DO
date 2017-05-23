<?php

class Application {

	/*
	 * La classe principale di DIDO
	 *
	 * Dido gestisce i documenti utilizzando 3 sorgenti principali:
	 *
	 * - Repository FTP
	 * - Master Document Manager (Database)
	 * - Lista di XML descrittivi per ogni tipologia di documento (files)
	 *
	 * Dido Importa i documenti anche da sorgenti esterne (attualmente solo da
	 * GECO)
	 *
	 * L'accesso a Dido è riservato agli utenti ISTI con vari livelli di
	 * permessi.
	 *
	 */
	
	/*
	 * Connettore al DB, verrà utilizzato da svariate classi
	 */
	private $_dbConnector;

	/*
	 * Sorgenti di dati FTP
	 */
	private $_FTPDataSource;

	/*
	 * Sorgente XML
	 */
	private $_XMLDataSource;

	/*
	 * Gestione dati dell'utente collegato
	 */
	private $_userManager;

	/*
	 * PARTI DELL'APPLICAZIONE
	 */
	const IMPORT = "Import";
	const NAVIGATOR = "Navigator";
	const DOCUMENTBROWSER = "DocumentBrowser";
	
	/*
	 * Classe che gestisce l'import
	 */
	private $_Application_Import;
	
	/*
	 * Classe che gestisce gli elementi HTML navigabili 
	 */
	private $_Application_Navigator;
	
	/*
	 * Classe che gestisce la ricerca dei MD
	 */
	private $_Application_DocumentBrowser;
	
	/*
	 * Classe che genera il dettaglio di un documento
	 */	
	private $_Application_Detail;
	
	public function __construct() {
		$this->_dbConnector = DBConnector::getInstance ();
		$this->_userManager = new UserManager ( $this->_dbConnector );
		$this->_FTPDataSource = new FTPDataSource ();
		$this->_XMLDataSource = new XMLDataSource ();
		
		$this->_Application_Import = new Application_Import($this->_dbConnector, $this->_userManager, $this->_XMLDataSource, $this->_FTPDataSource);
		$this->_Application_Navigator = new Application_Navigator($this->_dbConnector, $this->_XMLDataSource,$this->_userManager);
		$this->_Application_DocumentBrowser = new Application_DocumentBrowser($this->_dbConnector, $this->_userManager, $this->_XMLDataSource, $this->_FTPDataSource);
		$this->_Application_Detail = new Application_Detail($this->_dbConnector, $this->_userManager, $this->_FTPDataSource);
	}

	public function getApplicationPart($part){
		$instanceName = "_Application_$part";
		if(isset($this->$instanceName)) 
			return $this->$instanceName;
	}
	
	public function getUsername(){
		return $this->_userManager->getUser()->getCognome(). " ". $this->_userManager->getUser()->getNome();
	}
	
	public function getUserManager(){
		return $this->_userManager;
	}
	
	public function getDBConnector(){
		return $this->_dbConnector;
	}
	
	public function getXMLDataSource(){
		return $this->_XMLDataSource;
	}
	
	public function getFTPDataSource(){
		return $this->_FTPDataSource;
	}
	
}
?>