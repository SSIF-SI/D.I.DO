<?php 
class Application_Navigator{
	/*
	 * Connettore al DB, verrà utilizzato da svariate classi
	 */
	private $_dbConnector;
	
	/*
	 * Sorgente XML
	 */
	private $_XMLDataSource;
	
	public function __construct(IDBConnector $dbConnector, $XMLDataSource){
		$this->_dbConnector = $dbConnector;
		$this->_XMLDataSource = $XMLDataSource;
	}
	
	public function getLeftMenu(){
		
		$tree = PermissionHelper::getInstance ()->isGestore () ? XMLBrowser::getInstance ()->getXmlTree ( true ) : null;
		?>
<li><a href="<?=HTTP_ROOT?>"><i class="fa fa-dashboard fa-fw"></i>
		Dashboard</a></li>
<?php if(!empty($tree)): ?>
<li><a href="#"><i class="fa fa-files-o fa-fw"></i> Nuovo documento<span
		class="fa arrow"></span></a>
	<ul class="nav nav-second-level">
				<?php foreach($tree as $categoria => $dati):?>
				<li><a href="#"><?=ucfirst($categoria)?><span class="fa arrow"></span></a>
			<ul class="nav nav-third-level">
					<?php foreach($dati['documenti'] as $tipoDocumento => $xmlList):?>
						<li><a
					href="<?=BUSINESS_HTTP_PATH."document.php?t=$tipoDocumento"?>"><?=ucwords($tipoDocumento)?></a>
				</li>
					<?php endforeach;?>
					</ul></li>
				<?php endforeach;?>
			</ul></li>
<?php endif; ?>
		<?php if(PermissionHelper::getInstance()->isAdmin()):?>
<li><a href="<?=BUSINESS_HTTP_PATH."signature.php"?>"><i
		class="fa fa-pencil fa-fw"></i> Gestione firme</a></li>
<li><a href="<?=BUSINESS_HTTP_PATH."permessi.php"?>"><i
		class="fa fa-key fa-fw"></i> Gestione permessi</a></li>
<?php endif;?>
<?php
	}
	
	private function renderLeftMenu($tree){
	}
	
}
?>