<style>
.modal-dialog{
	width:80% !important
}
</style>

<div class="row">
	<div class="col-lg-12">
		<h1 class="page-header">Proposte da <?=$from?></h1>
	</div>
</div>
<?php 
if(!count(!$list)){
?>
<div class="alert alert-danger">Nessun documento da importare.</div>
<?php 
die();
}
?>
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
							<thead>
							<tr>
								<th>
									Seleziona&nbsp;tutti<br/>
									<input type="checkbox" class="select-all" rel="<?=$tipoDocumento_field?>" />
								</th>
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
									<a 
										class="btn btn-primary disabled action import-selected"
										href="#<?=$tipoDocumento_field?>"> 
										<span class="fa fa-sign-in fa-rotate-90 fa-1x fa-fw"></span> 
										Importa&nbsp;Selezionati
									</a>&nbsp;
									<a
										class="btn btn-primary disabled action link-selected"
										href="#<?=$tipoDocumento_field?>"> 
										<span class="fa fa-link fa-1x fa-fw"></span>
										Collega&nbsp;e&nbsp;importa
									</a>
								</th>
							</tr>
							</thead>		
<?php 
		foreach($items as $k=>$item):
			$formId = $tipoDocumento_field.$k;
			$obj = $Application_Import->getImportManager()->fromFileToPostMetadata(REAL_ROOT . $item[IExternalDataSource::IMPORT_FILENAME], $inputs);
?>
							<tr>
								<td>
									<div class="checkbox">
										<label> <input class="select-one" rel="<?=$tipoDocumento_field?>"
											id="form-<?=$formId?>" type="checkbox">
										</label>
									</div>
								</td>
<?php 
			foreach($inputs as $input):
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
			endforeach;			
?>
								<td>
									<form 
										style="display: none" 
										role="form" 
										method="POST"
										id="importform-<?=$formId?>">
										<input
											type="hidden"
											name="<?=ImportManager::LABEL_IMPORT_FILENAME?>"
											value="<?=$item[IExternalDataSource::IMPORT_FILENAME]?>" />
										<input
											type="hidden"
											name="<?=ImportManager::LABEL_MD_NOME?>"
											value="<?=$item[IExternalDataSource::MD_NOME]?>" />
										<input
											type="hidden"
											name="<?=ImportManager::LABEL_MD_TYPE?>"
											value="<?=$item[IExternalDataSource::MD_TYPE]?>" />
										<input
											type="hidden"
											name="<?=ImportManager::LABEL_MD_XML?>"
											value="<?=$lastXML[XMLDataSource::LABEL_FILE]?>" />
									</form>
									<a 
										class="btn btn-primary import"
										href="#importform-<?=$formId?>"> 
										<span class="fa fa-sign-in fa-rotate-90 fa-1x fa-fw"></span> 
										Importa
									</a>&nbsp;
									<a 
										class="btn btn-danger delete"
										href="#importform-<?=$formId?>"> 
										<span class="fa fa-trash fa-1x fa-fw"></span> 
										Elimina
									</a>
									
								</td>
							</tr>
<?php		
		endforeach;
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