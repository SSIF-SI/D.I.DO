<?php 
class TemplateHelper{
	static function LeftMenu(){
		$tree = PermissionHelper::getInstance()->isGestore() ? XMLBrowser::getInstance()->getXmlTree(true) : null;
?>
		<li>
			<a href="<?=HTTP_ROOT?>"><i class="fa fa-dashboard fa-fw"></i> Dashboard</a>
		</li>
		<?php if(!empty($tree)): ?>
        <li>
			<a href="#"><i class="fa fa-files-o fa-fw"></i> Nuovo documento<span class="fa arrow"></span></a>
			<ul class="nav nav-second-level">
				<?php foreach($tree as $categoria => $dati):?>
				<li>
					<a href="#"><?=ucfirst($categoria)?><span class="fa arrow"></span></a>
					<ul class="nav nav-third-level">
					<?php foreach($dati['documenti'] as $tipoDocumento => $xmlList):?>
						<li>
							<a href="<?=BUSINESS_HTTP_PATH."document.php?t=$tipoDocumento"?>"><?=ucwords($tipoDocumento)?></a>
						</li>
					<?php endforeach;?>
					</ul>					
				</li>
				<?php endforeach;?>
			</ul>
		</li>
		<?php endif; ?>
		<li>
        	<a href="<?=BUSINESS_HTTP_PATH."signature.php"?>"><i class="fa fa-pencil fa-fw"></i> Gestione firme</a>
        </li>
		<li>
        	<a href="<?=BUSINESS_HTTP_PATH."permessi.php"?>"><i class="fa fa-key fa-fw"></i> Gestione permessi</a>
        </li>
<?php 
	}
	
	private static function _createtimelineBadge($status){
		switch($status){
			case'success':
				return '<div class="timeline-badge success"><i class="fa fa-check"></i></div>';
				break;
			case'missing-document':
				return '<div class="timeline-badge"><i class="fa fa-times"></i></div>';
				break;
			case'missing-signatures':
				return '<div class="timeline-badge warning"><i class="fa fa-warning"></i></div>';
				break;
			case'not-mandatory':
				return '<div class="timeline-badge info"><i class="fa fa-plus"></i></div>';
				break;
						
		}
		
	}
	
	static function createTimeline($flowCheckereResult, $visibilityResult=null, $mdInfo = null){
		ob_start();
		$info = $flowCheckereResult['data']['info'];
		$xml = XMLBrowser::getInstance()->getSingleXml($info['md']['xml']);
		XMLParser::getInstance()->setXMLSource($xml, $info['md']['type']);
		$inputs = XMLParser::getInstance()->getMasterDocumentInputs();
		
		
?>
					<div class="row">
						<div class="col-lg-12 col-md-12">
		                    <div class="panel panel-primary">
		                        <div class="panel-heading">
		                            <h3 style="margin:0px;padding:0px; font-weight:bold"><?=ucfirst($info['md']['nome']).(!empty($info['md']['type']) ? " ".$info['md']['type'] : "")?></h3>
		                        </div>
		                        <div class="panel-body">
		                        	<div class="row">
		                        	<?php echo FormHelper::createInputsFromDB($inputs, $info['md_data'],true)?>
			                        </div>
		                        	<div class="row text-center">
		                        		<a class="btn btn-primary edit-info" href="?md=<?=$_GET['md']?>&edit">
		                                	<span class="fa fa-pencil fa-1x fa-fw"></span> 
		                                	Modifica informazioni
		                               	</a>
	                               	</div>
	                               	<hr/>
		                       		<div class="panel panel-info">
				                        <div class="panel-heading">
				                            <strong>Flusso documentale</strong>
				                        </div>
				                        <div class="panel-body">
				                            
				                        	<ul class="timeline">
											<?php 
											$firstError = false;
											foreach($flowCheckereResult['doclist'] as $docName=>$docData):
												if($firstError) continue;
												if(empty($docData->found)):
													$firstError = self::_createTimelineDocList('missing-document',$docData,0);
												else:
													foreach($docData->found as $id_doc => $pdf_name):
													$status = 
														empty($docData->errors) ? 
															($docData->mandatory ? 'success' : 'not-mandatory') :
															(isset($docData->errors['missing']) ? 'missing-document' : 'missing-signatures');
														$firstError = self::_createTimelineDocList($status, $docData,$info['documents_data'][$id_doc],$id_doc);
									                                   
													endforeach;
												endif;
											endforeach; ?> 
									        </ul>
	                           			</div>
	                           		</div>
                           		</div>
                           	</div>
		                </div>
		           </div>
                           
<?php 
		return ob_get_clean();
	}
	
