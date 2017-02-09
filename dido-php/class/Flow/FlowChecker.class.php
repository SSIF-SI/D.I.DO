<?php 
class FlowChecker extends ClassWithDependencies{
	const FILE_REGEX = "^([A-Za-z_\s]{1,})(_[0-9]{1,}){0,1}(\.pdf)$";
	
	private $_Masterdocument;
	private $_Signature;
	private $_XMLParser, $_XMLBrowser;
	private $_FTPConnector;
	private $_PDFParser;
	private $_Personale;
	
	public function __construct(){
		$this->_Masterdocument = new Masterdocument(Connector::getInstance());
		$this->_MasterdocumentData = new MasterdocumentData(Connector::getInstance());
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

			$Responder = new Responder();
			$return['data']['info'] = $Responder->getSingleMasterDocument($id['id_md']);
			
			$sigObj = new Signature(Connector::getInstance());
			$signers = $sigObj->getSigners($id['id_md'],$return['data']['info']['md_data']);

			// Parsing con XML (documenti richiesti)
			$this->_XMLParser->setXMLSource($this->_XMLBrowser->getSingleXml($md['xml']),$md['type']);
			$return['data']['xml_inputs'] = $this->_XMLParser->getMasterDocumentInputs();
			
			// Connessione FTP (documenti esistenti)
			//$md['path'] = $md['ftp_folder']. DIRECTORY_SEPARATOR. $md['nome']. "_". $id[key($id)];
			
			//$fileList = Utils::filterList($this->_FTPConnector->getContents($md['path'])['contents'],'isPDF',1);
			//$fileList = Utils::getListfromField($fileList, 'filename');
			
			//Utils::printr($fileList);
			//$fileList = array("ordine di missione.pdf","allegato_1.pdf","allegato_2.pdf");
			
			// Confronto liste con check su firme e quant'altro
			foreach($this->_XMLParser->getDocList() as $document){
				if(!is_null($document['load'])){
					$defaultXml = XML_STD_PATH . (string)$document['load'];
					$document = simplexml_load_file($defaultXml);
				}
				
				$docResult = new FlowCheckerResult();
				
				if(!is_null($document['md'])){
					// è un documento di tipo esterno (masterDocument)	
					// TODO: controllare se esiste e appendere il risulktato
				} else {
					$docResult->documentName = (string)$document['name'];
					$docResult->inputs = $document->inputs->input;
					$files = Utils::getListfromField(Utils::filterList($return['data']['info']['documents'], "nome", $docResult->documentName),"file_name");
					
					foreach($document->attributes() as $k=>$attr){
						$f_name = "_check_$k";
						if(method_exists(__CLASS__, $f_name)){
							self::$f_name($document['name'],array_map("basename", $files),$attr,$docResult);
						}
					}
					
					if(!is_null($document->signatures->signature) && empty($docResult->errors)){
						// Conbtrollo el firme secondo i seguewnti step:
						
						//$files = self::_getFtpFiles($docResult->documentName, $fileList);
						foreach($files as $k=>$filename){
							
							// $filename = $md['path'].DIRECTORY_SEPARATOR.$file;
							$result = $this->_checkSignatures($filename, $document, $signers, $k, $docResult, $return['data']['info']['md_data']);
							$docResult->signatures[$k] = $result;
							//$docResult->docData[$k] = isset($document_data[$file]) ? $document_data[$file] : null;
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
	
	private function _checkSignatures($filename, $document, $signers, $k, &$docResult, $md_data){
		$tmpPDF = $this->_FTPConnector->getTempFile($filename);
		$this->_PDFParser->loadPDF($tmpPDF);
		$signaturesOnDocument = $this->_PDFParser->getSignatures();
		
		unlink($tmpPDF);
	
		$checkResult = array();
		$sigRoles = new SignersRoles(Connector::getInstance());
		$sigRoles = Utils::getListfromField($sigRoles->getAll(),null,"sigla");
		
		foreach($document->signatures->signature as $signature){
			$who = (string)$signature['role'];
			$persona = $this->_Personale->getPersona($signers[$who]['id_persona']);
				
			if($who == "REQ") {
				$checkResult[$who]['result'] = 'skipped';
				continue;
			}
			
			if(!isset($signers[$who])){
				$docResult->errors[$k][] = 'Manca la firma del '.$sigRoles[$who]['descrizione'].' nel sistema DIDO!!!';
				$persona = Personale::getInstance()->getPersona($md_data[$sigRoles[$who]['descrizione']]);
				$checkResult[$who]['who'] = "<em>(Manca la firma nel sistema DIDO)</em><br/><br/>{$persona['nome']} {$persona['cognome']}";
				$checkResult[$who]['role'] = $sigRoles[$who]['descrizione'];
				$checkResult[$who]['result'] = false;
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
		$docName = FormHelper::fieldFromLabel($docName);
		$files = array();
		foreach($fileList as $k=>$file){
			preg_match("/".self::FILE_REGEX."/", $file,$fileInfo);
			if ($fileInfo[1]== $docName){
				$files[$k] = $fileInfo[0];
			}
		}
		return $files;
	}
}
?>