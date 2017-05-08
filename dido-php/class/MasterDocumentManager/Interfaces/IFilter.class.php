<?php
interface IFilter{

	function applyFilter($list,Array $filters = []);
	function clean();
	function adding($list);
	function getFilter();
}
?>