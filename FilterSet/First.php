<?php

class me_twomice_civicrm_aggregatehouseholdcontributions_FilterSet_First extends me_twomice_civicrm_aggregatehouseholdcontributions_FilterSet {

  
  function __construct() {
    $this->_name = 'first';
    parent::__construct();
  }
  
  function _buildFilterCriteriaFields() {
    parent::_buildFilterCriteriaFields();
    $this->_filter_criteria_fields['first_contribution_scope'] = array(
      'title' => E::ts('"First Contribution" filter scope'),
      'operatorType' => CRM_Report_Form::OP_SELECT,
      'type' => CRM_Utils_Type::T_INT,
      'options'      => array(
        CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_SCOPE_EVER => E::ts('First contribution ever meets these criteria'),
        CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_SCOPE_DATE_RANGE => E::ts('First contribution meeting these criteria was within this date range'),
        CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_SCOPE_AMOUNT_RANGE => E::ts('First contribution meeting these criteria was within this amount range'),
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

  }

  function _buildMyColumnTables($report) {
    /*
      'qualifier_expression' => 'min(t.receive_date)',
      'qualifier_join' => 'receive_date',
      'method' => CIVIREPORT_AGGREGATE_HOUSEHOLD_COLUMN_METHOD_JOINED,
     */
    $table_name_pre = "civireport_tmp_column_{$this->_name}_pre";
    $temporary = $this->_obj->_debug_temp_table($table_name_pre);
    $qualifier_column_name = "column_{$this->_name}";
    $query = "
      CREATE $temporary TABLE $table_name_pre (INDEX (  `aggid` ), INDEX (`$qualifier_column_name`))
      SELECT
        t.aggid, min(t.receive_date) as $qualifier_column_name
      FROM
    {$this->_obj->_tablename} t
      {$report->_where}
        group by aggid
      ;
    ";
    $this->_obj->_debugDsm($query, "PRE table query for column: {$this->_name}");
    CRM_Core_DAO::executeQuery($query);

    $temporary = $this->_obj->_debug_temp_table($this->_columnTableName);
    $query = "
      CREATE $temporary TABLE {$this->_columnTableName} (INDEX (`aggid`))
      SELECT
        t.aggid, t.total_amount as {$this->_columnFieldName}, t.receive_date as {$this->_columnFieldName}_date
      FROM
        {$this->_obj->_tablename} t
        INNER JOIN {$table_name_pre} p ON p.aggid = t.aggid AND p.$qualifier_column_name = t.receive_date
      {$report->_where}
      ;
    ";
    $this->_obj->_debugDsm($query, "Table query for column: {$filter_set_name}");
    CRM_Core_DAO::executeQuery($query);
  }
 
}