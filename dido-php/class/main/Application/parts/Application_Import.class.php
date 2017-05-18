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
		$this->_importManager->clean();
		
		if(isset($postData[ImportManager::MULTI_IMPORT])){
			// Import multiplo
			// Recupero i filenames di cui fare il rollback
			$filenames = $postData[ImportManager::LABEL_IMPORT_FILENAME];
			
			// Trasformo i dati in POST per renderli fruibili alla funzione di import
			$postData_docs  = [];
			for($i=0; $i< count($postData[ImportManager::LABEL_IMPORT_FILENAME]); $i++){
				array_push($postData_docs, [
					ImportManager::LABEL_IMPORT_FILENAME => $postData[ImportManager::LABEL_IMPORT_FILENAME][$i],
					ImportManager::LABEL_MD_NOME => $postData[ImportManager::LABEL_MD_NOME][$i],
					ImportManager::LABEL_MD_TYPE => $postData[ImportManager::LABEL_MD_TYPE][$i],
					ImportManager::LABEL_MD_XML => $postData[ImportManager::LABEL_MD_XML][$i]
				]);
			}
			
			// Inizio QUI la transazione
			$this->_dbConnector->begin();
			
			// Recupero i dati del documento prinzipale
			$mainData = $postData_docs[$postData[ImportManager::PRINCIPALE]];
			unset($postData_docs[$postData[ImportManager::PRINCIPALE]]);
			
			// La funzione di import in questo caso mi deve restituire l'MD creato
			$md = $this->_importManager->import($from, $mainData, true);
			
			// Se ho errori il risultato sarà un Errorhandler
			if($md instanceof ErrorHandler){
				$this->_rollbackFilenames($filenames);
				$this->_dbConnector->rollback();
				return $md;
			}
			
			// Se la creazione del MD principale va a buon fine importo anche gli altri MD
			// Ma li creo come fossero solo Allegati
			foreach($postData_docs as $data){
				$result = $this->_importManager->import($from, $data, $md);
				
				// Se qualcosa va male faccio rollback e restituisco l'errore.
				if($result->getErrors() !== false){
					$this->_rollbackFilenames($filenames);
					$this->_dbConnector->rollback();
					return $result;
				}
			}
			
			// Tutto ok
			$this->_dbConnector->commit();
			return new ErrorHandler(false);
		} 
		
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
	
	private function _rollbackFilenames($filenames){
		foreach($filenames as $filename){
			$this->_importManager->setImported($filename, IExternalDataSource::FILE_EXTENSION_IMPORTED, IExternalDataSource::FILE_EXTENSION_TO_BE_IMPORTED);
		}
	}
}
?>