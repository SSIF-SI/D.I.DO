<?php 
require_once("../config.php");
if(!Utils::checkAjax()) die();

$className = $_GET['source'];

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
	<div class="form-group">
		<label>Tipi di Procedimento</label>
<?php 
	foreach($types as $k=>$type):
		$label = ucwords($type['nome'].(isset($type['type']) ? " - ".$type['type'] : null)); 
?>
			<div id="ft-<?=$k?>" class="checkbox">
            	<label for="type-<?=$k?>">
                	<input id="type-<?=$k?>" type="checkbox"  value="<?=$label?>"><?=$label?>
				</label>
			</div>
<?php endforeach;?>
	</div>
	<div id="filterResult" class="btn-success">
	</div>
</form>
<script>
	$(".filter-box input").each(function(el){
		var idToRemove =  $(this).attr("id").replace(/filter-type-/,"ft-");
		$("#"+idToRemove).remove();
	});
	$(".checkbox input").click(function(e){
		var action = $(this).prop("checked");
		if(action)
			$('<input id="filter-'+$(this).attr("id")+'" type="hidden" name="nome['+$(this).attr("id")+']" value="'+$(this).val()+'" />').appendTo($("#filterResult"));
		else 
			$("#filter-"+$(this).attr("id")).remove();
		
	});
</script>
