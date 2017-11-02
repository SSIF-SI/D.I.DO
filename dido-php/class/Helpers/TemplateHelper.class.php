<?php 
class TemplateHelper {
	static function createDashboardPanels($data) {
		if(!empty($data)){
			$width = round(12/count($data));
			foreach ($data as $label => $detail){ 
				self::_createDashboardPanel ( $width, "panel-".$detail['color'], $detail['icon-class'], $detail[Common::N_TOT], $label, $detail['href'] );
			}
		}
	}
	
	private static function _createDashboardPanel($panel_measure, $panel_class, $icon_class, $nTot, $label, $href) {
?>
	<div class="col-lg-<?=$panel_measure?> col-md-<?=($panel_measure*2)?>">
		<div class="panel <?=$panel_class?>">
			<div class="panel-heading">
				<div class="row">
					<div class="col-xs-3">
						<i class="fa <?=$icon_class?> fa-4x"></i>
					</div>
					<div class="col-xs-9 text-right">
						<div class="huge"><?=$nTot?></div>
					</div>
				</div>
				<div class="row">
					<div class="text-right" style="padding-right:0.6em"><?=$label?></div>
				</div>
			</div>
			<a href="<?=$href?>">
				<div class="panel-footer">
					<span class="pull-left">Vedi Dettagli</span> <span
						class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
					<div class="clearfix"></div>
				</div>
			</a>
		</div>
	</div>
<?php
	}
}
?>