	private static function _createTimelineDocList($status, $docData, $info, $id_doc){
		$error = false;
		echo "<li>".self::_createtimelineBadge($status);?>
										    <div class="timeline-panel">
		                                        <div class="timeline-heading">
		                                        	<div class="row">
		                                        		<div class="col-lg-4">
		                                            		<h4 class="timeline-title"><?=ucfirst($docData->documentName)?></h4>
		                                            	</div>
		                                            	<div class="col-lg-8">
		                                            		<?php if($status != 'success'): $error = true;?>
				                                            <form class="text-right">
				                                            	<a class="btn btn-info upload-pdf" type="button"><span class="fa fa-upload fa-1x fa-fw"></span> <?= isset($docData->errors['missing']) ? "Carica" : "Aggiorna"?> il Pdf</a>
				                                            	<?php if(!isset($docData->errors['missing'])):?>
				                                               	<a class="btn btn-info download-pdf" type="button"><span class="fa fa-download fa-1x fa-fw"></span> Scarica il Pdf</a>
				                                               	<?php endif;?>
				                                            </form>
				                                            <?php endif;?>
		                                            	</div>
		                                            </div>
		                                        </div>
		                                        <br/>
		                                        <div class="timeline-body">
		                                        	<div class="row">
		                                        		<div class="col-lg-<?=count($docData->signatures) ? '6' : '12'?>">
				                                        	<div class="panel panel-default">
				                                        		<div class="panel-heading"> Informazioni: </div>
																<div class="panel-body">
																<?php if(!isset($docData->errors['missing'])): $docData->found?>
																	<div class="row">
																	<?php echo FormHelper::createInputsFromDB($docData->inputs,$info, true); ?>
												                	<?php echo FormHelper::createInputsFromDB($docData->defaultInputs,$info,true); ?>
												                	</div>
												                	<div class="row text-center">
																		<a class="btn btn-primary edit-info" href="?md=<?=$_GET['md']?>&d=<?=$id_doc?>&edit">
																		<span class="fa fa-pencil fa-1x fa-fw"></span> 
																			Modifica informazioni documento
																		</a>
																	</div>
											                    <?php endif;?>
																</div>
															</div>
														</div>
														<?php if(count($docData->signatures)):?>
														<div class="col-lg-6">
					                                        <div class="panel panel-info">
																<div class="panel-heading"> Firme Digitali: </div>
																<div class="panel-body">
															<?php foreach($docData->signatures[$id_doc] as $signData):if($signData['result'] == 'skipped') continue;?>
				                                        		<div class="alert <?=$signData['result'] == false ? 'alert-danger' : 'alert-success'?>"><span class="fa fa-<?=$signData['result'] == false ? 'times' : 'check'?>"></span>&nbsp;<?=$signData['who']." ({$signData['role']})"?></div>
				                                        	<?php endforeach; //endforeach;?>
				                                        		</div>
				                                            </div>
				                                        </div>
														<?php endif;?>
			                                        </div>
		                                        </div>
		                                    </div>
		                                </li>
	<?php return $error;
	}
	
	static function createDashboardPanels(){
		// Da Importare
		self::_createDashboardPanel(4,"panel-red","fa-sign-in fa-rotate-90",Geko::getInstance()->getFileToImport()['nTot'],"Proposte da Geco","?detail=documentToImport");
		
		// Aperti
		$Responder = new Responder();
		$Responder->createDocList(true);
		$md_open = $Responder->getMyMasterDocuments()['md'];
		$nTot = 0;
		foreach($md_open as $docs)
			$nTot += count($docs);
		
		self::_createDashboardPanel(4,"panel-yellow","fa-file-text",$nTot,"Procedimenti in sospeso","?detail=documentOpen");
	
		// Da firmare
		self::_createDashboardPanel(4,"panel-green","fa-edit",2,"Documenti da firmare","?detail=documentToSign");
	}
	
