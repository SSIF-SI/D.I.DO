<form id="upForm" method="POST" enctype="multipart/form-data">
<?=isset($docInfo) ? $docInfo : null?>
	<label for="pdfConFirma">Documento:</label><br />
	<input class="file" type="file" id="docToUpload" name="docToUpload"/>
	<label style="display:none" for="upFilePath">Documento:</label>
	<input type="hidden" id="upFilePath" name="upFilePath" value="" required="required"/>
	<input type="hidden" id="upFileName" name="upFileName" value=""/>
</form>
<script src="<?=LIB_PATH?>kartik-v-bootstrap-fileinput/js/fileinput.min.js"></script>
<script src="<?=LIB_PATH?>kartik-v-bootstrap-fileinput/js/locales/it.js"></script>

<script>
initFileInput();	

<?php if(!isset($docInfo)):?>
$('#salva').hide();
<?php endif;?>

$("#docToUpload").on('filebatchuploadsuccess', function(event, data) {
	var filename = $(".file-caption-name").first().text();
	$('#upFilePath').val(data.response.otherData.upFilePath);
	$('#upFileName').val(filename);
	
	$("#docToUpload").fileinput('destroy');
	$(".file").remove();
	$("<div class='alert alert-success'>"+filename+"<em></strong>").appendTo('#upForm');
<?php if(!isset($docInfo)):?>
	$('#salva').click();
<?php endif; ?>
});

function initFileInput(){
	$("#docToUpload")
		.fileinput('destroy')
		.fileinput({
			language: "it",
			showPreview: false,
			uploadUrl: 'importFile.php',
		    uploadAsync: false
   	 	}).fileinput('enable');
}

$('button[data-dismiss="modal"]').click(function(e){
	if($('#upFilePath').val() != ''){
		
	} 
});
</script>
<?php Utils::includeScript(SCRIPTS_PATH, "datepicker.js")?>