<div class="row">
	<div class="col-lg-12">
		<h1 class="page-header">Proposte da <?=$from?></h1>
	</div>
</div>
<div class="panel-body">
	<ul class="nav nav-tabs">
<?php 
foreach($list as $sezione => $nomeDocumento):
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
foreach($list as $sezione => $nomeDocumento): 
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
	foreach($nomeDocumento as $tipoDocumento=>$items):
	$tipoDocumento_field = Common::fieldFromLabel($tipoDocumento);
	$lastXML = $Application_Import->getLastXML($tipoDocumento);
	if(!$lastXML) continue;
	$XmlParser = new XMLParser ( $lastXML [XMLDataSource::LABEL_XML] );
	$inputs = $XmlParser->getMasterDocumentInputs();
?>
					<div class="tab-pane" id="<?=$tipoDocumento_field?>">
						<table class="table table-condensed table-striped <?=$tipoDocumento_field?>">
							<tr>
								<th>
									<input type="checkbox" class="select-all" rel="<?=$tipoDocumento_field?>">&nbsp;Seleziona&nbsp;tutti
								</th>
<?php 
		foreach($inputs as $input){
			if(isset($input[XMLParser::SHORTWIEW])):
?>
								<th><?=Common::labelFromField((string)$input);?></th>
<?php 
			endif;
?>
<?php 
		}
?>
								<th>
									<a 
										class="btn btn-primary disabled action import-selected"
										href="#<?=$catName?>"> 
										<span class="fa fa-sign-in fa-rotate-90 fa-1x fa-fw"></span> 
										Importa	Selezionati
									</a>&nbsp;
									<a
										class="btn btn-primary disabled action link-selected"
										href="#<?=$catName?>"> 
										<span class="fa fa-link fa-1x fa-fw"></span>
										Collega
									</a>
								</th>
							</tr>		
<?php 
		foreach($items as $k=>$item){
			$obj = $Application_Import->getImportManager()->fromFileToPostMetadata($item[IExternalDataSource::FILENAME], $inputs);
?>
							<tr>
								<td>
									<div class="checkbox">
										<label> <input class="select-one" rel="<?=$tipoDocumento_field?>"
											id="form-<?=$k?>" type="checkbox">
										</label>
									</div>
								</td>
<?php 
			foreach($inputs as $input){
				if(isset($input[XMLParser::SHORTWIEW])):
					$value = $obj[Common::fieldFromLabel((string)$input)];
					if(isset($input[XMLParser::VALUES])){
						$callback = ( string ) $input[XMLParser::VALUES];
						$values = ListHelper::$callback();
						$value = $values[$value];
					}
					
?>
								<td>
									<?=$value?>
								</td>
<?php 
				endif;
			}			
?>
								<td>
									<form 
										style="display: none" 
										role="form" 
										method="POST"
										id="importform-<?=$k?>">
										<input
											type="hidden"
											name="<?=ImportManager::LABEL_IMPORT_FILENAME?>"
											value="<?=$item[IExternalDataSource::FILENAME]?>" />
										<input
											type="hidden"
											name="<?=ImportManager::LABEL_MD_NOME?>"
											value="<?=$item[IExternalDataSource::MD_NOME]?>" />
										<input
											type="hidden"
											name="<?=ImportManager::LABEL_MD_TYPE?>"
											value="<?=$item[IExternalDataSource::TYPE]?>" />
										<input
											type="hidden"
											name="<?=ImportManager::LABEL_MD_XML?>"
											value="<?=$lastXML[XMLDataSource::LABEL_FILE]?>" />
									</form>
									<a 
										class="btn btn-primary import"
										href="#importform-<?=$k?>"> 
										<span class="fa fa-sign-in fa-rotate-90 fa-1x fa-fw"></span> 
										Importa
									</a>
								</td>
							</tr>
<?php		
		}
?>							
						</table>
					</div>
<?php 
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

