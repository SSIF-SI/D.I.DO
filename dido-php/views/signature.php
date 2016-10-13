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
	                                <li class="active"><a href="#elenco-firmatari" data-toggle="tab" aria-expanded="false">Elenco Firmatari</a>
	                                </li>
	                                <li class=""><a href="#firmatari-fissi" data-toggle="tab" aria-expanded="false">Firmatari fissi</a>
	                                </li>
	                                <li class=""><a href="#firmatari-variabili" data-toggle="tab" aria-expanded="true">Firmatari variabili</a>
	                                </li>
	                            </ul>
	
	                            <!-- Tab panes -->
	                            <div class="tab-content">
	                                <div class="tab-pane active" id="elenco-firmatari">
	                                    <h4>Elenco Firmatari</h4>
	                                    <div>
				                    		<a class="btn btn-primary" href="<?=BUSINESS_HTTP_PATH."editSigner.php"?>"><span class="glyphicon glyphicon-plus"></span> Nuovo firmatario</a>
				                    	</div>
				                    	<?=$signers['all']?>
					                </div>
					                <div class="tab-pane" id="firmatari-fissi">
	                                    <h4>Firmatari fissi</h4>
	                                    <div>
				                    		<a class="btn btn-primary" href="<?=BUSINESS_HTTP_PATH."editSigner.php?list=fixed"?>"><span class="glyphicon glyphicon-plus"></span> Aggiungi firmatario fisso</a>
				                    	</div>
				                    	<?=$signers['fixed']?>
	                                </div>
					                <div class="tab-pane" id="firmatari-variabili">
	                                    <h4>Firmatari variabili</h4>
	                                    <div>
				                    		<a class="btn btn-primary" href="<?=BUSINESS_HTTP_PATH."editSigner.php?list=variable"?>"><span class="glyphicon glyphicon-plus"></span> Aggiungi firmatario variabile</a>
				                    	</div>
					                	<?=$signers['variable']?>
	                                </div>
					        	</div>
	                        </div>
	                        <!-- /.panel-body -->
	                    </div>
                    
                    	
                    </div>
                </div>
