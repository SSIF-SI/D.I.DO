<?php

class Responder {

	private $_MasterDocumentManager;

	public function __construct() {
		$this->_MasterDocumentManager = new MasterDocumentManager ();
	}

	public function getMyMasterDocuments($filters = array()) {
		return $this->_MasterDocumentManager->getAllMyMasterDocuments ( $filters );
	}

	public function getSingleMasterDocument($id_md) {
		return $this->_MasterDocumentManager->getSingleMasterDocument ( $id_md );
	}

	public function actions($info, $id_doc) {
		// Posso scaricare un documento se il documento fa perte di quelli che
		// posso vedere o che posso scaricare:
		$docICanSee = $this->_MasterDocumentManager->getAllMyMasterDocuments ( [ 
				[ 
						'field' => 'id_md',
						'value' => $info ['md'] ['id_md'] 
				] 
		] );
		
		// Il documento può essere sempre scaricato se visibile dall'utente
		$canDownload = isset ( $docICanSee ['documents'] [$info ['md'] ['id_md']] [$id_doc] );
		
		// Il documento può essere caricato solo se:
		// 1. Il documento non è chiuso;
		$isDocumentstillOpen = $info ['documents'] [$id_doc] ['closed'] == 0;
		
		// 2. L'utente può gestire il master document padre
		$XMLParser = new XMLParser ( XMLBrowser::getInstance ()->getSingleXml ( $info ['md'] ['xml'] ) );
		$UserCanManageThis = PermissionHelper::getInstance ()->isGestore ( $XMLParser->getOwner () );
		
		// 3. L'utente ha firmato o deve firmare il documento.
		$DocMustBeSignedByUser = $info ['documents'] [$id_doc] ['mustBeSigned'] == 0;
		
		// Quindi:
		$canUpload = $isDocumentstillOpen && ($UserCanManageThis || $DocMustBeSignedByUser);
		
		// L'utente può modificare le info solo se è gestore del master document
		// padre
		return [ 
				'canDownload' => $canDownload,
				'canUpload' => $canUpload,
				'canEditInfo' => $UserCanManageThis 
		];
	}
}