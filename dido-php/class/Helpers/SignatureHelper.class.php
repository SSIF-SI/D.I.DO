<?php 
class SignatureHelper{
	
	static function createModalSigner($idP = null){
		$signersObj = new Signers(Connector::getInstance());
		$signer = is_null($idP) ? $signersObj->getStub() : $signersObj->get(array('id_persona' => $idP));
		
		$listPersone = ListHelper::listPersone(); 
		
		$signers = array_keys($signersObj->getAll(null,'id_persona'));
		foreach( $signers as $id_persona){
			if(array_key_exists($id_persona, $listPersone)&& $id_persona!=$idP)
				unset($listPersone[$id_persona]);
		}

		ob_start();
?>	
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
						<h4 class="modal-title" id="myModalLabel">Firmatario</h4>
					</div>
					<form id="firmatario" name="firmatario" method="POST">
						<div class="modal-body">
<?php 
				if(is_null($idP)){				
					echo HTMLHelper::select('id_persona', "Persona", $listPersone, isset($signer['id_persona']) ? $signer['id_persona'] : null);
				} else {
					echo"<label for=\"persona\">Persona:</label><p id=\"persona\">".PersonaleHelper::getNominativo($idP)."</p>";
					echo HTMLHelper::input('hidden', "id_persona", null, $idP);
				}
	 			echo HTMLHelper::input('textarea', "pkey", "Chiave Pubblica", isset($signer['pkey']) ? $signer['pkey'] : null);
?>
			 			</div>
			 			<div class="modal-footer">
			 				<button type="button" class="btn btn-default" data-dismiss="modal"><span class="fa fa-power-off fa-1x fa-fw"></span> Chiudi</button>
			                <button type="submit" class="btn btn-primary mymodal" id="mysubmit" form="firmatario"><span class="fa fa-save fa-1x fa-fw"></span> Salva firmatario</button>
			            </div>
		            </form>
<?php
		return ob_get_clean();
	}
	
	static function createModalFixedSigner($idFs = null){
			$signatureObj = new Signature(Connector::getInstance());
			$signatures = is_null($idFs) ? $signatureObj->getAll("sigla","id_item") : $signatureObj->getBy('id_item', $idFs);
			$signatures = Utils::filterList($signatures, 'fixed_role', 1);
			$roleselected=reset($signatures);
		if(is_null($idFs)){
			$signersRolesObj=new SignersRoles(Connector::getInstance());
			$fixedRoles=$signersRolesObj->getAll("id_sr","sigla");
			$fixedRoles= Utils::filterList($fixedRoles, 'fixed_role', 1);
			$signatures=Utils::getListfromField($signatures,"sigla");		
			foreach($signatures as $id){
				if(array_key_exists($id, $fixedRoles))
					unset($fixedRoles[$id]);
			}
			$sigle=Utils::getListfromField($fixedRoles,"sigla");
				
		}
		$listPersone = array_map(function($id){ return self::getNominativo($id);}, Utils::getListfromField(Personale::getInstance()->getPersone(),'idPersona'));
		$listDelegati = array_map(function($id){ return self::getNominativo($id);}, Utils::getListfromField(Personale::getInstance()->getPersone(),'idPersona'));
		$listDelegati[-1]="Nessuno";
				
		ob_start();
		?>
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
							<h4 class="modal-title" id="myModalLabel">Assegna Ruolo</h4>
						</div>
						<form id="firmatario" name="firmatario" method="POST">
							<div class="modal-body">
	<?php 
								
						if(!is_null($idFs)){
							echo"<label for=\"sigla\">Sigla:</label><p id=\"sigla\">".$roleselected["sigla"]."</p>";
						}else{
							echo HTMLHelper::select('sigla', "Sigla", $sigle);

						}
							echo HTMLHelper::select('id_persona', "Persona", $listPersone, isset($roleselected["id_persona"]) ? $roleselected['id_persona'] : null);
							echo HTMLHelper::select('id_delegato', "Delegato",$listDelegati, isset($roleselected["id_delegato"]) ? $roleselected['id_delegato'] : null);

						
	?>
				 			</div>
				 			<div class="modal-footer">
				 				<button type="button" class="btn btn-default" data-dismiss="modal"><span class="fa fa-power-off fa-1x fa-fw"></span> Chiudi</button>
				                <button type="submit" class="btn btn-primary mymodal" id="mysubmit" form="firmatario"><span class="fa fa-save fa-1x fa-fw"></span> Salva firmatario</button>
				            </div>
			            </form>
	<?php
			return ob_get_clean();
		}
	
	static function getSigners(){

		$signersObj = new Signers(Connector::getInstance());
		$signers = $signersObj->getAll(null,'id_persona');
		
		$metadata = self::createMetadata($signers,"all", array('id_persona' => 'PersonaleHelper::getNominativo', 'pkey' => 'Utils::shorten'));
		$signers = HTMLHelper::editTable($signers, $metadata['buttons'], $metadata['substitutes']);
		
		$signatureObj = new Signature(Connector::getInstance());
		$signatures = $signatureObj->getAll('sigla','id_item');
		
		$fixed_signers = Utils::filterList($signatures, 'fixed_role', 1);
		$metadata = self::createMetadata($fixed_signers,"fixed", array('id_persona' => 'PersonaleHelper::getNominativo', 'id_delegato'=> 'PersonaleHelper::getNominativo'));
		$fixed_signers = HTMLHelper::editTable($fixed_signers, $metadata['buttons'], $metadata['substitutes'], array('id_item','fixed_role','pkey','pkey_delegato'));
		
		$variable_signers = Utils::filterList($signatures, 'fixed_role', 0);
		$metadata = self::createMetadata($variable_signers,"variable", array('id_persona'=> 'PersonaleHelper::getNominativo'));
		$variable_signers = HTMLHelper::editTable($variable_signers, $metadata['buttons'], $metadata['substitutes'], array('id_item','fixed_role','pkey','id_delegato','pkey_delegato'));
		
		return array('all' => $signers, 'fixed' => $fixed_signers, 'variable' => $variable_signers);
	}
	
	static function createMetadata($list, $table_suffix, $substitutes_keys){
		$substitutes = array();
		$buttons = array();
	
	
		foreach($list as $k=>$signer){
			foreach($substitutes_keys as $key=>$callback){
				$substitutes[$k][$key] = call_user_func($callback,$signer[$key]);
			}
	
			$buttons[$k] = array(
					'Modifica'	=> array(
							'type' => 'primary',
							'href' => BUSINESS_HTTP_PATH."signature.php?list=$table_suffix&id=".$k,
							'icon' => 'pencil'),
					'Elimina'	=> array(
							'type' => 'danger',
							'href' => BUSINESS_HTTP_PATH."signature.php?list=$table_suffix&id=".$k."&delete",
							'icon' => 'trash')
			);
		}
	
		return array('substitutes' => $substitutes, 'buttons' => $buttons);
	}
	
	static function getNominativo($id){
		return Personale::getInstance()->getPersona($id)['cognome']." ".Personale::getInstance()->getPersona($id)['nome'];
	}
}
?>