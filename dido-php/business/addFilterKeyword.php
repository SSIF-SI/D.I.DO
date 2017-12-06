<?php
require_once ("../config.php");
if (! Utils::checkAjax ())
	die ();

$className = str_replace("sLinks", "", $_GET [Search::SOURCE]);
$dataclassName = $className . "Data";

$D = new $dataclassName ( $Application->getDBConnector () );
$XDS = new XMLDataSource ();
$XMLParser = new XMLParser ();
$D->useView ( true );

if (isset ( $_GET [SharedDocumentConstants::CLOSED] )) {
	$list = $D->getBy ( SharedDocumentConstants::CLOSED, $_GET [SharedDocumentConstants::CLOSED], Masterdocument::ID_MD );
	$listIdMdNome = $D->getRealDistinct ( $className::NOME, SharedDocumentConstants::CLOSED . "=" . $_GET [SharedDocumentConstants::CLOSED], $className::NOME );
	$listXMLSource = $D->getRealDistinct ( "xml", SharedDocumentConstants::CLOSED . "=" . $_GET [SharedDocumentConstants::CLOSED], "xml" );
} else {
	$list = $D->getAll ( Masterdocument::ID_MD );
	$listXMLSource = $D->getRealDistinct ( "xml" );
	$listIdMdNome = $D->getRealDistinct ( $className::NOME );
}
$listIdMdNome = Utils::getListfromField ( $listIdMdNome, $className::NOME );
$listkeyValue = Utils::getListfromField ( $list, $dataclassName::VALUE, $dataclassName::KEY );
$listXMLSource = Utils::getListfromField ( $listXMLSource, "xml" );
$listkeys = $D->getRealDistinct ( AnyDocumentData::KEY, 'true', AnyDocumentData::KEY );
$listkeys = Utils::getListfromField ( $listkeys, AnyDocumentData::KEY, AnyDocumentData::KEY );

$XDS->filter ( new XMLFilterFilename ( $listXMLSource ) );

$inputstype = array ();
$inputsvalues = array ();
foreach ( $listXMLSource as $fName ) {
	$xml = $XDS->getSingleXmlByFilename ( $fName );
	$XMLParser->setXMLSource ( $xml ["xml"] );
	
	if ($className == "Masterdocument") {
		$inputs = $XMLParser->getMasterDocumentInputs ();
		foreach ( $inputs as $input ) {
			if (isset ( $input [XMLParser::TYPE] )) {
				$inputstype = array_merge ( $inputstype, array (
						"$input" => "" . $input [XMLParser::TYPE] 
				) );
			}
			if (isset ( $input [XMLParser::VALUES] )) {
				$inputsvalues = array_merge ( $inputsvalues, array (
						"$input" => "" . $input [XMLParser::VALUES] 
				) );
			}
		}
	}
	if ($className == "Document") {
		// Carico gli input di default se esistono
		$XMLParser->load ( FILES_PATH . SharedDocumentConstants::DEFAULT_INPUT_SOURCE );
		$docinputs = $XMLParser->getXmlSource ()->input;
		if (! is_array ( $docinputs ))
			$docinputs = array ();
		foreach ( $listIdMdNome as $id_md => $nome ) {
			$tmp = $XMLParser->getDocumentInputs ( $nome );
			if (is_array ( $tmp )) {
				$docinputs = array_merge ( $docinputs, $tmp );
			}
		}
		foreach ( $docinputs as $input ) {
			if (isset ( $input [XMLParser::TYPE] )) {
				$inputstype = array_merge ( $inputstype, array (
						"$input" => "" . $input [XMLParser::TYPE] 
				) );
			}
			if (isset ( $input [XMLParser::VALUES] )) {
				$inputsvalues = array_merge ( $inputsvalues, array (
						"$input" => "" . $input [XMLParser::VALUES] 
				) );
			}
		}
	}
}

// Utils::printr("Tipi:");
// Utils::printr($inputstype);
// Utils::printr("Valori:");
// Utils::printr($inputsvalues);
// Utils::printr($listkeyValue);

?>

