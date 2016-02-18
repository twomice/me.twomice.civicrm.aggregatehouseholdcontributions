<?php

class me_twomice_civicrm_aggregatehouseholdcontributions_FilterSet_Last extends me_twomice_civicrm_aggregatehouseholdcontributions_FilterSet {


  function __construct() {
    $this->_name = 'last';
    $this->_requires_join = TRUE;
    $this->_column_settings = array(
      'qualifier_expression' => 'max(t.receive_date)',
      'qualifier_join' => 'receive_date',
      'method' => CIVIREPORT_AGGREGATE_HOUSEHOLD_COLUMN_METHOD_JOINED,
    );
    $this->_scope = array (
      'has_scope_option' => TRUE,
      'qualifier_expression' => 'max(t.receive_date)',
      'qualifier_filter' => 'last_contribution_date',
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
            'last_contribution_amount',
          ),
        ),
      ),
    );

    parent::__construct();
  }

  function _buildFilterCriteriaFields() {
    parent::_buildFilterCriteriaFields();
    $this->_filter_criteria_fields['last_contribution_scope'] = array(
      'title' => ts('"Last Contribution" filter scope'),
      'operatorType' => CRM_Report_Form::OP_SELECT,
      'type' => CRM_Utils_Type::T_INT,
      'options'      => array(
        CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_SCOPE_EVER => ts('Last contribution ever meets these criteria'),
        CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_SCOPE_DATE_RANGE => ts('Last contribution meeting these criteria was within this date range'),
        CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_SCOPE_AMOUNT_RANGE => ts('Last contribution meeting these criteria was within this amount range'),
      ),
      'grouping' => 'last-filters',
      '_force_pseudofield' => TRUE,
    );
  }
}

