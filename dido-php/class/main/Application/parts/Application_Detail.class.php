<?php 
class Application_Detail{
	private $_userManager;
	private $_ProcedureManager;
	private $_ftpDataSource;
	
	private $_defaultDocumentInputs;
	private $_Signature;
	private $_allSpecialSignatures;
	private $_mySpecialSignatures;
	private $_sigRoles;
	private $_SignatureChecker;
	
	private $_redirectUrl;
	private $_info;
	private $_createInfoPanel;
	
	private $_flowResults;
	private $_attachmentTimeline;
	private $_timeline;
	
	private $_canMdBeClosed = false;
	private $_ICanManageIt = false;
	private $_mdClosed = false;
	
	public function __construct(IDBConnector $dbConnector, IUserManager $userManager, IFTPDataSource $ftpDataSource){
		$this->_userManager = $userManager;
		$this->_ProcedureManager = new ProcedureManager($dbConnector, $ftpDataSource);
		$this->_ftpDataSource = $ftpDataSource;
		
		$XMLParser = new XMLParser();
		$XMLParser->load(FILES_PATH.SharedDocumentConstants::DEFAULT_INPUT_SOURCE);

		$this->_defaultDocumentInputs = $XMLParser->getXmlSource()->input;
		$this->_Signature = new Signature($dbConnector);
		$sigRoles = new SignersRoles($dbConnector);
		$this->_sigRoles = Utils::getListfromField($sigRoles->getAll(),SignersRoles::DESCRIZIONE, SignersRoles::SIGLA);
		$this->_SignatureChecker = new SignatureChecker($ftpDataSource);
		$this->_mySpecialSignatures = $this->_userManager->getUserSign()->getSpecialSignatures();
		$this->_allSpecialSignatures = $this->_userManager->getUserSign()->getAllSpecialSignatures();
		$this->_flowResults = new FlowTimeline();
		$this->_attachmentTimeline = new AttachmentTimeline();
		
	}
	
