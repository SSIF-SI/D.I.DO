<?php 
class FlowChecker extends ClassWithDependencies{
	const FILE_REGEX = "^([A-Za-z_\s]{1,})(_[0-9]{1,}){0,1}(\.pdf)$";
	
	private $_Masterdocument, $_MasterdocumentData;
	private $_Document, $_DocumentData;
	private $_Signature;
	private $_XMLParser, $_XMLBrowser;
	private $_FTPConnector;
	private $_PDFParser;
	private $_Personale;
	
	public function __construct(){
		$this->_Masterdocument = new Masterdocument(Connector::getInstance());
		$this->_MasterdocumentData = new MasterdocumentData(Connector::getInstance());
		$this->_Document = new Document(Connector::getInstance());
		$this->_DocumentData = new DocumentData(Connector::getInstance());
		$this->_XMLParser = XMLParser::getInstance();
		$this->_Signature = new Signature(Connector::getInstance());
		$this->_XMLBrowser = XMLBrowser::getInstance();
		$this->_FTPConnector = FTPConnector::getInstance();
		$this->_PDFParser = new PDFParser();
		$this->_Personale = Personale::getInstance();
	}
	
	public function checkMasterDocument(array $id){

		$md = $this->_Masterdocument->get($id);
		
		$return = array(
			'data' 		=> array(),
			'doclist'	=> array()
		);

		
		if($md){

			$md_data = Utils::getListfromField($this->_MasterdocumentData->getBy(key($id), $id[key($id)]), "value", "key");
			
			$pkD = $this->_Document->getPk();
			$id_doc_field = reset($pkD);
			
			$document = $this->_Document->getBy(key($id), $id[key($id)]);
			
			foreach($document as $doc){
				$document_data[$doc['file_name']] = Utils::getListfromField($this->_DocumentData->getBy($id_doc_field, $doc[$id_doc_field]), "value", "key");
			}
			
			$signers = $this->_Signature->getSigners($id[key($id)]);
			
			$return['data']['md_metadata'] = $md_data;
			
			//Utils::printr($this->_md);
			// Parsing con XML (documenti richiesti)
			$this->_XMLParser->setXMLSource($this->_XMLBrowser->getSingleXml($md['xml']),$md['type']);
			$return['data']['xml_inputs'] = $this->_XMLParser->getMasterDocumentInputs();
			
			// Connessione FTP (documenti esistenti)
			$md['path'] = $md['ftp_folder']. DIRECTORY_SEPARATOR. $md['nome']. "_". $id[key($id)];
			
			$fileList = Utils::filterList($this->_FTPConnector->getContents($md['path'])['contents'],'isPDF',1);
			$fileList = Utils::getListfromField($fileList, 'filename');
			
			//$fileList = array("ordine di missione.pdf","allegato_1.pdf","allegato_2.pdf");
			
			// Confronto liste con check su firme e quant'altro
			foreach($this->_XMLParser->getDocList() as $document){
				$docResult = new FlowCheckerResult();
				
				if(!is_null($document['load'])){
					$defaultXml = XML_STD_PATH . (string)$document['load'];
					$document = simplexml_load_file($defaultXml);
				}
				
				if(!is_null($document['md'])){
					// è un documento di tipo esterno (masterDocument)	
					// TODO: controllare se esiste e appendere il risulktato
				} else {
					$docResult->documentName = (string)$document['name'];
					foreach($document->attributes() as $k=>$attr){
						$f_name = "_check_$k";
						if(method_exists(__CLASS__, $f_name)){
							self::$f_name($document['name'],$fileList,$attr,$docResult);
						}
					}
					
					if(!is_null($document->signatures->signature) && empty($docResult->errors)){
						// Conbtrollo el firme secondo i seguewnti step:
						
						$files = self::_getFtpFiles($docResult->documentName, $fileList);
						
						foreach($files as $k=>$file){
							
							$filename = $md['path'].DIRECTORY_SEPARATOR.$file;
							$result = $this->_checkSignatures($filename, $document, $signers, $k, $docResult);
							$docResult->signatures[$k] = $result;
							$docResult->docData[$k] = isset($document_data[$file]) ? $document_data[$file] : null;
						}
					}
				}
				$return['doclist'][$docResult->documentName] = $docResult;
			}
			
			return $return;
		} else {
			// Eccezione
		}		
		
	}
	
	private static function _check_minOccur($docName, $fileList,$value, &$docResult){
		$docResult->mandatory = (int)$value;
			
		if($value > 0){
			self::_findFile($docName, $fileList, $docResult);
			if(count($docResult->found) < $value){
				$error = ($value-$found) == 1 ? "$docName assente" : ($value-$found)." documenti mancanti per $docName";
				$docResult->errors['missing'][] = $error;
			}
			
		} 
	}
	
	private static function _check_maxOccur($docName, $fileList,$value,&$docResult){
		$docResult->limit = (int)$value;
		if($value > 0){
			self::_findFile($docName, $fileList, $docResult);
			if(count($docResult->found) > $value){
				$docResult->errors['generic'][] = ($value-$found)." documenti in eccesso per $docName";
			}
		}
	}
	
	private function _checkSignatures($filename, $document, $signers, $k, &$docResult){
		$tmpPDF = $this->_FTPConnector->getTempFile($filename);

		$this->_PDFParser->loadPDF($tmpPDF);
		$signaturesOnDocument = $this->_PDFParser->getSignatures();
		
		unlink($tmpPDF);
	
		$checkResult = array();
		
		foreach($document->signatures->signature as $signature){
			$who = (string)$signature['role'];
			$persona = $this->_Personale->getPersona($signers[$who]['id_persona']);
				
			if($who == "REQ") {
				$checkResult[$who]['result'] = 'skipped';
				continue;
			}

			$checkResult[$who]['role'] = $signers[$who]['descrizione'];
				
			$pKeys = array($signers[$who]['id_persona'] => $signers[$who]['pkey'], $signers[$who]['id_delegato'] => $signers[$who]['pkey_delegato']);
			
			foreach ($signaturesOnDocument as $signatureData){
				$result = array_search($signatureData->publicKey,$pKeys); 
				if($result){
					if($result != $signers[$who]['id_persona']){
						$checkResult[$who]['role'] = "Delegato del {$signers[$who]['descrizione']}";
						$persona = $this->_Personale->getPersona($signers[$who]['id_delegato']);
					}
					break;
				}
			}
			
			$checkResult[$who]['who'] = "{$persona['nome']} {$persona['cognome']}";
				
				
			
			if(!$result){
				$docResult->errors[$k][] = "Manca la firma di {$persona['nome']} {$persona['cognome']} (".$signers[$who]['descrizione'].")";
			} 
			
			$checkResult[$who]['result'] = $result;
		}
		return $checkResult;
	}
	
	private static function _findFile($docName, $fileList, &$docResult){
		$docResult->found = self::_getFtpFiles($docName, $fileList);
	}
	
	private static function _getFtpFiles($docName, $fileList){
		$docName = str_replace(" ", "_", $docName);
		$files = array();
		foreach($fileList as $file){
			preg_match("/".self::FILE_REGEX."/", $file,$fileInfo);
			if ($fileInfo[1]== $docName){
				array_push($files,$fileInfo[0]);
			}
		}
		return $files;
	}
}
?>