	private static function _createDashboardPanel($panel_measure, $panel_class, $icon_class,$nTot,$label,$href){
?>
					<div class="col-lg-<?=$panel_measure?> col-md-<?=($panel_measure*2)?>">
	                    <div class="panel <?=$panel_class?>">
	                        <div class="panel-heading">
	                            <div class="row">
	                                <div class="col-xs-3">
	                                    <i class="fa <?=$icon_class?> fa-5x"></i>
	                                </div>
	                                <div class="col-xs-9 text-right">
	                                    <div class="huge"><?=$nTot?></div>
	                                    <div><?=$label?></div>
	                                </div>
	                            </div>
	                        </div>
	                        <a href="<?=$href?>">
	                            <div class="panel-footer">
	                                <span class="pull-left">Vedi Dettagli</span>
	                                <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
	                                <div class="clearfix"></div>
	                            </div>
	                        </a>
	                    </div>
	                </div>
<?php 
	}
	
	static function createListGroupToImport(){
		ob_start();
		$list=Geko::getInstance()->getFileToImport();
		unset($list['nTot']);
		if(count($list)):
?>
	<style>
		.panel-heading .accordion-toggle{
			text-decoration:none;
		}
		
		.panel-heading .accordion-toggle:after {
		    /* symbol for "opening" panels */
		    font-family: 'Glyphicons Halflings';  /* essential for enabling glyphicon */
		    content: "\e118";    /* adjust as needed, taken from bootstrap.css */
		    float: left;        /* adjust as needed */
		}
		.panel-heading .accordion-toggle.collapsed:after {
		    /* symbol for "collapsed" panels */
		    content: "\e117";    /* adjust as needed, taken from bootstrap.css */
		}
	</style>
	<div class="col-lg-12 col-md-12">
		<div class="panel-group" id="GroupToImport">
			<?php foreach ($list as $category=>$val): ?>
			<div class="panel panel-default">
				<div class="panel-heading">
					<div class="row">
						<div class="col-lg-6">
							<a class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#GroupToImport" href=<?php echo "#list".$category; ?>>&nbsp;<?php echo(ucfirst($category))?></a>
		                </div>
	                    <div class="col-lg-6 text-right">
							<span class="badge badge-info"><?php echo count($val)?></span>
		                </div>
                    </div>
				</div>
				<div id="<?php echo "list".$category;?>" class="panel-collapse collapse">
					<table class="table table-condensed table-striped">
					<?php
					$th = false;
					foreach ($val as $k=>$data):
						$obj = unserialize(file_get_contents(GECO_IMPORT_PATH.$category.DIRECTORY_SEPARATOR.$data['filename']));
						$xml = XMLBrowser::getInstance()->getXmlFromNameAndData($data['md_nome'], date('Y-m-d'));
						
						// aggiunto da federico
						XMLParser::getInstance()->setXMLSource($xml['xml'], $data['type']);
						$inputs = XMLParser::getInstance()->getMasterDocumentInputs();
						// fine
						$formId = rtrim(FormHelper::fieldFromLabel($data['filename']),".imp");
						if(!$th):
							$th = true;
					?>
						<tr>
							<?php foreach($inputs as $input):if(isset($input['shortView'])):?>
							<th><?=(string)($input);?>
							<?php endif; endforeach;?>
							<th></th>
						</tr>
					<?php endif;?>
						 <tr>
						    <?php 
						    $obj_new = array();
						    foreach ($inputs as $input): 
								$key = (string)$input['key'];
								$value = $obj[$key];
								$obj_new[(string)$input] = $value;

								if(isset($input['transform'])){
									$callback = (string)$input['transform'];
									$value = ImportHelper::$callback($value);
									if($input['type'] != 'data')
										$obj_new[(string)$input] = $value;
								} 
								
								if(isset($input['values'])){
									$callBack = (string)$input['values'];
									$values = ListHelper::$callBack();
									$value = $values[$value];
								}
								if(isset($input['shortView'])):
								?>
							<td><?=$value?></td>						
							<?php endif; endforeach; ?>
						    <td>
						    	<form style="display:none" role="form" method="POST" id="importform-<?=$formId?>" enctype="multipart/form-data">
									<input type="hidden" id="import_filename" name="import_filename" value="<?=$category.DIRECTORY_SEPARATOR.$data['filename']?>"/> 
				                	<input type="hidden" id="md_xml" name="md_xml" value="<?=$xml['xml_filename']?>"/> 
				                	<input type="hidden" id="md_nome" name="md_nome" value="<?=$data['md_nome']?>"/> 
				                	<input type="hidden" id="md_type" name="md_type" value="<?=$data['type']?>"/> 
				                	<?php foreach($obj_new as $id=>$val_n):?>
				                	<input type="hidden" id="<?=$id?>" name="<?=$id?>" value="<?=$val_n?>"/> 
				                	<?php endforeach;?>
								</form>
					            <a class="btn btn-primary import" href="#importform-<?=$formId?>">
				                	<span class="fa fa-sign-in fa-rotate-90 fa-1x fa-fw"></span> Importa
				                </a>
			                </td>
						</tr>
					<?php endforeach;?>
					</table>
				</div>
			</div>
			<?php endforeach;?>
		</div>
    </div>
<?
		else: 
?>
		<div class="alert alert-danger">
			Nessun documento da importare.
		</div>
<?php 
		endif;
		return ob_get_clean();
		
	}
	
