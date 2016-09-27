<?php 
class DocumentChecker{
	const FILE_REGEX = "^([A-Za-z_\s]{1,})(_[0-9]{1,}){0,1}(\.v[0-9]{1,}){0,1}(\.pdf)$";
	
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
			
			$fileList = array("pippo_e_sempronio_2.pdf","caio_1.v5.pdf");
			
			// Confronto liste con check su firme e quant'altro
			foreach($xml->getDocList() as $document){
				foreach($document->attributes() as $k=>$attr){
					$f_name = "check_$k";
					if(method_exists(__CLASS__, $f_name)){
						$result = self::$f_name($document['name'],$fileList,$attr);
						if(!empty($result)){
							$return[] = $result;
						}
					}
				}
			}
			
			return $return;
		} else {
			// Eccezione
		}		
		
	}
	
	static function check_minOccur($docName, $fileList,$value){
		Utils::printr(__FUNCTION__);
		if($value > 0){
			foreach($fileList as $file){
				preg_match("/".self::FILE_REGEX."/", $file,$fileInfo);
				Utils::printr($fileInfo);
			}			
		}
	}
	
	static function check_maxOccur($docName, $fileList,$value){
		Utils::printr(__FUNCTION__);
		return true;
	}
	
}
?>