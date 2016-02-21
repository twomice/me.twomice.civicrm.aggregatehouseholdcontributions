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
            'first_contribution_amount',
          ),
        ),
      ),
    );
    $this->_column_settings = array(
      'qualifier_expression' => 'min(t.receive_date)',
      'qualifier_join' => 'receive_date',
      'method' => CIVIREPORT_AGGREGATE_HOUSEHOLD_COLUMN_METHOD_JOINED,
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
      CREATE $temporary TABLE $this->_filterSetTableName_pre (INDEX (  `aggid` ), INDEX (`qualifier_first`))
        SELECT
          t.aggid, min(t.receive_date) as qualifier_first
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
      if ($field_name != 'first_contribution_scope') {
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
            INNER JOIN {$this->_filterSetTableName_pre} fc ON fc.aggid = t.aggid AND fc.qualifier_first = t.receive_date
            {$report->_where}
    ;
    ";
    $this->_obj->_debugDsm($query, 'query 2 for filter set '. $this->_name);
    CRM_Core_DAO::executeQuery($query);
  }

  function _buildFilterTablesForScopeDateRange($report) {
    $filter_set_fields = $this->_getFilterFields(FALSE);

    $report->_columns[$this->_obj->_tablename]['filters'] = array();
    $filter_set_fields['first_contribution_date']['having'] = TRUE;
    $filter_set_fields['first_contribution_date']['dbAlias'] = 'qualifier_first';
    $report->_columns[$this->_obj->_tablename]['filters'] = $filter_set_fields;
    $report->_filterWhere();
    $temporary = $this->_obj->_debug_temp_table($this->_filterSetTableName);
    $query =   "
      CREATE $temporary TABLE {$this->_filterSetTableName} (INDEX (`aggid`))
      SELECT
        min(t.receive_date) as qualifier_first, t.aggid
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
  /*
      'method' => CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_METHOD_GROUP,
      'supporting_table_filter_fields' => 'ALLEXCEPT',
      'primary_table_filter_fields' => array(
        'first_contribution_amount',
      ),
   */
    $filter_set_fields = $this->_getFilterFields(FALSE);

    $this->_filterSetTableName_pre = $this->_obj->_temp_table_prefix . "scope_{$this->_name}_pre";

    $report->_columns[$this->_obj->_tablename]['filters'] = array();

    $supporting_table_filter_fields = 'ALLEXCEPT';
    $primary_table_filter_fields = array(
      'first_contribution_amount',
    );

    foreach ($filter_set_fields as $field_name => $field) {
      if (
        $field_name != $this->_name . '_contribution_scope'
        && $field_name != 'first_contribution_amount'
      ) {
        $field['pseudofield'] = FALSE;
        $report->_columns[$this->_obj->_tablename]['filters'][$field_name] = $field;
      }
    }

    $report->_filterWhere();
    $temporary = $this->_obj->_debug_temp_table($this->_filterSetTableName_pre);
    $query = "
      CREATE $temporary TABLE $this->_filterSetTableName_pre (INDEX (  `aggid` ), INDEX (`qualifier_first`))
        SELECT
          t.aggid, min(t.receive_date) as qualifier_first
        FROM
          {$this->_obj->_tablename} t
          {$report->_where}
          group by aggid
      ;
    ";
    $this->_obj->_debugDsm($query, 'query 1 for filter set '. $this->_name);
    CRM_Core_DAO::executeQuery($query);

//      and create a temp table along these lines:
    $field = $filter_set_fields['first_contribution_amount'];
    $field['pseudofield'] = FALSE;
    $report->_columns[$this->_obj->_tablename]['filters'] = array(
      'first_contribution_amount' => $field
    );

    $report->_filterWhere();
    $temporary = $this->_obj->_debug_temp_table($this->_filterSetTableName);
    $query = "CREATE $temporary TABLE {$this->_filterSetTableName} (INDEX (`aggid`))
      SELECT
          t.aggid, fc.qualifier_first
        FROM
          {$this->_obj->_tablename} t
          INNER JOIN {$this->_filterSetTableName_pre} fc ON fc.aggid = t.aggid AND fc.qualifier_first = t.receive_date
          {$report->_where}
    ;
    ";
    $this->_obj->_debugDsm($query, 'query 2 for filter set '. $this->_name);

    CRM_Core_DAO::executeQuery($query);


///////////////////////////////////////////////////
/*

  elseif ($table_1_where_filters == 'ALLEXCEPT') {
    foreach ($filter_set_fields as $field_name => $field) {
      if (
        $field_name != $filter_set_name . '_contribution_scope'
        && is_array($table_2_where_filters)
        && !in_array($field_name, $table_2_where_filters)
      ) {
        $field['pseudofield'] = FALSE;
        $this->_columns[$this->_tablename]['filters'][$field_name] = $field;
      }
    }

    $this->_filterWhere();
    $query = "
      CREATE $temporary TABLE $table_name_pre (INDEX (  `aggid` ), INDEX (`$qualifier_column_name`))
        SELECT
          t.aggid, {$filter_set['scope_settings']['qualifier_expression']} as $qualifier_column_name
        FROM
          tmp_aggregated_household_contributions t
          {$this->_where}
          group by aggid
      ;
    ";
    $this->_debugDsm($query, 'query 1 for filter set '. $filter_set_name);
    CRM_Core_DAO::executeQuery($query);

//      and create a temp table along these lines:
    $this->_columns[$this->_tablename]['filters'] = array();

    foreach ($table_2_where_filters as $field_name) {
      $field = $filter_set_fields[$field_name];
      $field['pseudofield'] = FALSE;
      $this->_columns[$this->_tablename]['filters'][$field_name] = $field;
    }

    $this->_filterWhere();
    $query = "CREATE $temporary TABLE {$table_name} (INDEX (`aggid`))
      SELECT
          t.aggid
        FROM
          tmp_aggregated_household_contributions t
          INNER JOIN {$table_name_pre} fc ON fc.aggid = t.aggid AND fc.$qualifier_column_name = t.{$filter_set['scope_settings']['qualifier_join']}
          {$this->_where}
    ;
    ";
    $this->_debugDsm($query, 'query 2 for filter set '. $filter_set_name);

    CRM_Core_DAO::executeQuery($query);

 */
///////////////////////////////////////////////////

  }
  
}