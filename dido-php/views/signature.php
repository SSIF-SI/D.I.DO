
<div class="row">
	<div class="col-lg-12">
		<h1 class="page-header">Gestione Firme</h1>
	</div>
</div>
<div class="row">
	<div class="col-lg-12">
		<div class="panel panel-default">
			<div class="panel-body">
				<!-- Nav tabs -->
				<ul class="nav nav-tabs">
					<li class="active"><a href="#elenco-firmatari" data-toggle="tab"
						aria-expanded="false">Elenco Firmatari</a></li>
					<li class=""><a href="#firmatari-fissi" data-toggle="tab"
						aria-expanded="false">Firmatari fissi</a></li>
					<li class=""><a href="#firmatari-variabili" data-toggle="tab"
						aria-expanded="true">Firmatari variabili</a></li>
					<li class=""><a href="#applica-firma" data-toggle="tab"
						aria-expanded="true">Applica Firma</a></li>
				</ul>

				<!-- Tab panes -->
				<div class="tab-content">
					<div class="tab-pane active" id="elenco-firmatari">
						<h4>Elenco Firmatari</h4>
						<div>
							<a class="btn btn-primary mymodal edit"
								href="<?=BUSINESS_HTTP_PATH.basename($_SERVER['PHP_SELF'])."?list=Signers"?>"><span
								class="fa fa-plus fa-1x fa-fw"></span> Nuovo Firmatario</a>
						</div>
				                    	<?=$signers['all']?>
				                     </div>
					<div class="tab-pane" id="firmatari-fissi">
						<h4>Firmatari fissi</h4>
						<div>
							<a class="btn btn-primary mymodal edit "
								href="<?=BUSINESS_HTTP_PATH.basename($_SERVER['PHP_SELF'])."?list=FixedSigners"?>"><span
								class="fa fa-plus fa-1x fa-fw"></span> Aggiungi firmatario fisso</a>
						</div>
				                    	<?=$signers['fixed']?>
	                                </div>
					<div class="tab-pane" id="firmatari-variabili">
						<h4>Firmatari variabili</h4>
						<div>
							<a class="btn btn-primary mymodal edit"
								href="<?=BUSINESS_HTTP_PATH.basename($_SERVER['PHP_SELF'])."?list=VariableSigners"?>"><span
								class="fa fa-plus fa-1x fa-fw"></span> Aggiungi firmatario
								variabile</a>
						</div>
					                	<?=$signers['variable']?>
	                                </div>
					<div class="tab-pane" id="applica-firma">
						<h4>Applica firma</h4>
						<div>
							<a class="btn btn-primary mymodal sign"
								href="<?=BUSINESS_HTTP_PATH.basename($_SERVER['PHP_SELF'])."?list=ApplySign"?>"><span
								class="fa fa-plus fa-1x fa-fw"></span> Applica firma</a>
						</div>
					</div>
				</div>
			</div>
			<!-- /.panel-body -->
		</div>
	</div>
</div>

<div class="modal fade" id="myModal" tabindex="-1" role="dialog"
	aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content"></div>
	</div>
</div>


