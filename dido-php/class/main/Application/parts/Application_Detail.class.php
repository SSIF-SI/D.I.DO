<?php 
class Application_Detail{
	private $_FTPDataSource;
	private $_userManager;
	
	private $_redirectUrl;
	private $_info;
	private $_flowResults;
	
	private $_defaultDocumentInputs;
	
	public function __construct(IUserManager $userManager, IFTPDataSource $ftpDataSource){
		$this->_userManager = $userManager;
		$this->_FTPDataSource = $ftpDataSource;
		
		$XMLParser = new XMLParser();
		$XMLParser->load(FILES_PATH."defaultDocumentsInputs.xml");
		$this->_defaultDocumentInputs = $XMLParser->getXmlSource()->input;
		
	}
	
	public function createDetail($md){
		
		extract($md);
		
		$XMLParser = new XMLParser(
			$md[Masterdocument::XML],
			$md[Masterdocument::TYPE]
		);
		
		$this->_redirectUrl = TurnBack::getLastHttpReferer()."#".dirname($md[Masterdocument::XML])."/#".Common::fieldFromLabel($md[Masterdocument::NOME]);
		$this->_createAdditionalInfo($XMLParser, $md_data);
		$this->_flowResults = new FlowTimeline();
		
		$ICanManageIt =
			$this->_userManager->isAdmin() ||
			($this->_userManager->isGestore(true) && $XMLParser->isOwner($this->_userManager->getUser()->getGruppi()));

		// L'elenco dei documenti lo prendo sempre dall'XML
		foreach($XMLParser->getDocList() as $doc){
			
			$XMLParser->checkIfMustBeLoaded ( $doc );
			$docName = (string)$doc[XMLParser::DOC_NAME];
			
			$listOnDb = Utils::filterList($documents, Document::NOME, $docName);
				
			if(count($listOnDb) == 0){
				$this->_flowResults->addTimelineElement(
					new TimelineElementMissing(ucfirst($docName), (int)$doc[XMLParser::MIN_OCCUR], $ICanManageIt)
				);
			
				if($doc[XMLParser::MIN_OCCUR])
					break;				
			} else {
				//Utils::Printr($listOnDb);
				foreach($listOnDb as $id_doc => $docData){
					$docInfo = FormHelper::createInputsFromDB($XMLParser->getDocumentInputs($docName), $docData, true);
					$docInfo .= FormHelper::createInputsFromDB($this->_defaultDocumentInputs, $docData, true);
					
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
	
	private function _createAdditionalInfo($XMLParser, $md_data){
		$readOnly =
		!is_null($XMLParser->getSource()) ?
		true : (
				// - L'utente non è proprietario del documento oppure lo è ma non ha i permessi per editarli
				$this->_userManager->isAdmin() ||
				($XMLParser->isOwner($this->_userManager->getUser()->getGruppi()) && $this->_userManager->isGestore(true)) ?
				false :
				true
		);
		
		$this->_info = FormHelper::createInputsFromDB($XMLParser->getMasterDocumentInputs(), $md_data, $readOnly);
	}	
	
}
?>