	public function createDetail($md, $mdLinks){
// 		flog("mdLinks: %o",$mdLinks);
		Utils::printr($md);
		
		extract($md);
		
		
		$id_md = $md[Masterdocument::ID_MD];
		$MDSigners = $this->_Signature->getSigners($id_md, $md_data);
		
		$this->_mdClosed = $md[Masterdocument::CLOSED] != ProcedureManager::OPEN;
		
		$XMLParser = new XMLParser(
			$md[Masterdocument::XML],
			$md[Masterdocument::TYPE]
		);
		
		$innerValues = $XMLParser->getMasterDocumentInnerValues();
		
		$this->_redirectUrl = TurnBack::getLastHttpReferer()."#".dirname($md[Masterdocument::XML])."/#".Common::fieldFromLabel($md[Masterdocument::NOME]);
		$this->_createAdditionalInfo($XMLParser, $id_md, $md_data, $this->_mdClosed);
		
		
		$this->_ICanManageIt =
			$this->_userManager->isAdmin() ||
			($this->_userManager->isGestore(true) && $XMLParser->isOwner($this->_userManager->getUser()->getGruppi()));

		$almostOne = false;
		
		// L'elenco dei documenti lo prendo sempre dall'XML
		foreach($XMLParser->getDocList() as $doc){
			$mandatory = 0;
			$foundMandatory = 0;
			
			$this->_timeline =& $this->_flowResults;
			$this->_createInfoPanel = true;
			
			// Se il documento è un allegato fai qualcosa
			if(isset($doc[$XMLParser::ATTACHMENT_OF])){
				// IL nome del documento con allegati
				$docWithAttachment = (string) $doc[$XMLParser::ATTACHMENT_OF];
				//devo recuperare l'id del documento (ne DOVRO' AVERE solo 1 per ogni nome
				$docWithAttachment = Utils::filterList($documents, Document::NOME, $docWithAttachment);
				$id_docs = array_keys($docWithAttachment);
				$id_doc = reset($id_docs);
				
				$att_timeline = $this->_attachmentTimeline->getTimeline($id_doc);
				$this->_timeline =& $att_timeline;
				$this->_createInfoPanel = false;
			}
			
			if(isset($doc[XMLParser::MD])){
				
				// il documento in realtà è un Master Document esterno
				$docName = (string)$doc[XMLParser::MD];
				
				$listOnDb = Utils::filterList($mdLinks[Application_DocumentBrowser::LABEL_MD], Masterdocument::NOME, $docName);
				if(count($listOnDb) == 0){
					if($this->_mdClosed) break;
					$this->_timeline->addTimelineElement(
						new TimelineElementMissing(ucfirst($docName), (int)$doc[XMLParser::MIN_OCCUR], $this->_ICanManageIt, "?".Application_ActionManager::ACTION_LABEL."=".Application_ActionManager::ACTION_EDIT_MD_LINK."&".XMLParser::DOC_NAME."=$docName&".Masterdocument::ID_MD."={$id_md}", true)
					);
				
					# Se ne serve almeno uno si blocca il rendering
					if($doc[XMLParser::MIN_OCCUR])
						break;				
				} else {
					
					if(!$this->_parseMdLink($listOnDb, $mdLinks[Application_DocumentBrowser::LABEL_MD_LINKS],(int)$doc[XMLParser::MIN_OCCUR], (int)$doc[XMLParser::MAX_OCCUR], $mdLinks[Application_DocumentBrowser::LABEL_MD_DATA], $this->_mdClosed))
						break;
					$almostOne = true;
// 					flog("almostOne");
				}
			} else {
				$XMLParser->checkIfMustBeLoaded ( $doc );
				$docName = (string)$doc[XMLParser::DOC_NAME];
				
				// Se il documento ha il parametro onlyIfExists lo controllo se e solo se esiste già un documento col nome del parametro
				if(isset($doc[XMLParser::ONLYIFEXISTS])){
					$docExists = count(Utils::filterList($documents, Document::NOME, $docName)) > 0;
 					if(!$docExists) continue;
				}
				
				
				// è obbligatorio
				if((int)$doc[XMLParser::MIN_OCCUR])
					$mandatory++;
					
				$listOnDb = Utils::filterList($documents, Document::NOME, $docName);
				
				if(count($listOnDb) == 0){
					if($this->_mdClosed) break;
					$this->_timeline->addTimelineElement(
						new TimelineElementMissing(ucfirst($docName), (int)$doc[XMLParser::MIN_OCCUR], $this->_ICanManageIt, "?".Application_ActionManager::ACTION_LABEL."=".Application_ActionManager::ACTION_UPLOAD."&".XMLParser::DOC_NAME."=$docName&".Masterdocument::ID_MD."={$id_md}")
					);
					
					
					if($doc[XMLParser::MIN_OCCUR]){
						if(isset($doc[XMLParser::CLOSING_POINT])){
							$this->_canMdBeClosed = true;
						}
						break;				
					}
				} else {
					$almostOne = true;
					if(!$this->_parse($listOnDb, (int)$doc[XMLParser::MIN_OCCUR], (int)$doc[XMLParser::MAX_OCCUR], $md, $documents, $documents_data, $innerValues, $XMLParser->getDocumentInputs($docName),(boolean)$doc[XMLParser::PRIVATE_DOC], $XMLParser, $MDSigners))
						break;
					if((int)$doc[XMLParser::MIN_OCCUR])
						$foundMandatory++;
				}
			}
		}
		
		if($mandatory == $foundMandatory || $this->_canMdBeClosed){
			// Controllo che non ci siano input con valori obbligatori PRIMA della chiusura
			$mandatoryInputsBeforeClosing = true;
			
			$mdInputs = $XMLParser->getMasterDocumentInputs();
			foreach($mdInputs as $mdInput){
				if(isset($mdInput[XMLParser::MANDATORY_BEFORE_CLOSING]) && $mdInput[XMLParser::MANDATORY_BEFORE_CLOSING]){
					if(!isset($md_data[(string)$mdInput])){
						//var_dump($md_data[$id_md][(string)$mdInput]);
						$mandatoryInputsBeforeClosing = false;
						break;
					}
				}
			}
			
			$this->_canMdBeClosed = $this->_ICanManageIt && $md[Masterdocument::CLOSED] != ProcedureManager::CLOSED && $mandatoryInputsBeforeClosing ;
		}
		
		Session::getInstance()->delete(SignatureDispatcher::OVERWRITE_FILE_SIGNED);
		
		// ora devo aggiungere le eventuali timeline con attachment nei pannelli giusti del flowresult
		$attachmentTimelines = $this->_attachmentTimeline->getTimelines();
		if(count($attachmentTimelines)){
			foreach($attachmentTimelines as $id_doc=>$attachmentTimeline){
				$flow_timeline = $this->_flowResults->getTimelineElement($id_doc);
				$flow_timeline_panel_body = $flow_timeline->getPanel()->getPanelBody();
				ob_start();
				$attachmentTimeline->render();
				$attachments = ob_get_clean();
				$flow_timeline_panel_body->setAttachments($attachments);
			}
		}
		
		if(!$almostOne)
			return;
		
		if($md[Masterdocument::CLOSED] == ProcedureManager::INCOMPLETE) 
			return;
		
		$this->_timeline =& $this->_flowResults;
		$this->_createInfoPanel = true;
			
		// Ora si aggiunge eventuali allegati se c'è almeno il primo documento caricato
		$XMLParser->load(XML_STD_PATH."allegato.xml");
		$docToSearch = $XMLParser->getXmlSource();
		$docInputs = $docToSearch->inputs->input;
		$docName = $docToSearch[XMLParser::DOC_NAME];
		$listOnDb = Utils::filterList($documents, Document::NOME, $docName);
		
		if(count($listOnDb)){
			$this->_parse($listOnDb, (int)$docToSearch[XMLParser::MIN_OCCUR], (int)$docToSearch[XMLParser::MAX_OCCUR], $md, $documents, $documents_data, $innerValues, $docInputs,(boolean)$docToSearch[XMLParser::PRIVATE_DOC]);
		} else {
			if($this->_userManager->isGestore()){
				$this->_timeline->addTimelineElement(
						new TimelineElementMissing(ucfirst($docName), false, $this->_ICanManageIt, "?".Application_ActionManager::ACTION_LABEL."=".Application_ActionManager::ACTION_UPLOAD."&".XMLParser::DOC_NAME."=$docName&".Masterdocument::ID_MD."={$id_md}")
				);
			}
		} 
	}
	
