<?php 
class AttachmentTimeline{
	private $_aTimelines = [];
	
	public function getTimeline($key){
		
		if(!isset($this->_aTimelines[$key])){
			$this->_aTimelines[$key] = new FlowTimeline();
		}
		
		return $this->_aTimelines[$key]; 
	}
	
	public function getTimelines(){
		return $this->_aTimelines;
	}
}


class FlowTimeline{
	private $_timeline = [];
	
	public function __construct(){}
	
	public function addTimelineElement(ATimelineElement $timelineElement, $key=null){
		if(is_null($key))
			array_push($this->_timeline, $timelineElement);
		else 
			$this->_timeline[$key] = $timelineElement;
		return $this;
	}
	
	public function getTimelineElement($key){
		return isset($this->_timeline[$key]) ? $this->_timeline[$key] : false;
	}
	
	public function render($key = null){
		Utils::printr(array_keys($this->_timeline));
?>
		<ul class="timeline">
<?php
	foreach($this->_timeline as $k=>$item):
		if(!is_null($key) && $k != $key)
			continue;
?>
			<li>
				<?=$item->getBadge()->render()?>
				<?=$item->getPanel()->render()?>
			</li>
<?php 
	endforeach;
?>
		</ul>
<?php 
	}
}

abstract class ATimelineElement{
	protected $_FlowTimelineElement;
	
	public function getBadge(){
		return $this->_FlowTimelineElement->getBadge();
	}
	
	public function getPanel(){
		return $this->_FlowTimelineElement->getPanel();
	}
	
}

class TimelineElementMissing extends ATimelineElement{

	public function __construct($title, $mandatory, $upload, $href, $isLink = false){
		
		$buttons = [];
		
		if($upload){
			array_push($buttons,new FlowTimelineButtonAdd($href));
		}
		
		$this->_FlowTimelineElement = new FlowTimelineElement(
			new FlowTimelineBadgeMissingDocuments(), 
			new FlowTimelinePanel($title, null, null, $buttons, new FlowTimelinePanelBody(), $isLink ? "mdLink" : "")
		);
	}
}

class TimelineElementFull extends ATimelineElement{
	public function __construct($badge, $panel, $isLink = false){
		$this->_FlowTimelineElement = new FlowTimelineElement(
			$badge, 
			$panel
		);
	}
}

class FlowTimelineElement{
	private $_badge;
	private $_panel;
	
	public function __construct(FlowTimelineBadge $badge, FlowTimelinePanel $panel){
		$this->_badge = $badge;
		$this->_panel = $panel;
	}
	
	public function getBadge(){
		return $this->_badge;
	}

	public function getPanel(){
		return $this->_panel;
	}
	
	
}

class FlowTimelinePanel{
	
	private $_title;
	private $_subtitle;
	private $_buttons;
	private $_class;
	private	$_privateBTN;
	private $_body;
	private $_panel = 
<<<TLP
	<div class="timeline-panel %s">
		<div class="timeline-heading">
			<div class='row'>					
				<div class="col-lg-4">
					<h4><span class="timeline-title">%s</span> %s</h4> 
					<h5>%s</h5>
				</div>
				<div class="col-lg-8 text-right">
					%s
				</div>
			</div>
		</div>
		<br/>
		<div class="timeline-body">
			%s
		</div>
	</div>
TLP;
	public function __construct($title, $privateBTN = null, $subtitle = null, $buttons, FlowTimelinePanelBody $body, $class = null){
		$this->_buttons = $buttons;
		$this->_title = ucfirst($title);
		$this->_privateBTN = $privateBTN;
		$this->_subtitle = is_null($subtitle) ? null : ucfirst($subtitle);
		$this->_body = $body;
		$this->_class = $class;
	}
	
	public function render(){
		$buttonsHTML = "";
		
		$privateBTN = is_null($this->_privateBTN)? "" : "| ". $this->_privateBTN->get();
		
		if(count($this->_buttons)){
			foreach ($this->_buttons as $button)
				$buttonsHTML .= $button->get();
		}
		
		printf($this->_panel, $this->_class, $this->_title, $privateBTN, $this->_subtitle, $buttonsHTML, $this->_body->render());
	}
	
	public function getPanelBody(){
		return $this->_body;
	}
}

class FlowTimelinePanelBody{
	const INFO_HTML = 
<<<INFO
		<div class="col-lg-%s">
			<div class="panel panel-default">
				<div class="panel-heading">Informazioni:</div>
				<div class="panel-body">
					%s
				</div>
				<div class="row text-center">
					%s
				</div>
				<br/>
			</div>
		</div>
INFO;
	
	const SIGNATURES_INFO = 
<<<SIGINFO
	<div class="col-lg-6">
		<div class="panel panel-info">
			<div class="panel-heading">Firme Digitali:</div>
			<div class="panel-body">
				%s
			</div>
		</div>
	</div>
SIGINFO;

