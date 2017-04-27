<?php 
class Responder{
	private $_XMLBrowser, $_ownerRules;
	private $_Masterdocument, $_MasterdocumentData;
	private $_Document, $_DocumentData;
	private $_FTPConnector;
	private $_PDFParser;
	private $_md = array(), $_md_data = array();
	private $_documents = array(), $_documents_data = array();
	
	public function __construct(){
		$this->_XMLBrowser = XMLBrowser::getInstance();
		$this->_Masterdocument = new Masterdocument(Connector::getInstance());
		$this->_MasterdocumentData = new MasterdocumentData(Connector::getInstance());
		$this->_Document = new Document(Connector::getInstance());
		$this->_DocumentData = new DocumentData(Connector::getInstance());
		$this->_FTPConnector = FTPConnector::getInstance();
		$this->_PDFParser = new PDFParser();
		
		$this->_ownerRules = (array)simplexml_load_file(FILES_PATH."ownerRules.xml")->inputField;
	}
	
	public function firstCleanList($filters){
		// Step1. Lista di md di cui uno è proprietario
		$key_value = array();
		foreach($this->_ownerRules as $inputField){
			$key_value[$inputField] = (string)PermissionHelper::getInstance()->getUserId();
		}
		$md_data = $this->_MasterdocumentData->searchByKeyValue($key_value,null,'OR');
		$ids_md = Utils::getListfromField($md_data, "id_md");
		$this->_md = $this->_Masterdocument->getBy("id_md",join(",", array_values($ids_md)));
		
		// Step 2. Lista di md visibili in base al ruolo
		$xmlList = array_keys($this->_XMLBrowser->getXmlList(false, PermissionHelper::getInstance()->getUserField('gruppi')));
		$xmlList = array_map("Utils::apici",$xmlList);
		
		// Unione di questi due elenchi
		if(count($xmlList)) $this->_md = array_merge($this->_md, $this->_Masterdocument->getBy("xml",join(",", $xmlList)));
		
		if(count($filters)>0){
			foreach($filters as $k=>$rule){
				if(!isset($rule['operator']))
					$rule['operator'] = Utils::OP_EQUAL;
				$this->_md = Utils::filterList($this->_md, $rule['field'], $rule['value'], $rule['operator']);
			}
		} else {
			$this->_md = Utils::filterList($this->_md, 'closed', 0); // Di default ho quelli aperti
		}
		
		
	}
	
	
	public function createDocList($filters = array()){
		// Step1. Lista di md di cui uno è proprietario + elenco di md visibili in base al ruolo
		$this->firstCleanList($filters);
				
		// Creo l'albero di documenti
		$this->_md = Utils::getListfromField($this->_md,null,"id_md");
		
		$md_ids = array_keys($this->_md);
		if(count($md_ids)){
			$this->_md_data = $this->_compact(Utils::groupListBy($this->_MasterdocumentData->getBy("id_md", join(",",$md_ids)), "id_md"));
			$documents = Utils::getListfromField($this->_Document->getBy("id_md", join(",",$md_ids)), null, "id_doc");
			if(!empty ($documents)){
				foreach($documents as $k=>$document){
					$documents[$k]['ftp_name'] = FormHelper::fieldFromLabel($documents[$k]['nome']." ".$documents[$k]['id_doc'].".".$documents[$k]['extension']);
				}
				$this->_documents = Utils::groupListBy($documents,"id_md");
				$this->_documents_data = $this->_compact(Utils::groupListBy($this->_DocumentData->getBy("id_doc", join(",",array_keys($documents))),"id_doc"));
			} 
		}
		
		//Step 2. Se sono firmatario dovrei vedere anche quelli hanno o necessitano la mia firma
		$this->getMyMasterDocumentsWithSign();
		
	}
	
	public function getMyMasterDocuments($filters = array()){
		$this->createDocList($filters);
		
		foreach($this->_md as $id=>$item){
			$type = dirname($item['xml']);
			$this->_md[$type][$item['nome']][$id] = $item;
			unset($this->_md[$id]);
		}
		
		return array(
			'md' => $this->_md,
			'md_data' => $this->_md_data,
			'documents' => $this->_documents,
			'documents_data' => $this->_documents_data
		);
	}
	
