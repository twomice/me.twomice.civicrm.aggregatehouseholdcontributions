<?php

class me_twomice_civicrm_aggregatehouseholdcontributions_FilterSet_Total extends me_twomice_civicrm_aggregatehouseholdcontributions_FilterSet {


  function __construct() {
    $this->_name = 'total';
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
  
  function _buildFilterTablesForScopeDefault($report) {
    $report->_columns[$this->_obj->_tablename]['filters'] = array();

    $filter_set_fields = $this->_getFilterFields(FALSE);
    $filter_set_fields['total_contribution_total']['having'] = TRUE;
    $filter_set_fields['total_contribution_total']['dbAlias'] = 'qualifier_' . $this->_name;

    $report->_columns[$this->_obj->_tablename]['filters'] = $filter_set_fields;
    $report->_filterWhere();
    $temporary = $this->_obj->_debug_temp_table($this->_filterSetTableName);
    $query =   "
      CREATE $temporary TABLE {$this->_filterSetTableName} (INDEX (`aggid`))
      SELECT
        sum(t.total_amount) as qualifier_total, t.aggid
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

  function _buildMyColumnTables($report) {
    /*
      'qualifier_expression' => 'sum(t.total_amount)',
      'method' => CIVIREPORT_AGGREGATE_HOUSEHOLD_COLUMN_METHOD_SINGLE,
     */
    $temporary = $this->_obj->_debug_temp_table($this->_columnTableName);
    $query =   "
      CREATE $temporary TABLE $this->_columnTableName (INDEX (  `aggid` ))
      SELECT
        t.aggid, sum(t.total_amount) as {$this->_columnFieldName}
      FROM
        {$this->_obj->_tablename} t
        {$report->_where}
        group by aggid

    ";
    $this->_obj->_debugDsm($query, "Only query for column: {$this->_name}");
    CRM_Core_DAO::executeQuery($query);
  }

}