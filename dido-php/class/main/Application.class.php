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
	 * Sorgenti di dati
	 */
	private $_FTPDataSource;

	/*
	 * Sorgente XML
	 */
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

	public function __construct() {
		$this->_dbConnector = DBConnector::getInstance ();
		$this->_FTPDataSource = new FTPDataSource ();
		$this->_XMLDataSource = new XMLDataSource ();
		
		$this->_ImportManager = new ImportManager ( $this->_dbConnector, $this->_FTPDataSource );
		$this->_UserManager = new UserManager ( $this->_dbConnector );
	}

	/*
	 * Sezione di Import dei dati
	 */
	public function saveDataToBeImported() {
		// Funzione eseguita in cron
		$this->_ImportManager->saveDataToBeImported ();
	}

	public function getSavedDataToBeImported($from = null, $subCategory = null) {
		$owners = $this->_UserManager->isAdmin () ? [ ] : $this->_UserManager->getUser ()->getGruppi ();
		
		$xmlList = $this->_XMLDataSource->filter ( new XMLFilterOwner ( $owners ) )->filter ( new XMLFilterValidity ( date ( "Y-m-d" ) ) )->getXmlTree ();
		
		$catlist = array_keys ( $xmlList );
		return $this->_ImportManager->getSavedDataToBeImported ( $owners, $catlist, $from, $subCategory );
	}

	public function import($postData) {
		if (! isset ( $postData [ImportManager::LABEL_IMPORT_FILENAME] ) || ! isset ( $postData [ImportManager::LABEL_MD_NOME] ) || ! isset ( $postData [ImportManager::LABEL_MD_TYPE] ))
			return new ErrorHandler("Import fallito, mancano argomenti essenziali");
		
		$lastXML = $this->_XMLDataSource
			->filter ( new XMLFilterDocumentType ( [ $postData [ImportManager::LABEL_MD_NOME] ] ) )
			->filter ( new XMLFilterValidity ( date ( "Y-m-d" ) ) )
			->getFirst ();
		
		if (! $lastXML)
			return new ErrorHandler("Impossibile associare un XML al tipo di Master Document");
		
		$XmlParser = new XMLParser ( $lastXML [XMLDataSource::LABEL_XML] );
		$from = (string) $XmlParser->getSource ();
		
		$postData [ImportManager::LABEL_MD_XML] = $lastXML [XMLDataSource::LABEL_FILE];
		
		return $this->_ImportManager->import ( $from, $postData );
	}
}
?>