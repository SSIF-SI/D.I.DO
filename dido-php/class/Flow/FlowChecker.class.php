<?php 
class FlowChecker{
	const FILE_REGEX = "^([A-Za-z_\s]{1,})(_[0-9]{1,}){0,1}(\.pdf)$";
	
	private $_Masterdocument;
	private $_Signature;
	private $_XMLParser, $_XMLBrowser;
	private $_FTPConnector;
	private $_PDFParser;
	private $_Personale;
	
	public function __construct(){
		$this->_Masterdocument = new Masterdocument(Connector::getInstance());
		$this->_XMLParser = XMLParser::getInstance();
		$this->_Signature = new Signature(Connector::getInstance());
		$this->_XMLBrowser = XMLBrowser::getInstance();
		$this->_FTPConnector = FTPConnector::getInstance();
		$this->_PDFParser = new PDFParser();
		$this->_Personale = Personale::getInstance();
	}
	
	public function setDependency($key,$value){
		$this->__set($key,$value);
	}
	
	public function __set($key, $value){
		if(property_exists($this, $key))
			$this->$key = $value;
	}
	
	public function checkMasterDocument(array $id){

		$md = $this->_Masterdocument->get($id);
		$signers = $this->_Signature->getSigners($id['id_md']);
		
		$return = array();

		if($md){
		
			//Utils::printr($this->_md);
			// Parsing con XML (documenti richiesti)
			$this->_XMLParser->setXMLSource($this->_XMLBrowser->getSingleXml($md['xml']),$md['type']);
			
			// Connessione FTP (documenti esistenti)
			$fileList = Utils::filterList($this->_FTPConnector->getContents($md['ftp_folder'])['contents'],'isPDF',1);
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
							$filename = $md['ftp_folder'].$file;
							$result = $this->_checkSignatures($filename, $document, $signers, $k, $docResult);
							$docResult->signatures[$k] = $result;
						}
					}
				}
				$return[$docResult->documentName] = $docResult;
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