	private function getMyMasterDocumentsWithSign(){
		if(PermissionHelper::getInstance()->isSigner()){
			//Utils::printr("Sono un firmatario");
			$roles_signatures = PermissionHelper::getInstance()->getUserSign(); //(Ruolo => dati firma )
			//Utils::printr($roles_signatures);
			
			//Utils::printr($this->_md);
			foreach($this->_md as $id_md => $md){
				$xml = XMLBrowser::getInstance()->getSingleXml($md['xml']);
				XMLParser::getInstance()->setXMLSource($xml,$md['type']);
				
				$docList = Utils::filterList($this->_documents[$id_md],'cloded',0); //solo i documenti non chiusi

				//Utils::printr($docList);
				
				if(count($docList)){
					foreach($docList as $id_doc=>$document){
						
						$filename = $md['ftp_folder']. DIRECTORY_SEPARATOR. $md['nome']. "_". $id_md . DIRECTORY_SEPARATOR . $document['ftp_name'];
						
						//Utils::printr($filename." to check");
						
						$xmlDoc = XMLParser::getInstance()->getDocByName($document['nome']);
						
						// Di default non deve essere firmato da me e non devo firmarlo;
						$this->_documents[$id_md][$id_doc]['mustBeSigned'] = 0;
						$this->_documents[$id_md][$id_doc]['signed'] = 0;
						if(count($xmlDoc->signatures->signature)){
							$found = 0;
							foreach($xmlDoc->signatures->signature as $signature){
								//Utils::printr("This document must be signed by {$signature['role']}");
								if(isset($roles_signatures['signRoles'][(string)$signature['role']])){
									if($roles_signatures['signRoles'][(string)$signature['role']]['fixed_role']){
										$this->_documents[$id_md][$id_doc]['mustBeSigned'] = 1;
										//Utils::printr("I'm the {$signature['role']}");
										// è un ruolo fisso, devo verificarlo sempre
										if($this->_checkSignature($filename,$roles_signatures['mySignature']))
											$found++;
									} else {
										// è un ruolo variabile, devo firmare solo se definito negli inputs del master document
										//Utils::printr("Ok, I'm a possible {$signature['role']}");
											
										$inputToFind = $roles_signatures['signRoles'][(string)$signature['role']]['descrizione'];
										//Utils::printr("Need to find $inputToFind in inputs");
										if(isset($this->_md_data[$id_md][$inputToFind])){
											//Utils::printr("found in the inputs");
											if($this->_md_data[$id_md][$inputToFind] == PermissionHelper::getInstance()->getUserId()){
												//Utils::printr("Ok, I'm the signer");
												$this->_documents[$id_md][$id_doc]['mustBeSigned'] = 1;
											}
											//Utils::printr($this->_documents[$id_md]);
											if($this->_checkSignature($filename,$roles_signatures['mySignature'])){
												//Utils::printr("already signed");
												$found++;
											}
										} 
									}
								} 
							}
							if($found>0){
								$this->_documents[$id_md][$id_doc]['signed'] = 1;
							}
						} 
					}
				} 
			}
		}	
		
	}
	
	public function actions($info, $id_doc){
		// Posso scaricare un documento se il documento fa perte di quelli che posso vedere o che posso scaricare:
		$docICanSee = $this->getMyMasterDocuments([['field' => 'id_md', 'value' => $info['md']['id_md']]]);
		
		// Il documento può essere sempre scaricato se visibile dall'utente
		$canDownload = isset($docICanSee['documents'][$info['md']['id_md']][$id_doc]);
		
		// Il documento può essere caricato solo se:
		// 1. Il documento non è chiuso;
		$isDocumentstillOpen = $info['documents'][$id_doc]['closed'] == 0;
		
		// 2. L'utente può gestire il master document padre
		XMLParser::getInstance()->setXMLSource(XMLBrowser::getInstance()->getSingleXml($info['md']['xml']));
		$UserCanManageThis = PermissionHelper::getInstance()->isGestore(XMLParser::getInstance()->getOwner());
		
		// 3. L'utente ha firmato o deve firmare il documento.
		$DocMustBeSignedByUser = $info['documents'][$id_doc]['mustBeSigned'] == 0;
		
		// Quindi:
		$canUpload = $isDocumentstillOpen && ($UserCanManageThis || $DocMustBeSignedByUser);
		
		// L'utente può modificare le info solo se è gestore del master document padre
		return ['canDownload' => $canDownload, 'canUpload' => $canUpload, 'canEditInfo' => $UserCanManageThis];
	}
	
	private function _checkSignature($filename,$signature){
		//Utils::printr("Checking signature $signature");
		$tmpPDF = $this->_FTPConnector->getTempFile($filename);
		$this->_PDFParser->loadPDF($tmpPDF);
		$signaturesOnDocument = $this->_PDFParser->getSignatures();
		
		unlink($tmpPDF);
		
		//Utils::printr("found ".count($signaturesOnDocument)." signature(s).");
		
		if(count($signaturesOnDocument)){
			foreach($signaturesOnDocument as $sod){
				//Utils::printr($sod);
				if($sod->publicKey == $signature) return true;
			}
		} else return false;
		
	}
	
	public static function _purge(&$md, $id_md, $id_doc){
		unset($md['md_data'][$id_md], $md['documents'][$id_md],$md['documents_data'][$id_md]);
		foreach ($md['md'] as $category=>$subcategory){
			foreach($subcategory as $name => $md_data){
				unset($md['md'][$category][$name][$id_md]);
				if(count($md['md'][$category][$name]) == 0)
					unset($md['md'][$category][$name]);
			}
			if(count($md['md'][$category]) == 0) unset($md['md'][$category]);
		}
	}
	
	private function _compact($md_data){
		foreach($md_data as $id_md=>$data){
			$metadata = array();
			foreach($data as $input){
				$metadata[$input['key']] = $input['value'];
			}
			$md_data[$id_md] = $metadata;
		}
		return $md_data;
	}
	
	public function getSingleMasterDocument($id_md){
		$this->createDocList();
		
		if(!array_key_exists($id_md, $this->_md)) return false;
		
		$documents_data = array();
		
		if(count($this->_documents[$id_md])){
			foreach($this->_documents[$id_md] as $id_doc=>$data){
				$documents_data[$id_doc] = $this->_documents_data[$id_doc];
			}
		}
		
		return array(
			'md' => $this->_md[$id_md],
			'md_data' => $this->_md_data[$id_md],
			'documents' => $this->_documents[$id_md],
			'documents_data' => $documents_data
		);
	}
}