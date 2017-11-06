<?php 
require_once("../config.php");
if(!Utils::checkAjax()) die();

$className = $_GET[Search::SOURCE];

$class = new ReflectionClass($className);
$static = $class->getConstants();

$A = new Application();
$D = new $className($A->getDBConnector());

$field = $className::NOME;

if(isset($static['TYPE']))
	$field .= ",".$className::TYPE;

$types = $D->getRealDistinct($field);
?>

<form>

	<fieldset>
		<legend>Tipi di Procedimento</legend>
<?php 
	foreach($types as $k=>$type):
		$label = ucwords($type[SharedDocumentConstants::NOME].(isset($type[Masterdocument::TYPE]) ? " - ".$type[Masterdocument::TYPE] : null)); 
?>
			<div id="ft-<?=$k?>" class="checkbox checkbox-success checkbox-circle">
            	<input id="type-<?=$k?>" class="styled" type="checkbox" value="<?=$label?>">
            	<label for="type-<?=$k?>"><?=$label?></label>
			</div>
<?php endforeach;?>
	<div id="filterResult" class="btn-success">
	</div>
	</fieldset>
</form>
<script>
	$(".filter-box input").each(function(el){
		var idToRemove =  $(this).attr("id").replace(/filter-type-/,"ft-");
		$("#"+idToRemove).remove();
	});

	$(".checkbox input").click(function(e){
		var action = $(this).prop("checked");
		if(action)
			$('<input id="filter-'+$(this).attr("id")+'" type="hidden" name="nome['+$(this).attr("id")+']" value="'+$(this).val()+'" class="success"/>').appendTo($("#filterResult"));
		else 
			$("#filter-"+$(this).attr("id")).remove();
		
	});
</script>
<script type="text/javascript">
    function changeState(el) {
        if (el.readOnly) el.checked=el.readOnly=false;
        else if (!el.checked) el.readOnly=el.indeterminate=true;
    }
</script>