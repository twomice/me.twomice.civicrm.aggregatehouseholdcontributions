<?php

class me_twomice_civicrm_aggregatehouseholdcontributions_FilterSet_Any extends me_twomice_civicrm_aggregatehouseholdcontributions_FilterSet {

  public function __construct() {
    $this->_name = 'any';
    parent::__construct();
  }

  public function _buildColumnCriteriaFields() {
    return;
  }

  public function _buildFilterTables($object) {
    return;
  }

  public function _adjustPseudofield($filters) {
    foreach ($filters as &$filter) {
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
