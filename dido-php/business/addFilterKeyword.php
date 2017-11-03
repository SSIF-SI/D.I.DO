<?php
require_once ("../config.php");
if (! Utils::checkAjax ())
	die ();

$className = $_GET [Search::SOURCE];
$dataclassName = $className . "Data";

$A = new Application ();
$D = new $dataclassName ( $A->getDBConnector () );
$XDS = new XMLDataSource ();
$XMLParser = new XMLParser ();

$D->useView ( true );

if (isset ( $_GET [SharedDocumentConstants::CLOSED] )) {
	$list = $D->getBy ( SharedDocumentConstants::CLOSED, $_GET [SharedDocumentConstants::CLOSED], "id_md" );
	$listIdMdNome = $D->getRealDistinct ( $className::NOME, SharedDocumentConstants::CLOSED . "=" . $_GET [SharedDocumentConstants::CLOSED], $className::NOME );
	$listXMLSource = $D->getRealDistinct ( "xml", SharedDocumentConstants::CLOSED . "=" . $_GET [SharedDocumentConstants::CLOSED], "xml" );
} else {
	$list = $D->getAll ( "id_md" );
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
						"$input" => " " . $input [XMLParser::TYPE] 
				) );
			}
			if (isset ( $input [XMLParser::VALUES] )) {
				$inputsvalues = array_merge ( $inputsvalues, array (
						"$input" => " " . $input [XMLParser::VALUES] 
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
						"$input" => " " . $input [XMLParser::TYPE] 
				) );
			}
			if (isset ( $input [XMLParser::VALUES] )) {
				$inputsvalues = array_merge ( $inputsvalues, array (
						"$input" => " " . $input [XMLParser::VALUES] 
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


  $(function() {

	  var selected=[];
	  $('#keyword').change(function () {
	  	  selected  = $("#keyword").val();
	  	 $("#spotlight").autocomplete({
	    		source: "search.php?keyword="+ selected});
	  });
	  
	  $("#spotlight").autocomplete({
    		source: "search.php?keyword="+ selected,
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
    	    }
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
						<option id="kw-all" value="all">-- Tutte --</option>
		
<?php
foreach ( $listkeys as $k => $val ) :
	$option = ucwords ( $val );
	?>
					<option id="kw-<?=$k?>" value="<?=$val?>"><?=$option?></option>

<?php endforeach;?>
				</select>
				</div>
			</div>
		</div>
		<div class="col-lg-6">
			<div class="form-group">
				<label for="spotlight">Cerca:</label> <input type="text"
					class="form-control" id="spotlight" placeholder="Cerca"
					autocomplete="off" />
			</div>
		</div>
	</div>
</form>

<script>
	$("#keyword").selectpicker();
</script>



