<?php class FlowTimeline{
	private $_timeline = [];
	
	public function __construct(){}
	
	public function addTimelineElement(ATimelineElement $timelineElement){
		array_push($this->_timeline, $timelineElement);
	}
	
	public function render(){
?>
		<ul class="timeline">
<?php
	foreach($this->_timeline as $item): 
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

	public function __construct($title, $download){
		
		$buttons = [];
		
		if($download){
			array_push($buttons,new FlowTimelineButtonUpload());
		}
		$this->_FlowTimelineElement = new FlowTimelineElement(
			new FlowTimelineBadgeMissingDocuments(), 
			new FlowTimelinePanel($title, $buttons, new FlowTimelinePanelBody())
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
	private $_buttons;
	private $_panel = 
<<<TLP
	<div class="timeline-panel">
		<div class="timeline-body">
			<div class='row'>					
				<div class="col-lg-4">
					<h4 class="timeline-title">%s</h4>
				</div>
				<div class="col-lg-8 text-right">
					%s
				</div>
			</div>
		</div>
	</div>
TLP;
	public function __construct($title, $buttons, FlowTimelinePanelBody $body){
		$this->_buttons = $buttons;
		$this->_title = $title;
		$this->_body = $body;
	}
	
	public function render(){
		$buttonsHTML = "";
		if(count($this->_buttons)){
			foreach ($this->_buttons as $button)
				$buttonsHTML .= $button->get();
		}
		printf($this->_panel, $this->_title, $buttonsHTML);
	}
	
}

class FlowTimelinePanelBody{
	private $_infoTable;
	private $_signatures;
		
	public function construct($infoTable, $signatures){
		$this->_infoTable = $infoTable;
		$this->_signatures = $signatures;
	}
	
	public function render(){
			
	}
}

abstract class AFlowTimelinePanelButton{
	const HTML =
<<<HTML
	<a class="btn btn-info %s" type="button">
		<span class="fa %s fa-1x fa-fw"></span> %s</a>
HTML;
	
	protected $_button;

	public function get(){
		return $this->_button;
	}
}

class FlowTimelineButtonUpload extends AFlowTimelinePanelButton{
	public function __construct(){
		$this->_button = sprintf(self::HTML, "upload-doc","fa-upload", "Carica il pdf");
	}
}

class FlowTimelineButtonDownload extends AFlowTimelinePanelButton{
	public function __construct(){
		$this->_button = sprintf(self::HTML, "download-doc","fa-download", "Scarica il pdf");
	}
}

abstract class FlowTimelineBadge{
	protected $_badge;
	
	public function render(){
		echo $this->_badge;
	}
}

class FlowTimelineBadgeSuccess extends FlowTimelineBadge{
	public function __construct(){
		$this->_badge = '<div class="timeline-badge success"><i class="fa fa-check"></i></div>';
	}
}

class FlowTimelineBadgeMissingDocuments extends FlowTimelineBadge{
	public function __construct(){
		$this->_badge = '<div class="timeline-badge"><i class="fa fa-times"></i></div>';
	}
}

class FlowTimelineBadgeMissingSignatures extends FlowTimelineBadge{
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