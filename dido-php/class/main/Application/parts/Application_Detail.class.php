<?php 
class Application_Detail{
	private $_FTPDataSource;
	private $_userManager;
	
	private $_redirectUrl;
	private $_info;
	private $_flowResults;
	
	public function __construct(IUserManager $userManager, IFTPDataSource $ftpDataSource){
		$this->_userManager = $userManager;
		$this->_FTPDataSource = $ftpDataSource;
	}
	
	public function createDetail($md){
		extract($md);
		
		$XMLParser = new XMLParser(
			$md[Masterdocument::XML],
			$md[Masterdocument::TYPE]
		);
		
		$this->_redirectUrl = TurnBack::getLastHttpReferer()."#".dirname($md[Masterdocument::XML])."/#".Common::fieldFromLabel($md[Masterdocument::NOME]);
		
		$this->_createInfo($XMLParser, $md_data);
		
		$this->_flowResults = new FlowTimeline();
		// L'elenco dei documenti lo prendo sempre dall'XML
		foreach($XMLParser->getDocList() as $doc){
			
			$XMLParser->checkIfMustBeLoaded ( $doc );
			$docName = (string)$doc[XMLParser::DOC_NAME];
			
			$listOnDb = Utils::filterList($documents, Document::NOME, $docName);
				
			if(count($listOnDb) == 0){
				if($doc[XMLParser::MIN_OCCUR]){
					$this->_flowResults->addTimelineElement(
						new TimelineElementMissing($docName)
					);
					break;				
				}
			}
		}
	}
	
	
	public function getRedirectUrl(){
		return $this->_redirectUrl;
	}
	
	public function getReadOnly(){
		return $this->_readOnly;
	}
	
	public function info(){
		return $this->_info;
	}
	
	public function getFlowResults(){
		return $this->_flowResults;
	}
	
	private function _createInfo($XMLParser, $md_data){
		$readOnly =
		!is_null($XMLParser->getSource()) ?
		true : (
				// - L'utente non è proprietario del documento oppure lo è ma non ha i permessi per editarli
				in_array($XMLParser->getOwner(),$this->_userManager->getUser()->getGruppi()) && $this->_userManager->isGestore() ?
				false :
				true
		);
		
		$this->_info = FormHelper::createInputsFromDB($XMLParser->getMasterDocumentInputs(), $md_data, $readOnly);
	}	
	
}
?>