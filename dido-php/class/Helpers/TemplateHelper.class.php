<?php 
class TemplateHelper{
	static function LeftMenu(){
?>
		<li>
			<a href="#"><i class="fa fa-files-o fa-fw"></i> Nuovo documento<span class="fa arrow"></span></a>
			<ul class="nav nav-second-level">
				<?php foreach(XMLBrowser::getInstance()->getXmlTree() as $categoria => $dati):?>
				<li>
					<a href="#"><?=ucfirst($categoria)?><span class="fa arrow"></span></a>
					<ul class="nav nav-third-level">
					<?php foreach($dati['documenti'] as $tipoDocumento => $xmlList):?>
						<li>
							<a href="<?=BUSINESS_HTTP_PATH."$tipoDocumento.php"?>"><?=ucwords($tipoDocumento)?></a>
						</li>
					<?php endforeach;?>
					</ul>					
				</li>
				<?php endforeach;?>
			</ul>
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
	
	static function createTimeline($flowCheckereResult, $visibilityResult=null, $metadata = null){?>
                            <ul class="timeline">
<?php 
		$firstError = false;
		foreach($flowCheckereResult as $docName=>$docData):
			if($firstError) continue;
			$status = 
				empty($docData->errors) ? 
					($docData->mandatory ? 'success' : 'not-mandatory') :
					(isset($docData->errors['missing']) ? 'missing-document' : 'missing-signatures');
		?>
                                <li>
                                    <?php echo self::_createtimelineBadge($status);?>
                                    <div class="timeline-panel">
                                        <div class="timeline-heading">
                                        	<div class="row">
                                        		<div class="col-lg-4">
                                            		<h4 class="timeline-title"><?=ucfirst($docData->documentName)?></h4>
                                            	</div>
                                            	<div class="col-lg-8">
                                            		<?php if(!$firstError && $status != 'success'): $firstError = true;?>
		                                            <form class="text-right">
		                                            	<a class="btn btn-primary edit-metadata" type="button"><span class="fa fa-pencil fa-1x fa-fw"></span> Modifica Metadati</a>
		                                               	<a class="btn btn-info upload-pdf" type="button"><span class="fa fa-upload fa-1x fa-fw"></span> Aggiorna Pdf</a>
		                                               	<a class="btn btn-info download-pdf" type="button"><span class="fa fa-download fa-1x fa-fw"></span> Scarica Pdf</a>
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
		                                        		<div class="panel-heading"> Metadati: </div>
														<div class="panel-body"></div>
													</div>
												</div>
												<?php if(count($docData->signatures)):?>
												<div class="col-lg-6">
			                                        <div class="panel panel-info">
														<div class="panel-heading"> Firme Digitali: </div>
														<div class="panel-body">
													<?php foreach($docData->signatures as $nDoc => $signature):foreach($signature as $signData):if($signData['result'] == 'skipped') continue;?>
		                                        		<div class="alert <?=$signData['result'] == false ? 'alert-danger' : 'alert-success'?>"><span class="fa fa-<?=$signData['result'] == false ? 'times' : 'check'?>"></span>&nbsp;<?=$signData['who']." ({$signData['role']})"?></div>
		                                        	<?php endforeach; endforeach;?>
		                                        		</div>
		                                            </div>
		                                        </div>
												<?php endif;?>
	                                        </div>
                                        </div>
                                    </div>
                                </li>
<?php 	endforeach; ?> 
                           </ul>
<?php 
	}
}
?>