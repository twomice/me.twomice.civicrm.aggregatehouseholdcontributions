<?php

class me_twomice_civicrm_aggregatehouseholdcontributions_FilterSet_Any extends me_twomice_civicrm_aggregatehouseholdcontributions_FilterSet {


  function __construct() {
    $this->_name = 'any';
    $this->_requires_join = FALSE;
    parent::__construct();
  }

  function _buildColumnCriteriaFields() {
    return;
  }


}