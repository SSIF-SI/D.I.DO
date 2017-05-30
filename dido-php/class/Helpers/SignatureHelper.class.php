<?php

class SignatureHelper {

	static function createModalSigner($idP = null) {
		$signersObj = new Signers ( DBConnector::getInstance () );
		$signer = is_null ( $idP ) ? $signersObj->getStub () : $signersObj->get ( array (
				Signers::ID_PERSONA => $idP 
		) );
		
		$listPersone = ListHelper::persone ();
		
		$signers = array_keys ( $signersObj->getAll ( null, Signers::ID_PERSONA ) );
		foreach ( $signers as $id_persona ) {
			if (array_key_exists ( $id_persona, $listPersone ) && $id_persona != $idP)
				unset ( $listPersone [$id_persona] );
		}
		
		ob_start ();
		?>	

<?php	if(is_null($idP) && count($listPersone) == 0): ?>
<div class="alert alert-danger">Non è possibile aggiungere ulteriori
	firmatari</div>
<?php 	else: ?>
<form id="firmatario" name="firmatario" method="POST">
<?php
			if (is_null ( $idP )) {
				echo HTMLHelper::select ( Signers::ID_PERSONA, "Persona", $listPersone, isset ( $signer [Signers::ID_PERSONA] ) ? $signer [Signers::ID_PERSONA] : null, null,false, true );
			} else {
				echo "<label for=\"persona\">Persona:</label><p id=\"persona\">" . PersonaleHelper::getNominativo ( $idP ) . "</p>";
				echo HTMLHelper::input ( 'hidden', Signers::ID_PERSONA, null, $idP );
			}
			echo HTMLHelper::input ( 'textarea', Signers::PKEY, "Chiave Pubblica", isset ( $signer [Signers::PKEY] ) ? $signer [Signers::PKEY] : null, true, true );
			?>
			            <div class="signatures list-group"></div>
	<div class="errorbox"></div>
	<label for="pdfConFirma">Pdf con firma digitale:</label><br /> <input
		class="file" type="file" id="pdfConFirma" name="pdfConFirma"
		data-allowed-file-extensions='["pdf", "p7m"]' />
</form>
<script
	src="<?=LIB_PATH?>kartik-v-bootstrap-fileinput/js/fileinput.min.js"></script>
<script src="<?=LIB_PATH?>kartik-v-bootstrap-fileinput/js/locales/it.js"></script>
<script>
    					$('#pkey').on("keyup",function(){
    						$(".signatures a").removeClass("active");
            			});

    					pdfConFirma();	
		    	    	
            			$("#pdfConFirma").on('filebatchuploadsuccess', function(event, data) {
			    	    	$(".signatures").html("");
			    	    	
			    	    	pdfConFirma();	
			    	    	$('<div class="panel panel-success">'+
			    	    			'<div class="panel-heading"> FIRME TROVATE: </div>'+
			    	    			'<div class="panel-body"></div>'+
			    	    			'<div class="panel-footer"> clicca su una delle firme trovate per aggiornare il campo "Chiave Pubblica" </div>'+
			    	    			'</div>').appendTo(".signatures");
	    	    			
							for (i = 0; i < data.response.signatures.length; i++) {
			    	    		$('<a href="#" data-pkey="'+data.response.signatures[i].publicKey+'" class="list-group-item list-group-item-action"><span class="fa fa-check-circle"></span>&nbsp;'+data.response.signatures[i].signer+'</a>').appendTo('.signatures .panel-body');
			    	    	}
							$(".signatures a").click(function(e){
								$(".signatures a").removeClass("active");
								$(this).addClass("active");
								$("#pkey").val($(this).attr("data-pkey"));
							}); 			    	   
			    		});

            			function pdfConFirma(){
	    					$("#pdfConFirma").fileinput('destroy')
		    	    		.fileinput({
				    	        language: "it",
				    	        uploadUrl: '../importPdf.php',
				    	        uploadAsync: false,
				    	        showPreview: false,
				    	        uploadExtraData: {getOnlySignatures:true},
				    	        elErrorContainer: '.errorbox'
				    	    })
			    	    	.fileinput('enable');
            			}
		    	    </script>



<?php
		endif;
		return ob_get_clean ();
	}