	public function canMdBeClosed(){
		return $this->_canMdBeClosed;
	}
	
	public function canIManageIt(){
		return $this->_ICanManageIt;
	}
	public function renderStatus($status){
		$model = '<span class="btn btn-%s"><i class="fa fa-%s"> </i> %s</span>';
		switch($status){
			case ProcedureManager::CLOSED:
				$model = sprintf($model, "danger", "lock", "CHIUSO");
				break;
			case ProcedureManager::INCOMPLETE:
				$model = sprintf($model, "warning", "warning", "INCOMPLETO");
				break;
			default:
				$model = sprintf($model, "success", "unlock", "APERTO");
				break;
		}
		return $model;
	}
	
	private function _parseMdLink($listOnDb, $mdLinks, $lowerLimit, $upperLimit, $documents_data){
// 		flog("listOnDB: %o",$listOnDb);
// 		flog("mdLinks: %o",$mdLinks);
// 		flog("data: %o",$documents_data);
		$XMLParser = new XMLParser();
		foreach($listOnDb as $id_md => $docData){
			$link = Utils::filterList($mdLinks, MasterdocumentsLinks::ID_CHILD, $id_md);
			$id_link = key($link);
			$id_father = $link[$id_link][MasterdocumentsLinks::ID_FATHER];
			$XMLParser->setXMLSource($docData[Masterdocument::XML],$docData[Masterdocument::TYPE]);
			$docName = $listOnDb[$id_md][Masterdocument::NOME];
				
			$docInfo = $this->createMdTableInfo($id_md, $XMLParser->getMasterDocumentInputs(), $documents_data[$id_md]);
			
			
			$panelBody = new FlowTimelinePanelBody($docInfo, null, null);
			$panelButtons = [];
			
			if($this->_ICanManageIt){
				if((!$this->_mdClosed))
					array_push($panelButtons, new FlowTimelineButtonEdit("?".Application_ActionManager::ACTION_LABEL."=".Application_ActionManager::ACTION_EDIT_MD_LINK."&".XMLParser::DOC_NAME."=$docName&".Masterdocument::ID_MD."={$id_father}&".MasterdocumentsLinks::ID_LINK."={$id_link}"));				
	
				// Se non c'è il maxoccur o comunque il numero di documenti è inferiore al maxoccur posso caricarne di nuovi
				if(!$upperLimit || count($listOnDb) < $upperLimit)
					array_push($panelButtons, new FlowTimelineButtonAdd("?".Application_ActionManager::ACTION_LABEL."=".Application_ActionManager::ACTION_EDIT_MD_LINK."&".XMLParser::DOC_NAME."=$docName&".Masterdocument::ID_MD."={$id_father}"));
				
				if(!$lowerLimit || count($listOnDb) > $lowerLimit)
					array_push($panelButtons, new FlowTimelineButtonDelete("?".Application_ActionManager::ACTION_LABEL."=".Application_ActionManager::ACTION_DELETE_MD_LINK."&".MasterdocumentsLinks::ID_LINK."={$id_link}"));
			}
			
			$panel = new FlowTimelinePanel("Collegamento ".$docName, null ,null, $panelButtons, $panelBody, "mdLink");
			
			$badge =
				new FlowTimelineBadgeSuccess();
				
			$this->_timeline->addTimelineElement(
					new TimelineElementFull($badge, $panel),
					$id_md,
					true
			);
				
				
		}
		return true;
	}
	
