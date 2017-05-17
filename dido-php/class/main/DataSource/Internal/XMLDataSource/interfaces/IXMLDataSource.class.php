<?php 
interface IXMLDataSource{
	public function getXmlTree($onlyFilelist = false);
	public function filter(IXMLFilter $filter);
	public function resetFilters();
	public function getFirst();
	public function getSingleXmlByFilename($xmlFilename);
}
?>