	static function createModalFixedSigner($id_fs = null) {
		$FixedSigner = new FixedSigners ( DBConnector::getInstance () );
		$fixed_signer = is_null ( $id_fs ) ? $FixedSigner->getStub () : $FixedSigner->get ( array (
				FixedSigners::ID_FS => $id_fs 
		) );
		
		$signersRoles = new SignersRoles ( DBConnector::getInstance () );
		$signer_roles = Utils::getListfromField ( Utils::filterList ( $signersRoles->getAll ( SignersRoles::SIGLA, SignersRoles::ID_SR ), SignersRoles::FIXED_ROLE, 1 ), SignersRoles::DESCRIZIONE );
		$assignable_roles = array_diff_key ( $signer_roles, Utils::getListfromField ( $FixedSigner->getAll (), null, FixedSigners::ID_SR ) );
		
		$listSigners = ListHelper::signers ();
		$listDelegati = array (
				null => "--Nessuno--" 
		) + $listSigners;
		
		ob_start ();
		?>
						
<?php	if(is_null($id_fs) && count($assignable_roles) == 0): ?>

<div class="alert alert-danger">Non è possibile aggiungere ulteriori
	firmatari fissi</div>
<?php 	else: ?>
<form id="firmatario" name="firmatario" method="POST">
<?php
			if (! is_null ( $id_fs )) {
				echo "<label for=\"".Roles::RUOLO."\">Ruolo firmatario:</label><p id=\"".Roles::RUOLO."\">" . $signer_roles [$fixed_signer [FixedSigners::ID_SR]] . "</p>";
				echo HTMLHelper::input ( 'hidden', FixedSigners::ID_FS, null, $id_fs );
			} else {
				echo HTMLHelper::select ( SignersRoles::ID_SR, "Ruolo", $assignable_roles );
			}
			echo HTMLHelper::select ( FixedSigners::ID_PERSONA, "Persona", $listSigners, $fixed_signer [FixedSigners::ID_PERSONA], null, false, true );
			echo HTMLHelper::select ( FixedSigners::ID_DELEGATO, "Delegato", $listDelegati, $fixed_signer [FixedSigners::ID_DELEGATO] );
			?>
				          </form>
<script>

			              		selectControl();
		              		
				        		$('#id_persona').on('change', function(){
				        			selectControl();
				     			});

								function selectControl(){
									$('#id_delegato option').removeAttr('disabled');
									var selected = $('#id_persona option:selected').val();
									$('#id_delegato').find('option[value="'+selected+'"]').attr('disabled','disabled');
									if($('#id_delegato option:selected').val() == $('#id_persona option:selected').val())
										$('#id_delegato').val(null);
				     		   	}
				         </script>



<?php
		endif;
		return ob_get_clean ();
	}

	static function createModalVariableSigner($id_vs = null) {
		$VariableSigner = new VariableSigners ( DBConnector::getInstance () );
		$variable_signer = is_null ( $id_vs ) ? $VariableSigner->getStub () : $VariableSigner->get ( array (
				VariableSigners::ID_VS => $id_vs 
		) );
		
		$signersRoles = new SignersRoles ( DBConnector::getInstance () );
		$signer_roles = Utils::getListfromField ( Utils::filterList ( $signersRoles->getAll ( SignersRoles::SIGLA, SignersRoles::ID_SR ), SignersRoles::FIXED_ROLE, 0 ), SignersRoles::DESCRIZIONE );
		
		$listPersone = ListHelper::Signers ();
		
		ob_start ();
		?>

<form id="firmatario" name="firmatario" method="POST">
	<?php
		if (! is_null ( $id_vs ))
			echo HTMLHelper::input ( 'hidden', VariableSigners::ID_VS, null, $id_vs );
		echo HTMLHelper::select ( SignersRoles::ID_SR, "Ruolo", $signer_roles, $variable_signer [SignersRoles::ID_SR], null, false, true );
		echo HTMLHelper::select ( VariableSigners::ID_PERSONA, "Persona", $listPersone, $variable_signer [VariableSigners::ID_PERSONA], null, false, true );
		?>
				            </form>
<?php
		return ob_get_clean ();
	}

