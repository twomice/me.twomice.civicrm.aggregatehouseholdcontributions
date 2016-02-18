<?php

class me_twomice_civicrm_aggregatehouseholdcontributions_FilterSet_Total extends me_twomice_civicrm_aggregatehouseholdcontributions_FilterSet {


  function __construct() {
    $this->_name = 'total';
    $this->_requires_join = TRUE;
    parent::__construct();
  }
  function _buildFilterCriteriaFields() {
    parent::_buildFilterCriteriaFields();
    $this->_filter_criteria_fields['total_contribution_total'] = array(
      '_is_filter_criteria' => TRUE,
      '_is_column_criteria' => FALSE,
      'title' => ts('Total contribution: total'),
      'type' => CRM_Utils_Type::T_MONEY,
      'dbAlias' => 'sum(total_amount)',
      'having' => TRUE,
      'grouping' => $grouping,
//      'pseudofield' => $pseudofield,
    );
  }

  function fdsa_buildFilterCriteriaFields() {
    parent::_buildFilterCriteriaFields();
//    $this-
      // Total filter needs one additional field.
      if ($filter_set_name == 'total') {
      }
  }
}