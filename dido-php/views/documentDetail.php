<div class="row">
	<div class="col-lg-12">
		<h1><?=ucfirst($md[Masterdocument::NOME])?></h1>
		<h3><?=ucfirst($md[Masterdocument::TYPE])?></h3>
		<h4>Stato: <?=$Application_Detail->renderStatus($md[Masterdocument::CLOSED]);?></h4>
	</div>
</div>
<a href="<?=$Application_Detail->getRedirectUrl()?>">Torna all'elenco</a>

<div class="panel-body">
	<ul class="nav nav-tabs">
		<li class="">
			<a href="#info" data-toggle="tab" aria-expanded="false">Informazioni</a>
		</li>
		<li class="">
			<a href="#flusso" data-toggle="tab" aria-expanded="false">Flusso documentale</a>
		</li>
	</ul>
	
	<!-- Tab panes -->
	<div class="tab-content">
		
		<div class="tab-pane" id="info">
			<br/>
			<div class="panel panel-primary">
				<div class="panel-heading"> Informazioni </div>
				<div class="panel-body">
					<?=$Application_Detail->info();?>
				</div>
			</div>
		</div>
	
		<div class="tab-pane" id="flusso">
			<br/>
			<div class="panel panel-primary">
				<div class="panel-heading"> 
					Flusso documentale 
				</div>
				<div class="panel-body">
					<p class="text-center" >
					<?php if(!$md[Masterdocument::CLOSED]):?>
						<?php if ($Application_Detail->canMdBeClosed()):?>
						<a class="btn btn-danger closeMd" href="?action=<?=Application_ActionManager::ACTION_CLOSE_MD?>&id_md=<?=$_GET[Masterdocument::ID_MD]?>"><i class="fa fa-lock"> </i> CHIUDI PROCEDIMENTO</a>
						<?php else:?>
						<a class="btn btn-danger closeIncompleteMd" href="?action=<?=Application_ActionManager::ACTION_CLOSE_MD?>&id_md=<?=$_GET[Masterdocument::ID_MD]."&".Masterdocument::CLOSED."=".ProcedureManager::INCOMPLETE?>"><i class="fa fa-warning"> </i> CHIUDI PROCEDIMENTO INCOMPLETO</a>
						<?php endif;?>
					<?php endif;?>
					</p>
					<?=$Application_Detail->getFlowResults()->render();?>
				</div>
			</div>
		</div>
	</div>
</div>
	
