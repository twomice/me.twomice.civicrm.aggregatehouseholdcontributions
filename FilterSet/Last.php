<?php

class me_twomice_civicrm_aggregatehouseholdcontributions_FilterSet_Last extends me_twomice_civicrm_aggregatehouseholdcontributions_FilterSet {


  function __construct() {
    $this->_name = 'Last';

    parent::__construct();

    $this->_scope_fields = array(
      'total_contribution_scope' => array(
        'title' => ts('"Last Contribution" filter scope'),
        'operatorType' => CRM_Report_Form::OP_SELECT,
        'type' => CRM_Utils_Type::T_INT,
        'options'      => array(
          CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_SCOPE_EVER => ts('Last contribution ever meets these criteria'),
          CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_SCOPE_DATE_RANGE => ts('Last contribution meeting these criteria was within this date range'),
          CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_SCOPE_AMOUNT_RANGE => ts('Last contribution meeting these criteria was within this amount range'),
        ),
        'grouping' => 'Last-filters',
        'pseudofield' => TRUE,
      ),
    );
  }

}