	static function getSigners() {
		$signersObj = new Signers ( DBConnector::getInstance () );
		$signers = $signersObj->getAll ( null, Signers::ID_PERSONA );
		
		$metadata = self::createMetadata ( $signers, "Signers", Signers::ID_PERSONA, array (
				Signers::ID_PERSONA => 'PersonaleHelper::getNominativo',
				Signers::PKEY		=> 'Utils::shorten' 
		) );
		$signers = HTMLHelper::editTable ( $signers, $metadata ['buttons'], $metadata ['substitutes'] );
		
		$signatureObj = new Signature ( DBConnector::getInstance () );
		$signatures = $signatureObj->getAll ( Signature::SIGLA, Signature::ID_ITEM );
		
		$fixed_signers = Utils::filterList ( $signatures, Signature::FIXED_ROLE, 1 );
		$metadata = self::createMetadata ( $fixed_signers, "FixedSigners", [Signature::ID_ITEM => FixedSigners::ID_FS], array (
				FixedSigners::ID_PERSONA 	=> 'PersonaleHelper::getNominativo',
				FixedSigners::ID_DELEGATO 	=> 'PersonaleHelper::getNominativo' 
		) );
		$fixed_signers = HTMLHelper::editTable ( $fixed_signers, $metadata ['buttons'], $metadata ['substitutes'], array (
				SignersRoles::DESCRIZIONE => 'Ruolo' 
		), array (
				Signature::ID_ITEM,
				Signature::FIXED_ROLE,
				Signature::PKEY,
				Signature::PKEY_DELEGATO,
				Signature::SIGLA 
		) );
		
		$variable_signers = Utils::filterList ( $signatures, Signature::FIXED_ROLE, 0 );
		$metadata = self::createMetadata ( $variable_signers, "VariableSigners", [Signature::ID_ITEM => VariableSigners::ID_VS], array (
				VariableSigners::ID_PERSONA => 'PersonaleHelper::getNominativo' 
		) );
		$variable_signers = HTMLHelper::editTable ( $variable_signers, $metadata ['buttons'], $metadata ['substitutes'], array (
				SignersRoles::DESCRIZIONE => 'Ruolo' 
		), array (
				Signature::ID_ITEM,
				Signature::FIXED_ROLE,
				Signature::PKEY,
				Signature::ID_DELEGATO,
				Signature::PKEY_DELEGATO,
				Signature::SIGLA  
		) );
		
		return array (
				'all' => $signers,
				'fixed' => $fixed_signers,
				'variable' => $variable_signers 
		);
	}

	static function createMetadata($list, $table_suffix, $idname, $substitutes_keys) {
		if(!is_array($idname))
			$idname = [$idname];
		return HTMLHelper::createMetadata ( $list, basename ( $_SERVER ['PHP_SELF'] ) . "?list=$table_suffix", $idname 
		, $substitutes_keys );
	}

	static function createModalApplySign() {
		ob_start ();
		?>
<form id="firmatario" name="firmatario" method="POST"
	enctype="multipart/form-data" target="download_iframe"
	action="http://servsup.isti.cnr.it:8080/dido-php-test/business/signPdf.php?list=ApplySign">
	<div class="signatures list-group"></div>
	<div class="errorbox"></div>
	<label for="pdfDaFirmare">Pdf da firmare digitalmente:</label><br /> <input
		class="file" type="file" id="pdfDaFirmare" name="pdfDaFirmare"
		data-allowed-file-extensions='["pdf", "p7m"]' /> <label for="keystore">Keystore
		da utilizzare per la firma:</label><br /> <input class="file"
		type="file" id="keystore" name="keystore"
		data-allowed-file-extensions='["ks", "jks"]' /> <label for="pwd">Password:</label>
	<input type="password" class="form-control" name="pwd" id="pwd">
</form>
<iframe id='download_iframe' name='download_iframe'
	style="display: none"></iframe>
<script
	src="<?=LIB_PATH?>kartik-v-bootstrap-fileinput/js/fileinput.min.js"></script>
<script src="<?=LIB_PATH?>kartik-v-bootstrap-fileinput/js/locales/it.js"></script>
<script>
	    					signPdf();	
							function signPdf(){
								$("#keystore").fileinput('destroy')
			    	    		.fileinput({
					    	        language: "it",
// 					    	        uploadUrl: 'signPdf.php',
					    	        uploadAsync: true,
					    	        showPreview: false,
					    	        showUpload:	false,
					    	        uploadExtraData: {keystore:true},
					    	        elErrorContainer: '.errorbox',
					    	        allowedFileExtensions:['ks', 'jks']
					    	        
					    	    })
				    	    	.fileinput('enable');
				    	    	$("#pdfDaFirmare").fileinput('destroy')
			    	    		.fileinput({
					    	        language: "it",
// 					    	        uploadUrl: 'signPdf.php',
					    	        uploadAsync: true,
					    	        showPreview: false,
					    	        showUpload: false,
					    	        uploadExtraData: {pdf:true},
					    	        elErrorContainer: '.errorbox',
					    	        allowedFileExtensions:['pdf', 'p7m']
							    	        
					    	    })
				    	    	.fileinput('enable');
	            			}
			    	    </script>
<?php
		return ob_get_clean ();
	}
}
?>