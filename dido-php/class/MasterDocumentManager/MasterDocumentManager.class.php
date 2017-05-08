<?php
class MasterDocumentManager extends ClassWithDependencies {
	/*
	 * Questa classe si occuperà di fornire all'applicazione l'elenco completo dei Master document e relativi dati allegati che l'utente potrà visualizzare in base ai permessi che ha. Il formato di uscita sarà un array associativo come segue: Array ( [md] 				=> [id_md => record masterdocument] [md_data]			=> [id_md => [md_inputs]] [documents] 		=> [id_md => [id_doc => record document]] [documents_data]	=> [id_doc => [document_inputs]] )
	 */

	/*
	 * L'array dove verrà memorizzato il risultato 
	 */	
	private $_resultArray;
	
	/*
	 * Il connettore
	 */
	private $_DBConnector;
	
	/*
	 * Le variabili delle classi per recuperare dati dal DB
	 */
	private $_Masterdocument, $_MasterdocumentData, $_Document, $_DocumentData;
	
	/*
	 * Devo accedere anche ai permessi dell'utente
	 */
	private $_PermissionHelper;
	
	/*
	 * Mi serve il browser XML
	 */
	private $_XMLBrowser;
	/*
	 * Dovrò fare accessi anche in ftp
	 */
	private $_FTPConnector;
	/*
	 * odvrò controllare le firme ? non lo so ma lo metto
	 */
	private $_PDFParser;
	public function __construct() {
		$this->_DBConnector = DBConnector::getInstance ();
		
		$this->_Masterdocument = new Masterdocument ( $this->_DBConnector );
		$this->_MasterdocumentData = new MasterdocumentData ( $this->_DBConnector );
		$this->_Document = new Document ( $this->_DBConnector );
		$this->_DocumentData = new DocumentData ( $this->_DBConnector );
		
		$this->_PermissionHelper = PermissionHelper::getInstance ();
		
		$this->_XMLBrowser = XMLBrowser::getInstance ();
		$this->_FTPConnector = FTPConnector::getInstance ();
		$this->_PDFParser = new PDFParser ();
	}
	public function getAllMyMasterDocuments(Array $filters = [], $categorized = true) {
		// Resettiamo l'array
		$this->_emptyResult ();
		
		// Se non ci sono filtri la ricerca la faccio solo sui master document aperti
		// impostando come chiave gli id dei MD
		
		if (empty ( $filters ))
			$mdToSearch = $this->_Masterdocument->getBy ( 'closed', 0 );
		else {
			$mdFilter = new MasterDocumentsFilter ( $filters );
			$mdToSearch = $mdFilter->applyFilter ( $this->_Masterdocument->getAll () );
		}
		
		$mdToSearch = Utils::getListfromField ( $mdToSearch, null, 'id_md' );
		$docToSearch = array ();
		
		// Step 1. Tutti i documenti di cui l'utente è proprietario
		// ossia quelli i cui campi di input relativi al MD, definiti nel file ownerRules.xml
		// hanno come valore l'id dell'utente
		$key_value = array ();
		
		$ownerRules = new OwnerRules();
		$inputOwnerRules=$ownerRules->getInputField();
		foreach ( $inputOwnerRules as $inputField ) {
			$key_value [$inputField] = ( string ) $this->_PermissionHelper->getUserId ();
		}
		
		$md_data = $this->_MasterdocumentData->searchByKeyValue ( $key_value, null, 'OR' );
		if (count ( $md_data )) {
			$ids_md = Utils::getListfromField ( $md_data, "id_md" );
			// Utils::printr($ids_md);
			// Utils::printr(array_intersect($ids_md, array_keys($mdToSearch)));
			$otherMDS = array_intersect ( $ids_md, array_keys ( $mdToSearch ) );
			if (count ( $otherMDS ))
				$this->_resultArray ['md'] = $this->_Masterdocument->getBy ( "id_md", join ( ",", $otherMDS ) );
		}
		
		// Step 2. Lista di MD visibili in base al tipo e ai permessi utente,
		// ossia in base agli xml in cui sono proprietario o comunque ho visibilità
		// TODO:Gestione XML
		$xmlList = array_keys ( $this->_XMLBrowser->getXmlList ( false, PermissionHelper::getInstance ()->getUserField ( 'gruppi' ) ) );
		if (count ( $xmlList )) {
			$mdToSearchXML = array_intersect ( Utils::getListfromField ( $mdToSearch, 'xml' ), $xmlList );
			$otherMDS = array_intersect_key ( $mdToSearch, $mdToSearchXML );
			// $this->_resultArray['md'] = array_merge($this->_resultArray['md'], $this->_Masterdocument->getBy("xml",join(",", array_map("Utils::apici",$xmlList))));
			if (count ( $otherMDS ))
				$this->_resultArray ['md'] = array_merge ( $this->_resultArray ['md'], $otherMDS );
		}
		
		// Step 3. Se l'utente è firmatario, lista di MD che hanno al loro interno
		// documenti firmati o da firmare
		
		if ($this->_PermissionHelper->isSigner () && count ( $mdToSearch )) {
			$roles_signatures = $this->_PermissionHelper->getUserSign ();
			$docToSearch = Utils::groupListBy ( Utils::getListfromField ( $this->_Document->getBy ( "id_md", join ( ",", array_keys ( $mdToSearch ) ) ), null, 'id_doc' ), 'id_md' );
			$XMLParser = new XMLParser ();
			
			foreach ( $mdToSearch as $id_md => $md ) {
				$xml = XMLBrowser::getInstance ()->getSingleXml ( $md ['xml'] );
				$XMLParser->setXMLSource ( $xml, $md ['type'] );
				
				$docList = $docToSearch [$id_md];
				
				// Utils::printr($docList);
				
				if (count ( $docList )) {
					foreach ( $docList as $id_doc => $document ) {
						
						$filename = $md ['ftp_folder'] . DIRECTORY_SEPARATOR . $md ['nome'] . "_" . $id_md . DIRECTORY_SEPARATOR . FormHelper::fieldFromLabel ( $document ['nome'] . " " . $document ['id_doc'] . "." . $document ['extension'] );
						
						// Utils::printr($filename." to check");
						
						$xmlDoc = $XMLParser->getDocByName ( $document ['nome'] );
						
						if (count ( $xmlDoc->signatures->signature )) {
							$found = 0;
							foreach ( $xmlDoc->signatures->signature as $signature ) {
								// Utils::printr("This document must be signed by {$signature['role']}");
								if (isset ( $roles_signatures ['signRoles'] [( string ) $signature ['role']] )) {
									if ($roles_signatures ['signRoles'] [( string ) $signature ['role']] ['fixed_role']) {
										$docToSearch [$id_md] [$id_doc] ['mustBeSigned'] = 1;
										// Utils::printr("I'm the {$signature['role']}");
										// è un ruolo fisso, devo verificarlo sempre
										if ($this->_checkSignature ( $filename, $roles_signatures ['mySignature'] ))
											$found ++;
									} else {
										// è un ruolo variabile, devo firmare solo se definito negli inputs del master document
										// Utils::printr("Ok, I'm a possible {$signature['role']}");
										
										$inputToFind = $roles_signatures ['signRoles'] [( string ) $signature ['role']] ['descrizione'];
										// Utils::printr("Need to find $inputToFind in inputs");
										$md_data = $this->_compact ( Utils::groupListBy ( $this->_MasterdocumentData->getBy ( "id_md", $id_md ), "id_md" ) );
										;
										if (isset ( $md_data [$id_md] [$inputToFind] )) {
											// Utils::printr("found in the inputs");
											if ($md_data [$id_md] [$inputToFind] == PermissionHelper::getInstance ()->getUserId ()) {
												// Utils::printr("Ok, I'm the signer");
												$docToSearch [$id_md] [$id_doc] ['mustBeSigned'] = 1;
											}
											// Utils::printr($this->_documents[$id_md]);
											if ($this->_checkSignature ( $filename, $roles_signatures ['mySignature'] )) {
												// Utils::printr("already signed");
												$found ++;
											}
										}
										unset ( $md_data );
									}
								}
							}
							if ($docToSearch [$id_md] [$id_doc] ['mustBeSigned']) {
								$docToSearch [$id_md] [$id_doc] ['signed'] = $found > 0 ? 1 : 0;
							} else
								unset ( $docToSearch [$id_md] );
						} else
							unset ( $docToSearch [$id_md] );
					}
				}
			}
			if (count ( $docToSearch )) {
				$otherMDS = array_intersect_key ( $mdToSearch, $docToSearch );
				$this->_resultArray ['md'] = array_merge ( $this->_resultArray ['md'], $otherMDS );
			}
		}
		
		$this->_createResultTree ( $docToSearch );
		
		if ($categorized) {
			foreach ( $this->_resultArray ['md'] as $id => $item ) {
				$type = dirname ( $item ['xml'] );
				$this->_resultArray ['md'] [$type] [$item ['nome']] [$id] = $item;
				unset ( $this->_resultArray ['md'] [$id] );
			}
		}
		return $this->_resultArray;
	}
	public function _createResultTree($docListAlreadyExistent = null) {
		// Creo l'albero di documenti
		$this->_resultArray ['md'] = Utils::getListfromField ( $this->_resultArray ['md'], null, "id_md" );
		
		$md_ids = array_keys ( $this->_resultArray ['md'] );
		if (count ( $md_ids )) {
			$this->_resultArray ['md_data'] = $this->_compact ( Utils::groupListBy ( $this->_MasterdocumentData->getBy ( "id_md", join ( ",", $md_ids ) ), "id_md" ) );
			
			$documents = Utils::getListfromField ( $this->_Document->getBy ( "id_md", join ( ",", $md_ids ) ), null, "id_doc" );
			if (! empty ( $documents )) {
				foreach ( $documents as $k => $document ) {
					$documents [$k] ['mustBeSigned'] = 0;
					$documents [$k] ['signed'] = 0;
					
					// Se ho le info aggiuntive sulla firma sovrascrivo il documento
					if (isset ( $docListAlreadyExistent [$documents [$k] ['id_md']] [$k] ))
						$documents [$k] = $docListAlreadyExistent [$documents [$k] ['id_md']] [$k];
					
					$documents [$k] ['ftp_name'] = FormHelper::fieldFromLabel ( $documents [$k] ['nome'] . " " . $documents [$k] ['id_doc'] . "." . $documents [$k] ['extension'] );
				}
				$this->_resultArray ['documents'] = Utils::groupListBy ( $documents, "id_md" );
				$this->_resultArray ['documents_data'] = $this->_compact ( Utils::groupListBy ( $this->_DocumentData->getBy ( "id_doc", join ( ",", array_keys ( $documents ) ) ), "id_doc" ) );
			}
		}
	}
	public function getSingleMasterDocument($id_md) {
		$this->_emptyResult ();
		
		$this->getAllMyMasterDocuments ( array (
				[ 
						'field' => 'id_md',
						'value' => $id_md 
				] 
		), false );
		
		if (! empty ( $this->_resultArray ['md'] )) {
			$this->_createResultTree ();
			$documents_data = array ();
			
			if (count ( $this->_resultArray ['documents'] [$id_md] )) {
				foreach ( $this->_resultArray ['documents'] [$id_md] as $id_doc => $data ) {
					$documents_data [$id_doc] = $this->_resultArray ['documents_data'] [$id_doc];
				}
			}
			
			$this->_resultArray ['md'] = $this->_resultArray ['md'] [$id_md];
			$this->_resultArray ['md_data'] = $this->_resultArray ['md_data'] [$id_md];
			$this->_resultArray ['documents'] = $this->_resultArray ['documents'] [$id_md];
			$this->_resultArray ['documents_data'] = $documents_data;
		}
		
		return $this->_resultArray;
	}
	public static function _purge(&$md, $id_md, $id_doc) {
		unset ( $md ['md_data'] [$id_md], $md ['documents'] [$id_md], $md ['documents_data'] [$id_md] );
		foreach ( $md ['md'] as $category => $subcategory ) {
			foreach ( $subcategory as $name => $md_data ) {
				unset ( $md ['md'] [$category] [$name] [$id_md] );
				if (count ( $md ['md'] [$category] [$name] ) == 0)
					unset ( $md ['md'] [$category] [$name] );
			}
			if (count ( $md ['md'] [$category] ) == 0)
				unset ( $md ['md'] [$category] );
		}
	}
	private function _emptyResult() {
		$this->_resultArray = array (
				'md' => [ ],
				'md_data' => [ ],
				'documents' => [ ],
				'documents_data' => [ ] 
		);
	}
	
	// private function _filterMD($list, $filters){
	// foreach($filters as $k=>$rule){
	// if(!isset($rule['operator']))
	// $rule['operator'] = Utils::OP_EQUAL;
	// if(!isset($rule['field']) || !isset($rule['value']))
	// continue;
	// $list = Utils::filterList($list, $rule['field'], $rule['value'], $rule['operator']);
	// }
	// return $list;
	// }
	
	private function _checkSignature($filename, $signature) {
		// Utils::printr("Checking signature $signature");
		$tmpPDF = $this->_FTPConnector->getTempFile ( $filename );
		$this->_PDFParser->loadPDF ( $tmpPDF );
		$signaturesOnDocument = $this->_PDFParser->getSignatures ();
		
		unlink ( $tmpPDF );
		
		// Utils::printr("found ".count($signaturesOnDocument)." signature(s).");
		
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