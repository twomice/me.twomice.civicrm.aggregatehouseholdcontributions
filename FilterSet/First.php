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

  function _buildFilterTables($obj) {

    // Get scope for this filter from params.
    $selected_scope = $obj->_params[$this->_name . '_contribution_scope_value'];

    // Define a table name for the temporary table to be built for this filterset.
    $table_name = $obj->_temp_table_prefix . $this->_name;

    $report = clone $obj;
    // Remove any filters from $report->_columns.
    foreach ($report->_columns as &$components) {
      unset($components['filters']);
    }

    $filter_set_fields = $this->_getFields(FALSE);


    switch($selected_scope) {
      case CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_SCOPE_EVER:

  //      and create a temp table along these lines:
        $report->_columns[$obj->_tablename]['filters'] = array();

        // Re-build filterset fields for this filterset.
        foreach ($filter_set_fields as $field_name => $field) {
          if ($field_name != $this->_name . '_contribution_scope') {
            $field['pseudofield'] = FALSE;
            $report->_columns[$obj->_tablename]['filters'][$field_name] = $field;
          }
        }
        $report->_filterWhere();
        $temporary = $obj->_debug_temp_table($table_name);
        $query = "CREATE $temporary TABLE {$table_name} (INDEX (`aggid`))
          SELECT
              t.aggid, min(t.receive_date) as qualifier_first
            FROM
              $obj->_tablename t
              {$report->_where}
        ;
        ";
        $obj->_debugDsm($query, 'query (only) for filter set '. $this->_name);
        CRM_Core_DAO::executeQuery($query);

        break;
      case CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_SCOPE_DATE_RANGE:
        /*
                'method' => CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_METHOD_HAVING,
         */

          $report->_columns[$obj->_tablename]['filters'] = array();
          $filter_set_fields[$this->_scope['qualifier_filter']]['having'] = TRUE;
          $filter_set_fields[$this->_scope['qualifier_filter']]['dbAlias'] = 'qualifier_first';
          $report->_columns[$obj->_tablename]['filters'] = $filter_set_fields;
          $report->_filterWhere();
          $temporary = $obj->_debug_temp_table($table_name);
          $query =   "
            CREATE $temporary TABLE {$table_name} (INDEX (`aggid`))
            SELECT
              min(t.receive_date) as qualifier_first, t.aggid
            FROM
              $obj->_tablename t
            {$report->_where}
            group by aggid
            {$report->_having}
            ;
          ";
          $obj->_debugDsm($query, 'query (only) for filter set '. $this->_name);
          CRM_Core_DAO::executeQuery($query);
        break;

      case CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_SCOPE_AMOUNT_RANGE:
        /*
                'method' => CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_METHOD_GROUP,
                'supporting_table_filter_fields' => 'ALLEXCEPT',
                'primary_table_filter_fields' => array(
                  'first_contribution_amount',
                ),
         */

        $table_name_pre = $obj->_temp_table_prefix . "scope_{$this->_name}_pre";

        $report->_columns[$obj->_tablename]['filters'] = array();

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
            $report->_columns[$obj->_tablename]['filters'][$field_name] = $field;
          }
        }

        $report->_filterWhere();
        $temporary = $obj->_debug_temp_table($table_name_pre);
        $query = "
          CREATE $temporary TABLE $table_name_pre (INDEX (  `aggid` ), INDEX (`qualifier_first`))
            SELECT
              t.aggid, min(t.receive_date) as qualifier_first
            FROM
              $obj->_tablename t
              {$report->_where}
              group by aggid
          ;
        ";
        $obj->_debugDsm($query, 'query 1 for filter set '. $this->_name);
        CRM_Core_DAO::executeQuery($query);

  //      and create a temp table along these lines:
        $field = $filter_set_fields['first_contribution_amount'];
        $field['pseudofield'] = FALSE;
        $report->_columns[$obj->_tablename]['filters'] = array(
          'first_contribution_amount' => $field
        );

        $report->_filterWhere();
        $temporary = $obj->_debug_temp_table($table_name);
        $query = "CREATE $temporary TABLE {$table_name} (INDEX (`aggid`))
          SELECT
              t.aggid, fc.qualifier_first
            FROM
              $obj->_tablename t
              INNER JOIN {$table_name_pre} fc ON fc.aggid = t.aggid AND fc.qualifier_first = t.receive_date
              {$report->_where}
        ;
        ";
        $obj->_debugDsm($query, 'query 2 for filter set '. $this->_name);

        CRM_Core_DAO::executeQuery($query);

        break;
      default:
        return;
    }

    $obj->_extraJoinTables[] = array(
      'name' => $table_name,
      'join' => 'INNER',
    );

  }
  
}