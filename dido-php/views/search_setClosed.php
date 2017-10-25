<div class="row">
	<div class="col-lg-6">
		<a href="<?=$Search->getRequestUri()?>&all" class="btn btn-lg btn-block btn-primary" style="margin-bottom:0.3em">
			Tutti
        </a>
	</div>
	<div class="col-lg-6">
		<a href="<?=$Search->getRequestUri()?>&closed=0" class="btn btn-lg btn-block btn-warning" style="margin-bottom:0.3em">
			Aperti
        </a>
	</div>
</div>
<div class="row">
	<div class="col-lg-6">
		<a href="<?=$Search->getRequestUri()?>&closed=1" class="btn btn-lg btn-block btn-success" style="margin-bottom:0.3em">
			Chiusi
        </a>
	</div>
	<div class="col-lg-6">
		<a href="<?=$Search->getRequestUri()?>&closed=-1" class="btn btn-lg btn-block btn-danger" style="margin-bottom:0.3em">
			Incompleti
        </a>
	</div>
</div>
