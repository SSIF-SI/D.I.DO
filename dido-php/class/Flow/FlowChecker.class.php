<?php 
class FlowChecker{
	public static $instance = null;
	
	private $_md;
	private $_md_data;
	private $_xml;
	
	const FILE_REGEX = "^([A-Za-z_\s]{1,})(_[0-9]{1,}){0,1}(\.pdf)$";
	
	public static function getInstance(){
		if(is_null(self::$instance))
			self::$instance = new FlowChecker();
		return self::$instance;
	} 
	
	private function __construct(){
		$this->_xml = XMLParser::getInstance();
	}
	
	public function checkMasterDocument($id){
		
		/*
		 * array(
		 * 	[Ruolo] 	=> chiave
		 * )
		 * 
		 */
		$masterDocument = new Masterdocument(Connector::getInstance());
		$this->md = $masterDocument->get($id);
		
		/*
		$masterDocumentData = new MasterdocumentData(Connector::getInstance());
		$this->_md_data = $masterDocumentData->searchByKeyValue(array(
				'id_md'	=> $id,
				$sigObj->getDescrizioni()
		));
		*/
		$sigObj = new Signature(Connector::getInstance());
		$signers = $sigObj->getSigners($id['id_md']);
		
		$return = array();
		
		/*
		$record = array(
			'xml' 		=> XML_MD_PATH. "missioni/missione.xml",
			'md_type' 	=> "senza anticipo",
			'ftp_folder'	=> 'missioni/201609/missione_1/'
		);
		*/
		
		$this->md['ftp_folder']	= 'missioni/201609/missione_1/';
		if($this->md){
		
			Utils::printr($this->md);
			// Parsing con XML (documenti richiesti)
			$this->_xml->setXMLSource(XML_MD_PATH.$this->md['xml'],$this->md['type']);
			// Connessione FTP (documenti esistenti)
			
			$ftp = FTPConnector::getInstance();
			$fileList = Utils::filterList($ftp->getContents($this->md['ftp_folder'])['contents'],'isPDF',1);
			$fileList = Utils::getListfromField($fileList, 'filename');
			
			Utils::printr($fileList);
			
			//$fileList = array("ordine di missione.pdf","allegato_1.pdf","allegato_2.pdf");
			
			// Confronto liste con check su firme e quant'altro
			foreach($this->_xml->getDocList() as $document){
				$docResult = new FlowCheckerResult();
				
				if(!is_null($document['load'])){
					$defaultXml = XML_STD_PATH . (string)$document['load'];
					$document = simplexml_load_file($defaultXml);
				}
				
				if(!is_null($document['md'])){
					// è un documento di tipo esterno (masterDocument)	
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
							$filename = $this->md['ftp_folder'].$file;
							$result = $this->checkSignatures($filename, $document, $signers, $k, $docResult);
							$docResult->signatures[$k] = $result;
						}
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
				$docResult->errors['generic'][] = $error;
			}
			
		} 
	}
	
	static function check_maxOccur($docName, $fileList,$value,&$docResult){
		$docResult->limit = (int)$value;
		if($value > 0){
			self::_findFile($docName, $fileList, $docResult);
			if(count($docResult->found) > $value){
				$docResult->errors['generic'][] = ($value-$found)." documenti in eccesso per $docName";
			}
		}
	}
	
	public function checkSignatures($filename, $document, $signers, $k, &$docResult){
		$checkResult = array();
		// 1-Scarico il pdf da FTP
		$tmpPDF = FTPConnector::getInstance()->getTempFile($filename);
			
		// 2-Lo passo alla classe Java per recuperare le firme
		$sigClass = new Java('dido.signature.SignatureManager');
		$sigClass->loadPDF($tmpPDF);
		$signaturesOnDocument = json_decode((string)$sigClass->getSignatures());
		
		Utils::printr($signaturesOnDocument);
		// 3-cancello il file temporaneo
		unlink($tmpPDF);
	
		// 4-confronto le firme trovate con quelle attese..
		foreach($document->signatures->signature as $signature){
			$who = (string)$signature['role'];
			if($who == "REQ") {
				$checkResult[$who] = 'skipped';
				continue;
			}

			$pKey = $signers[$who]['pKey'];
			$result = false;
			foreach ($signaturesOnDocument as $signatureData){
				if($pKey == $signatureData->publicKey) $result = $signatureData->signer;
			}
			$checkResult[$who] = $result;
			if(!$result)
				$docResult->errors[$k][] = "Manca la firma del ".$signers[$who]['descrizione'];
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