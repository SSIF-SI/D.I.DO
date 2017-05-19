<style>
.modal-dialog{
	width:80% !important
}
</style>

<div class="row">
	<div class="col-lg-12">
		<h1 class="page-header">Procedimenti in sospeso</h1>
	</div>
</div>
<?php 
if(!count(!$list[Application_DocumentBrowser::LABEL_MD])){
?>
<div class="alert alert-danger">Nessuno.</div>
<?php
die();
} 
?>
<div class="panel-body">
	<ul class="nav nav-tabs">
<?php 
$XmlParser = new XMLParser();
foreach($list[Application_DocumentBrowser::LABEL_MD] as $sezione => $nomeDocumento):
	$badgeValue = Common::countMultipleMultiArrayItems($nomeDocumento, array_keys($nomeDocumento));
	$sezione_field = Common::fieldFromLabel($sezione);
?>
	<!-- Nav tabs -->
		<li class="">
			<a href="<?="#".$sezione_field?>" data-toggle="tab" aria-expanded="false"><?=$sezione." ($badgeValue)"?></a>
		</li>
<?php 
endforeach; 
?>
	</ul>
	<!-- Tab panes -->
	<div class="tab-content">
<?php 
foreach($list[Application_DocumentBrowser::LABEL_MD] as $sezione => $nomeDocumento): 
	$sezione_field = Common::fieldFromLabel($sezione);
?>
		<div class="tab-pane" id="<?=$sezione_field?>">
			<div class="panel-body">
				<ul class="nav nav-tabs">
<?php 
	foreach($nomeDocumento as $tipoDocumento=>$items):
		$tipoDocumento_field = Common::fieldFromLabel($tipoDocumento);
		$badgeValue = Common::countMultipleMultiArrayItems($nomeDocumento, array($tipoDocumento));
	
?>
				<!-- Nav tabs -->
					<li class="">
						<a href="<?="#".$tipoDocumento_field?>" data-toggle="tab" aria-expanded="false"><?=$tipoDocumento." ($badgeValue)"?></a>
					</li>
<?php 
	endforeach;
?>
				</ul>
				<!-- Tab panes -->
				<div class="tab-content">
<?php 
	foreach($nomeDocumento as $tipoDocumento=>$versioneXML):
	
		$xmlToBeParsed = $XMLDataSource->getSingleXmlByFilename(key($versioneXML));
		$tipoDocumento_field = Common::fieldFromLabel($tipoDocumento);
		$XmlParser->setXMLSource( $xmlToBeParsed[XMLDataSource::LABEL_XML]);
		$inputs = $XmlParser->getMasterDocumentInputs();
?>
					<div class="tab-pane" id="<?=$tipoDocumento_field?>">
<?php 
		foreach($versioneXML as $items){
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
								<th>
								</th>
							</tr>
							</thead>		
<?php 
			foreach($items as $k=>$item):
				$formId = $tipoDocumento_field.$k;
?>
							<tr>
<?php 
				$obj = $list[Application_DocumentBrowser::LABEL_MD_DATA][$k];
				foreach($inputs as $input):
					if(isset($input[XMLParser::SHORTWIEW])):
						$value = $obj[(string)$input];
						if(isset($input[XMLParser::VALUES])){
							$callback = ( string ) $input[XMLParser::VALUES];
							$values = ListHelper::$callback();
							$value = $values[$value];
						}
						if($input[XMLParser::TYPE] == "data")
							$value = Common::convertDateFormat($value, DB_DATE_FORMAT, "d/m/Y");
				
?>
								<td>
									<?=$value?>
								</td>
<?php 
					endif;
				endforeach;			
?>
								<td>
									
									
								</td>
							</tr>
<?php		
			endforeach;
?>					
						</table>
					</div>
<?php 
		}
		endforeach;
		
?>
				</div>		
			</div>
		</div>
<?php 
endforeach;
?>
	</div>
</div>

