<?php 
require_once("../config.php");
if(!Utils::checkAjax()) die();

$className = $_GET[Search::SOURCE];
$dataclassName=$className."Data";

$A = new Application();
$D = new $className($A->getDBConnector());
$D_data=new $dataclassName($A->getDBConnector());

if(isset($_GET[SharedDocumentCostants::CLOSED])){
	$listIdDoc=$D->getBy(Document::CLOSED, $_GET[SharedDocumentCostants::CLOSED], Document::ID_DOC);
	$listIdDoc=	Utils::getListfromField($listIdDoc,Document::ID_DOC,Document::ID_DOC);
	$ids=implode(",", $listIdDoc);
	$listdoc_data=$D_data->getBy(Document::ID_DOC,$ids,Document::ID_DOC);
}
else {
	$listIdDoc=$D->getAll();
	$listIdDoc=	Utils::getListfromField($listIdDoc,Document::ID_DOC,Document::ID_DOC);
	$ids=implode(",", $listIdDoc);
}

$keys = $D_data->getRealDistinct(AnyDocument::KEY,Document::ID_DOC . " IN (".$ids." )");
$keys=Utils::getListfromField($keys,AnyDocument::KEY);
?>

<form>
	<div class="form-group">
		<label>Parole Chiave</label>
		<div class="select">
		<select id="select" name="kw-option" >
<?php 
	foreach($keys as $k=>$val):
	$option=ucwords($val);
?>
	<option id="kw-<?=$k?>"  value="<?=$val?>"><?=$option?></option>

<?php endforeach;?>
<!-- TODO: MODIFICARE SCRIPT  E SELECT PER ASSOCIAZIONE VALORI -->
</select>
</div>

	</div>
	<div id="filterResult" class="btn-warning">
	</div>
</form>
<script>
	$(".filter-box input").each(function(el){
		var idToRemove =  $(this).attr("id").replace(/filter-kw-/,"kw-");
		$("#"+idToRemove).remove();
	});
	$(".select input").click(function(e){
		var action = $(this).prop("kw-option");
		echo action;
		if(action)
			$('<input id="filter-'+$(this).attr("id")+'" type="hidden" name="nome['+$(this).attr("id")+']" value="'+$(this).val()+'" />').appendTo($("#filterResult"));
		else 
			$("#filter-"+$(this).attr("id")).remove();
		
	});
</script>