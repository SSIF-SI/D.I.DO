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
	public function __construct($title){
		$this->_FlowTimelineElement = new FlowTimelineElement(
			new FlowTimelineBadgeMissingDocuments(), 
			new FlowTimelinePanel($title, null)
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
	private $_body;
	
	private $_panel = 
<<<TLP
	<div class="timeline-panel">
		<div class="timeline-heading">
			<h4 class="timeline-title">%s</h4>
		</div>
		<div class="timeline-body">
			%s
		</div>
	</div>
TLP;
	public function __construct($title, $body){
		$this->_title = $title;
		$this->_body = $body;
	}
	
	public function render(){
		printf($this->_panel, $this->_title, $this->_body);
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