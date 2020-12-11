<?php

/**
 * Base class for filterSets. Extends CRM_Report_Form as a lazy way to gain access
 * to read and write CRM_Report_Form::_columns when working with a cloned report
 * object (see $this->_obj).
 */
class me_twomice_civicrm_aggregatehouseholdcontributions_FilterSet extends CRM_Report_Form {
  var $_filter_criteria_fields = array();
  var $_column_criteria_fields = array();
  var $_name = '';
  var $_obj;
  var $_filterSetTableName = '';
  var $_columnFieldName = '';
  var $_columnTableName = '';

  public function __construct() {
    $this->_criteria_fields_base = array(
      $this->_name . '_contribution_date' => array(
        'name' => 'receive_date',
        'dbAlias' => 'receive_date',
        '_is_filter_criteria' => TRUE,
        '_is_column_criteria' => TRUE,
        '_base_title' => 'date',
        'title' => $this->_buildFilterCriteriaFieldLabel('date'),
        'type' => (CRM_Utils_Type::T_DATE | CRM_Utils_Type::T_TIME),
        'operatorType' => CRM_Report_Form::OP_DATE,
        'grouping' => $this->_name . '-filters',
      ),
      $this->_name . '_contribution_financial_type_id' => array(
        'name' => 'financial_type_id',
        'dbAlias' => 'financial_type_id',
        '_is_filter_criteria' => TRUE,
        '_is_column_criteria' => TRUE,
        '_base_title' => 'type',
        'title' => $this->_buildFilterCriteriaFieldLabel('type'),
        'operatorType' => CRM_Report_Form::OP_MULTISELECT,
        'type' => CRM_Utils_Type::T_INT,
        'options'      => CRM_Contribute_PseudoConstant::financialType(),
        'grouping' => $this->_name . '-filters',
      ),
      $this->_name . '_contribution_page_id' => array(
        'name' => 'contribution_page_id',
        'dbAlias' => 'contribution_page_id',
        '_is_filter_criteria' => TRUE,
        '_is_column_criteria' => TRUE,
        '_base_title' => 'page',
        'title' => $this->_buildFilterCriteriaFieldLabel('page'),
        'operatorType' => CRM_Report_Form::OP_MULTISELECT,
        'type' => CRM_Utils_Type::T_INT,
        'options'      => CRM_Contribute_PseudoConstant::contributionPage(),
        'grouping' => $this->_name . '-filters',
      ),
      $this->_name . '_contribution_status_id' => array(
        'name' => 'contribution_status_id',
        'dbAlias' => 'contribution_status_id',
        '_is_filter_criteria' => TRUE,
        '_is_column_criteria' => TRUE,
        '_base_title' => 'status',
        'title' => $this->_buildFilterCriteriaFieldLabel('status'),
        'operatorType' => CRM_Report_Form::OP_MULTISELECT,
        'type' => CRM_Utils_Type::T_INT,
        'options'      => CRM_Contribute_PseudoConstant::contributionStatus(),
        'default' => '1',
        'grouping' => $this->_name . '-filters',
      ),
      $this->_name . '_contribution_campaign_id' => array(
        'name' => 'campaign_id',
        'dbAlias' => 'campaign_id',
        '_is_filter_criteria' => TRUE,
        '_is_column_criteria' => TRUE,
        '_base_title' => 'campaign',
        'title' => $this->_buildFilterCriteriaFieldLabel('campaign'),
        'operatorType' => CRM_Report_Form::OP_MULTISELECT,
        'type' => CRM_Utils_Type::T_INT,
        'options'      => CRM_Campaign_BAO_Campaign::getCampaigns(NULL, NULL, NULL, FALSE),
        'grouping' => $this->_name . '-filters',
      ),
      $this->_name . '_contribution_source' => array(
        'name' => 'source',
        'dbAlias' => 'source',
        '_is_filter_criteria' => TRUE,
        '_is_column_criteria' => TRUE,
        '_base_title' => 'source',
        'title' => $this->_buildFilterCriteriaFieldLabel('source'),
        'operatorType' => CRM_Report_Form::OP_STRING,
        'type' => CRM_Utils_Type::T_STRING,
        'grouping' => $this->_name . '-filters',
      ),
      $this->_name . '_contribution_amount' => array(
        'name' => 'total_amount',
        'dbAlias' => 'total_amount',
        '_is_filter_criteria' => TRUE,
        '_is_column_criteria' => TRUE,
        '_base_title' => 'amount',
        'title' => $this->_buildFilterCriteriaFieldLabel('amount'),
        'type' => CRM_Utils_Type::T_MONEY,
        'grouping' => $this->_name . '-filters',
      ),
    );

    $this->_buildFilterCriteriaFields();
    $this->_buildColumnCriteriaFields();
  }

  public function _buildFilterCriteriaFields() {
    foreach ($this->_criteria_fields_base as $key => $field) {
      if (!$field['_is_column_criteria']) {
        continue;
      }
      $this->_filter_criteria_fields[$key] = $field;
    }
  }

