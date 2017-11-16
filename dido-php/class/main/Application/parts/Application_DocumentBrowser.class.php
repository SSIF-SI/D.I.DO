<?php 
class Application_DocumentBrowser{
	const LABEL_MD = "md";
	const LABEL_MD_DATA  = "md_data";
	const LABEL_DOCUMENTS = "documents";
	const LABEL_DOCUMENTS_DATA = "documents_data";
	const LABEL_MD_LINKS = "md_links";
	
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
	private $_Masterdocument, $_MasterdocumentData, $_Document, $_DocumentData, $_MasterdocumentsLinks;
	
	/*
	 * L'array dove verrà memorizzato il risultato
	 */
	private $_resultArray;
	
	/*
	 * Per il controllo dei documenti da firmare;
	 */
	private $_SignatureChecker;
	
	public function __construct(IDBConnector $dbConnector, IUserManager $userManager, IXMLDataSource $XMLDataSource, IFTPDataSource $ftpDataSource){
		$this->_dbConnector = $dbConnector;
		$this->_userManager = $userManager;
		$this->_XMLDataSource = $XMLDataSource;
		
		$this->_Masterdocument = new Masterdocument ( $this->_dbConnector );
		$this->_MasterdocumentData = new MasterdocumentData ( $this->_dbConnector );
		$this->_Document = new Document ( $this->_dbConnector );
		$this->_DocumentData = new DocumentData ( $this->_dbConnector );
		$this->_MasterdocumentsLinks = new MasterdocumentsLinks( $this->_dbConnector );
		
		$this->_SignatureChecker = new SignatureChecker($ftpDataSource);
	}
	
	public function get($id_md){
		return $this
			->_emptyResult()
			->_fillResultArray(self::LABEL_MD, $this->_just($id_md))
			->_createResultTree()
			->_complete()
			->_only($id_md)
			->getResult();
	}
	
	public function getLinkedMd($id_md){
		$mds = $this->_MasterdocumentsLinks->getBy(MasterDocumentsLinks::ID_FATHER, $id_md);
		$id_mds = array_keys(Utils::getListfromField($mds, null, MasterdocumentsLinks::ID_CHILD));
		if(empty($id_mds)) return null;
		
		$this->_emptyResult()
			->_fillResultArray(self::LABEL_MD, $this->_just($id_mds))
			->_createResultTree()
			->_complete();
		
		$this->_resultArray [self::LABEL_MD_LINKS] = Utils::getListfromField($mds, null, MasterdocumentsLinks::ID_LINK);
		
		return $this->getResult();
	}
	
	public function getLinkableMd($md_name){
		$mds = Utils::getListfromField($this->_Masterdocument->getBy(Masterdocument::NOME, Utils::apici($md_name)),null,Masterdocument::ID_MD);
		//$id_mds = Utils::getListfromField(Utils::filterList($id_mds, Masterdocument::CLOSED, ProcedureManager::CLOSED), Masterdocument::ID_MD);
		if(empty($mds)) return null;
// 		flog("md: %o",$mds);
		return $this
			->_emptyResult()
			->_fillResultArray(self::LABEL_MD, $mds)
			->_createResultTree()
			->getResult();
	}
	
	public function getAllMyPendingDocuments(){
		return $this
			->_allMyPendingDocuments()
			// Quindi restituisco l'array
			->getResult();
	}
	
	public function getAllMyPendingDocumentsToSign(){
		return $this->
			_allMyPendingDocuments()
			->_docFilter(self::MUST_BE_SIGNED_BY_ME, 1)
			->getResult();
	}
	
	public function getAllMyClosedDocuments(){
		return $this
			->_allMyClosedDocuments()
			// Quindi restituisco l'array
			->getResult();
	}
	
