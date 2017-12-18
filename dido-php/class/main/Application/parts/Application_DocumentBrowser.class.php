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
	
	private $_allSpecialSignatures;
	
	public function __construct(IDBConnector $dbConnector, IUserManager $userManager, IXMLDataSource $XMLDataSource, IFTPDataSource $ftpDataSource){
		$this->_dbConnector = $dbConnector;
		$this->_userManager = $userManager;
		$this->_XMLDataSource = $XMLDataSource;
		
		$this->_Masterdocument = new Masterdocument ( $this->_dbConnector );
		$this->_MasterdocumentData = new MasterdocumentData ( $this->_dbConnector );
		$this->_Document = new Document ( $this->_dbConnector );
		$this->_DocumentData = new DocumentData ( $this->_dbConnector );
		$this->_MasterdocumentsLinks = new MasterdocumentsLinks( $this->_dbConnector );
		$this->_MasterdocumentsLinksData = $this->_MasterdocumentData;
		
		$this->_SignatureChecker = new SignatureChecker($ftpDataSource);
		$this->_allSpecialSignatures = $this->_userManager->getUserSign()->getAllSpecialSignatures();
		
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
		$mds = Utils::getListfromField($this->_Masterdocument->getBy(Masterdocument::NOME, $md_name),null,Masterdocument::ID_MD);
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
			->_docFilter(self::IS_SIGNED_BY_ME, 0)
			->getResult();
	}
	
	public function getAllMyClosedDocuments(){
		return $this
			->_allMyClosedDocuments()
			// Quindi restituisco l'array
			->getResult();
	}
	
	public function searchDocuments($source, $closed = null, $types = array(), $keywords = array(), $typekey = array(), $isLink = false){
		$source = "_$source";
		$sourceData = $source."Data";
		$qb = new QueryBuilder($this->_dbConnector);
		
		$mainWhere="";
		$keyQueries=[];
		
		if(!is_null($closed)){
			$qb->opEqual(SharedDocumentConstants::CLOSED, $closed);
			if(count($types))
				$qb->joinAnd();
		}
		if(count($types)){
			$valueSet=$qb->createInValues($types);
			$qb->opIn( SharedDocumentConstants::NOME, $valueSet);
		}
		$mainWhere=$qb->getWhere();
		$qb->reset();
		if(count($keywords)){
			$keyvalues=[];
			
			foreach ($keywords as $key=>$value){
				$x=strstr($key,"+",true);
				$x=str_replace('_', ' ',$x);
				if(!isset ($keyvalues[$x]))
 					$keyvalues[$x]=[];	
				array_push($keyvalues[$x],$value);
			}
				
			foreach ($keyvalues as $key=>$value){
				if(count($typekey))
					$textArea=$typekey[$key]==XMLParser::INPUT_TYPE_TEXTAREA;
				
				$apiciKey=Utils::apici($key);
				$qb->opEqual(AnyDocumentData::KEY, $apiciKey)->joinAnd();
				
				if(!$textArea){
					if (is_array ( $value )) {
						$valueSet=$qb->createInValues($value);
						$qb->opIn ( AnyDocumentData::VALUE, $valueSet );
						array_push ( $keyQueries, $qb->getWhere () );
					} else {
						array_push ( $keyQueries,$qb->opEqual(AnyDocumentData::VALUE, $value)->getWhere());
						
					}
				}else{
					if (is_array ( $value )) {
						$qb->openBracket();
						$valueEnd=end($value);
						foreach($value as $index=>$term){
							$qb->opLike(AnyDocumentData::VALUE, $term);
							if($term!= $valueEnd)
								$qb->joinOr();
						}
						array_push ( $keyQueries, $qb->closeBracket()->getWhere() );
					} else {
						array_push ( $keyQueries,$qb->opLike(AnyDocumentData::VALUE,$value)->getWhere() );
					}
				}
				$qb->reset();

			}

		}
		
		$list = array();
		$mainList = array();
		$dataList = array();
		
		$id_md = $isLink ? MasterdocumentsLinks::ID_FATHER : Masterdocument::ID_MD;
				
		// Riempimento array
		if(empty($mainFilters) && empty($keyQueries)){
			if($isLink) $this->$source->useView(true);
			$mainList = $this->$source->getAll(null, $id_md);
			if($isLink) $this->$source->useView(false);
				
		}
		
		if(!empty($mainWhere)){
			$this->$source->useView(true);
			$mainList=$qb->reset()->select( " DISTINCT " . Masterdocument::ID_MD)
			->from($this->$source->getFrom())
			->orderBy(Masterdocument::ID_MD)
			->setWhere($mainWhere)->getList();
// 			$mainList = $this->$source->searchBy($mainFilters, " AND ",$id_md);
			$mainList = Utils::getListfromField($mainList,null,$id_md);
			$this->$source->useView(false);
		}
		
		if(!empty($keyQueries)){
			$this->$sourceData->useView(true);
			$qb->select("DISTINCT ".Masterdocument::ID_MD)
			->from($this->$sourceData->getFrom())
			->orderBy(Masterdocument::ID_MD);
			foreach ($keyQueries as $where){
				$qb->setWhere($where);			
				array_push($dataList,Utils::getListfromField($qb->getList(),Masterdocument::ID_MD));
			}				
			if(count($dataList)>1)
				$dataList=call_user_func_array('array_intersect',$dataList);
			else if(!empty($dataList)){
				$dataList=$dataList[0];
			}
						//$dataList= $this->$sourceData->searchBy($dataFilters," AND ",Masterdocument::ID_MD);
			if($isLink && count($dataList)){
				$dataList = $qb->reset()->select("*")->from("master_documents_links_search_view")->opIn(MasterdocumentsLinks::ID_CHILD, array_keys($dataList));
				$dataList = Utils::getListfromField($dataList, null, MasterdocumentsLinks::ID_FATHER);
			}
			$this->$sourceData->useView(false);
			
		}
		
		// calcolo finale della lista
		switch(true){
			case empty($mainFilters) && empty($keyQueries):
			case empty($keyQueries): 	 
				$list = array_keys($mainList);
				break;
			case empty($mainFilters):
				$list = array_values($dataList);
				break;
			default:
				$list = array_intersect(array_values($dataList), array_keys($mainList));
				break;
		}
		$list = array_map ( "Utils::apici", $list );
		
		$list = sprintf ( " %s ", join ( ", ", $list ) );
		$list = $qb->reset()->select("*")->from($this->_Masterdocument->getFrom())->opIn(Masterdocument::ID_MD, $list)->getList();

		$list = Utils::getListfromField($list,null,Masterdocument::ID_MD);
		
			
		return $this->_emptyResult()
			->_fillResultArray(self::LABEL_MD, $list)
			->_createResultTree()
			->_complete()
			->getResult();
		
	}
	
	private function _just($value){
		$list = $this->_Masterdocument->searchBy([
				[
						Crud::SEARCHBY_FIELD => Masterdocument::ID_MD,
						Crud::SEARCHBY_VALUE => $value
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
			
			//Utils::printr("BeforeSignatureCheck");
			//Utils::printr($this->_resultArray);
				
			
			$signRoles = $this->_userManager->getUserSign()->getSignatureRoles();
			$mySignature = $this->_userManager->getUserSign()->getSignature();
			$mySpecialSignatures = $this->_userManager->getUserSign()->getSpecialSignatures();
			
			$XMLParser = new XMLParser();
			
			foreach($this->_resultArray[self::LABEL_MD] as $id_md => $md){
				$docsToBeSigned = [];
				$xml = $this->_XMLDataSource->getSingleXmlByFilename($md[Masterdocument::XML]);
				$XMLParser->setXMLSource($xml[XMLDataSource::LABEL_XML], $md[Masterdocument::TYPE]);
				
				foreach($this->_resultArray[self::LABEL_DOCUMENTS][$id_md] as $id_doc => $document){
					
					$docName = $document[Document::NOME];
					$docType = $document[Document::TYPE];
					
					$filename =
					$this->_resultArray[self::LABEL_MD][$id_md][Masterdocument::FTP_FOLDER] .
					Common::getFolderNameFromMasterdocument(
							$this->_resultArray[self::LABEL_MD][$id_md]
					) .
					DIRECTORY_SEPARATOR .
					$this->_resultArray[self::LABEL_DOCUMENTS][$id_md][$id_doc][self::FTP_NAME];
					
					$alreadyLoaded = false;
						
					$signatures = $XMLParser->getDocumentSignatures($docName, $docType);
					$specialSignatures = $XMLParser->getDocumentSpecialSignatures($docName, $docType);
					
					
					if(!SignatureChecker::emptySignatures($signatures->signature)){
						foreach($signatures->signature as $signature){
							
							$role = (string) $signature[XMLParser::ROLE];
							
							if(isset($signRoles[$role])){
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
									$alreadyLoaded = true;
									$this->_SignatureChecker->load($filename);
									
									if($this->_SignatureChecker->checkSignature($mySignature)){
										$this->_resultArray[self::LABEL_DOCUMENTS][$id_md][$id_doc][self::IS_SIGNED_BY_ME] = 1;
									} else {
										if(!in_array($id_doc, $docsToBeSigned))
											array_push($docsToBeSigned, $id_doc);
									}
									
								}
							}
						}
					}
					
					if( $this->_resultArray[self::LABEL_DOCUMENTS][$id_md][$id_doc][self::MUST_BE_SIGNED_BY_ME] &&
					   !$this->_resultArray[self::LABEL_DOCUMENTS][$id_md][$id_doc][self::IS_SIGNED_BY_ME])
						continue;
					   
					if(!SignatureChecker::emptySignatures($specialSignatures->specialSignature) && !empty($mySpecialSignatures)){
						foreach($specialSignatures->specialSignature as $specialSignature){
					   		$type = (string) $specialSignature[XMLParser::TYPE];
					   		
					   		if(!isset($mySpecialSignatures[$type]))
					   			continue;
					   		
					   		if(!$alreadyLoaded)
					   			$this->_SignatureChecker->load($filename);
					   		
					   		// Se è già firmato da me lo marco e vado avanti.
					   		if ($this->_SignatureChecker->checkSignature($mySpecialSignatures[$type])){
					   			
					   			$this->_resultArray[self::LABEL_MD][$id_md][self::IS_MY_DOC] = 1;
					   			$this->_resultArray[self::LABEL_DOCUMENTS][$id_md][$id_doc][self::MUST_BE_SIGNED_BY_ME] = 1;
					   			$this->_resultArray[self::LABEL_DOCUMENTS][$id_md][$id_doc][self::IS_SIGNED_BY_ME] = 1;
					   			break;
					   		}
					   		
					   		// Se sono arrivato qui controllo se è stato firmato da altri.
					   		if(isset($this->_allSpecialSignatures[$type])){
					   				
					   			$listOfSpecialSigners = $this->_allSpecialSignatures[$type];
					   				
					   			$signerFound = false;
					   			foreach($listOfSpecialSigners as $specialSigner){
					   		
					   				$result = $this->_SignatureChecker->checkSignature($specialSigner[SpecialSignatures::PKEY]);
					   		
					   				if($result){
					   					$signerFound = true;
					   					//Utils::printr("Domanda firmata da altri");
					   					break;
					   				}
					   		
					   			}
					   				
					   			// Se sono qui allora è umn documento che posso firmare io
					   			if(!$signerFound){
					   				
					   				$this->_resultArray[self::LABEL_MD][$id_md][self::IS_MY_DOC] = 1;
					   				$this->_resultArray[self::LABEL_DOCUMENTS][$id_md][$id_doc][self::MUST_BE_SIGNED_BY_ME] = 1;
					   		
					   				if(!in_array($id_doc, $docsToBeSigned))
					   					array_push($docsToBeSigned, $id_doc);
					   			}
					   		
					   		}
					   	}
					}
					
				}
				
				/*
				
				if($isSigner){
				
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
								$filename =
								$this->_resultArray[self::LABEL_MD][$id_md][Masterdocument::FTP_FOLDER] .
								Common::getFolderNameFromMasterdocument(
										$this->_resultArray[self::LABEL_MD][$id_md]
								) .
								DIRECTORY_SEPARATOR .
								$this->_resultArray[self::LABEL_DOCUMENTS][$id_md][$id_doc][self::FTP_NAME];
								
								$this->_SignatureChecker->load($filename);
								
								$this->_checkIfSigned($docsToBeSigned, $id_md, $id_doc, $mySignature);
							} 
						}
					}
					//$this->_resultArray[self::LABEL_MD][$id_md][self::DOC_TO_SIGN_INSIDE] = count($docsToBeSigned);
				}
				
				if($isSpecialSigner){
					foreach($isSpecialSigner as $type=>$listOfDocTypes){
						$listOfDocTypes = array_unique(array_values($listOfDocTypes));
						$iddocToInspect = $this->_filterDocByDocType($listOfDocTypes,$id_md);
						foreach($iddocToInspect as $id_doc){
							// Se risulta già da firmare da me skippo tutti i controlli
							if(	$this->_resultArray[self::LABEL_DOCUMENTS][$id_md][$id_doc][self::MUST_BE_SIGNED_BY_ME] &&
								!$this->_resultArray[self::LABEL_DOCUMENTS][$id_md][$id_doc][self::IS_SIGNED_BY_ME])
								continue;
									
							$filename =
							$this->_resultArray[self::LABEL_MD][$id_md][Masterdocument::FTP_FOLDER] .
							Common::getFolderNameFromMasterdocument(
									$this->_resultArray[self::LABEL_MD][$id_md]
							) .
							DIRECTORY_SEPARATOR .
							$this->_resultArray[self::LABEL_DOCUMENTS][$id_md][$id_doc][self::FTP_NAME];
								
							$this->_SignatureChecker->load($filename);
								
							// Se è già firmato da me lo marco e vado avanti.
							if ($this->_SignatureChecker->checkSignature($mySpecialSignatures[$type])){
								$this->_resultArray[self::LABEL_MD][$id_md][self::IS_MY_DOC] = 1;
								$this->_resultArray[self::LABEL_DOCUMENTS][$id_md][$id_doc][self::MUST_BE_SIGNED_BY_ME] = 1;
								$this->_resultArray[self::LABEL_DOCUMENTS][$id_md][$id_doc][self::IS_SIGNED_BY_ME] = 1;
								continue;
							}
								
							// Se sono arrivato qui controllo se è stato firmato da altri.
							if(isset($this->_allSpecialSignatures[$type])){
									
								$listOfSpecialSigners = $this->_allSpecialSignatures[$type];
									
								$signerFound = false;
								foreach($listOfSpecialSigners as $specialSigner){
										
									$result = $this->_SignatureChecker->checkSignature($specialSigner[SpecialSignatures::PKEY]);
										
									if($result){
										$signerFound = true;
										break;
									}
				
								}
									
								if(!$signerFound){
				
									$this->_resultArray[self::LABEL_MD][$id_md][self::IS_MY_DOC] = 1;
									$this->_resultArray[self::LABEL_DOCUMENTS][$id_md][$id_doc][self::MUST_BE_SIGNED_BY_ME] = 1;
										
									if(!in_array($id_doc, $docsToBeSigned))
										array_push($docsToBeSigned, $id_doc);
								}
				
							}
						}
					}
						
				}
				*/
									
				// Riepilogo del numero di documenti da firmare all'interno del mio Master Document;
				$this->_resultArray[self::LABEL_MD][$id_md][self::DOC_TO_SIGN_INSIDE] = count($docsToBeSigned);
				
				
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
		//$myProjects = $this->_userManager->getUser()->getProgetti();
		
		foreach($this->_resultArray[self::LABEL_MD_DATA] as $id_md => $md_data){
			// se è già stata assegnata la proprietà ed è già mio saltro tutto 
			if( isset($this->_resultArray[self::LABEL_MD][$id_md][self::IS_MY_DOC]) && 
				$this->_resultArray[self::LABEL_MD][$id_md][self::IS_MY_DOC]){
				continue;
			}
			
			// Se sono "proprietario" in quanto destinatario, anche senza un riolo specifico, lo vedo
			foreach($inputFields as $key){
				if(isset($md_data[$key]) && $md_data[$key] == $uid){
					$this->_resultArray[self::LABEL_MD][$id_md][self::IS_MY_DOC] = 1;
					continue 2;
				}
			}
			
			// Se non ho ruoli taglio
			if(!$this->_userManager->getUserRole()){
				$this->_purge($id_md);
				continue;
			}
				
			// Se sono gestore lo vedo sempre
			if($this->_userManager->isGestore(true)){
				$this->_resultArray[self::LABEL_MD][$id_md][self::IS_MY_DOC] = 1;
				continue;
			}
			
			// Se sono consultatore controllo che i MD siano legati a
			// - i msiei gruppi
			// Se si, segno la proprietà e salto il resto
			if($this->_userManager->isConsultatore()){
				foreach($md_data as $k=>$v){
					if(in_array($v,$myServices)/* || in_array($v, $myProjects)*/){
						$this->_resultArray[self::LABEL_MD][$id_md][self::IS_MY_DOC] = 1;
						continue 2;
					}
				}
			}	
			
			
				
			
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
				Crud::SEARCHBY_FIELD => Masterdocument::CLOSED,
				Crud::SEARCHBY_VALUE => ProcedureManager::OPEN
			]
		]);
			
		return Utils::getListfromField($list,null,Masterdocument::ID_MD);
	}
	
	private function _closedDocuments(){
		$list = $this->_Masterdocument->searchBy([
			[
				Crud::SEARCHBY_FIELD => Masterdocument::CLOSED,
				Crud::SEARCHBY_VALUE => ProcedureManager::OPEN,
				Crud::SEARCHBY_OPERATOR => "!="
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
					$this->_MasterdocumentData->getBy ( MasterdocumentData::ID_MD, $md_ids), MasterdocumentData::ID_MD 
				)
			);
				
			$documents = Utils::getListfromField ( 
				$this->_Document->getBy ( Document::ID_MD, $md_ids ), null, Document::ID_DOC 
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
						$this->_DocumentData->getBy ( DocumentData::ID_DOC, array_keys ( $documents )  ), DocumentData::ID_DOC 
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
	
// 	private function _addTextAreaFilters( &$dataFilters, $values){
// 		array_push ( $dataFilters, [ 
// 				Crud::SEARCHBY_OPEN_BRACKET => true 
// 		] );
		
// 		foreach ( $values as $n => $term ) {
// 			array_push ( $dataFilters, [ 
// 					Crud::SEARCHBY_INSIDE_BRACKET => true,
// 					Crud::SEARCHBY_FIELD => AnyDocumentData::VALUE,
// 					Crud::SEARCHBY_VALUE => "%" . $term . "%",
// 					Crud::SEARCHBY_OPERATOR => "ilike",
// 					Crud::SEARCHBY_INSIDE_OPERATOR => "OR" 
// 			] );
// 		}
// 		array_push ( $dataFilters, [ 
// 				Crud::SEARCHBY_CLOSE_BRACKET => true,
// 				Crud::SEARCHBY_OPERATOR => "OR" 
// 		] );
		
// 		array_push ( $dataFilters, [ 
// 				Crud::SEARCHBY_CLOSE_BRACKET => true,
// 				Crud::SEARCHBY_END_BRACKET => true 
// 		]
// 		 );
// 	}
	
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