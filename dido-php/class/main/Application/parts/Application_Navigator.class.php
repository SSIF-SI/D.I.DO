<?php
class Application_Navigator {
	/*
	 * Connettore al DB, verrà utilizzato da svariate classi
	 */
	private $_dbConnector;
	
	/*
	 * Sorgente XML
	 */
	private $_XMLDataSource;
	
	/*
	 * Gestione dati dell'utente collegato
	 */
	private $_userManager;
	public function __construct(IDBConnector $dbConnector, XMLDataSource $XMLDataSource, UserManager $UserManager) {
		$this->_dbConnector = $dbConnector;
		$this->_XMLDataSource = $XMLDataSource;
		$this->_userManager = $UserManager;
	}
	private function getLeftMenu() {
		
		if (! $this->_userManager->isGestore ())
			return null;
		
		$this->_XMLDataSource
			->filter ( new XMLFilterSource ( [null] ) );
		if ($this->_userManager->isAdmin ())
			return $this->_XMLDataSource->getXmlTree ();
		return 
			$this->_XMLDataSource
				->filter ( new XMLFilterOwner ( $this->_userManager->getUser ()->getGruppi () ) )
				->filter ( new XMLFilterValidity ( date ( DB_DATE_FORMAT ) ) )
				->getXmlTree ();
	}
	
	public function renderLeftMenu() {
		$tree = $this->getLeftMenu ();
		?>
<li><a href="<?=HTTP_ROOT?>"><i class="fa fa-dashboard fa-fw"></i>
		Scrivania</a></li>
<?php if(!empty($tree)): ?>
<li><a href="#"><i class="fa fa-files-o fa-fw"></i> Nuovo documento<span
		class="fa arrow"></span></a>
	<ul class="nav nav-second-level">
			<?php foreach($tree as $categoria => $dati):?>
					<li><a href="#"><?=ucfirst($categoria)?><span class="fa arrow"></span></a>
			<ul class="nav nav-third-level">
					<?php foreach($dati as $tipoDocumento => $xmlList):?>
						<li><a
					href="<?=BUSINESS_HTTP_PATH."documento.php?type=$tipoDocumento"?>"><?=ucwords($tipoDocumento)?></a>
				</li>
					<?php endforeach;?>
					</ul></li>
			<?php endforeach;?>
			</ul></li>
<?php endif; ?>
	<?php if($this->_userManager->isAdmin()):?>
<li><a href="<?=ADMIN_BUSINESS_PATH."signature.php"?>"><i
		class="fa fa-pencil fa-fw"></i> Gestione firme</a></li>
<li><a href="<?=ADMIN_BUSINESS_PATH."permessi.php"?>"><i
		class="fa fa-key fa-fw"></i> Gestione permessi</a></li>

		<?php endif;
	}
}
?>

