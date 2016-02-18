<?php

class me_twomice_civicrm_aggregatehouseholdcontributions_FilterSet_Largest extends me_twomice_civicrm_aggregatehouseholdcontributions_FilterSet {


  function __construct() {
    $this->_name = 'largest';
    $this->_requires_join = TRUE;
    $this->_column_settings = array(
      'qualifier_expression' => 'max(t.total_amount)',
      'method' => CIVIREPORT_AGGREGATE_HOUSEHOLD_COLUMN_METHOD_SINGLE,
    );
    $this->_scope = array (
      'has_scope_option' => TRUE,
      'qualifier_expression' => 'max(t.total_amount)',
      'qualifier_filter' => 'largest_contribution_amount',
      'qualifier_join' => 'total_amount',
      'scopes' => array(
        CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_SCOPE_EVER => array(
          'method' => CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_METHOD_GROUP,
          'table_1_where_filters' => 'NONE',
          'table_2_where_filters' => 'ALL',
        ),
        CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_SCOPE_DATE_RANGE => array(
          'method' => CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_METHOD_GROUP,
          'table_1_where_filters' => 'ALLEXCEPT',
          'table_2_where_filters' => array(
            'largest_contribution_date',
          ),
        ),
        CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_SCOPE_AMOUNT_RANGE => array(
          'method' => CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_METHOD_HAVING,
        ),
      ),
    );
    parent::__construct();
  }


  function _buildFilterCriteriaFields() {
    parent::_buildFilterCriteriaFields();
    $this->_filter_criteria_fields['largest_contribution_scope'] = array(
      'title' => ts('"Largest Contribution" filter scope'),
      'operatorType' => CRM_Report_Form::OP_SELECT,
      'type' => CRM_Utils_Type::T_INT,
      'options'      => array(
        CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_SCOPE_EVER => ts('Largest contribution ever meets these criteria'),
        CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_SCOPE_DATE_RANGE => ts('Largest contribution meeting these criteria was within this date range'),
        CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_SCOPE_AMOUNT_RANGE => ts('Largest contribution meeting these criteria was within this amount range'),
      ),
      'grouping' => 'largest-filters',
      '_force_pseudofield' => TRUE,
    );
  }

}