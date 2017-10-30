<?php 
require_once("../config.php");
if(!Utils::checkAjax()) die();

$className = $_GET['source'];
$dataclassName=$className."Data";

$A = new Application();
$D = new $className($A->getDBConnector());
$D_data=new $dataclassName($A->getDBConnector());

if(isset($_GET['closed'])){
	$listIdDoc=$D->getBy("closed", $_GET['closed'], 'id_doc');
	$listIdDoc=	Utils::getListfromField($listIdDoc,"id_doc","id_doc");
	$ids=implode(",", $listIdDoc);
	$listdoc_data=$D_data->getBy("id_doc",$ids,'id_doc');
}
else {
	$listIdDoc=$D->getAll();
	$listIdDoc=	Utils::getListfromField($listIdDoc,"id_doc","id_doc");
	$ids=implode(",", $listIdDoc);
}

$keys = $D_data->getRealDistinct("key","id_doc IN (".$ids." )");
$keys=Utils::getListfromField($keys,"key");
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
	$("#select").on("change",function(){
		alert($(this).val());

// 		var action = $(this).prop("kw-option");
// 		echo action;
// 		if(action)
// 			$('<input id="filter-'+$(this).attr("id")+'" type="text" name="nome['+$(this).attr("id")+']" value="'+$(this).val()+'" />').appendTo($("#filterResult"));
// 		else 
// 			$("#filter-"+$(this).attr("id")).remove();
		
	});
</script>