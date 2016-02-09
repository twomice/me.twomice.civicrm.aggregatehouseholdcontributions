<?php

define ('CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_SCOPE_NONE', 0);
define ('CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_SCOPE_EVER', 1);
define ('CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_SCOPE_DATE_RANGE', 2);
define ('CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_SCOPE_AMOUNT_RANGE', 3);
define ('CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_METHOD_GROUP', 1);
define ('CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_METHOD_HAVING', 2);
define ('CIVIREPORT_AGGREGATE_HOUSEHOLD_COLUMN_METHOD_SINGLE', 1);
define ('CIVIREPORT_AGGREGATE_HOUSEHOLD_COLUMN_METHOD_JOINED', 2);

class me_twomice_civicrm_aggregatehouseholdcontributions extends CRM_Report_Form {
  var $_debug = FALSE;
  var $_tablename = 'tmp_aggregated_household_contributions';
  var $_temp_table_prefix = "civireport_tmp_";
  var $_filterSets = array(
    'total' => array (
      'name' => 'total',
      'requires_join' => TRUE,
      'column_settings' => array(
        'qualifier_expression' => 'sum(t.total_amount)',
        'method' => CIVIREPORT_AGGREGATE_HOUSEHOLD_COLUMN_METHOD_SINGLE,
      ),
      'scope_settings' => array (
        'has_scope_option' => FALSE,
        'qualifier_expression' => 'sum(t.total_amount)',
        'qualifier_filter' => 'total_contribution_total',
        'scopes' => array(
          CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_SCOPE_NONE => array(
            'method' => CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_METHOD_HAVING,
          ),
        ),
      ),
    ),
    'any' => array (
      'name' => 'any',
      'requires_join' => FALSE,
    ),
    'first' => array (
      'name' => 'first',
      'requires_join' => TRUE,
      'column_settings' => array(
        'qualifier_expression' => 'min(t.receive_date)',
        'qualifier_join' => 'receive_date',
        'method' => CIVIREPORT_AGGREGATE_HOUSEHOLD_COLUMN_METHOD_JOINED,
      ),
      'scope_settings' => array (
        'has_scope_option' => TRUE,
        'qualifier_expression' => 'min(t.receive_date)',
        'qualifier_filter' => 'first_contribution_date',
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
              'first_contribution_amount',
            ),
          ),
        ),
      ),
    ),
    'last' => array (
      'name' => 'last',
      'requires_join' => TRUE,
      'column_settings' => array(
        'qualifier_expression' => 'max(t.receive_date)',
        'qualifier_join' => 'receive_date',
        'method' => CIVIREPORT_AGGREGATE_HOUSEHOLD_COLUMN_METHOD_JOINED,
      ),
      'scope_settings' => array (
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
      ),
    ),
    'largest' => array (
      'name' => 'largest',
      'requires_join' => TRUE,
      'column_settings' => array(
        'qualifier_expression' => 'max(t.total_amount)',
        'method' => CIVIREPORT_AGGREGATE_HOUSEHOLD_COLUMN_METHOD_SINGLE,
      ),
      'scope_settings' => array (
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
      ),
    ),
  );
  var $_extraJoinTables = array();

  function __construct() {
    $this->_columns = array(
      'civicrm_contact' => array(
        'dao' => 'CRM_Contact_DAO_Contact',
        'grouping' => 'contact-fields',
        'fields' => array(
          'first_name' => array(
            'title' => ts('First Name'),
          ),
          'last_name' => array(
            'title' => ts('Last Name'),
          ),
          'display_name' => array(
            'title' => ts('Display Name'),
          ),
          'sort_name' => array(
            'title' => ts('Sort Name'),
          ),
          'contact_type' => array(
            'title' => ts('Contact Type'),
          ),
          'prefix_id' => array(
            'title' => ts('Prefix'),
          ),
          'suffix_id' => array(
            'title' => ts('Suffix'),
          ),
          'external_identifier' => array(
            'title' => ts('External ID'),
          ),
          'is_deceased' => array(
            'dbAlias' => "if(is_deceased, 'Yes', 'No')",
          ),
          'source' => array(
            'title' => ts('Source'),
          ),
          'id' => array(
            'required' => TRUE,
            'no_display' => TRUE,
          ),
        ),
      ),
      'civicrm_email' => array(
        'dao' => 'CRM_Core_DAO_Email',
        'grouping' => 'contact-fields',
        'fields' => array(
          'email' => array(
            'title' => ts('Email'),
            'no_repeat' => TRUE,
          ),
        ),
      ),
      'civicrm_address' => array(
        'dao' => 'CRM_Core_DAO_Address',
        'grouping' => 'contact-fields',
        'fields' => array(
          'street_address' => NULL,
          'city' => NULL,
          'postal_code' => NULL,
        ),
      ),
      'civicrm_state_province' => array(
        'dao' => 'CRM_Core_DAO_StateProvince',
        'grouping' => 'contact-fields',
        'alias' => 'state',
        'fields' => array(
          'state_name' => array(
            'title' => ts('State/Province'),
            'dbAlias' => 'state_civireport.name',
          ),
        ),
      ),
      'civicrm_country' => array(
        'dao' => 'CRM_Core_DAO_Country',
        'grouping' => 'contact-fields',
        'alias' => 'country',
        'fields' => array(
          'country_name' => array(
            'title' => ts('Country'),
            'dbAlias' => 'country_civireport.name',
          ),
        ),
      ),
      'civicrm_phone' => array(
        'dao' => 'CRM_Core_DAO_Phone',
        'grouping' => 'contact-fields',
        'fields' => array(
          'phone' => NULL,
          'phone_ext' => array(
            'title' => ts('Phone Extension'),
          ),
        ),
      ),

      // Add options to filter the values of the aggregated amount columns.
      // These are all calculated using temporary tables, so dbAlias is never
      // actually used.
      $this->_tablename => array(
        'grouping' => 'aggregate-fields',
        'fields' => array(
          'total_contribution' => array(
            'title' => ts('Total contribution'),
            'dbAlias' => 'CALCULATE',
          ),
          'first_contribution' => array(
            'title' => ts('First contribution'),
            'dbAlias' => 'CALCULATE',
          ),
          'last_contribution' => array(
            'title' => ts('Last contribution'),
            'dbAlias' => 'CALCULATE',
          ),
          'largest_contribution' => array(
            'title' => ts('Largest contribution'),
            'dbAlias' => 'CALCULATE',
          ),
        ),
        'filters' => array(),
      ),
    );

    foreach ($this->_filterSets as $filter_set_name => $filter_set) {
      $this->_columns[$this->_tablename]['filters'] = array_merge($this->_columns[$this->_tablename]['filters'], $this->_getFilterSetFields($filter_set_name, TRUE));
      $this->_columns[$this->_tablename]['filters'] = array_merge($this->_columns[$this->_tablename]['filters'], $this->_getFilterSetFields($filter_set_name, TRUE, TRUE));
    }

    $this->_groupFilter = TRUE;
    $this->_tagFilter = TRUE;


    parent::__construct();

  }

  function preProcess() {
    parent::preProcess();

    $this->addElement('checkbox', 'is_filter_total', ts('Apply "Total Contribution" filter'));
    $this->addElement('checkbox', 'is_filter_any', ts('Apply "Any Contribution" filter'));
    $this->addElement('checkbox', 'is_filter_largest', ts('Apply "Largest Contribution" filter'));
    $this->addElement('checkbox', 'is_filter_last', ts('Apply "Last Contribution" filter'));
    $this->addElement('checkbox', 'is_filter_first', ts('Apply "First Contribution" filter'));


    $options = array();
    $options[] = $this->createElement('radio', NULL, NULL, ts('First contribution ever'), 1);
    $options[] = $this->createElement('radio', NULL, NULL, ts('Use custom settings'), 2);
    $this->addGroup($options, 'first_contribution_column_filter', ts('"First contribution" column'));
    $this->setDefaults(array('first_contribution_column_filter' => 1));

    $options = array();
    $options[] = $this->createElement('radio', NULL, NULL, ts('Last contribution ever'), 1);
    $options[] = $this->createElement('radio', NULL, NULL, ts('Use custom settings'), 2);
    $this->addGroup($options, 'last_contribution_column_filter', ts('"Last contribution" column'));
    $this->setDefaults(array('last_contribution_column_filter' => 1));

    $options = array();
    $options[] = $this->createElement('radio', NULL, NULL, ts('Largest contribution ever'), 1);
    $options[] = $this->createElement('radio', NULL, NULL, ts('Use custom settings'), 2);
    $this->addGroup($options, 'largest_contribution_column_filter', ts('"Largest contribution" column'));
    $this->setDefaults(array('largest_contribution_column_filter' => 1));

    $options = array();
    $options[] = $this->createElement('radio', NULL, NULL, ts('Total of all contributions ever'), 1);
    $options[] = $this->createElement('radio', NULL, NULL, ts('Use custom settings'), 2);
    $this->addGroup($options, 'total_contribution_column_filter', ts('"Total contribution" column'));
    $this->setDefaults(array('total_contribution_column_filter' => 1));

    $tpl = CRM_Core_Smarty::singleton();
    $bhfe = $tpl->get_template_vars('beginHookFormElements');
    if (!$bhfe) {
      $bhfe = array();
    }
    $bhfe[] = 'first_contribution_column_filter';
    $bhfe[] = 'last_contribution_column_filter';
    $bhfe[] = 'total_contribution_column_filter';
    $bhfe[] = 'largest_contribution_column_filter';
    $bhfe[] = 'is_filter_total';
    $bhfe[] = 'is_filter_last';
    $bhfe[] = 'is_filter_first';
    $bhfe[] = 'is_filter_any';
    $bhfe[] = 'is_filter_largest';
    $this->assign('beginHookFormElements', $bhfe);

  }

  function from() {
    $this->_from = "
      FROM
        civicrm_contact {$this->_aliases['civicrm_contact']} {$this->_aclFrom}
        INNER JOIN {$this->_tablename} {$this->_aliases[$this->_tablename]}
          ON {$this->_aliases[$this->_tablename]}.aggid = {$this->_aliases['civicrm_contact']}.id
    ";

    if (
      $this->isTableSelected('civicrm_address')
            || $this->isTableSelected('civicrm_state_province')
            || $this->isTableSelected('civicrm_country')
    ) {
      $this->_from .= "
        LEFT JOIN civicrm_address {$this->_aliases['civicrm_address']}
          ON {$this->_aliases['civicrm_address']}.contact_id = {$this->_aliases['civicrm_contact']}.id
            AND {$this->_aliases['civicrm_address']}.is_primary = 1
      ";
    }

    if ($this->isTableSelected('civicrm_state_province')) {
      $this->_from .= "
        LEFT JOIN civicrm_state_province {$this->_aliases['civicrm_state_province']}
          ON {$this->_aliases['civicrm_address']}.state_province_id = {$this->_aliases['civicrm_state_province']}.id
            AND {$this->_aliases['civicrm_address']}.is_primary = 1
      ";
    }

    if ($this->isTableSelected('civicrm_country')) {
      $this->_from .= "
        LEFT JOIN civicrm_country {$this->_aliases['civicrm_country']}
          ON {$this->_aliases['civicrm_address']}.country_id = {$this->_aliases['civicrm_country']}.id
            AND {$this->_aliases['civicrm_address']}.is_primary = 1
      ";
    }

    if ($this->isTableSelected('civicrm_email')) {
      $this->_from .= "
        LEFT JOIN civicrm_email {$this->_aliases['civicrm_email']}
          ON ({$this->_aliases['civicrm_contact']}.id = {$this->_aliases['civicrm_email']}.contact_id
            AND {$this->_aliases['civicrm_email']}.is_primary = 1)
      ";
    }

    if ($this->isTableSelected('civicrm_phone')) {
      $this->_from .= "
        LEFT JOIN civicrm_phone {$this->_aliases['civicrm_phone']}
          ON {$this->_aliases['civicrm_contact']}.id = {$this->_aliases['civicrm_phone']}.contact_id
            AND {$this->_aliases['civicrm_phone']}.is_primary = 1
        ";
    }

    foreach ($this->_extraJoinTables as $join_table) {
      $this->_from .= "
        {$join_table['join']} JOIN {$join_table['name']} ON {$join_table['name']}.aggid = {$this->_aliases[$this->_tablename]}.aggid
      ";
    }
  }

  function groupBy() {
    parent::groupBy();

    if (empty($groupBys)) {
      $this->_groupBy = "GROUP BY ";
    }
    else {
      $this->_groupBy .= ', ';
    }
    $this->_groupBy .= " {$this->_aliases[$this->_tablename]}.aggid";
  }

  function validate() {
    $ret = parent::validate();
    if ($ret !== FALSE) {
      if (empty($this->_submitValues['fields'])) {
        CRM_Core_Session::setStatus('Please select some Display Columns.', 'Incomplete', 'error');
        $ret = FALSE;
      }
    }
    return $ret;
  }

  /**
   * beginPostProcess function run in both report mode and non-report mode (api)
   */
  function beginPostProcessCommon() {
    parent::beginPostProcessCommon();

    // Build a temporary table for each filterset that's enabled.
    foreach ($this->_filterSets as $filter_set_name => $filter_set) {
      // Handle filter sets.
      if (!$this->_params["is_filter_{$filter_set_name}"]) {
        // Clear default filter params if the filter itself is not used.
        $prefix = "{$filter_set_name}_contribution_";
        $prefixLength = strlen($prefix);
        foreach ($this->_columns as $tableName => $columns) {
          foreach ($columns['filters'] as $fieldName => $field) {
            if (substr($fieldName, 0, $prefixLength) == $prefix) {
              unset($this->_params["{$fieldName}_value"]);
              unset($this->_params["{$fieldName}_op"]);
              unset($this->_params["{$fieldName}_min"]);
              unset($this->_params["{$fieldName}_max"]);
              unset($this->_params["{$fieldName}_relative"]);
              unset($this->_params["{$fieldName}_from"]);
              unset($this->_params["{$fieldName}_to"]);
            }
          }
        }
      }
    }

    // We're about to build a table containing all contributions for contacts
    // (individuals and households) that match the group and tag filters, such
    // that each contrib is attributed to the aggregated household.

    // Build the where clauses for this query.
    $whereClauses = array();

    // Support tag and group filters.
    $field = $this->_columns["civicrm_tag"]['filters']['tagid'];
    $value = CRM_Utils_Array::value("tagid_value", $this->_params);
    $op = CRM_Utils_Array::value("tagid_op", $this->_params);
    if ($value) {
      $whereClauses[] = $this->whereTagClause($field, $value, $op);
    }

    $field = $this->_columns["civicrm_group"]['filters']['gid'];
    $value = CRM_Utils_Array::value("gid_value", $this->_params);
    $op = CRM_Utils_Array::value("gid_op", $this->_params);
    if ($value) {
      $whereClauses[] = $this->whereGroupClause($field, $value, $op);
    }

    if (!empty($whereClauses)) {
      $where = "WHERE ". implode(' AND ', $whereClauses);
    }
    else {
      $where = 'WHERE (1)';
    }

    // Build the query and run it.
    $temporary = $this->_debug_temp_table($this->_tablename);

    $query = "
      CREATE $temporary TABLE $this->_tablename
      SELECT u.aggid, u.id AS cid, contrib.* FROM
      (SELECT IF(r.id IS NOT NULL, r.contact_id_b, {$this->_aliases['civicrm_contact']}.id) AS aggid, {$this->_aliases['civicrm_contact']}.id
      FROM
      civicrm_contact {$this->_aliases['civicrm_contact']}
      LEFT JOIN civicrm_relationship r ON r.relationship_type_id IN (6,7) AND
       r.contact_id_a = {$this->_aliases['civicrm_contact']}.id
       $where AND {$this->_aliases['civicrm_contact']}.contact_type = 'individual'
      UNION
      SELECT {$this->_aliases['civicrm_contact']}.id AS aggid, {$this->_aliases['civicrm_contact']}.id
      FROM
      civicrm_contact {$this->_aliases['civicrm_contact']}
       $where AND {$this->_aliases['civicrm_contact']}.contact_type = 'household'
      ) u
      INNER JOIN civicrm_contribution contrib ON contrib.contact_id = u.id
    ";

    CRM_Core_DAO::executeQuery($query);
    $this->_debugDsm($query, 'query');
    $this->_debugDsm($this->_params, 'params');

    foreach($this->_filterSets as $filter_set_name => $filter_set) {
      if ($this->_params["is_filter_{$filter_set_name}"]) {
        $this->_buildFilterSetTempTable($filter_set_name);
      }
      $this->_buildColumnTempTable($filter_set_name);
   }
  }


  function alterDisplay(&$rows) {
    // custom code to alter rows
    $entryFound = FALSE;

    foreach ($rows as $rowNum => $row) {
      // convert display name to links
      if (
        array_key_exists('civicrm_contact_id', $row) &&
        (
          array_key_exists('civicrm_contact_display_name', $row)
          || array_key_exists('civicrm_contact_sort_name', $row)
        )
      ) {
        $url = CRM_Utils_System::url('civicrm/contact/view',
          'reset=1&selectedChild=contribute&cid=' . $row['civicrm_contact_id']
        );
        $rows[$rowNum]['civicrm_contact_display_name_link'] = $url;
        $rows[$rowNum]['civicrm_contact_display_name_hover'] = ts("Lists detailed contribution(s) for this record.");
        $rows[$rowNum]['civicrm_contact_sort_name_link'] = $url;
        $rows[$rowNum]['civicrm_contact_sort_name_hover'] = ts("Lists detailed contribution(s) for this record.");
        $entryFound = TRUE;
      }
      // skip looking further in rows, if first row itself doesn't
      // have the column we need
      if (!$entryFound) {
        ($row);
        break;
      }
    }
  }

  function _buildFilterSetTempTable($filter_set_name) {

    $filter_set = $this->_filterSets[$filter_set_name];

    // If filter set doesn't require a separate joined table, we have nothing
    // to do here. Just return.
    if (!$filter_set['requires_join']) {
      return;
    }

    $backup_columns = $this->_columns;

    foreach ($this->_columns as $table_name => &$components) {
      unset($components['filters']);
    }
    $filter_set_fields = $this->_getFilterSetFields($filter_set_name, FALSE);

    // If the scope is not set, then scope is CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_SCOPE_NONE.
    $scope = $this->_params[$filter_set_name . '_contribution_scope_value'] ?: CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_SCOPE_NONE;

    $method = $filter_set['scope_settings']['scopes'][$scope]['method'];
    $table_name = $this->_temp_table_prefix . $filter_set_name;

    // Delete or make temporary the table, depending on $this->-debug setting.
    $temporary = $this->_debug_temp_table($table_name);

    $qualifier_column_name = "qualifier_{$filter_set_name}";

    if ($method == CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_METHOD_GROUP) {
      $table_name_pre = "civireport_tmp_{$filter_set_name}_pre";
      $temporary = $this->_debug_temp_table($table_name_pre);

      $this->_columns[$this->_tablename]['filters'] = array();

      $table_1_where_filters = $filter_set['scope_settings']['scopes'][$scope]['table_1_where_filters'];
      $table_2_where_filters = $filter_set['scope_settings']['scopes'][$scope]['table_2_where_filters'];

      if (is_array($table_1_where_filters)) {
        foreach ($table_1_where_filters as $field_name) {
          $field = $filter_set_fields[$field_name];
          $field['pseudofield'] = FALSE;
          $this->_columns[$this->_tablename]['filters'][$field_name] = $field;
        }
      }
      elseif ($table_1_where_filters == 'ALL') {
        foreach ($filter_set_fields as $field_name => $field) {
          if ($field_name != $filter_set_name . '_contribution_scope') {
            $field['pseudofield'] = FALSE;
            $this->_columns[$this->_tablename]['filters'][$field_name] = $field;
          }
        }
      }
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

      if (is_array($table_2_where_filters)) {
        foreach ($table_2_where_filters as $field_name) {
          $field = $filter_set_fields[$field_name];
          $field['pseudofield'] = FALSE;
          $this->_columns[$this->_tablename]['filters'][$field_name] = $field;
        }
      }
      elseif ($table_2_where_filters == 'ALL') {
        foreach ($filter_set_fields as $field_name => $field) {
          if ($field_name != $filter_set_name . '_contribution_scope') {
            $field['pseudofield'] = FALSE;
            $this->_columns[$this->_tablename]['filters'][$field_name] = $field;
          }
        }
      }
      elseif ($table_2_where_filters == 'ALLEXCEPT') {
        foreach ($filter_set_fields as $field_name => $field) {
          if (
            $field_name != $filter_set_name . '_contribution_scope'
            && is_array($table_1_where_filters)
            && !in_array($field_name, $table_1_where_filters)
          ) {
            $field['pseudofield'] = FALSE;
            $this->_columns[$this->_tablename]['filters'][$field_name] = $field;
          }
        }
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
    }
    elseif ($method == CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_METHOD_HAVING) {
      $this->_columns[$this->_tablename]['filters'] = array();

      // Otherwise, if the method is HAVING, create one temp table, along these lines:
      $filter_set_fields[$filter_set['scope_settings']['qualifier_filter']]['having'] = TRUE;
      $filter_set_fields[$filter_set['scope_settings']['qualifier_filter']]['dbAlias'] = $qualifier_column_name;

      $this->_columns[$this->_tablename]['filters'] = $filter_set_fields;
      $this->_filterWhere();
      $query =   "
        CREATE $temporary TABLE {$table_name} (INDEX (`aggid`))
        SELECT
          {$filter_set['scope_settings']['qualifier_expression']} as $qualifier_column_name, t.aggid
        FROM
          tmp_aggregated_household_contributions t
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

  function _getFilterSetFields($filter_set_name, $is_constructor = TRUE, $is_columns = FALSE) {

    if ($filter_set_name == 'any') {
      $pseudofield = FALSE;
    }
    else {
      $pseudofield = (bool)$is_constructor;
    }

    if (!$is_columns) {
      $label_prefix = "$filter_set_name contribution: qualifying ";
      $key_prefix = '';
      $grouping = $filter_set_name . '-filters';
    }
    else {
      if ($filter_set_name == 'any') {
        return array();
      }
      $label_prefix = '';
      $key_prefix = 'column_';
      $grouping = 'column-filters';
    }

    $filter_set = $this->_filterSets[$filter_set_name];
    $fields = array(
        $key_prefix . $filter_set_name . '_contribution_date' => array(
        'name' => 'receive_date',
        'dbAlias' => 'receive_date',
        'title' => ts($label_prefix . 'date'),
        'type' => CRM_Utils_Type::T_DATE,
        'operatorType' => CRM_Report_Form::OP_DATE,
        'grouping' => $grouping,
        'pseudofield' => $pseudofield,
      ),
      $key_prefix . $filter_set_name . '_contribution_financial_type_id' => array(
        'name' => 'financial_type_id',
        'dbAlias' => 'financial_type_id',
        'title' => ts($label_prefix . 'type'),
        'operatorType' => CRM_Report_Form::OP_MULTISELECT,
        'type' => CRM_Utils_Type::T_INT,
        'options'      => CRM_Contribute_PseudoConstant::financialType(),
        'grouping' => $grouping,
        'pseudofield' => $pseudofield,
      ),
      $key_prefix . $filter_set_name . '_contribution_page_id' => array(
        'name' => 'contribution_page_id',
        'dbAlias' => 'contribution_page_id',
        'title' => ts($label_prefix . 'page'),
        'operatorType' => CRM_Report_Form::OP_MULTISELECT,
        'type' => CRM_Utils_Type::T_INT,
        'options'      => CRM_Contribute_PseudoConstant::contributionPage(),
        'grouping' => $grouping,
        'pseudofield' => $pseudofield,
      ),
      $key_prefix . $filter_set_name . '_contribution_status_id' => array(
        'name' => 'contribution_status_id',
        'dbAlias' => 'contribution_status_id',
        'title' => ts($label_prefix . 'status'),
        'operatorType' => CRM_Report_Form::OP_MULTISELECT,
        'type' => CRM_Utils_Type::T_INT,
        'options'      => CRM_Contribute_PseudoConstant::contributionStatus(),
        'default' => '1',
        'grouping' => $grouping,
        'pseudofield' => $pseudofield,
      ),
      $key_prefix . $filter_set_name . '_contribution_campaign_id' => array(
        'name' => 'campaign_id',
        'dbAlias' => 'campaign_id',
        'title' => ts($label_prefix . 'campaign'),
        'operatorType' => CRM_Report_Form::OP_MULTISELECT,
        'type' => CRM_Utils_Type::T_INT,
        'options'      => CRM_Campaign_BAO_Campaign::getCampaigns(NULL, NULL, NULL, FALSE),
        'grouping' => $grouping,
        'pseudofield' => $pseudofield,
      ),
      $key_prefix . $filter_set_name . '_contribution_source' => array(
        'name' => 'source',
        'dbAlias' => 'source',
        'title' => ts($label_prefix . 'source'),
        'operatorType' => CRM_Report_Form::OP_STRING,
        'type' => CRM_Utils_Type::T_STRING,
        'grouping' => $grouping,
        'pseudofield' => $pseudofield,
      ),
      $key_prefix . $filter_set_name . '_contribution_amount' => array(
        'name' => 'total_amount',
        'dbAlias' => 'total_amount',
        'title' => ts($label_prefix . 'amount'),
        'type' => CRM_Utils_Type::T_MONEY,
        'grouping' => $grouping,
        'pseudofield' => $pseudofield,
      ),
    );

    if (!$is_columns) {
      if ($filter_set['scope_settings']['has_scope_option']) {
        $scope_label_prefix = ucfirst($filter_set_name);
        $fields[$filter_set_name . '_contribution_scope'] = array(
          'title' => ts("\"$scope_label_prefix Contribution\" filter scope"),
          'operatorType' => CRM_Report_Form::OP_SELECT,
          'type' => CRM_Utils_Type::T_INT,
          'options'      => array(
            CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_SCOPE_EVER => ts($scope_label_prefix .' contribution ever meets these criteria'),
            CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_SCOPE_DATE_RANGE => ts($scope_label_prefix .' contribution meeting these criteria was within this date range'),
            CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_SCOPE_AMOUNT_RANGE => ts($scope_label_prefix .' contribution meeting these criteria was within this amount range'),
          ),
          'grouping' => $grouping,
          'pseudofield' => TRUE,
        );
      }

      // Total filter needs one additional field.
      if ($filter_set_name == 'total') {
        $fields[$filter_set_name . '_contribution_total'] = array(
          'title' => ts('Total contribution: total'),
          'type' => CRM_Utils_Type::T_MONEY,
          'dbAlias' => 'sum(total_amount)',
          'having' => TRUE,
          'grouping' => $grouping,
          'pseudofield' => $pseudofield,
        );
      }
    }
    foreach ($fields as &$field) {
      $field['title'] = ucfirst($field['title']);
    }
    return $fields;
  }

  function _debug_temp_table($table_name) {

    if ($this->_debug) {
      $query = "DROP TABLE IF EXISTS {$table_name}";
      CRM_Core_DAO::executeQuery($query);
      $temporary = '';
    }
    else {
      $temporary = 'TEMPORARY';
    }
    return $temporary;
  }

  function _filterWhere() {
    $this->_havingClauses = array();
    $this->_whereClauses = array();
    $this->where();
    $this->_havingClauses = array();
    $this->_whereClauses = array();
  }

  function _buildColumnTempTable($filter_set_name) {
    $column_filter_param_name = "{$filter_set_name}_contribution_column_filter";
    $filter_set = $this->_filterSets[$filter_set_name];
    $field_name = "{$filter_set_name}_contribution";
    $field = $this->_columns[$this->_tablename]['fields'][$field_name];

    // If this field was not selected for display, just return.
    if (!$this->_params['fields'][$field_name]) {
      return;
    }

    $table_name = $this->_temp_table_prefix . 'column_' . $filter_set_name;
    $temporary = $this->_debug_temp_table($table_name);

    // If the filter is in use, then the column filter may use the same settings
    // (so if it's not set to "__ contributions ever", check for identical settings).
    if (
      $this->_params["is_filter_{$filter_set_name}"]
      && $this->_params["{$filter_set_name}_contribution_column_filter"] != CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_SCOPE_EVER
    ) {
      // If settings for the column are the same as for the filter, just use
      // the qualifier column from the joined table.
      $exact_match = TRUE;
      foreach($this->_params as $param_name => $param) {
        if ($param_name == $column_filter_param_name) {
          continue;
        }
        // If the param name begins with the filter set name, compare the
        // corresponding column_* parameter.
        if (strpos($param_name, "{$filter_set_name}_") === 0) {
          $column_param_name = "column_{$param_name}";
          if (
            array_key_exists($column_param_name, $this->_params)
            && $this->_params[$column_param_name] !== $param
          ) {
            $exact_match = FALSE;
            break;
          }
        }
      }
    }
    if ($exact_match) {
      // If the filter settings are an exact match, then just use the value
      // already calculated in the joined table.
      $qualifier_column_name = "qualifier_{$filter_set_name}";
      $field['dbAlias'] = "qualifier_{$filter_set_name}";
      unset($this->_columns[$this->_tablename]['fields'][$field_name]);
      $this->_columns['civireport_tmp_total']['fields'][$field_name] = $field;
    }
    else {
      // If the filter settings are different, then we have to build another
      // joined table to do the calculation.

      $backup_columns = $this->_columns;

      foreach ($this->_columns as $columns_table_name => &$components) {
        unset($components['filters']);
      }
      $filter_set_fields = $this->_getFilterSetFields($filter_set_name, FALSE, TRUE);

      $method = $filter_set['column_settings']['method'];

      $qualifier_column_name = "column_{$filter_set_name}";

      // Incorporate filter values, if the column is not set to "__ contributions
      // ever".
      if ($this->_params["{$filter_set_name}_contribution_column_filter"] != CIVIREPORT_AGGREGATE_HOUSEHOLD_FILTERSET_SCOPE_EVER) {
        $this->_columns[$this->_tablename]['filters'] = $filter_set_fields;
        $this->_filterWhere();
      }

      if ($method == CIVIREPORT_AGGREGATE_HOUSEHOLD_COLUMN_METHOD_JOINED) {
        $table_name_pre = "civireport_tmp_column_{$filter_set_name}_pre";
        $temporary = $this->_debug_temp_table($table_name_pre);

        $query = "
          CREATE $temporary TABLE $table_name_pre (INDEX (  `aggid` ), INDEX (`$qualifier_column_name`))
          SELECT
            t.aggid, {$filter_set['column_settings']['qualifier_expression']} as $qualifier_column_name
          FROM
            tmp_aggregated_household_contributions t
          {$this->_where}
            group by aggid
          ;
        ";
        $this->_debugDsm($query, "PRE table query for column: {$filter_set_name}");
        CRM_Core_DAO::executeQuery($query);

        $query = "
          CREATE $temporary TABLE {$table_name} (INDEX (`aggid`))
          SELECT
            t.aggid, t.total_amount as {$field_name}
          FROM
            tmp_aggregated_household_contributions t
            INNER JOIN {$table_name_pre} p ON p.aggid = t.aggid AND p.$qualifier_column_name = t.{$filter_set['column_settings']['qualifier_join']}
          {$this->_where}
          ;
        ";
        $this->_debugDsm($query, "Table query for column: {$filter_set_name}");
        CRM_Core_DAO::executeQuery($query);
      }
      elseif ($method == CIVIREPORT_AGGREGATE_HOUSEHOLD_COLUMN_METHOD_SINGLE) {
        $query =   "
          CREATE $temporary TABLE $table_name (INDEX (  `aggid` ))
          SELECT
            t.aggid, {$filter_set['column_settings']['qualifier_expression']} as {$field_name}
          FROM
            tmp_aggregated_household_contributions t
            {$this->_where}
            group by aggid

        ";
        $this->_debugDsm($query, "Only query for column: {$filter_set_name}");
        CRM_Core_DAO::executeQuery($query);
      }

      $this->_extraJoinTables[] = array(
        'name' => $table_name,
        'join' => 'LEFT',
      );

      $this->_columns = $backup_columns;
      $this->_havingClauses = $this->_whereClauses = array();
      $this->_where = $this->_having = '';


      $field['dbAlias'] = $field_name;
      unset($this->_columns[$this->_tablename]['fields'][$field_name]);
      $this->_columns[$table_name]['fields'][$field_name] = $field;


    }
  }

  /**
   * Overrides parent::filterStat(). Before calling parent, remove all
   * 'column_*' filters, so they're not displayed in the statistics table.
   *
   * @param <type> $statistics
   */
  function filterStat(&$statistics) {
    // Back-up columns array, since we're modifying it temporarily.
    $backup_columns = $this->_columns;

    // Remove 'column_*' filters.
    $filters = $this->_columns[$this->_tablename]['filters'];
    foreach ($filters as $filter_name => $filter) {
      if (strpos($filter_name, "column_") === 0) {
        unset($filters[$filter_name]);
      }
    }
    $this->_columns[$this->_tablename]['filters'] = $filters;

    // Call parent method.
    parent::filterStat($statistics);

    // Restore columns array to its original value.
    $this->_columns = $backup_columns;
  }

  function buildQuery($applyLimit = TRUE) {
    $sql = parent::buildQuery($applyLimit);
    $this->_debugDsm($sql, __FUNCTION__ . ' query');
    return $sql;
  }

  function _debugDsm($var, $label = NULL) {
    if ($this->_debug && function_exists('dsm')) {
      dsm($var, $label);
    }
  }
}