	private function _parse($listOnDb, $lowerLimit, $upperLimit, $md, $documents, $documents_data, $innerValues, $docInputs, $private, $XMLParser= null, $MDSigners = null){
		$id_md = $md[Masterdocument::ID_MD];
		$private = is_null($private)? false : $private;
		$this->_mdClosed = $md[Masterdocument::CLOSED] != ProcedureManager::OPEN;

		foreach($listOnDb as $id_doc => $docData){
			$docName = $documents[$id_doc][Document::NOME];
			$docType = $documents[$id_doc][Document::TYPE];
			$docPrivacy= $documents[$id_doc][Document::PRIVATE_DOC];
			$privateBTN=null;
			
			if(!is_null($XMLParser)){
				$signatures = $XMLParser->getDocumentSignatures($docName, $docType);
				$specialSignatures = $XMLParser->getDocumentSpecialSignatures($docName, $docType);
			}

			$documentClosed = $documents[$id_doc][Document::CLOSED] == ProcedureManager::CLOSED;
			$IMustSignIt =
				$documents[$id_doc][Application_DocumentBrowser::MUST_BE_SIGNED_BY_ME] &&
				!$documents[$id_doc][Application_DocumentBrowser::IS_SIGNED_BY_ME];
			
			$docPath =
				$md[Masterdocument::FTP_FOLDER] .
				Common::getFolderNameFromMasterdocument($md) .
				DIRECTORY_SEPARATOR .
				Common::getFilenameFromDocument($documents[$id_doc]);
				
			$docInfo = $this->_createInfoPanel ?
				$this->createDocumentInfoPanel($docInputs, $documents_data[$id_doc], $innerValues):
				null;

			
			$editInfoBTN =
			($this->_ICanManageIt && !$this->_mdClosed /*&& !$documentClosed*/) ?
			new FlowTimelineButtonEditInfo("?".Application_ActionManager::ACTION_LABEL."=".Application_ActionManager::ACTION_EDIT_INFO."&".Masterdocument::ID_MD."={$id_md}&".Document::ID_DOC."={$id_doc}") :
			null;
		
			if(!$documentClosed){	
				$docSignatures = $this->_createDocumentSignaturesPanel($docPath, $signatures->signature, $specialSignatures->specialSignature, $MDSigners);
			}
			
			$panelBody = new FlowTimelinePanelBody($docInfo, !is_null($editInfoBTN) ? $editInfoBTN->get() : null, $docSignatures['html']);
			$panelButtons = [];
				
			// Posso caricare il documento se:
			// - il documento non è chiuso e
			// - posso gestirlo o devo firmarlo
			if( !$documentClosed && ( $this->_ICanManageIt || $IMustSignIt ) )
				array_push($panelButtons, new FlowTimelineButtonUpload("?".Application_ActionManager::ACTION_LABEL."=".Application_ActionManager::ACTION_UPLOAD."&".Masterdocument::ID_MD."={$id_md}&".Document::ID_DOC."={$id_doc}&".XMLParser::DOC_NAME."=$docName"));
				
			// Se private_doc è true posso scaricare solamente se sICanManageIT altrimente posso scaricarlo. Se private è false posso scaricarlo sempre
			if(!$private || !$docPrivacy  || $this->_ICanManageIt)
				array_push($panelButtons, new FlowTimelineButtonDownload("?".Application_ActionManager::ACTION_LABEL."=".Application_ActionManager::ACTION_DOWNLOAD."&".Masterdocument::ID_MD."={$id_md}&".Document::ID_DOC."={$id_doc}"));

			if($this->_ICanManageIt){
			// Se non c'è il maxoccur o comunque il numero di documenti è inferiore al maxoccur posso caricarne di nuovi
				if($documentClosed && (!$upperLimit || count($listOnDb) < $upperLimit))
					array_push($panelButtons, new FlowTimelineButtonAdd("?".Application_ActionManager::ACTION_LABEL."=".Application_ActionManager::ACTION_UPLOAD."&".XMLParser::DOC_NAME."=".$docName."&".Masterdocument::ID_MD."={$id_md}"));
				
				// Se non c'è il minoccur o comunque minOccur = 0
				if(!$lowerLimit || ($lowerLimit >= 1 && count($listOnDb) > $lowerLimit))
					array_push($panelButtons, new FlowTimelineButtonDelete("?".Application_ActionManager::ACTION_LABEL."=".Application_ActionManager::ACTION_DELETE."&".Masterdocument::ID_MD."={$id_md}&".Document::ID_DOC."={$id_doc}"));
				
				//Se il master Document non è chiuso posso settare il documento come privato
				if(!$this->_mdClosed ){
					$privateBTN = new FlowTimelineButtonTogglePrivate("?".Application_ActionManager::ACTION_LABEL."=".Application_ActionManager::ACTION_SETPRIVATE."&".Masterdocument::ID_MD."={$id_md}&".Document::ID_DOC."={$id_doc}",$docPrivacy);
				}
				
				
				// Il documento se non ci sono errori e non è già chiuso lo posso chiudere
				if(!$documentClosed && !$docSignatures['errors'])
					array_push($panelButtons, new FlowTimelineButtonCloseDocument("?".Application_ActionManager::ACTION_LABEL."=".Application_ActionManager::ACTION_CLOSE_DOC."&".Masterdocument::ID_MD."={$id_md}&".Document::ID_DOC."={$id_doc}"));
			}
			
			$panel = new FlowTimelinePanel($docName,$privateBTN, $docType, $panelButtons, $panelBody);
			
			$badge =
				($signatures && $docSignatures['errors']) || !$documentClosed ?
				new FlowTimelineBadgeWarning() :
				new FlowTimelineBadgeSuccess($documentClosed);

			// Se Non attachment va bene:
			
			$this->_timeline->addTimelineElement(
					new TimelineElementFull($badge, $panel),
					$id_doc
			);
			
			// Altrimenti iniettare 
				
			if(isset($docSignatures['firstMissingSignature'])){
				$SD = new SignatureDispatcher($this->_ftpDataSource);
				$SD->dispatch($docSignatures['firstMissingSignature'], $docPath);
			} 
			// Se ci sono errori oppure il documento risulta ancora aperto si salta tutto il resto.
			if(($signatures && $docSignatures['errors']) || !$documentClosed ){
				Session::getInstance()->delete(SignatureDispatcher::OVERWRITE_FILE_SIGNED);
				return false;
			}
		}
		
		return true;
	}
	
