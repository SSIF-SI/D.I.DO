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
<?php	if(is_null($idP) && count($listPersone) == 0): ?> 
						<div class="modal-body">
							<div class="alert alert-danger">
								Non è possibile aggiungere ulteriori firmatari
							</div>
						</div>
<?php 	else: ?>					
					<form id="firmatario" name="firmatario" method="POST">
						<div class="modal-body">
<?php 
			if(is_null($idP)){				
				echo HTMLHelper::select('id_persona', "Persona", $listPersone, isset($signer['id_persona']) ? $signer['id_persona'] : null);
			} else {
				echo"<label for=\"persona\">Persona:</label><p id=\"persona\">".PersonaleHelper::getNominativo($idP)."</p>";
				echo HTMLHelper::input('hidden', "id_persona", null, $idP);
			}
	 		echo HTMLHelper::input('textarea', "pkey", "Chiave Pubblica", isset($signer['pkey']) ? $signer['pkey'] : null,null,true);
?>
			 			</div>
			 			<div class="modal-footer">
			 				<button type="button" class="btn btn-default" data-dismiss="modal"><span class="fa fa-power-off fa-1x fa-fw"></span> Chiudi</button>
			                <button type="submit" class="btn btn-primary mymodal" id="mysubmit" form="firmatario"><span class="fa fa-save fa-1x fa-fw"></span> Salva firmatario</button>
			            </div>
		            </form>