	static function createListGroupOpen($md_open){
		ob_start();
		if(count($md_open['md'])):
?>
		<style>
		.panel-heading .accordion-toggle{
			text-decoration:none;
		}
		
		.panel-heading .accordion-toggle:after {
		    /* symbol for "opening" panels */
		    font-family: 'Glyphicons Halflings';  /* essential for enabling glyphicon */
		    content: "\e118";    /* adjust as needed, taken from bootstrap.css */
		    float: left;        /* adjust as needed */
		}
		.panel-heading .accordion-toggle.collapsed:after {
		    /* symbol for "collapsed" panels */
		    content: "\e117";    /* adjust as needed, taken from bootstrap.css */
		}
	</style>
	<div class="col-lg-12 col-md-12">
		<div class="panel-group" id="documentsOpen">
			<?php 
			foreach ($md_open['md'] as $xml=>$val): 
				$category = dirname($xml);
				$xml = XMLBrowser::getInstance()->getSingleXml($xml);
				XMLParser::getInstance()->setXMLSource($xml, $data['type']);
				$inputs = XMLParser::getInstance()->getMasterDocumentInputs();
				if(isset($xml['validEnd']))
					$category .= "(fino al ".Utils::convertDateFormat($xml['validEnd'], DB_DATE_FORMAT, "d/m/Y").")";
			?>
			<div class="panel panel-default">
				<div class="panel-heading">
					<div class="row">
						<div class="col-lg-6">
							<a class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#documentsOpen" href=<?php echo "#list".FormHelper::fieldFromLabel($category); ?>>&nbsp;<?php echo(ucfirst($category))?></a>
		                </div>
	                    <div class="col-lg-6 text-right">
							<span class="badge badge-info"><?php echo count($val)?></span>
		                </div>
                    </div>
				</div>
			</div>
			<div id="<?php echo "list".FormHelper::fieldFromLabel($category);?>" class="panel-collapse collapse">
				<table class="table table-condensed table-striped">
					<tr>
						<?php foreach($inputs as $input):if(isset($input['shortView'])):?>
						<th><?=(string)($input);?>
						<?php endif; endforeach;?>
						<th></th>
					</tr>
					<?php foreach (array_keys($val) as $id_md):?>
					<tr>
						<?php 
							foreach ($inputs as $input): 
								if(isset($input['shortView'])):
									$key = (string)$input;
									$value = $md_open['md_data'][$id_md][$key];
									if(isset($input['values'])){
										$callBack = (string)$input['values'];
										$values = ListHelper::$callBack();
										$value = $values[$value];
									}
									if(isset($input['type']) && $input['type'] == "data")
										$value = Utils::convertDateFormat($value, DB_DATE_FORMAT, "d/m/Y"); 
						?>
						<td><?=$value?></td>						
						<?php endif; endforeach; ?>
						<td class="text-center"><a class="btn btn-primary" href="?md=<?=$id_md?>"><span class="fa fa-search fa-1x fa-fw"></span> Dettaglio</a></td>
					</tr>
						
					<?php endforeach; ?>
				</table>
			</div>
			<?php endforeach;?>
		</div>
	</div>
<?php 		
		else:
?>
		<div class="alert alert-danger">
			Nessun documento aperto.
		</div>
<?php 
		endif;
		return ob_get_clean();
	}
	
}
?>