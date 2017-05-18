<?php 
class Application_DocumentBrowser{
	const LABEL_MD = "md";
	const LABEL_MD_DATA  = "md_data";
	const LABEL_DOCUMENTS = "documents";
	const LABEL_DOCUMENTS_DATA = "documents_data";
	
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
	 * Le classi per recuperare dati dal DB
	 */
	private $_Masterdocument, $_MasterdocumentData, $_Document, $_DocumentData;
	
	/*
	 * L'array dove verrà memorizzato il risultato
	 */
	private $_resultArray;
	
	
	/*
	 * Per il controllo dei documenti da firmare;
	 */
	private $_PDFParser;
	
	public function __construct(IDBConnector $dbConnector, IUserManager $userManager, IXMLDataSource $XMLDataSource, IFTPDataSource $FTPDataSource){
		$this->_dbConnector = $dbConnector;
		$this->_userManager = $userManager;
		$this->_XMLDataSource = $XMLDataSource;
		$this->_FTPDataSource = $FTPDataSource;
		
		$this->_Masterdocument = new Masterdocument ( $this->_dbConnector );
		$this->_MasterdocumentData = new MasterdocumentData ( $this->_dbConnector );
		$this->_Document = new Document ( $this->_dbConnector );
		$this->_DocumentData = new DocumentData ( $this->_dbConnector );
		
		$this->_PDFParser = new PDFParser();
	}
	
	public function getAllMyPendingsDocument(){
		$this->_emptyResult();
		
		// Tutti i documenti aperti
		$this->_fillResultArray(self::LABEL_MD, $this->_openDocuments());
		$this->_createResultTree();
		
		//if($this->_userManager->isSigner()){
			$this->_searchDocWithMySignature();
		//}
		
		return $this->getResult();
	}
	
	public function getResult(){
		return $this->_resultArray;
	}
	
	private function _searchDocWithMySignature(){
		
		foreach($this->_resultArray[self::LABEL_DOCUMENTS] as $documents){
			foreach($documents as $id_md => $doc){
				$filename = $MDdocToInspect[$id_md] . $doc['ftp_name'];
				Utils::printr($filename);
			}
		}
	}
	
	private function _openDocuments(){
		$list = $this->_Masterdocument->searchBy([
			[
				CRUD::SEARCHBY_FIELD => Masterdocument::CLOSED,
				CRUD::SEARCHBY_VALUE => ProcedureManager::OPEN
			]
		]);
			
		return Utils::getListfromField($list,null,Masterdocument::ID_MD);
	}
	
	private function _fillResultArray($key, $values){
		$this->_resultArray[$key] = $this->_resultArray[$key] + $values;
	}

	public function _createResultTree($docListAlreadyExistent = null) {
		// Creo l'albero di documenti
		//$this->_resultArray [self::LABEL_MD] = Utils::getListfromField ( $this->_resultArray [self::LABEL_MD], null, "id_md" );
	
		$md_ids = array_keys ( $this->_resultArray [self::LABEL_MD] );
		if (count ( $md_ids )) {
			$this->_resultArray [self::LABEL_MD_DATA] = $this->_compact ( 
				Utils::groupListBy ( 
					$this->_MasterdocumentData->getBy ( MasterdocumentData::ID_MD, join ( ",", $md_ids ) ), MasterdocumentData::ID_MD 
				)
			);
				
			$documents = Utils::getListfromField ( 
				$this->_Document->getBy ( Document::ID_MD, join ( ",", $md_ids ) ), null, Document::ID_DOC 
			);
			if (! empty ( $documents )) {
				foreach ( $documents as $k => $document ) {
					$documents [$k] ['mustBeSigned'] = 0;
					$documents [$k] ['signed'] = 0;
						
					// Se ho le info aggiuntive sulla firma sovrascrivo il
					// documento
					if (isset ( $docListAlreadyExistent [$documents [$k] [Document::ID_MD]] [$k] ))
						$documents [$k] = $docListAlreadyExistent [$documents [$k] [Document::ID_MD]] [$k];
						
					$documents [$k] ['ftp_name'] = Common::getFilenameFromDocument($documents [$k]);
				}
				$this->_resultArray [self::LABEL_DOCUMENTS] = Utils::groupListBy ( 
					$documents, Document::ID_MD 
				);
				$this->_resultArray [self::LABEL_DOCUMENTS_DATA] = $this->_compact ( 
					Utils::groupListBy ( 
						$this->_DocumentData->getBy ( DocumentData::ID_DOC, join ( ",", array_keys ( $documents ) ) ), DocumentData::ID_DOC 
					) 
				);
			}
		}
	}
	private function _emptyResult() {
		$this->_resultArray = array (
				self::LABEL_MD => [ ],
				self::LABEL_MD_DATA => [ ],
				self::LABEL_DOCUMENTS => [ ],
				self::LABEL_DOCUMENTS_DATA => [ ]
		);
	}
	
	/*
	 * Questa non dovrebbe essere qui
	 * ma in un componente che "gestisce le firme"
	 */
	private function _checkSignature($filename, $signature) {
		$tmpPDF = $this->_FTPConnector->getTempFile ( $filename );
	
		$this->_PDFParser->loadPDF ( $tmpPDF );
		$signaturesOnDocument = $this->_PDFParser->getSignatures ();
	
		unlink ( $tmpPDF );
	
		// Utils::printr("found ".count($signaturesOnDocument)."
		// signature(s).");
	
		if (count ( $signaturesOnDocument )) {
		foreach ( $signaturesOnDocument as $sod ) {
		// Utils::printr($sod);
			if ($sod->publicKey == $signature)
			return true;
		}
		} else
			return false;
	}
	
	private function _compact($md_data) {
		foreach ( $md_data as $id_md => $data ) {
			$metadata = array ();
			foreach ( $data as $input ) {
				$metadata [$input ['key']] = $input ['value'];
			}
			$md_data [$id_md] = $metadata;
		}
		return $md_data;
	}
}
?>