	public function createDocumentInfoPanel($inputs, $docData, $innerValues = null, $readonly = true, $mdInfo = false){
		$docInfo = FormHelper::createInputs($inputs, $docData, $innerValues, $readonly);
		if(!$mdInfo) $docInfo .= FormHelper::createInputs($this->_defaultDocumentInputs, $docData, $innerValues, $readonly);
		return $docInfo;
	}
	
	public function createMdTableInfo($id_md, $inputs, $docData){
		ob_start();
?>
<table class="table table-condensed table-striped">
	<thead>
		<tr>
<?php
		foreach($inputs as $input):
			if(isset($input[XMLParser::SHORTWIEW])):
?>
						<th><?=Common::labelFromField((string)$input);?></th>
<?php 
			endif;
?>
<?php 
		endforeach;
?>
			<th></th>
		</tr>
	</thead>
	<tr>
<?php 
		foreach($inputs as $input):
			if(isset($input[XMLParser::SHORTWIEW])):
				$key = Common::labelFromField((string)$input, false);
				$value= Common::renderValue($docData[$key],$input);
?>
			<td>
				<?=$value?>
			</td>
<?php 
			endif;
		endforeach;			
?>
			<td class="text-right"><a target="_blank"
			class="btn btn-primary detail"
			href="?<?=Masterdocument::ID_MD?>=<?=$id_md?>"><span
				class="fa fa-search fa-1x fa-fw"></span> Dettaglio</a></td>
	</tr>
</table>
<?php 
		return ob_get_clean();
	}
	
