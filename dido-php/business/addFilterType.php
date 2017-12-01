<?php 
require_once("../config.php");
if(!Utils::checkAjax()) die();

$className = $_GET[Search::SOURCE];
$filterClass = "FilterType".$className;

$D = new $className($Application->getDBConnector());

$field = $className::NOME;
$typesOnDb = Utils::getListfromField($D->useView(true)->getRealDistinct($field),SharedDocumentConstants::NOME);
//$closed = isset($_GET[SharedDocumentConstants::CLOSED]) ? $_GET[SharedDocumentConstants::CLOSED] : null; 

$ADB = $Application->getApplicationPart(Application::DOCUMENTBROWSER);

$types = $filterClass::getTypes($ADB, $className, $typesOnDb);
 
?>

<form>

	
<?php 
	if(count($types)) foreach($types as $cat=>$typeList):
?>
	<fieldset>
		<legend><?= ucfirst($cat) ?></legend>
	<?php foreach($typeList as $k=>$label): $label = ucfirst($label)?> 
	
		<div id="ft-<?=$k?>" class="checkbox checkbox-success checkbox-circle">
	       	<input id="type-<?=$k?>" class="styled" type="checkbox" value="<?=$label?>">
	       	<label for="type-<?=$k?>"><?=$label?></label>
		</div>
	<?php endforeach;?>
	</fieldset>
<?php endforeach;?>
	<div id="filterResult" class="btn-success">
	</div>
	
</form>
<script>
	$("#boxFilters input").each(function(el){
		var idToRemove =  $(this).attr("id").replace(/filter-type-/,"ft-");
		$("#"+idToRemove).remove();
	});

	$(".checkbox input").click(function(e){
		var action = $(this).prop("checked");
		if(action)
			$('<input id="filter-'+$(this).attr("id")+'" data-label="'+$(this).val()+'" type="hidden" name="nome['+$(this).attr("id")+']" value="'+$(this).val()+'" class="success"/>').appendTo($("#filterResult"));
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