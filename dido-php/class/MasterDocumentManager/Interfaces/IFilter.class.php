<?php

interface IFilter {

	function applyFilter($listOfFilters);

	function clean();

	function adding($list);

	function getFilter();
}
?>