	public function updateDocumentData($id_doc, $docInputs, $documents_data){
		$documents_data = Common::createPostMetadata($documents_data,$id_doc);
		$result = $this->_ProcedureManager->updateDocumentData($documents_data);
		return new ErrorHandler($result ? false : "Impossibile aggiornare i dati");
	}

	private function _createDocumentSignaturesPanel($docPath, $docSignatures, $specialSignatures, $MDSigners){
		
		//Utils::printr($MDSigners);
		
		$signResult = [
			'errors' => false,
			'html'		=> []	
		];

		if(SignatureChecker::emptySignatures($docSignatures) && SignatureChecker::emptySignatures($specialSignatures))
			return $signResult;
		
// 		flog("docPath: %s",$docPath);
		
		$this->_SignatureChecker->load($docPath);
		
		$firstSignature = null;
		
		if(!SignatureChecker::emptySignatures($docSignatures)) {
			foreach ( $docSignatures as $signature ) {
				/*
				if(!$signature)
				break;	
				*/
				$role = ( string ) $signature [XMLParser::ROLE];
				/* if($role == "REQ") continue; */
				if (! isset ( $MDSigners [$role] )) {
					$signResult ['errors'] = true;
					$signResult ['html'] [] = "<div class=\"alert alert-danger\"><span class=\"fa fa-times\"></span> Manca la firma del {$this->_sigRoles [$role]} nel sistema DIDO!!!</div>";
					continue;
				}
				
				$who = $MDSigners [$role];
				
				$whoIs = Personale::getInstance ()->getNominativo ( $who [Signers::ID_PERSONA] );
				
				if ($this->_SignatureChecker->checkSignature ( $who [Signature::PKEY] )) {
					$signResult ['html'] [] = "<div class=\"alert alert-success\"><span class=\"fa fa-check\"></span> {$whoIs} ({$who[SignersRoles::DESCRIZIONE]}) </div>";
					break;
				}
				
				if ($this->_SignatureChecker->checkSignature ( $who [Signature::PKEY_DELEGATO] )) {
					$whoIs_Delegato = Personale::getInstance ()->getNominativo ( $who [Signature::ID_DELEGATO] );
					$signResult ['html'] [] = "<div class=\"alert alert-success\"><span class=\"fa fa-check\"></span> {$whoIs_Delegato} - delegato da {$whoIs} ({$who[SignersRoles::DESCRIZIONE]}) </div>";
					break;
				}

				if(is_null($firstSignature)){
					$firstSignature = $role;
					$signResult ['firstMissingSignature'] = $role;
				}
					
				$signResult ['errors'] = true;
				$signResult ['html'] [] = "<div class=\"alert alert-warning\"><span class=\"fa fa-warning\"></span> Manca la firma di {$whoIs} ({$who[SignersRoles::DESCRIZIONE]})</div>";
			}
		}
		
		if (!SignatureChecker::emptySignatures($specialSignatures)){	
			foreach($specialSignatures as $specialSignature){
				/*
				if(!$specialSignature)
					break;
				*/
				$type = (string) $specialSignature[XMLParser::SIGNATURE_TYPE];

				if(isset($this->_allSpecialSignatures[$type])){
					$listOfSpecialSigners = $this->_allSpecialSignatures[$type];
					
					$signerFound = false;
					foreach($listOfSpecialSigners as $specialSigner){
						$result = $this->_SignatureChecker->checkSignature($specialSigner[SpecialSignatures::PKEY]);
						
						if($result){
							$sSigner = Personale::getInstance()->getNominativo($specialSigner[SpecialSignatures::ID_PERSONA]);
							$signResult['html'][] =	"<div class=\"alert alert-success\"><span class=\"fa fa-check\"></span> Firma per $type effettuata da $sSigner</div>";
							$signerFound = true;
							break;
						}
						
					}
					
					if(!$signerFound){
						if(is_null($firstSignature)){
							$firstSignature = $role;
							$signResult ['firstMissingSignature'] = $role;
						}
						$signResult['errors'] = true;
						$signResult['html'][] =	"<div class=\"alert alert-danger\"><span class=\"fa fa-danger\"></span> Manca la firma per $type </div>";
					}
				} else {
					$signResult['errors'] = true;
					$signResult['html'][] =	"<div class=\"alert alert-danger\"><span class=\"fa fa-danger\"></span> Manca la firma per $type </div>";
				}						
			}
			
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
	
	private function _createAdditionalInfo($XMLParser, $id_md, $md_data){
		$readOnly =
		!is_null($XMLParser->getSource()) ?
		true : (
				// - L'utente non è proprietario del documento oppure lo è ma non ha i permessi per editarli
				$this->_userManager->isAdmin() ||
				($XMLParser->isOwner($this->_userManager->getUser()->getGruppi()) && $this->_userManager->isGestore(true)) ?
				false :
				true
		);
		
		$infoPanel = FormHelper::createInputs($XMLParser->getMasterDocumentInputs(), $md_data, $XMLParser->getMasterDocumentInnerValues(), true);
		if(!$readOnly && !$this->_mdClosed)
			$infoPanel .= "<div class=\"text-center\"><a href=\"?action=".Application_ActionManager::ACTION_EDIT_MD_INFO."&".Masterdocument::ID_MD."=".$id_md."\" class=\"btn btn-primary ".Application_ActionManager::ACTION_EDIT_MD_INFO."\">Modifica informazioni</a></div>";
		
		$this->_info = $infoPanel;
	}	
	
}
?>