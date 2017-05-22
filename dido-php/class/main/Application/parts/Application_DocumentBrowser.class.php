<?php 
class Application_DocumentBrowser{
	const LABEL_MD = "md";
	const LABEL_MD_DATA  = "md_data";
	const LABEL_DOCUMENTS = "documents";
	const LABEL_DOCUMENTS_DATA = "documents_data";
	
	const MUST_BE_SIGNED_BY_ME = "mustBeSignedByMe";
	const IS_SIGNED_BY_ME = "isSigned";
	const FTP_NAME = "ftp_name";
	const IS_MY_DOC = "isMyDoc";
	const DOC_TO_SIGN_INSIDE = "docToSignIside";
	
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
	
	public function get($id_md){
		return $this
			->_emptyResult()
			->_fillResultArray(self::LABEL_MD, $this->_single($id_md))
			->_createResultTree()
			->_complete()
			->_only($id_md)
			->getResult();
	}
	
	private function _single($id_md){
		$list = $this->_Masterdocument->searchBy([
				[
						CRUD::SEARCHBY_FIELD => Masterdocument::ID_MD,
						CRUD::SEARCHBY_VALUE => $id_md
				]
		]);
			
		return Utils::getListfromField($list,null,Masterdocument::ID_MD);
	}
	
	public function getAllMyPendingsDocument(){
		return $this
			// Svuoto l'array
			->_emptyResult()
			// Ci metto tutti i MD aperti
			->_fillResultArray(self::LABEL_MD, $this->_openDocuments())
			// Creo a cascata l'albero dei risultati
			->_createResultTree()
			// Completo applicando i filtri in base ai "permessi utente"
			->_complete()
			// Quindi restituisco l'array
			->getResult();
	}
	
	
	public function getResult(){
		
		return $this->_resultArray;
	}
	
	private function _complete(){
		return $this
			// Se sono firmatario aggiorno le info sulla firma e sulla proprietà
			->_signatureCheck()
			// Se sono proprietario aggiorno le info sulla proprietà
			->_propertyCheck()
			// Aggiorno la visibilità in base alle regole XML
			->_xmlRulesCheck();
	}
	
	private function _signatureCheck(){
		if($this->_userManager->isSigner()){
			
			$signRoles = $this->_userManager->getUserSign()->getSignatureRoles();
			$mySignature = $this->_userManager->getUserSign()->getSignature();
			
			$XMLParser = new XMLParser();
			foreach($this->_resultArray[self::LABEL_MD] as $id_md => $md){
				
				$xml = $this->_XMLDataSource->getSingleXmlByFilename($md[Masterdocument::XML]);
				$XMLParser->setXMLSource($xml[XMLDataSource::LABEL_XML], $md[Masterdocument::TYPE]);
				// Se non ho il ruolo id uno dei firmatari del documento skippo
				$isSigner = $XMLParser->isSigner(array_keys($signRoles));
				if(!$isSigner)
					continue;
				
				foreach($isSigner as $role=>$listOfDocTypes){
					$id_md = $md[Masterdocument::ID_MD];
					$listOfDocTypes = array_values($listOfDocTypes);
					
					$iddocToInspect = $this->_filterDocByDocType($listOfDocTypes,$id_md);
					
					foreach($iddocToInspect as $id_doc){
						if($signRoles[$role][Signature::FIXED_ROLE]){
							// E' un ruolo fisso, lo devo firmare sempre
							$this->_resultArray[self::LABEL_DOCUMENTS][$id_md][$id_doc][self::MUST_BE_SIGNED_BY_ME] = 1;
						} else {
							// è variabile, devo vedere nel md data se io sono uno dei firmatari
							$signatureInput = $signRoles[$role][Signature::DESCRIZIONE];
							if($this->_resultArray[self::LABEL_MD_DATA][$id_md][$signatureInput] == $this->_userManager->getFieldToWriteOnDb()){
								$this->_resultArray[self::LABEL_DOCUMENTS][$id_md][$id_doc][self::MUST_BE_SIGNED_BY_ME] = 1;
							}
						}
						if(	$this->_resultArray[self::LABEL_DOCUMENTS][$id_md][$id_doc][self::MUST_BE_SIGNED_BY_ME]){
							$this->_resultArray[self::LABEL_MD][$id_md][self::IS_MY_DOC] = 1;
							// Se lo devo firmare controllo che sia effettivamente firmato
							// per ora alla vecchia maniera
							$filename = 
								$this->_resultArray[self::LABEL_MD][$id_md][Masterdocument::FTP_FOLDER] .
								Common::getFolderNameFromMasterdocument(
									$this->_resultArray[self::LABEL_MD][$id_md]
								) .
								DIRECTORY_SEPARATOR .
								$this->_resultArray[self::LABEL_DOCUMENTS][$id_md][$id_doc][self::FTP_NAME];
							
							if($this->_checkSignature($filename, $mySignature)){
								$this->_resultArray[self::LABEL_DOCUMENTS][$id_md][$id_doc][self::IS_SIGNED_BY_ME] = 1;
							} else {
								$this->_resultArray[self::LABEL_MD][$id_md][self::DOC_TO_SIGN_INSIDE]++;
							}
						} 
					}
				}
			}
		}
		
		return $this;
	}
	
