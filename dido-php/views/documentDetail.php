<div class="row">
	<div class="col-lg-12">
		<h1><?=ucfirst($md[Masterdocument::NOME]." ".$md[Masterdocument::TYPE])?></h1>
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
					<?=$Application_Detail->getFlowResults()->render();?>
				</div>
			</div>
		</div>
	</div>
</div>
	
