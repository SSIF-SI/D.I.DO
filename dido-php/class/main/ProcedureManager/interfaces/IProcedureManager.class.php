<?php

interface IProcedureManager {
	// MasterDocument
	public function createMasterdocument($md, $md_data);

	public function updateMasterdocument($md_data);

	public function deleteMasterdocument($md);

	public function removeMasterdocumentFolder($repositoryPath);
	// Document
	public function createDocument($doc, $data, $filePath, $repositoryPath);

	public function updateDocument($document, $data, $filePath = null, $repositoryPath = null);

	public function deleteDocument($doc, $ftpFolder);
}
?>