<?php	endif;
		return ob_get_clean();
	}
	
	static function createModalFixedSigner($id_fs = null){
		$FixedSigner = new FixedSigners(Connector::getInstance());
		$fixed_signer = is_null($id_fs) ? $FixedSigner->getStub() : $FixedSigner->get(array('id_fs' =>$id_fs));
		
		$signersRoles = new SignersRoles(Connector::getInstance());
		$signer_roles = Utils::getListfromField(Utils::filterList($signersRoles->getAll('sigla','id_sr'),'fixed_role',1),'descrizione');
		$assignable_roles = array_diff_key($signer_roles,Utils::getListfromField($FixedSigner->getAll(),null,'id_sr'));
		
		$listPersone = ListHelper::listSigners();
		$listDelegati = array(null => "--Nessuno--") + $listPersone;
		
		ob_start();
?>
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
							<h4 class="modal-title" id="myModalLabel">Firmatario fisso</h4>
						</div>
<?php	if(is_null($id_fs) && count($assignable_roles) == 0): ?> 
						<div class="modal-body">
							<div class="alert alert-danger">
								Non è possibile aggiungere ulteriori firmatari fissi
							</div>
						</div>
<?php 	else: ?>
						<form id="firmatario" name="firmatario" method="POST">
							<div class="modal-body">
<?php 
			if(!is_null($id_fs)){
				echo"<label for=\"ruolo\">Ruolo firmatario:</label><p id=\"ruolo\">".$signer_roles[$fixed_signer["id_sr"]]."</p>";
				echo HTMLHelper::input('hidden', "id_fs", null, $id_fs);
			}else{
				echo HTMLHelper::select("id_sr", "Ruolo", $assignable_roles);		
			}
			echo HTMLHelper::select("id_persona", "Persona", $listPersone,$fixed_signer['id_persona']);		
			echo HTMLHelper::select("id_delegato", "Delegato", $listDelegati,$fixed_signer['id_delegato']);		
?>
				 			</div>
				 			<div class="modal-footer">
				 				<button type="button" class="btn btn-default" data-dismiss="modal"><span class="fa fa-power-off fa-1x fa-fw"></span> Chiudi</button>
				                <button type="submit" class="btn btn-primary" id="mysubmit" form="firmatario"><span class="fa fa-save fa-1x fa-fw"></span> Salva firmatario</button>
				            </div>
			            </form>
<?php 	endif;
		return ob_get_clean();
	}
	
	static function createModalVariableSigner($id_vs = null){
		$VariableSigner = new VariableSigners(Connector::getInstance());
		$variable_signer = is_null($id_vs) ? $VariableSigner->getStub() : $VariableSigner->get(array('id_vs' =>$id_vs));
	
		$signersRoles = new SignersRoles(Connector::getInstance());
		$signer_roles = Utils::getListfromField(Utils::filterList($signersRoles->getAll('sigla','id_sr'),'fixed_role',0),'descrizione');
		
		$listPersone = ListHelper::listSigners();
		
		ob_start();
		?>
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
								<h4 class="modal-title" id="myModalLabel">Firmatario Variabile</h4>
							</div>
							<form id="firmatario" name="firmatario" method="POST">
								<div class="modal-body">
	<?php									
								if(!is_null($id_vs)) echo HTMLHelper::input('hidden', "id_vs", null, $id_vs);
								echo HTMLHelper::select("id_sr", "Ruolo", $signer_roles,$variable_signer['id_sr']);		
								echo HTMLHelper::select("id_persona", "Persona", $listPersone,$variable_signer['id_persona']);		
	?>
					 			</div>
					 			<div class="modal-footer">
					 				<button type="button" class="btn btn-default" data-dismiss="modal"><span class="fa fa-power-off fa-1x fa-fw"></span> Chiudi</button>
					                <button type="submit" class="btn btn-primary" id="mysubmit" form="firmatario"><span class="fa fa-save fa-1x fa-fw"></span> Salva firmatario</button>
					            </div>
				            </form>
	<?php
			return ob_get_clean();
		}
	
	static function getSigners(){

		$signersObj = new Signers(Connector::getInstance());
		$signers = $signersObj->getAll(null,'id_persona');
		
		$metadata = self::createMetadata($signers,"Signers",'id_persona', array('id_persona' => 'PersonaleHelper::getNominativo', 'pkey' => 'Utils::shorten'));
		$signers = HTMLHelper::editTable($signers, $metadata['buttons'], $metadata['substitutes']);
		
		$signatureObj = new Signature(Connector::getInstance());
		$signatures = $signatureObj->getAll('sigla','id_item');
		
		$fixed_signers = Utils::filterList($signatures, 'fixed_role', 1);
		$metadata = self::createMetadata($fixed_signers,"FixedSigners",'id_fs', array('id_persona' => 'PersonaleHelper::getNominativo', 'id_delegato'=> 'PersonaleHelper::getNominativo'));
		$fixed_signers = HTMLHelper::editTable($fixed_signers, $metadata['buttons'], $metadata['substitutes'], array('descrizione'=>'Ruolo'), array('id_item','fixed_role','pkey','pkey_delegato','sigla'));
		
		$variable_signers = Utils::filterList($signatures, 'fixed_role', 0);
		$metadata = self::createMetadata($variable_signers,"VariableSigners","id_vs", array('id_persona'=> 'PersonaleHelper::getNominativo'));
		$variable_signers = HTMLHelper::editTable($variable_signers, $metadata['buttons'], $metadata['substitutes'], array('descrizione'=>'Ruolo'), array('id_item','fixed_role','pkey','id_delegato','pkey_delegato','sigla'));
		
		return array('all' => $signers, 'fixed' => $fixed_signers, 'variable' => $variable_signers);
	}
	
	static function createMetadata($list, $table_suffix,$idname, $substitutes_keys){
		$substitutes = array();
		$buttons = array();
	
	
		foreach($list as $k=>$signer){
			foreach($substitutes_keys as $key=>$callback){
				$substitutes[$k][$key] = call_user_func($callback,$signer[$key]);
			}
	
			$buttons[$k] = array(
					'Modifica'	=> array(
							'type' => 'primary',
							'href' => BUSINESS_HTTP_PATH."signature.php?list=$table_suffix&$idname=".$k,
							'icon' => 'pencil',
							'class'=> 'mymodal edit'),
					'Elimina'	=> array(
							'type' => 'danger',
							'href' => BUSINESS_HTTP_PATH."signature.php?list=$table_suffix&$idname=".$k."&delete",
							'icon' => 'trash',
							'class'=> 'mymodal delete')
			);
		}
	
		return array('substitutes' => $substitutes, 'buttons' => $buttons);
	}
	
}
?>