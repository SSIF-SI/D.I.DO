<div class="row">
	<div class="col-lg-6">
		<a href="<?=$Search->getRequestUri()?>&all" class="btn btn-lg btn-block btn-primary" style="margin-bottom:0.3em">
			<span class="fa fa-th"> </span> Tutti
        </a>
	</div>
	<div class="col-lg-6">
		<a href="<?=$Search->getRequestUri()?>&closed=<?=ProcedureManager::OPEN?>" class="btn btn-lg btn-block btn-success" style="margin-bottom:0.3em">
			<span class="fa fa-unlock"> </span> Aperti
        </a>
	</div>
</div>
<div class="row">
	<div class="col-lg-6">
		<a href="<?=$Search->getRequestUri()?>&closed=<?=ProcedureManager::CLOSED?>" class="btn btn-lg btn-block btn-danger" style="margin-bottom:0.3em">
			<span class="fa fa-lock"> </span> Chiusi
        </a>
	</div>
	<?php if($_GET['source'] == "Masterdocument"):?>
	<div class="col-lg-6">
		<a href="<?=$Search->getRequestUri()?>&closed=<?=ProcedureManager::INCOMPLETE?>" class="btn btn-lg btn-block btn-warning" style="margin-bottom:0.3em">
			<span class="fa fa-warning"> </span> Incompleti
        </a>
	</div>
	<?php endif;?>
</div>
