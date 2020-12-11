<?php

class me_twomice_civicrm_aggregatehouseholdcontributions_FilterSet_Largest extends me_twomice_civicrm_aggregatehouseholdcontributions_FilterSet {

  public function __construct() {
    $this->_name = 'largest';
    parent::__construct();
  }

  public function _buildFilterCriteriaFields() {
    parent::_buildFilterCriteriaFields();
    $this->_filter_criteria_fields['largest_contribution_scope'] = array(
      'title' => E::ts('"Largest Contribution" filter scope'),
      'operatorType' => CRM_Report_Form::OP_SELECT,
      'type' => CRM_Utils_Type::T_INT,
      'options'      => array(
        CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_SCOPE_EVER => E::ts('Largest contribution ever meets these criteria'),
        CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_SCOPE_DATE_RANGE => E::ts('Largest contribution meeting these criteria was within this date range'),
        CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_SCOPE_AMOUNT_RANGE => E::ts('Largest contribution meeting these criteria was within this amount range'),
      ),
      'grouping' => 'largest-filters',
      '_force_pseudofield' => TRUE,
    );
  }

  public function _buildFilterTablesForScopeEver($report) {
    $filter_set_fields = $this->_getFilterFields(FALSE);
    /*
    CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_SCOPE_EVER => array(
    'method' => CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_METHOD_GROUP,
    'supporting_table_filter_fields' => 'NONE',
    'primary_table_filter_fields' => 'ALL',
    ),
     */
    $this->_filterSetTableName_pre = $this->_obj->_temp_table_prefix . "scope_{$this->_name}_pre";

    $report->_columns[$this->_obj->_tablename]['filters'] = array();
    $report->_filterWhere();
    $temporary = $this->_obj->_debug_temp_table($this->_filterSetTableName_pre);
    $query = "
      CREATE $temporary TABLE $this->_filterSetTableName_pre (INDEX (  `aggid` ), INDEX (`qualifier_largest`))
        SELECT
          t.aggid, max(t.total_amount) as qualifier_largest
        FROM
          tmp_aggregated_household_contributions t
          {$report->_where}
          group by aggid
      ;
    ";
    $this->_obj->_debugDsm($query, 'query 1 for filter set ' . $this->_name);
    CRM_Core_DAO::executeQuery($query);

    $report->_columns[$this->_obj->_tablename]['filters'] = array();
    foreach ($filter_set_fields as $field_name => $field) {
      if ($field_name != 'largest_contribution_scope') {
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
            INNER JOIN {$this->_filterSetTableName_pre} fc ON fc.aggid = t.aggid AND fc.qualifier_largest = t.total_amount
            {$report->_where}
    ;
    ";
    $this->_obj->_debugDsm($query, 'query 2 for filter set ' . $this->_name);
    CRM_Core_DAO::executeQuery($query);
  }

  public function _buildFilterTablesForScopeDateRange($report) {
    $filter_set_fields = $this->_getFilterFields(FALSE);

    /*
    CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_SCOPE_DATE_RANGE => array(
    'method' => CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_METHOD_GROUP,
    'supporting_table_filter_fields' => 'ALLEXCEPT',
    'primary_table_filter_fields' => array(
    'largest_contribution_date',
    ),
    ),
     */

    $report->_columns[$this->_obj->_tablename]['filters'] = array();

    foreach ($filter_set_fields as $field_name => $field) {
      if (
        $field_name != 'largest_contribution_scope'
        && $field_name != 'largest_contribution_date'
      ) {
        $field['pseudofield'] = FALSE;
        $report->_columns[$this->_obj->_tablename]['filters'][$field_name] = $field;
      }
    }

    $report->_filterWhere();
    $this->_filterSetTableName_pre = $this->_obj->_temp_table_prefix . "scope_{$this->_name}_pre";
    $temporary = $this->_obj->_debug_temp_table($this->_filterSetTableName_pre);
    $query = "
      CREATE $temporary TABLE $this->_filterSetTableName_pre (INDEX (  `aggid` ), INDEX (`qualifier_largest`))
        SELECT
          t.aggid, max(t.total_amount) as qualifier_largest
        FROM
          {$this->_obj->_tablename} t
          {$report->_where}
          group by aggid
      ;
    ";
    $this->_obj->_debugDsm($query, 'query 1 for filter set ' . $this->_name);
    CRM_Core_DAO::executeQuery($query);

    // and create a temp table along these lines:
    $report->_columns[$this->_obj->_tablename]['filters'] = array();

    $field = $filter_set_fields['largest_contribution_date'];
    $field['pseudofield'] = FALSE;
    $report->_columns[$this->_obj->_tablename]['filters']['largest_contribution_date'] = $field;

    $report->_filterWhere();
    $temporary = $this->_obj->_debug_temp_table($this->_filterSetTableName);
    $query = "CREATE $temporary TABLE {$this->_filterSetTableName} (INDEX (`aggid`))
      SELECT
          t.aggid
        FROM
          {$this->_obj->_tablename} t
          INNER JOIN {$this->_filterSetTableName_pre} fc ON fc.aggid = t.aggid AND fc.qualifier_largest = t.total_amount
          {$report->_where}
    ;
    ";
    $this->_obj->_debugDsm($query, 'query 2 for filter set ' . $this->_name);
    CRM_Core_DAO::executeQuery($query);
  }

  public function _buildFilterTablesForScopeAmountRange($report) {
    /*
    CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_SCOPE_AMOUNT_RANGE => array(
    'method' => CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_METHOD_HAVING,
    ),
     */
    $report->_columns[$this->_obj->_tablename]['filters'] = array();

    $filter_set_fields = $this->_getFilterFields(FALSE);
    $filter_set_fields['largest_contribution_amount']['having'] = TRUE;
    $filter_set_fields['largest_contribution_amount']['dbAlias'] = 'qualifier_largest';

    $report->_columns[$this->_obj->_tablename]['filters'] = $filter_set_fields;
    $report->_filterWhere();
    $temporary = $this->_obj->_debug_temp_table($this->_filterSetTableName);
    $query = "
      CREATE $temporary TABLE {$this->_filterSetTableName} (INDEX (`aggid`))
      SELECT
        max(t.total_amount) as qualifier_largest, t.aggid
      FROM
        {$this->_obj->_tablename} t
      {$report->_where}
      group by aggid
      {$report->_having}
      ;
    ";
    $this->_obj->_debugDsm($query, 'query (only) for filter set ' . $this->_name);
    CRM_Core_DAO::executeQuery($query);
  }

  public function _buildMyColumnTables($report) {
    /*
    'qualifier_expression' => 'max(t.total_amount)',
    'method' => CIVIREPORT_AGGREGATE_HOUSEHOLD_COLUMN_METHOD_SINGLE,
     */
    $temporary = $this->_obj->_debug_temp_table($this->_columnTableName);
    $query = "
      CREATE $temporary TABLE $this->_columnTableName (INDEX (  `aggid` ))
      SELECT
        t.aggid, max(t.total_amount) as {$this->_columnFieldName}
      FROM
        {$this->_obj->_tablename} t
        {$report->_where}
        group by aggid

    ";
    $this->_obj->_debugDsm($query, "Only query for column: {$this->_name}");
    CRM_Core_DAO::executeQuery($query);
  }

}
