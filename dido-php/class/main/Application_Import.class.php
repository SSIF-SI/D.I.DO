<?php 
class Application_Import{
	/*
	 * Connettore al DB, verrà utilizzato da svariate classi
	 */
	private $_dbConnector;
	
	/*
	 * Sorgenti di dati
	 */
	private $_FTPDataSource;
	
	/*
	 * Gestione dati dell'utente collegato
	 */
	private $_userManager;
	
	/*
	 * Sorgente XML
	 */
	private $_XMLDataSource;
	
	/*
	 * Per gestire i dati importati/Da importare
	 */
	private $_importManager;
	
	public function __construct(IDBConnector $dbConnector, IUserManager $userManager, IXMLDataSource $XMLDataSource, IFTPDataSource $FTPDataSource){
		$this->_dbConnector = $dbConnector;
		$this->_UserManager = $userManager;
		$this->_XMLDataSource = $XMLDataSource;
		$this->_FTPDataSource = $FTPDataSource;
		
		$this->_importManager = new ImportManager($dbConnector, $FTPDataSource);
	}
	
	public function getImportManager(){
		return $this->_importManager;
	}
	
	public function getXMLDataSource(){
		return $this->_XMLDataSource;
	}
	/*
	 * Sezione di Import dei dati
	 */
	public function saveDataToBeImported() {
		// Funzione eseguita in cron
		$this->_importManager->saveDataToBeImported ();
	}
	
	public function getSavedDataToBeImported($from = null, $subCategory = null) {
		$owners = $this->_UserManager->isAdmin () ? [ ] : $this->_UserManager->getUser ()->getGruppi ();
	
		$xmlList = $this->_XMLDataSource->filter ( new XMLFilterOwner ( $owners ) )->filter ( new XMLFilterValidity ( date ( "Y-m-d" ) ) )->getXmlTree ();
		$this->_XMLDataSource->resetFilters();
		$catlist = array_keys ( $xmlList );
		return $this->_importManager->getSavedDataToBeImported ( $owners, $catlist, $from, $subCategory );
	}
	
	public function import( $from, $postData) {
		
		if (! isset ( $postData [ImportManager::LABEL_IMPORT_FILENAME] ) || ! isset ( $postData [ImportManager::LABEL_MD_NOME] ) || ! isset ( $postData [ImportManager::LABEL_MD_TYPE] ))
			return new ErrorHandler("Import fallito, mancano argomenti essenziali");
	
		/*
		$lastXML = $this->getLastXML($postData [ImportManager::LABEL_MD_NOME]);
	
		if (! $lastXML)
			return new ErrorHandler("Impossibile associare un XML al tipo di Master Document");
	
		$XmlParser = new XMLParser ( $lastXML [XMLDataSource::LABEL_XML] );
		$from = (string) $XmlParser->getSource ();
		
		$postData [ImportManager::LABEL_MD_XML] = $lastXML [XMLDataSource::LABEL_FILE];
		*/
		
		return $this->_importManager->import ( $from, $postData );
	}
	
	public function getLastXML($tipoDocumento){
		$XML = $this->_XMLDataSource
			->filter ( new XMLFilterDocumentType ( [ $tipoDocumento ] ) )
			->filter ( new XMLFilterValidity ( date ( "Y-m-d" ) ) )
			->getFirst ();
		$this->_XMLDataSource->resetFilters();
		return $XML;
	}
}
?>