	public function searchDocuments($source, $closed = null, $types = array(), $keywords = array()){
		$source = "_$source";
		$sourceData = $source."Data";

		$mainFilters = [];
		$dataFilters= [];
		if(!is_null($closed)){
			array_push($mainFilters, 
				[
					CRUD::SEARCHBY_FIELD => SharedDocumentConstants::CLOSED,
					CRUD::SEARCHBY_VALUE => $closed
				]
			);
		}
		
		if(count($types)){
			foreach ($types as $type){
				//$type = explode(" - ", $type);
				array_push($mainFilters,
					[
						CRUD::SEARCHBY_FIELD => SharedDocumentConstants::NOME,
						CRUD::SEARCHBY_VALUE => $type
					]
				);
				/*
				if(isset($type[1])){
					array_push($mainFilters,
							[
									CRUD::SEARCHBY_FIELD => Masterdocument::TYPE,
									CRUD::SEARCHBY_VALUE => $type[1]
							]
					);
				}
				*/
			}
		}
		if(count($keywords)){
			$keyvalues=[];
			
			foreach ($keywords as $key=>$value){
				$x=strstr($key,"+",true);
				$x=str_replace('_', ' ',$x);
				$v=strstr($key,"+");
				$v=str_replace('+', '',$v);
				if(!isset ($keyvalues[$x]))
 					$keyvalues[$x]=[];	
				array_push($keyvalues[$x],$v);
			}
			foreach ($keyvalues as $key=>$value){
				array_push($dataFilters,
					[
						Crud::SEARCHBY_OPEN_BRACKET=>true,
						CRUD::SEARCHBY_FIELD => AnyDocumentData::KEY,
						CRUD::SEARCHBY_VALUE => $key,
						CRUD::SEARCHBY_LOGIC_OPERATOR=>"AND"
					]
				);
				array_push($dataFilters,
					[
						CRUD::SEARCHBY_CLOSE_BRACKET=>true,
						CRUD::SEARCHBY_FIELD => AnyDocumentData::VALUE,
						CRUD::SEARCHBY_VALUE => $value,
						CRUD::SEARCHBY_OPERATOR=>"in",
						CRUD::SEARCHBY_LOGIC_OPERATOR=>"AND"
					]
				);
			}	
			/*			
			$this->$sourceData->useView(true);
			$datalist= $this->$sourceData->searchBy($dataFilters," AND ","id_md");
			$this->$sourceData->useView(false);
			$dataIdMD=Utils::getListfromField($datalist,null,Masterdocument::ID_MD);
			*/
		}
		
		$list = array();
		$mainList = array();
		$dataList = array();
		
		
		// Riempimento array
		if(empty($mainFilters) && empty($dataFilters)){
			$mainList = $this->$source->getAll(null, Masterdocument::ID_MD);
		}
		
		if(!empty($mainFilters)){
			$this->$source->useView(true);
			$mainList = $this->$source->searchBy($mainFilters, " OR ",Masterdocument::ID_MD);
			$mainList = Utils::getListfromField($mainList,null,Masterdocument::ID_MD);
			$this->$source->useView(false);
		}
		
		if(!empty($dataFilters)){
			$this->$sourceData->useView(true);
			$dataList= $this->$sourceData->searchBy($dataFilters," AND ",Masterdocument::ID_MD);
			$dataList = Utils::getListfromField($dataList,null,Masterdocument::ID_MD);
			$this->$sourceData->useView(false);
		}
		
		// calcolo finale della lista
		switch(true){
			case empty($mainFilters) && empty($dataFilters):
			case empty($dataFilters): 	 
				$list = array_keys($mainList);
				break;
			case empty($mainFilters):
				$list = array_keys($dataList);
				break;
			default:
				$list = array_intersect(array_keys($dataList), array_keys($mainList));
				break;
		}
		
		$list = $this->_Masterdocument
			->searchBy([
			[
					CRUD::SEARCHBY_FIELD => Masterdocument::ID_MD,
					CRUD::SEARCHBY_VALUE => $list,
					CRUD::SEARCHBY_OPERATOR => "in"
			]
		]);
		
		$list = Utils::getListfromField($list,null,Masterdocument::ID_MD);
		
		/*
		if(empty($mainFilters) && !empty($dataFilters))
			$list=$dataIdMD;
		else {
			$mainlist = $this->$source->searchBy($mainFilters, " AND ","id_md");
			$mainIdMd=Utils::getListfromField($mainlist,null,Masterdocument::ID_MD);
			$list=$mainIdMd;
		}
		if(!empty($mainFilters) && !empty($dataFilters))
			$list=array_intersect_key($mainIdMd, $dataIdMD);
		*/
			
		return $this->_emptyResult()
			->_fillResultArray(self::LABEL_MD, $list)
			->_createResultTree()
			->_complete()
			->getResult();
		
	}
	
