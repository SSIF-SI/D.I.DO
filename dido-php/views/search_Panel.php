<form method="POST">
<div class="row">
	<div class="col-lg-12 filter-box">
		<ul id="filterList">
		</ul>
		<?php 
			if(Session::getInstance()->exists("Search_filters")){
				$P = Session::getInstance()->get("Search_filters");
				
				
				foreach(Session::getInstance()->get("Search_filters") as $key=>$values):
					foreach($values as $ik=>$value):
		?>
		<input id="filter-<?=$ik?>" type="hidden" name="<?=$key."[$ik]"?>" value="<?=$value?>" />
		<?php 			
					endforeach;
				endforeach;
			}
		?>	
	</div>
</div>
<div class="row">
	<div class="col-lg-6">
		<a id="addFilterType" href="<?=$Search->getRequestUri()."&addFilter=Type"?>" class="btn btn-lg btn-block btn-success mymodal search" style="margin-bottom:0.3em">
			<i class="fa fa-plus"> </i> Aggiungi filtro tipologia
        </a>
	</div>
	<div class="col-lg-6">
		<a id="addFilterKeyword" href="<?=$Search->getRequestUri()."&addFilter=Keyword"?>" class="btn btn-lg btn-block btn-warning mymodal search" style="margin-bottom:0.3em">
			<i class="fa fa-plus"> </i> Aggiungi filtro parole chiave
        </a>
	</div>
</div>
<div class="row">
	<div class="col-lg-6">
		<button type="submit" class="btn btn-lg btn-primary btn-block" style="margin-bottom:0.3em">
			<i class="fa fa-search"> </i> Cerca
        </button>
	</div>
	<div class="col-lg-6">
		<a href="<?=$Search->getRequestUri()."&reset"?>" class="btn btn-lg btn-danger btn-block" style="margin-bottom:0.3em">
			<i class="fa fa-times"> </i> Reset
        </a>
	</div>
</div>
<input type="hidden" name="postIt" value="" />
</form>
