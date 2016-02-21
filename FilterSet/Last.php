<?php

class me_twomice_civicrm_aggregatehouseholdcontributions_FilterSet_Last extends me_twomice_civicrm_aggregatehouseholdcontributions_FilterSet {


  function __construct() {
    $this->_name = 'last';
    $this->_requires_join = TRUE;
    $this->_scope = array (
      'qualifier_expression' => 'max(t.receive_date)',
      'qualifier_filter' => 'last_contribution_date',
      'qualifier_join' => 'receive_date',
      'scopes' => array(
        CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_SCOPE_EVER => array(
          'method' => CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_METHOD_GROUP,
          'supporting_table_filter_fields' => '',
          'primary_table_filter_fields' => 'ALL',
        ),
        CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_SCOPE_DATE_RANGE => array(
          'method' => CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_METHOD_HAVING,
        ),
        CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_SCOPE_AMOUNT_RANGE => array(
          'method' => CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_METHOD_GROUP,
          'supporting_table_filter_fields' => 'ALLEXCEPT',
          'primary_table_filter_fields' => array(
            'last_contribution_amount',
          ),
        ),
      ),
    );
    $this->_column_settings = array(
      'qualifier_expression' => 'max(t.receive_date)',
      'qualifier_join' => 'receive_date',
      'method' => CIVIREPORT_AGGREGATE_HOUSEHOLD_COLUMN_METHOD_JOINED,
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

  function _buildFilterTablesForScopeEver($report) {
    /*
          'method' => CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_METHOD_GROUP,
          'supporting_table_filter_fields' => '',
          'primary_table_filter_fields' => 'ALL',
     */
    $filter_set_fields = $this->_getFilterFields(FALSE);
    $this->_filterSetTableName_pre = $this->_obj->_temp_table_prefix . "scope_{$this->_name}_pre";
    $report->_columns[$this->_obj->_tablename]['filters'] = array();
    $report->_filterWhere();
    $temporary = $this->_obj->_debug_temp_table($this->_filterSetTableName_pre);
    $query = "
      CREATE $temporary TABLE $this->_filterSetTableName_pre (INDEX (  `aggid` ), INDEX (`qualifier_last`))
        SELECT
          t.aggid, max(t.receive_date) as qualifier_last
        FROM
          tmp_aggregated_household_contributions t
          {$report->_where}
          group by aggid
      ;
    ";
    $this->_obj->_debugDsm($query, 'query 1 for filter set '. $this->_name);
    CRM_Core_DAO::executeQuery($query);

    $report->_columns[$this->_obj->_tablename]['filters'] = array();
    foreach ($filter_set_fields as $field_name => $field) {
      if ($field_name != 'last_contribution_scope') {
        $field['pseudofield'] = FALSE;
        $report->_columns[$this->_obj->_tablename]['filters'][$field_name] = $field;
      }
    }
    $report->_filterWhere();
    $temporary = $this->_obj->_debug_temp_table($this->_filterSetTableName);
    $query = "CREATE $temporary TABLE {$this->_filterSetTableName} (INDEX (`aggid`))
      SELECT
          t.aggid
          FROM
            {$this->_obj->_tablename} t
            INNER JOIN {$this->_filterSetTableName_pre} fc ON fc.aggid = t.aggid AND fc.qualifier_last = t.receive_date
            {$report->_where}
    ;
    ";
    $this->_obj->_debugDsm($query, 'query 2 for filter set '. $this->_name);
    CRM_Core_DAO::executeQuery($query);
  }

  function _buildFilterTablesForScopeDateRange($report) {
    $filter_set_fields = $this->_getFilterFields(FALSE);

    $report->_columns[$this->_obj->_tablename]['filters'] = array();
    $filter_set_fields['last_contribution_date']['having'] = TRUE;
    $filter_set_fields['last_contribution_date']['dbAlias'] = 'qualifier_last';
    $report->_columns[$this->_obj->_tablename]['filters'] = $filter_set_fields;
    $report->_filterWhere();
    $temporary = $this->_obj->_debug_temp_table($this->_filterSetTableName);
    $query =   "
      CREATE $temporary TABLE {$this->_filterSetTableName} (INDEX (`aggid`))
      SELECT
        max(t.receive_date) as qualifier_last, t.aggid
      FROM
        {$this->_obj->_tablename} t
      {$report->_where}
      group by aggid
      {$report->_having}
      ;
    ";
    $this->_obj->_debugDsm($query, 'query (only) for filter set '. $this->_name);
    CRM_Core_DAO::executeQuery($query);
  }

  function _buildFilterTablesForScopeAmountRange($report) {
    $filter_set_fields = $this->_getFilterFields(FALSE);

    $this->_filterSetTableName_pre = $this->_obj->_temp_table_prefix . "scope_{$this->_name}_pre";

    $report->_columns[$this->_obj->_tablename]['filters'] = array();

    $supporting_table_filter_fields = 'ALLEXCEPT';
    $primary_table_filter_fields = array(
      'last_contribution_amount',
    );

    foreach ($filter_set_fields as $field_name => $field) {
      if (
        $field_name != $this->_name . '_contribution_scope'
        && $field_name != 'last_contribution_amount'
      ) {
        $field['pseudofield'] = FALSE;
        $report->_columns[$this->_obj->_tablename]['filters'][$field_name] = $field;
      }
    }

    $report->_filterWhere();
    $temporary = $this->_obj->_debug_temp_table($this->_filterSetTableName_pre);
    $query = "
      CREATE $temporary TABLE $this->_filterSetTableName_pre (INDEX (  `aggid` ), INDEX (`qualifier_last`))
        SELECT
          t.aggid, max(t.receive_date) as qualifier_last
        FROM
          {$this->_obj->_tablename} t
          {$report->_where}
          group by aggid
      ;
    ";
    $this->_obj->_debugDsm($query, 'query 1 for filter set '. $this->_name);
    CRM_Core_DAO::executeQuery($query);

//      and create a temp table along these lines:
    $field = $filter_set_fields['last_contribution_amount'];
    $field['pseudofield'] = FALSE;
    $report->_columns[$this->_obj->_tablename]['filters'] = array(
      'last_contribution_amount' => $field
    );

    $report->_filterWhere();
    $temporary = $this->_obj->_debug_temp_table($this->_filterSetTableName);
    $query = "CREATE $temporary TABLE {$this->_filterSetTableName} (INDEX (`aggid`))
      SELECT
          t.aggid, fc.qualifier_last
        FROM
          {$this->_obj->_tablename} t
          INNER JOIN {$this->_filterSetTableName_pre} fc ON fc.aggid = t.aggid AND fc.qualifier_last = t.receive_date
          {$report->_where}
    ;
    ";
    $this->_obj->_debugDsm($query, 'query 2 for filter set '. $this->_name);

    CRM_Core_DAO::executeQuery($query);
  }

}

