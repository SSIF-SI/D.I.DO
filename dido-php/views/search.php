<div class="row">
	<div class="col-lg-12">
		<h1 class="page-header">Ricerca</h1>
	</div>
</div>
<div class="row">
	<div class="col-lg-12"><h4><em><?=$Search->getTree();?>&nbsp;</em></h4></div>
</div>
<?php if(!is_null($Search->getView())) include VIEWS_PATH. $Search->getView() ?>