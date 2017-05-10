<?php

class MasterDocumentsFilter implements IFilter {

	private $_Filters = array ();

	public function __construct(Array $listOfFilters = null) {
		if (is_array ( $listOffilters ))
			$this->_Filters = $list;
	}

	/*
	 * applica il filtro selezionato ad una lista, se vuoto ritorna la lista
	 */
	function applyFilter($list) {
		if (empty ( $this->_Filters ))
			return $list;
		foreach ( $this->_Filters as $k => $rule ) {
			if (! isset ( $rule ['operator'] ))
				$rule ['operator'] = Utils::OP_EQUAL;
			if (! isset ( $rule ['field'] ) || ! isset ( $rule ['value'] ))
				continue;
			$list = Utils::filterList ( $list, $rule ['field'], $rule ['value'], $rule ['operator'] );
		}
		return $list;
	}

	/*
	 * Svuota i filtri aggiunti
	 */
	function clean() {
		$this->_Filters = array ();
	}

	/*
	 * Aggiunge al filtro nuovi parametri
	 */
	function adding($list) {
		foreach ( $list as $key => $value ) {
			$this->_Filters [] = $value;
		}
		return count ( $list );
	}

	/*
	 * Ritorna l'array contentene i filtri
	 */
	function getFilter() {
		return $this->_Filter;
	}
}
?>