	private function _just($value){
		$list = $this->_Masterdocument->searchBy([
				[
						CRUD::SEARCHBY_FIELD => Masterdocument::ID_MD,
						CRUD::SEARCHBY_VALUE => $value
				]
		]);
			
		return Utils::getListfromField($list,null,Masterdocument::ID_MD);
	}
	
	public function getResult(){
		
		return $this->_resultArray;
	}
	
private function _allMyPendingDocuments(){
		return $this
			// Svuoto l'array
			->_emptyResult()
			// Ci metto tutti i MD aperti
			->_fillResultArray(self::LABEL_MD, $this->_openDocuments())
			// Creo a cascata l'albero dei risultati
			->_createResultTree()
			// Completo applicando i filtri in base ai "permessi utente"
			->_complete();
	}
	
	private function _allMyClosedDocuments(){
		return $this
			// Svuoto l'array
			->_emptyResult()
			// Ci metto tutti i MD aperti
			->_fillResultArray(self::LABEL_MD, $this->_closedDocuments())
			// Creo a cascata l'albero dei risultati
			->_createResultTree()
			// Completo applicando i filtri in base ai "permessi utente"
			->_complete();
	}
	
	private function _docFilter($field, $value){
		foreach($this->_resultArray[self::LABEL_DOCUMENTS] as $id_md => $docList){
			$filtered = Utils::filterList($docList, $field, $value);
			if(count($filtered)){
				$this->_resultArray[self::LABEL_DOCUMENTS][$id_md] = $filtered;
				
				$dDatatoBeRemoved = array_diff($docList,$filtered);
				foreach($dDatatoBeRemoved as $id_doc =>$docData){
					unset ($this->_resultArray[self::LABEL_DOCUMENTS_DATA][$id_doc]);
				}
			} else 
				$this->_purge($id_md);
		}
		
		// Alla fine devo riallineare tutto;
		$mdToBeRemoved = array_diff(array_keys($this->_resultArray[self::LABEL_MD]),array_keys($this->_resultArray[self::LABEL_DOCUMENTS]));
		if(count($mdToBeRemoved)){
			foreach($mdToBeRemoved as $id_md){
				//Utils::printr($id_md);
				$this->_purge($id_md);
			}
		}
		return $this;
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
			$mySpecialSignatures = $this->_userManager->getUserSign()->getSpecialSignatures();
			
			$XMLParser = new XMLParser();
			foreach($this->_resultArray[self::LABEL_MD] as $id_md => $md){
				$docsToBeSigned = [];
				$xml = $this->_XMLDataSource->getSingleXmlByFilename($md[Masterdocument::XML]);
				$XMLParser->setXMLSource($xml[XMLDataSource::LABEL_XML], $md[Masterdocument::TYPE]);
				// Se non ho il ruolo id uno dei firmatari del documento skippo
				
				$isSpecialSigner = $XMLParser->isSpecialSigner(array_keys($mySpecialSignatures));
				
				if($isSpecialSigner){
					foreach($isSpecialSigner as $type=>$listOfDocTypes){
						$listOfDocTypes = array_unique(array_values($listOfDocTypes));
						$iddocToInspect = $this->_filterDocByDocType($listOfDocTypes,$id_md);
						foreach($iddocToInspect as $id_doc){
							$this->_resultArray[self::LABEL_MD][$id_md][self::IS_MY_DOC] = 1;
							$this->_resultArray[self::LABEL_DOCUMENTS][$id_md][$id_doc][self::MUST_BE_SIGNED_BY_ME] = 1;
							$this->_checkIfSigned($docsToBeSigned, $id_md, $id_doc, $mySpecialSignatures[$type]);
						}			
					}
				}
				
				$isSigner = $XMLParser->isSigner(array_keys($signRoles));
				
				if(!$isSigner)
					continue;
				
				foreach($isSigner as $role=>$listOfDocTypes){
					$listOfDocTypes = array_unique(array_values($listOfDocTypes));
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
							$this->_checkIfSigned($docsToBeSigned, $id_md, $id_doc, $mySignature);
						} 
					}
				}
				$this->_resultArray[self::LABEL_MD][$id_md][self::DOC_TO_SIGN_INSIDE] = count($docsToBeSigned);
			}
		}
		return $this;
	}
	
	private function checkIfSigned(&$docsToBeSigned, $id_md, $id_doc, $signature){
		// Se lo devo firmare controllo che sia effettivamente firmato
		// per ora alla vecchia maniera
		$filename =
		$this->_resultArray[self::LABEL_MD][$id_md][Masterdocument::FTP_FOLDER] .
		Common::getFolderNameFromMasterdocument(
				$this->_resultArray[self::LABEL_MD][$id_md]
		) .
		DIRECTORY_SEPARATOR .
		$this->_resultArray[self::LABEL_DOCUMENTS][$id_md][$id_doc][self::FTP_NAME];
		
		if($this->_SignatureChecker->load($filename)->checkSignature($signature)){
			$this->_resultArray[self::LABEL_DOCUMENTS][$id_md][$id_doc][self::IS_SIGNED_BY_ME] = 1;
		} else {
			if(!in_array($id_doc, $docsToBeSigned))
				array_push($docsToBeSigned, $id_doc);
		}
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
				$this->_resultArray[self::LABEL_MD][$id_md][self::IS_MY_DOC]){
				continue;
			}
			
			
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
	
	private function _closedDocuments(){
		$list = $this->_Masterdocument->searchBy([
			[
				CRUD::SEARCHBY_FIELD => Masterdocument::CLOSED,
				CRUD::SEARCHBY_VALUE => ProcedureManager::OPEN,
				CRUD::SEARCHBY_OPERATOR => "!="
			]
		]);
			
		return Utils::getListfromField($list,null,Masterdocument::ID_MD);
	}
	
	
	private function _fillResultArray($key, $values){
		if(!empty($values))
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
		if(isset($this->_resultArray[self::LABEL_DOCUMENTS][$id_md])){
			$ddataKeys = array_keys($this->_resultArray[self::LABEL_DOCUMENTS][$id_md]);
			foreach($ddataKeys as $id_ddata){
				unset ($this->_resultArray[self::LABEL_DOCUMENTS_DATA][$id_ddata]);
			}
		}
		
		unset(
				$this->_resultArray[self::LABEL_MD][$id_md],
				$this->_resultArray[self::LABEL_MD_DATA][$id_md],
				$this->_resultArray[self::LABEL_DOCUMENTS][$id_md]
		);
	
		
		
		
	}
	
	public static function purge($id_mds, $list){
		if(count($id_mds)) foreach($id_mds as $id_md){
			if(isset($list[self::LABEL_DOCUMENTS][$id_md])){
				$ddataKeys = array_keys($list[self::LABEL_DOCUMENTS][$id_md]);
				foreach($ddataKeys as $id_ddata){
					unset ($list[self::LABEL_DOCUMENTS_DATA][$id_ddata]);
				}
			}
		
			unset(
					$list[self::LABEL_MD][$id_md],
					$list[self::LABEL_MD_DATA][$id_md],
					$list[self::LABEL_DOCUMENTS][$id_md]
			);
		}
		return $list;
	}
}
?>