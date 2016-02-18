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

  function _buildFilterTables($object) {
    $report = clone $object;

    // Remove any filters from $this->_columns.
    foreach ($report->_columns as $table_name => &$components) {
      unset($components['filters']);
    }

    // Re-build filterset fields for this filterset.
    $filters = $report->_getFilterSetFields($filter_set_name);
    $filter_set_fields = $this->_adjustFilterSetPseudofield($filters, FALSE, $filterset_name);

    // Get scope for this filter from params, or default to CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_SCOPE_NONE.
    $selected_scope = $this->_params[$filter_set_name . '_contribution_scope_value'] ?: CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_SCOPE_NONE;

    // Each scope has a method (see CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_METHOD_GROUP
    // and CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_METHOD_HAVING).
    $method = $filter_set->_scope['scopes'][$selected_scope]['method'];

    // Define a table name for the temporary to be built for this filterset,
    // and delete or make temporary the table, depending on $this->-debug setting.
    $table_name = $this->_temp_table_prefix . $filter_set_name;
    $temporary = $this->_debug_temp_table($table_name);

    $qualifier_column_name = "qualifier_{$filter_set_name}";

    if ($method == CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_METHOD_GROUP) {
      $table_name_pre = $this->_temp_table_prefix . "scope_{$filter_set_name}_pre";
      $temporary = $this->_debug_temp_table($table_name_pre);

      $this->_columns[$this->_tablename]['filters'] = array();

      $supporting_table_filter_fields = $filter_set->_scope['scopes'][$selected_scope]['supporting_table_filter_fields'];
      $primary_table_filter_fields = $filter_set->_scope['scopes'][$selected_scope]['primary_table_filter_fields'];

      if (is_array($supporting_table_filter_fields)) {
        foreach ($supporting_table_filter_fields as $field_name) {
          $field = $filter_set_fields[$field_name];
          $field['pseudofield'] = FALSE;
          $this->_columns[$this->_tablename]['filters'][$field_name] = $field;
        }
      }
      elseif ($supporting_table_filter_fields == 'ALL') {
        foreach ($filter_set_fields as $field_name => $field) {
          if ($field_name != $filter_set_name . '_contribution_scope') {
            $field['pseudofield'] = FALSE;
            $this->_columns[$this->_tablename]['filters'][$field_name] = $field;
          }
        }
      }
      elseif ($supporting_table_filter_fields == 'ALLEXCEPT') {
        foreach ($filter_set_fields as $field_name => $field) {
          if (
            $field_name != $filter_set_name . '_contribution_scope'
            && is_array($primary_table_filter_fields)
            && !in_array($field_name, $primary_table_filter_fields)
          ) {
            $field['pseudofield'] = FALSE;
            $this->_columns[$this->_tablename]['filters'][$field_name] = $field;
          }
        }
      }

      $this->_filterWhere();
      $query = "
        CREATE $temporary TABLE $table_name_pre (INDEX (  `aggid` ), INDEX (`$qualifier_column_name`))
          SELECT
            t.aggid, {$filter_set->_scope['qualifier_expression']} as $qualifier_column_name
          FROM
            $this->_tablename t
            {$this->_where}
            group by aggid
        ;
      ";
      $this->_debugDsm($query, 'query 1 for filter set '. $filter_set_name);
      CRM_Core_DAO::executeQuery($query);

//      and create a temp table along these lines:
      $this->_columns[$this->_tablename]['filters'] = array();

      if (is_array($primary_table_filter_fields)) {
        foreach ($primary_table_filter_fields as $field_name) {
          $field = $filter_set_fields[$field_name];
          $field['pseudofield'] = FALSE;
          $this->_columns[$this->_tablename]['filters'][$field_name] = $field;
        }
      }
      elseif ($primary_table_filter_fields == 'ALL') {
        foreach ($filter_set_fields as $field_name => $field) {
          if ($field_name != $filter_set_name . '_contribution_scope') {
            $field['pseudofield'] = FALSE;
            $this->_columns[$this->_tablename]['filters'][$field_name] = $field;
          }
        }
      }
      elseif ($primary_table_filter_fields == 'ALLEXCEPT') {
        foreach ($filter_set_fields as $field_name => $field) {
          if (
            $field_name != $filter_set_name . '_contribution_scope'
            && is_array($supporting_table_filter_fields)
            && !in_array($field_name, $supporting_table_filter_fields)
          ) {
            $field['pseudofield'] = FALSE;
            $this->_columns[$this->_tablename]['filters'][$field_name] = $field;
          }
        }
      }

      $this->_filterWhere();
      $query = "CREATE $temporary TABLE {$table_name} (INDEX (`aggid`))
        SELECT
            t.aggid, fc.{$qualifier_column_name}
          FROM
            $this->_tablename t
            INNER JOIN {$table_name_pre} fc ON fc.aggid = t.aggid AND fc.$qualifier_column_name = t.{$filter_set->_scope['qualifier_join']}
            {$this->_where}
      ;
      ";
      $this->_debugDsm($query, 'query 2 for filter set '. $filter_set_name);

      CRM_Core_DAO::executeQuery($query);
    }
    elseif ($method == CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_METHOD_HAVING) {
      $this->_columns[$this->_tablename]['filters'] = array();

      // Otherwise, if the method is HAVING, create one temp table, along these lines:
      $filter_set_fields[$filter_set->_scope['qualifier_filter']]['having'] = TRUE;
      $filter_set_fields[$filter_set->_scope['qualifier_filter']]['dbAlias'] = $qualifier_column_name;

      $this->_columns[$this->_tablename]['filters'] = $filter_set_fields;
      dsm($this->_columns);
      $this->_filterWhere();
      $query =   "
        CREATE $temporary TABLE {$table_name} (INDEX (`aggid`))
        SELECT
          {$filter_set->_scope['qualifier_expression']} as $qualifier_column_name, t.aggid
        FROM
          $this->_tablename t
        {$this->_where}
        group by aggid
        {$this->_having}
        ;
      ";
      $this->_debugDsm($query, 'query (only) for filter set '. $filter_set_name);
      CRM_Core_DAO::executeQuery($query);
    }
    $this->_extraJoinTables[] = array(
      'name' => $table_name,
      'join' => 'INNER',
    );

    $this->_columns = $backup_columns;
    $this->_havingClauses = $this->_whereClauses = array();
    $this->_where = $this->_having = '';
  }
  
}