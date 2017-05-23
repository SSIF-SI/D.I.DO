<?php 
class Application_Detail{
	private $_userManager;
	
	private $_redirectUrl;
	private $_info;
	private $_flowResults;
	
	private $_defaultDocumentInputs;
	private $_Signature;
	private $_sigRoles;
	private $_SignatureChecker;
	
	public function __construct(IDBConnector $dbConnector, IUserManager $userManager, IFTPDataSource $ftpDataSource){
		$this->_userManager = $userManager;
		$this->_SignatureChecker = new SignatureChecker($ftpDataSource);
		
		$XMLParser = new XMLParser();
		$XMLParser->load(FILES_PATH."defaultDocumentsInputs.xml");
		$this->_defaultDocumentInputs = $XMLParser->getXmlSource()->input;
		
		$this->_Signature = new Signature($dbConnector);
		$sigRoles = new SignersRoles($dbConnector);
		$this->_sigRoles = Utils::getListfromField($sigRoles->getAll(),SignersRoles::DESCRIZIONE, SignersRoles::SIGLA);
	}
	
	public function createDetail($md){
		extract($md);
		
		$id_md = $md[Masterdocument::ID_MD];
		$MDSigners = $this->_Signature->getSigners($id_md, $md_data);
		
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
					
					$documentClosed = $documents[$id_doc][Document::CLOSED] != ProcedureManager::CLOSED;
					
					$docPath = 
						$md[Masterdocument::FTP_FOLDER] .
						Common::getFolderNameFromMasterdocument($md) . 
						DIRECTORY_SEPARATOR . 
						Common::getFilenameFromDocument($documents[$id_doc]);
					
					$docInfo = $this->_createDocumentInfo($XMLParser->getDocumentInputs($docName), $docData);
					
					$editInfoBTN = 
						$ICanManageIt ?
						new FlowTimelineButtonEditInfo("?md={$id_md}&d={$id_doc}") :
						null;
		
					$docSignatures = $this->_createDocumentSignatures($docPath, $XMLParser->getDocumentSignatures($docName), $MDSigners);
					
					$panelBody = new FlowTimelinePanelBody($docInfo, !is_null($editInfoBTN) ? $editInfoBTN->get() : null, $docSignatures['html']);
					$panelButtons = [];
					
					if($ICanManageIt || $documents[$id_doc][Application_DocumentBrowser::MUST_BE_SIGNED_BY_ME])
						array_push($panelButtons, NEW FlowTimelineButtonUpload("?upload&md={$id_md}&d=".$id_doc));
					
					array_push($panelButtons, NEW FlowTimelineButtonDownload("?download&md={$id_md}&d=".$id_doc));
					$panel = new FlowTimelinePanel(ucfirst($docName), $panelButtons, $panelBody);
					
					$badge = 
						$docSignatures['errors'] || !$documentClosed ?
						new FlowTimelineBadgeWarning() :
						new FlowTimelineBadgeSuccess();
					
					$this->_flowResults->addTimelineElement(
							new TimelineElementFull($badge, $panel)
					);
					
					// Se ci sono errori oppure il documento risulta ancora aperto si salta tutto il resto.
					if($docSignatures['errors'] || !$documentClosed);
						break 2;
				}
			}
			
		}
	}
	
	private function _createDocumentInfo($inputs, $docData){
		$docInfo = FormHelper::createInputsFromDB($inputs, $docData, true);
		$docInfo .= FormHelper::createInputsFromDB($this->_defaultDocumentInputs, $docData, true);
		return $docInfo;
	}
	
	private function _createDocumentSignatures($docPath, $docSignatures, $MDSigners){
		if(!$docSignatures) 
			return null;
		
		$signResult = [
			'errors' => false,
			'html'		=> []	
		];
		
		$this->_SignatureChecker->load($docPath);
		
		foreach($docSignatures as $signature){
			$role = (string)$signature[XMLParser::ROLE];
			if($role == "REQ") continue;
			if(!isset($MDSigners[$role])){
				$signResult['errors'] = true;
				$signResult['html'][] = "<div class=\"alert alert-danger\"><span class=\"fa fa-times\"></span> Manca la firma del {$this->_sigRoles [$role]} nel sistema DIDO!!!</div>";
				continue;
			}
			
			$who = $MDSigners[$role];
			
			$whoIs = Personale::getInstance()->getNominativo($who[Signers::ID_PERSONA]);
		
			if($this->_SignatureChecker->checkSignature($who[Signature::PKEY])){
				$signResult['html'][] = "<div class=\"alert alert-success\"><span class=\"fa fa-check\"></span> {$whoIs} ({$who[SignersRoles::DESCRIZIONE]}) </div>";
				break;
			}
			
			if($this->_SignatureChecker->checkSignature($who[Signature::PKEY_DELEGATO])){
				$whoIs_Delegato = Personale::getInstance()->getNominativo($who[Signature::ID_DELEGATO]);
				$signResult['html'][] = "<div class=\"alert alert-success\"><span class=\"fa fa-check\"></span> {$whoIs_Delegato} - delegato da {$whoIs} ({$who[SignersRoles::DESCRIZIONE]}) </div>";
				break;
			}
			$signResult['html'][] = "<div class=\"alert alert-warning\"><span class=\"fa fa-warning\"></span> Manca la firma di {$whoIs} ({$who[SignersRoles::DESCRIZIONE]})</div>";
		}
		$signResult['html'] = join(PHP_EOL,$signResult['html']);
		return $signResult;
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