<script>
// 	$(".filter-box input").each(function(el){
// 		var idToRemove =  $(this).attr("id").replace(/filter-type-/,"ft-");
// 		$("#"+idToRemove).remove();
// 	});


	function beforeFillFilterBox(){
		var keyword = $("#keyword").find("option:selected").val();
		var autocomplete=$("#spotlight").val();
		var originalValue=$("#hiddenkey").val()!=""? $("#hiddenkey").val():autocomplete;
		var nameid=(keyword+"+"+originalValue).split(' ').join('_');
// 		var optid=$("#keyword").find("option:selected").attr("id")+(autocomplete.split(' ').join('-'));
		$('<input id="filter-'+nameid+'" data-label="'+keyword[0].toUpperCase() + keyword.slice(1)+": "+autocomplete +'"type="hidden" name="keyword['+nameid+']" value="'+autocomplete+'" class="warning"/>').appendTo($("#filterResult"));
// 		if(typeof $("#keyword").find("option:selected").attr('transform')!= 'undefined'){
// 			$('<input id="transform-'+nameid+'" type="hidden" name="transform['+nameid+']" value="'+originalValue+'" class="transform" />').appendTo($("#filterResult"));
// 		}
				
	};
</script>

<script>


  $(function() {

	  $("#spotlight").autocomplete({
    		source: location.href+"&keyword="+ $("#keyword").find("option:selected").val(),
    		open: function(event) {
    	        $('.ui-autocomplete').css('height', 'auto');
    	        var $input = $(event.target),
    	            inputTop = $input.offset().top,
    	            inputHeight = $input.height(),
    	            autocompleteHeight = $('.ui-autocomplete').height(),
    	            windowHeight = $(window).height();
    	        
    	        if ((inputHeight + inputTop+ autocompleteHeight) > windowHeight) {
    	            $('.ui-autocomplete').css('height', (windowHeight - inputHeight - inputTop - 20) + 'px');
    	        }
    	    },
	  		select: function (event, ui) {
	  			 var key = ui.item.key;
	  		     var value = ui.item.value;
	  		     $("#hiddenkey").val(key);	  		     
	  		   //store in session
	  		     document.valueSelectedForAutocomplete = value;
		    }
	    });
    	
	  $('.selectpicker').on('change', function(){
		    var selected=$(this).find("option:selected").val();
		   	var transform=typeof $(this).find("option:selected").attr('transform')!= "undefined" ?"&transform="+$(this).find("option:selected").attr('transform'):"";
		   	var type=typeof $(this).find("option:selected").attr('type')!= "undefined" ?"&type="+$(this).find("option:selected").attr('type'):"";
		    $("#spotlight")
		  	 	.autocomplete({source: location.href+type+transform+"&keyword="+selected})
		  	 	.autocomplete('search');
	  });
	  
	  
  });
  </script>
<form id="search-form">
	<div class="row">
		<div class="col-lg-6">
			<div class="form-group">
				<label for="keyword">Parola chiave:</label>
				<div class="select">
					<select id="keyword" name="kw-option" class="selectpicker"
						data-live-search="true" data-container="body" data-width="100%">
		
<?php
foreach ( $listkeys as $k => $val ) :
	$option = ucwords ( $val );
	$transform = isset ( $inputsvalues [$val] ) ? "transform='" . $inputsvalues [$val] . "'" : "";
	$type = isset ( $inputstype [$val] ) ? "type='" . $inputstype [$val] . "'" : "";
	?>
					<option id="<?=$k?>" <?=$type?> <?=$transform?> value="<?=$val?>"><?=$option?></option>

<?php endforeach;?>
				</select>
				</div>
			</div>
		</div>
		<div class="col-lg-6">
			<div class="form-group">
				<label for="spotlight">Cerca:</label> <input type="text"
					class="form-control" id="spotlight" placeholder="Cerca"
					autocomplete="off" /><input type="hidden" id="hiddenkey" />
			</div>
		</div>
	</div>

</form>
<div id="filterResult" class="btn-warning"></div>


<script>
	$("#keyword").selectpicker();
</script>



