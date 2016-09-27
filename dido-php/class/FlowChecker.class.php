<?php 
class FlowChecker{
	const FILE_REGEX = "^([A-Za-z_\s]{1,})(_[0-9]{1,}){0,1}(\.pdf)$";
	
	public static function checkMasterDocument($id){
		$return = array();
		// Connessione al db e redupero dati documento (categoria, tipo, TODO:versione)
		
		//$masterDocument = new MasterDocument(Connector::getInstance());
		//$record = $masterDocument->get($id);
		
		$record = array(
			'xml' 		=> XML_PATH. "missioni/missione.xml",
			'md_type' 	=> "senza anticipo",
			'ftp_folder'	=> 'CAMPUS'
		);
		
		if($record){
		
			// Parsing con XML (documenti richiesti)
			$xml = XMLParser::getInstance();
			$xml->setXMLSource($record['xml'],$record['md_type']);
			// Connessione FTP (documenti esistenti)
			
			//$ftp = FTPConnector::getInstance();
			//$fileList = Utils::filterList($ftp->getContents($record['ftp_folder'])['contents'],'isPDF',1);
			//$fileList = Utils::getListfromField($fileList, 'filename');
			
			$fileList = array("ordine di missione.pdf","allegato_1.pdf","allegato_2.pdf");
			
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
					
					// 1-Scarico l'ultimo pdf (della history) da FTP
					$tmpPDF = FTPConnector::getInstance()->getTempFile($docResult->documentName);
						
					// 2-Lo passo alla classe Java per recuperare le firme
					$sigClass = new Java('dido.SignatureManager');
					$sigClass->loadPDF($tmpPDF);
					$signaturesOnDocument = $sigClass->getSignatures();
					
					// 3-cancello il file temporaneo
					unlink($tmpPDF);
					
					// 4-confronto le firme trovate con quelle attese..
					foreach($document->signatures->signature as $signature){
						$who = $signature['role'];
						$alt = $signature['alt'];
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
			
			$found = self::_findFile($docName, $fileList, $docResult);
			if($found < $value){
				array_push($docResult->errors, ($value-$found)." documenti mancanti per $docName");
			}
			
		} 
	}
	
	static function check_maxOccur($docName, $fileList,$value,&$docResult){
		$docResult->limit = (int)$value;
		if($value > 0){
			$found = self::_findFile($docName, $fileList, $docResult);
			if($found > $value){
				array_push($docResult->errors, ($value-$found)." documenti in eccesso per $docName");
			}
		}
	}
	
	private static function _findFile($docName, $fileList, &$docResult){
		$found = 0;
		foreach($fileList as $file){
			preg_match("/".self::FILE_REGEX."/", $file,$fileInfo);
			if ($fileInfo[1]== $docName){
				$found++;
			}
		}
		$docResult->found = $found;
		return $found;
	}
}
?>