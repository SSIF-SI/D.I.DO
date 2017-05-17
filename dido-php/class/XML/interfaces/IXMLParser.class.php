<?php

interface IXMLParser {

	public function getXMLSource();
	
	public function load($filename);

	public function setXMLSource($xml, $md_type = null);

	public function getMasterDocumentInputs();

	public function getDocList();

	public function getDocTypes();

	public function getSource();

	public function getOwner();

	public function isOwner(array $groups);

	public function isVisible(array $groups);

	public function isValid($date = null);

	public function getDocByName($docname);

	public function checkIfMustBeLoaded(&$document);
}