	private function _propertyCheck(){
		$XMLParser = new XMLParser();
		$XMLParser->load(FILES_PATH."ownerRules.xml");
		$inputFields = (array)$XMLParser->getXmlSource()->input;
		$uid = $this->_userManager->getFieldToWriteOnDb();
		
		$myServices = $this->_userManager->getUser()->getGruppi();
		$myProjects = $this->_userManager->getUser()->getProgetti();
		
		foreach($this->_resultArray[self::LABEL_MD_DATA] as $id_md => $md_data){
			// se è già stata assegnata la proprietà ed è già mio saltro tutto 
			if( isset($this->_resultArray[self::LABEL_MD][$id_md][self::IS_MY_DOC]) && 
				$this->_resultArray[self::LABEL_MD][$id_md][self::IS_MY_DOC])
				continue;
			
			// Se sono proprietario segno la proprietà e salto il resto
			foreach($inputFields as $key){
				if(isset($md_data[$key]) && $md_data[$key] == $uid){
					$this->_resultArray[self::LABEL_MD][$id_md][self::IS_MY_DOC] = 1;
					continue 2;
				}
			}
			
			// Se sono consultatore controllo che i MD siano legati a
			// - i msiei gruppi
			// - i miei progetti
			// Se si, segno la proprietà e salto il resto
			if($this->_userManager->isConsultatore()){
				foreach($md_data as $k=>$v){
					if(in_array($v,$myServices) || in_array($v, $myProjects)){
						$this->_resultArray[self::LABEL_MD][$id_md][self::IS_MY_DOC] = 1;
						continue 2;
					}
				}
			}	
			
			// Se non ho ruoli taglio
			if(!$this->_userManager->getUserRole())
				$this->_purge($id_md);
			
		}
		
		return $this;
	}
	

	private function _xmlRulesCheck(){
		if($this->_userManager->isGestore(true) || $this->_userManager->isConsultatore(true)){
				
			$services = $this->_userManager->getUser()->getGruppi();
				
			$XMLParser = new XMLParser();
			foreach($this->_resultArray[self::LABEL_MD] as $id_md => $md){
				// Se è un mio documento salto il controllo
				if($md[self::IS_MY_DOC])
					continue;
				$xml = $this->_XMLDataSource->getSingleXmlByFilename($md[Masterdocument::XML]);
				$XMLParser->setXMLSource($xml[XMLDataSource::LABEL_XML], $md[Masterdocument::TYPE]);
				if($XMLParser->isVisible($services)) continue;
	
				/*
					foreach($this->_resultArray[self::LABEL_DOCUMENTS] as $docList){
						
					foreach($docList as $doc){
					if($doc[self::MUST_BE_SIGNED_BY_ME])
						continue 2;
						}
						}
						*/
					$this->_purge($id_md);
			}
		}
	
		return $this;
	}
	
	private function _filterDocByDocType($listOfDocTypes, $id_md){
		$filtered = [];
		foreach($this->_resultArray[self::LABEL_DOCUMENTS] as $idmd => $docList){
			if($id_md != $idmd)
				continue;
			foreach($docList as $id_doc => $docData){
				if(in_array($docData[Document::NOME],$listOfDocTypes))
					array_push($filtered,$id_doc);
			}
		}
		return $filtered;
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
		return $this;
	}

	public function _createResultTree() {
		// Creo l'albero di documenti
		// Appendo info utili
		foreach($this->_resultArray [self::LABEL_MD] as $id_md => $md){
			$this->_resultArray[self::LABEL_MD][$id_md][self::IS_MY_DOC] = 0;
			$this->_resultArray[self::LABEL_MD][$id_md][self::DOC_TO_SIGN_INSIDE] = 0;
		}
		
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
					$documents [$k] [self::MUST_BE_SIGNED_BY_ME] = 0;
					$documents [$k] [self::IS_SIGNED_BY_ME] = 0;
					$documents [$k] [self::FTP_NAME] = Common::getFilenameFromDocument($document);
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
		return $this;
	}
	
	private function _only($id_md){
		if(isset($this->_resultArray [self::LABEL_MD][$id_md])){
			$this->_resultArray [self::LABEL_MD] = $this->_resultArray [self::LABEL_MD][$id_md];
			$this->_resultArray [self::LABEL_MD_DATA] = $this->_resultArray [self::LABEL_MD_DATA][$id_md];
			$this->_resultArray [self::LABEL_DOCUMENTS] = $this->_resultArray [self::LABEL_DOCUMENTS][$id_md];
		}
		return $this;
	}
	
	private function _emptyResult() {
		$this->_resultArray = array (
				self::LABEL_MD => [ ],
				self::LABEL_MD_DATA => [ ],
				self::LABEL_DOCUMENTS => [ ],
				self::LABEL_DOCUMENTS_DATA => [ ]
		);
		return $this;
	}
	
	/*
	 * Questa non dovrebbe essere qui
	 * ma in un componente che "gestisce le firme"
	 */
	private function _checkSignature($filename, $signature) {
		$tmpPDF = $this->_FTPDataSource->getTempFile ( $filename );
	
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
	
	private function _purge($id_md){
		$ddataKeys = array_keys($this->_resultArray[self::LABEL_DOCUMENTS][$id_md]);
		unset(
				$this->_resultArray[self::LABEL_MD][$id_md],
				$this->_resultArray[self::LABEL_MD_DATA][$id_md],
				$this->_resultArray[self::LABEL_DOCUMENTS][$id_md]
		);
	
		foreach($ddataKeys as $id_ddata)
			unset ($this->_resultArray[self::LABEL_DOCUMENTS_DATA][$id_ddata]);
	
	}
}
?>