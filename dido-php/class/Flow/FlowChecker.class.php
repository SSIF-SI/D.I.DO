<?php 
class FlowChecker{
	const FILE_REGEX = "^([A-Za-z_\s]{1,})(_[0-9]{1,}){0,1}(\.pdf)$";
	
	public static function checkMasterDocument(array $id){

		$masterDocument = new Masterdocument(Connector::getInstance());
		$md = $masterDocument->get($id);
		
		$sigObj = new Signature(Connector::getInstance());
		$signers = $sigObj->getSigners($id['id_md']);
		
		$return = array();

		if($md){
		
			//Utils::printr($this->_md);
			// Parsing con XML (documenti richiesti)
			$xml = XMLParser::getInstance();
			$xml->setXMLSource(XMLBrowser::getInstance()->getSingleXml($md['xml']),$md['type']);
			
			// Connessione FTP (documenti esistenti)
			$ftp = FTPConnector::getInstance();
			$fileList = Utils::filterList($ftp->getContents($md['ftp_folder'])['contents'],'isPDF',1);
			$fileList = Utils::getListfromField($fileList, 'filename');
			
			// Utils::printr($fileList);
			
			//$fileList = array("ordine di missione.pdf","allegato_1.pdf","allegato_2.pdf");
			
			// Confronto liste con check su firme e quant'altro
			foreach($xml->getDocList() as $document){
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
						$f_name = "check_$k";
						if(method_exists(__CLASS__, $f_name)){
							self::$f_name($document['name'],$fileList,$attr,$docResult);
						}
					}
					
					if(!is_null($document->signatures->signature) && empty($docResult->errors)){
						// Conbtrollo el firme secondo i seguewnti step:
						
						$files = self::_getFtpFiles($docResult->documentName, $fileList);
						
						foreach($files as $k=>$file){
							$filename = $md['ftp_folder'].$file;
							$result = self::checkSignatures($filename, $document, $signers, $k, $docResult);
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
	
	private static function check_minOccur($docName, $fileList,$value, &$docResult){
		$docResult->mandatory = (int)$value;
			
		if($value > 0){
			
			self::_findFile($docName, $fileList, $docResult);
			if(count($docResult->found) < $value){
				$error = ($value-$found) == 1 ? "$docName assente" : ($value-$found)." documenti mancanti per $docName";
				$docResult->errors['missing'][] = $error;
			}
			
		} 
	}
	
	private static function check_maxOccur($docName, $fileList,$value,&$docResult){
		$docResult->limit = (int)$value;
		if($value > 0){
			self::_findFile($docName, $fileList, $docResult);
			if(count($docResult->found) > $value){
				$docResult->errors['generic'][] = ($value-$found)." documenti in eccesso per $docName";
			}
		}
	}
	
	private static function checkSignatures($filename, $document, $signers, $k, &$docResult){
		$tmpPDF = FTPConnector::getInstance()->getTempFile($filename);

		$pdfParser = new PDFParser($tmpPDF);
		$signaturesOnDocument = $pdfParser->getSignatures();
		
		unlink($tmpPDF);
	
		$checkResult = array();
		
		foreach($document->signatures->signature as $signature){
			$who = (string)$signature['role'];
			$persona = Personale::getInstance()->getPersona($signers[$who]['id_persona']);
				
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
						$persona = Personale::getInstance()->getPersona($signers[$who]['id_delegato']);
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