<?php

class me_twomice_civicrm_aggregatehouseholdcontributions_FilterSet_Any extends me_twomice_civicrm_aggregatehouseholdcontributions_FilterSet {

  function __construct() {
    $this->_name = 'any';
    parent::__construct();
  }

  function _buildColumnCriteriaFields() {
    return;
  }
  
  function _buildFilterTables($object) {
    return;
  }

  function _adjustPseudofield($filters, $is_constructor) {
    foreach ($filters as &$filter){
      if (array_key_exists('_force_pseudofield', $filter) && $filter['_force_pseudofield']) {
        $filter['pseudofield'] = TRUE;
      }
      else {
        $filter['pseudofield'] = FALSE;
      }
    }
    return $filters;
  }
  
}