  public function _buildFilterCriteriaFieldLabel($base_title) {
    return ucfirst($this->_name) . " contribution: qualifying $base_title";
  }

  public function _buildColumnCriteriaFieldLabel($base_title) {
    return ucfirst($base_title);
  }

  public function _buildColumnCriteriaFields() {
    foreach ($this->_criteria_fields_base as $key => $field) {
      if (!$field['_is_column_criteria']) {
        continue;
      }
      $new_key = 'column_' . $key;
      $field['grouping'] = 'column-filters';
      $field['title'] = $this->_buildColumnCriteriaFieldLabel($field['_base_title']);
      $this->_column_criteria_fields[$new_key] = $field;
    }
  }

  public function _buildFilterTables($obj) {
    $this->_obj = $obj;
    $this->_filterSetTableName = $obj->_temp_table_prefix . $this->_name;

    $report = clone $this->_obj;
    // Remove any filters from $report->_columns.
    foreach ($report->_columns as &$components) {
      unset($components['filters']);
    }

    // Get scope for this filter from params.
    $selected_scope = $obj->_params[$this->_name . '_contribution_scope_value'];
    switch ($selected_scope) {
      case CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_SCOPE_EVER:
        $this->_buildFilterTablesForScopeEver($report);
        break;

      case CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_SCOPE_DATE_RANGE:
        $this->_buildFilterTablesForScopeDateRange($report);
        break;

      case CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_SCOPE_AMOUNT_RANGE:
        $this->_buildFilterTablesForScopeAmountRange($report);
        break;

      default:
        $this->_buildFilterTablesForScopeDefault($report);
        break;
    }

    $obj->_extraJoinTables[] = array(
      'name' => $this->_filterSetTableName,
      'join' => 'INNER',
    );
  }

  public function _buildColumnTables($obj) {
    $this->_obj = $obj;
    $this->_columnTableName = $obj->_temp_table_prefix . 'column_' . $this->_name;
    $this->_columnFieldName = "{$this->_name}_contribution";

    $report = clone $this->_obj;
    // Remove any filters from $report->_columns.
    foreach ($report->_columns as &$components) {
      unset($components['filters']);
    }

    // Incorporate filter values, if the column is not set to "__ contributions
    // ever".
    if ($this->_obj->_params["{$this->_name}_contribution_column_filter"] != CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_SCOPE_EVER) {
      $filter_set_fields = $this->_getColumnFields(FALSE);
      $report->_columns[$this->_obj->_tablename]['filters'] = $filter_set_fields;
      $report->_filterWhere();
    }

    $this->_buildMyColumnTables($report);

    $obj->_extraJoinTables[] = array(
      'name' => $this->_columnTableName,
      'join' => 'LEFT',
    );

    // In $obj->_columns, remove the field from $obj->_tablename['fields'] to
    // $this->_columnTableName['fields'], so it will be pulled from $this->_columnTableName.
    $field = $obj->_columns[$obj->_tablename]['fields'][$this->_columnFieldName];
    $field['dbAlias'] = $this->_columnFieldName;
    unset($obj->_columns[$obj->_tablename]['fields'][$this->_columnFieldName]);
    $obj->_columns[$this->_columnTableName]['fields'][$this->_columnFieldName] = $field;

    // If this filterset has a "*_date" aggregate field (e.g., first_contribution_date),
    // do the same for the *_date field as for the base (amount) field.
    if ($obj->_columns[$obj->_tablename]['fields'][$this->_columnFieldName . '_date']) {
      $field = $obj->_columns[$obj->_tablename]['fields'][$this->_columnFieldName . '_date'];
      $field['dbAlias'] = $this->_columnFieldName . '_date';
      unset($obj->_columns[$obj->_tablename]['fields'][$this->_columnFieldName . '_date']);
      $obj->_columns[$this->_columnTableName]['fields'][$this->_columnFieldName . '_date'] = $field;
    }
  }

  public function _buildMyColumnTables($report) {
  }

  public function _getFields($is_constructor) {
    $fields = $this->_getFilterFields($is_constructor);
    $fields = array_merge($fields, $this->_getColumnFields($is_constructor));
    return $fields;
  }

  public function _getFilterFields($is_constructor) {
    $fields = $this->_filter_criteria_fields;
    return $this->_adjustPseudofield($fields, $is_constructor);
  }

  public function _getColumnFields($is_constructor) {
    $fields = $this->_column_criteria_fields;
    return $this->_adjustPseudofield($fields, $is_constructor);
  }

  public function _adjustPseudofield($filters, $is_constructor) {
    foreach ($filters as &$filter) {
      if (array_key_exists('_force_pseudofield', $filter) && $filter['_force_pseudofield']) {
        $filter['pseudofield'] = TRUE;
      }
      else {
        $filter['pseudofield'] = (bool) $is_constructor;
      }
    }
    return $filters;
  }

}
