<?php

class me_twomice_civicrm_aggregatehouseholdcontributions_FilterSet_First extends me_twomice_civicrm_aggregatehouseholdcontributions_FilterSet {


  function __construct() {
    $this->_name = 'first';
    $this->_requires_join = TRUE;
    $this->_scope = array(
      'qualifier_expression' => 'min(t.receive_date)',
      'qualifier_filter' => 'first_contribution_date',
      'qualifier_join' => 'receive_date',
      'scopes' => array(
        CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_SCOPE_EVER => array(
          'method' => CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_METHOD_GROUP,
          'table_1_where_filters' => 'NONE',
          'table_2_where_filters' => 'ALL',
        ),
        CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_SCOPE_DATE_RANGE => array(
          'method' => CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_METHOD_HAVING,
        ),
        CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_SCOPE_AMOUNT_RANGE => array(
          'method' => CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_METHOD_GROUP,
          'table_1_where_filters' => 'ALLEXCEPT',
          'table_2_where_filters' => array(
            'first_contribution_amount',
          ),
        ),
      ),
    );
    parent::__construct();
  }
  
  function _buildFilterCriteriaFields() {
    parent::_buildFilterCriteriaFields();
    $this->_filter_criteria_fields['first_contribution_scope'] = array(
      'title' => ts('"First Contribution" filter scope'),
      'operatorType' => CRM_Report_Form::OP_SELECT,
      'type' => CRM_Utils_Type::T_INT,
      'options'      => array(
        CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_SCOPE_EVER => ts('First contribution ever meets these criteria'),
        CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_SCOPE_DATE_RANGE => ts('First contribution meeting these criteria was within this date range'),
        CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_SCOPE_AMOUNT_RANGE => ts('First contribution meeting these criteria was within this amount range'),
      ),
      'grouping' => 'first-filters',
      '_force_pseudofield' => TRUE,
      'pseudofield' => TRUE,
    );
  }
}