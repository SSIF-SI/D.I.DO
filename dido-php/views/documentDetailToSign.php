<div class="row">
	<div class="col-lg-12">
		<h1><?=ucfirst($md[Masterdocument::NOME]." ".$md[Masterdocument::TYPE])?></h1>
	</div>
</div>
<a href="<?=$Application_Detail->getRedirectUrl()?>">Torna all'elenco</a>

<div class="panel-body">
	<ul class="nav nav-tabs">
		<li class="">
			<a href="#docToSign" data-toggle="tab" aria-expanded="false">Documento da firmare</a>
		</li>
		<li class="">
			<a href="#info" data-toggle="tab" aria-expanded="false">Informazioni</a>
		</li>
		
	</ul>
	
	<!-- Tab panes -->
	<div class="tab-content">
		
		<div class="tab-pane" id="docToSign">
			<br/>
			<div class="panel panel-primary">
				<div class="panel-heading"> 
					Documento da firmare 
				</div>
				<div class="panel-body">
					<?=$Application_Detail->getFlowResults()->render($_GET[Document::ID_DOC]);?>
				</div>
			</div>
		</div>
		
		<div class="tab-pane" id="info">
			<br/>
			<div class="panel panel-primary">
				<div class="panel-heading"> Informazioni </div>
				<div class="panel-body">
					<?=$Application_Detail->info();?>
				</div>
			</div>
		</div>
	</div>
</div>
	