	const ATTACHMENT_HTML =
<<<ATTACHMENT_HTML
		<div class="col-lg-12">
			<div class="panel panel-info">
				<div class="panel-heading">Allegati al documento:</div>
				<div class="panel-body">
					%s
				</div>
				<br/>
			</div>
		</div>
ATTACHMENT_HTML;
	
	private $_infoTable = null;
	private $_attachments = null;
	private $_signatures = null;
		
	public function __construct($infoTable = null, $editInfoBTN = null, $signatures = null, $attachments = null){
		$col = empty($signatures) || empty($infoTable) ? 12 : 6;
		
		if(!is_null($infoTable))
			$this->_infoTable = sprintf(self::INFO_HTML, $col, $infoTable, $editInfoBTN);
		if(!empty($signatures))
			$this->_signatures = sprintf(self::SIGNATURES_INFO, $signatures);
		if(!empty($attachments))
			$this->_attachments = sprintf(self::ATTACHMENT_HTML, $attachments);
		
	}
	
	public function setAttachments($attachments){
		$this->_attachment = sprintf(self::ATTACHMENT_HTML, $attachments);
	}
	
	public function render(){
		return $this->_infoTable.PHP_EOL.$this->_signatures.PHP_EOL.$this->_attachment;	
	}
}

abstract class AFlowTimelinePanelButton{
	const HTML =
<<<HTML
	<a class="btn btn-%s %s" href="%s" type="button">
		<span class="fa %s fa-1x fa-fw"></span> %s</a>
HTML;
	
	protected $_button;

	public function get(){
		return $this->_button;
	}
}

class FlowTimelineButtonDelete extends AFlowTimelinePanelButton{
	public function __construct($href="#"){
		$this->_button = sprintf(self::HTML, "danger", "delete-doc", $href, "fa-trash", "Elimina");
	}
}

class FlowTimelineButtonUpload extends AFlowTimelinePanelButton{
	public function __construct($href="#"){
		$this->_button = sprintf(self::HTML, "info","upload-doc", $href, "fa-upload", "Carica");
	}
}

class FlowTimelineButtonDownload extends AFlowTimelinePanelButton{
	public function __construct($href="#"){
		$this->_button = sprintf(self::HTML, "info","download-doc",$href, "fa-download", "Scarica");
	}
}

class FlowTimelineButtonEdit extends AFlowTimelinePanelButton{
	public function __construct($href){
		$this->_button = sprintf(self::HTML, "info", "edit-doc",$href, "fa-pencil", "Modifica");
	}
}

class FlowTimelineButtonEditInfo extends AFlowTimelinePanelButton{
	public function __construct($href){
		$this->_button = sprintf(self::HTML, "info", "edit-info",$href, "fa-pencil", "Modifica informazioni documento");
	}
}

class FlowTimelineButtonCloseDocument extends AFlowTimelinePanelButton{
	public function __construct($href){
		$this->_button = sprintf(self::HTML, "info", "close-doc", $href, "fa-lock", "Chiudi il documento");
	}
}

class FlowTimelineButtonTogglePrivate extends AFlowTimelinePanelButton{
	public function __construct($href,$set=false){
		$this->_button = sprintf(self::HTML, $set?"danger":"success", "private-doc", $href, $set?"fa-eye-slash":"fa-eye", $set?Application_ActionManager::LABEL_PRIVATE:Application_ActionManager::LABEL_VISIBLE);
	}
}


class FlowTimelineButtonAdd extends AFlowTimelinePanelButton{
	public function __construct($href){
		$this->_button = sprintf(self::HTML, "success", "add-doc",$href, "fa-plus", "Nuovo");
	}
}

abstract class FlowTimelineBadge{
	protected $_badge;
	
	public function render(){
		echo $this->_badge;
	}
}

class FlowTimelineBadgeSuccess extends FlowTimelineBadge{
	public function __construct($closed = false){
		$this->_badge = '<div class="timeline-badge success'.($closed ? ' disabled' : '').'"><i class="fa fa-'.($closed ? 'lock' : 'check').'"></i></div>';
	}
}

class FlowTimelineBadgeMissingDocuments extends FlowTimelineBadge{
	public function __construct(){
		$this->_badge = '<div class="timeline-badge"><i class="fa fa-times"></i></div>';
	}
}

class FlowTimelineBadgeWarning extends FlowTimelineBadge{
	public function __construct(){
		$this->_badge = '<div class="timeline-badge warning"><i class="fa fa-warning"></i></div>';
	}
}

class FlowTimelineBadgeNotMandatory extends FlowTimelineBadge{
	public function __construct(){
		$this->_badge = '<div class="timeline-badge info"><i class="fa fa-plus"></i></div>';
	}
}

?>