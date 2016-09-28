<?php 
class FlowChecker{
	const FILE_REGEX = "^([A-Za-z_\s]{1,})(_[0-9]{1,}){0,1}(\.pdf)$";
	
	static function checkMasterDocument($id){
		$return = array();
		// Connessione al db e redupero dati documento (categoria, tipo, TODO:versione)
		
		//$masterDocument = new MasterDocument(Connector::getInstance());
		//$record = $masterDocument->get($id);
		
		$record = array(
			'xml' 		=> XML_PATH. "missioni/missione.xml",
			'md_type' 	=> "senza anticipo",
			'ftp_folder'	=> 'missioni/201609/missione_1/'
		);
		
		if($record){
		
			// Parsing con XML (documenti richiesti)
			$xml = XMLParser::getInstance();
			$xml->setXMLSource($record['xml'],$record['md_type']);
			// Connessione FTP (documenti esistenti)
			
			$ftp = FTPConnector::getInstance();
			$fileList = Utils::filterList($ftp->getContents($record['ftp_folder'])['contents'],'isPDF',1);
			$fileList = Utils::getListfromField($fileList, 'filename');
			
			Utils::printr($fileList);
			
			//$fileList = array("ordine di missione.pdf","allegato_1.pdf","allegato_2.pdf");
			
			// Confronto liste con check su firme e quant'altro
			foreach($xml->getDocList() as $document){
				$docResult = new FlowCheckerResult();
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
						$filename = $record['ftp_folder'].$file;
						$result = SignatureChecker::checkSignatures($filename, $document);
						$docResult->signatures[$k] = $result;
					}
				}
				
				array_push($return,$docResult);
			}
			
			Utils::printr($return);
			
			return $return;
		} else {
			// Eccezione
		}		
		
	}
	
	static function check_minOccur($docName, $fileList,$value, &$docResult){
		$docResult->mandatory = (int)$value;
			
		if($value > 0){
			
			self::_findFile($docName, $fileList, $docResult);
			if(count($docResult->found) < $value){
				$error = ($value-$found) == 1 ? "$docName assente" : ($value-$found)." documenti mancanti per $docName";
				array_push($docResult->errors, $error);
			}
			
		} 
	}
	
	static function check_maxOccur($docName, $fileList,$value,&$docResult){
		$docResult->limit = (int)$value;
		if($value > 0){
			self::_findFile($docName, $fileList, $docResult);
			if(count($docResult->found) > $value){
				array_push($docResult->errors, ($value-$found)." documenti in eccesso per $docName");
			}
		}
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