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
  var $_supportedRelationshipTypes = array(
    6, // Household member of/is
    7, // Head of household for/is
  );
  var $_filterSetNames = array(
    'total',
    'any',
    'first',
    'last',
    'largest',
  );

  /**
   * Array of tables that may be required by various filters and "Aggregate"
   * columns (e.g., '"Total contribution" column').
   * @var <type>
   */
  var $_extraJoinTables = array();

  function __construct() {
    // Add this extension's templates directory to Smarty's template path.
    $smarty = CRM_Core_Smarty::singleton();
    $extension_root = dirname( __FILE__ ) . DIRECTORY_SEPARATOR;
    $extension_templates_directory = $extension_root . 'templates';
    if ( is_array( $smarty->template_dir ) ) {
        array_unshift( $smarty->template_dir, $extension_templates_directory );
    } else {
        $smarty->template_dir = array( $extension_templates_directory, $smarty->template_dir );
    }

    // Register this extension's auto-loader for loading classes.
    $this->_registerAutoloader();

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
          'first_contribution' => array(
            'title' => ts('First contribution amount'),
            'dbAlias' => 'CALCULATE',
          ),
          'last_contribution' => array(
            'title' => ts('Last contribution amount'),
            'dbAlias' => 'CALCULATE',
          ),
          'largest_contribution' => array(
            'title' => ts('Largest contribution amount'),
            'dbAlias' => 'CALCULATE',
          ),
          'total_contribution' => array(
            'title' => ts('Total contribution'),
            'dbAlias' => 'CALCULATE',
          ),
          'first_contribution_date' => array(
            'title' => ts('First contribution date'),
            'dbAlias' => 'CALCULATE',
          ),
          'last_contribution_date' => array(
            'title' => ts('Last contribution date'),
            'dbAlias' => 'CALCULATE',
          ),
        ),
        'filters' => array(),
      ),
    );

    // Add the fields provided by each filterSet to _columns.
    foreach ($this->_filterSetNames as $filter_set_name) {
      $filter_set = $this->_getFilterSet($filter_set_name);
      $filters = $filter_set->_getFields(TRUE);
      $this->_columns[$this->_tablename]['filters'] = array_merge($this->_columns[$this->_tablename]['filters'], $filters);
    }

    $this->_groupFilter = TRUE;
    $this->_tagFilter = TRUE;

    parent::__construct();

    // Update label for Tag and Group filters to explain altered meaning.
    $this->_columns['civicrm_tag']['filters']['tagid']['title'] = ts('Tag (for any Aggregated Household member)');
    $this->_columns['civicrm_group']['filters']['gid']['title'] = ts('Group (for any Aggregated Household member)');
  }

  /**
   * Overrides parent::preProcess().
   */
  function preProcess() {
    parent::preProcess();

    // Add special filters for Total, Any, Largest, Last, First.
    $this->addElement('checkbox', 'is_filter_total', ts('Apply "Total Contribution" filter'));
    $this->addElement('checkbox', 'is_filter_any', ts('Apply "Any Contribution" filter'));
    $this->addElement('checkbox', 'is_filter_largest', ts('Apply "Largest Contribution" filter'));
    $this->addElement('checkbox', 'is_filter_last', ts('Apply "Last Contribution" filter'));
    $this->addElement('checkbox', 'is_filter_first', ts('Apply "First Contribution" filter'));


    // Add special fields for "Aggregate Column Values".
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

    // Place all these new fields into the template in 'beginHookFormElements'
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

    // Add the AggregateColumns tab.
    $this->tabs['AggregateColumns'] = array(
      'title' => ts('Aggregate Columns'),
      'tpl' => 'AggregateColumns',
      'div_label' => 'AggregateColumns',
    );

    // Hide the "beginHookFormElements" table (which is unfornately not given
    // and ID or other easy css selectors.
    CRM_Core_Resources::singleton()->addStyle('form.me_twomice_civicrm_aggregatehouseholdcontributions > table.form-layout-compressed:first-of-type {display: none;}', 0, 'html-header');
  }

  /**
   * Overrides parent::from().
   */
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

    // If any filters or "Aggregate" columns require any additional table joins,
    // build them in now.
    foreach ($this->_extraJoinTables as $join_table) {
      $this->_from .= "
        {$join_table['join']} JOIN {$join_table['name']} ON {$join_table['name']}.aggid = {$this->_aliases[$this->_tablename]}.aggid
      ";
    }
  }

  /**
   * Overrides parent::groupBy().
   * Ensure all report output is grouped by {$this->_tablename}.aggid, which is
   * the unique ID of every aggregated household. This ensures each aggregated
   * household is listed only once, and that totals are grouped by household.
   */
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

  /**
   * Overrides parent::validate().
   * Enusre some display columns are selected.
   */
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
   * Overrides parent::beginPostProcessCommon().
   */
  function beginPostProcessCommon() {
    parent::beginPostProcessCommon();

    // For each filterset, clear default filter params if the filter itself is
    // not used.
    foreach ($this->_filterSetNames as $filter_set_name) {
      if (!$this->_params["is_filter_{$filter_set_name}"]) {
        // Clear default filter params if the filter itself is not used.
        $filterset_prefix = "{$filter_set_name}_contribution_";
        $prefixLength = strlen($filterset_prefix);
        // Loop through all the filters defined in $this->_columns, and unset
        // all params related to that filter.
        foreach ($this->_columns as $tableName => $columns) {
          if (is_array($columns['filters'])) {
            foreach ($columns['filters'] as $fieldName => $field) {
              $field_prefix = substr($fieldName, 0, $prefixLength);
                if ($field_prefix == $filterset_prefix) {
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
    }

    // Build the central table for this report.
    $this->_buildCentralReportTable();


    // Build any additional tables that may be required by enabled filtersets.
    foreach($this->_filterSetNames as $filter_set_name) {
      if ($this->_params["is_filter_{$filter_set_name}"]) {
        // If this filterset is enabled, build tables required by this filterset.
        $this->_buildFilterSetTempTable($filter_set_name);
      }
      // Build any tables required by aggregate columns.
      $this->_buildColumnTempTable($filter_set_name);
    }
  }

  /**
   * Overrides parent::alterDisplay().
   */
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

  /**
   * Build and populate any tables that may be required by a given filterset.
   *
   * @param <type> $filter_set_name
   * @return <type>
   */
  function _buildFilterSetTempTable($filter_set_name) {
    $filter_set = $this->_getFilterSet($filter_set_name);
    $filter_set->_buildFilterTables($this);
  }

  /**
   * Depending on the value of $this->_debug, either indicate that the given
   * table should be temporary, or that it should be created as a regular table
   * for later review. For regular tables, drop the table in case it exists
   * already.
   *
   * @param <type> $table_name
   * @return string
   */
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

  /**
   * Set up where and having clauses for filtersets.
   */
  function _filterWhere() {
    $this->_havingClauses = array();
    $this->_whereClauses = array();
    $this->where();
    $this->_havingClauses = array();
    $this->_whereClauses = array();
  }

  /**
   * Build any tables that may be required by aggregate columns.
   */
  function _buildColumnTempTable($filter_set_name) {
    $field_name = "{$filter_set_name}_contribution";
    $field = $this->_columns[$this->_tablename]['fields'][$field_name];
    // If this field was not selected for display, just return.
    if (!$this->_params['fields'][$field_name]) {
      return;
    }

    $filter_set = $this->_getFilterSet($filter_set_name);
    $filter_set->_buildColumnTables($this);
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

  /**
   * Overrides parent::buildQuery().
   * Simply calls parent::buildQuery() and forwards the query SQL to our debug
   * logger.
   */
  function buildQuery($applyLimit = TRUE) {
    // Temporarily remove all filter params so they don't apply to the $where clause
    // in parent::buildQuery(). We've already applied filters in building the
    // various temp tables, so now we just want all the rows in $this->_tablename;
    // applying the filters again at this point will likely screen out some
    // records that we want to keep.  The exception here is the "Any 
    // Contribution" filter, because there is no temp-table for that filter set;
    // so we'll not remove filter params if the param is named "any_*".
    $backup_params = $this->_params;
    foreach ($this->_columns as $columns) {
      if (array_key_exists('filters', $columns) && is_array($columns['filters'])) {
        foreach ($columns['filters'] as $filter_name => $filter) {
          if (substr($filter_name, 0, 4) != 'any_') {
            unset($this->_params[$filter_name . '_value']);
            unset($this->_params[$filter_name . '_max']);
            unset($this->_params[$filter_name . '_min']);
          }
        }
      }
    }
    // Build the query.
    $sql = parent::buildQuery($applyLimit);
    $this->_debugDsm($sql, __FUNCTION__ . ' query');
    // Return params to their original values; don't know who else will be using them.
    $this->_params = $backup_params;

    return $sql;
  }

  /**
   * Debug logger. If $this->_debug is TRUE, send $var to dsm() with label $label.
   */
  function _debugDsm($var, $label = NULL) {
    if ($this->_debug && function_exists('dsm')) {
      dsm($var, $label);
    }
  }

  /* Build a table containing all contributions for contacts (individuals and
   * households) that match the group and tag filters, such that each contrib is
   * attributed to the aggregated household.
   * This is the central table in the report output. It's named $this->_tablename.
   */
  function _buildCentralReportTable() {
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

    $relationship_types_in_string = implode(',', $this->_supportedRelationshipTypes);
    $query = "
      CREATE $temporary TABLE $this->_tablename
      SELECT u.aggid, u.id AS cid, contrib.* FROM
        (
          -- All individuals with the tag/group
          SELECT IF(r.id IS NOT NULL, r.contact_id_b, contact_civireport.id) AS aggid, contact_civireport.id
          FROM
            civicrm_contact contact_civireport
          LEFT JOIN civicrm_relationship r ON r.relationship_type_id IN ($relationship_types_in_string)
            AND r.is_active = 1
            AND (
              r.end_date >= now()
              OR r.end_date IS NULL
            )
            AND r.contact_id_a = contact_civireport.id

          $where
          AND contact_civireport.contact_type = 'individual'

          -- All households with the tag/group
          UNION
          SELECT contact_civireport.id AS aggid, contact_civireport.id
            FROM
              civicrm_contact contact_civireport
          $where
          AND contact_civireport.contact_type = 'household'

          -- All households related to individuals with the tag/group
          UNION
          SELECT r.contact_id_b AS aggid, r.contact_id_b as id
          FROM
            civicrm_contact contact_civireport
          INNER JOIN civicrm_relationship r ON r.relationship_type_id IN ($relationship_types_in_string)
            AND r.is_active = 1
            AND (
              r.end_date >= now()
              OR r.end_date IS NULL
            )
            AND r.contact_id_a = contact_civireport.id
          $where

          -- All individuals related to households with the tag/group
          UNION
          SELECT r.contact_id_b AS aggid, r.contact_id_a as id
          FROM
            civicrm_contact contact_civireport
          INNER JOIN civicrm_relationship r ON r.relationship_type_id IN ($relationship_types_in_string)
            AND r.is_active = 1
            AND (
              r.end_date >= now()
              OR r.end_date IS NULL
            )
            AND r.contact_id_b = contact_civireport.id
          $where

          -- All individuals sharing a household with individuals with the tag/group
          UNION
          SELECT r.contact_id_b AS aggid, r_related.contact_id_a as id
          FROM
            civicrm_contact contact_civireport
          INNER JOIN civicrm_relationship r
            ON r.relationship_type_id IN ($relationship_types_in_string)
              AND r.is_active = 1
              AND (
                r.end_date >= now()
                OR r.end_date IS NULL
              )
              AND r.contact_id_a = contact_civireport.id
          INNER JOIN civicrm_relationship r_related
            ON r_related.relationship_type_id in ($relationship_types_in_string)
              AND r_related.is_active = 1
              AND (
                r_related.end_date >= now()
                OR r_related.end_date IS NULL
              )
              AND r_related.contact_id_b = r.contact_id_b
              AND r_related.id <> r.id
          $where
        ) u
        INNER JOIN civicrm_contribution contrib ON contrib.contact_id = u.id
    ";

    CRM_Core_DAO::executeQuery($query);
    $this->_debugDsm($query, 'Central Table query');
    $this->_debugDsm($this->_params, 'params');

  }

  /**
   * Register this extension's auto-loader for classes.
   */
  function _registerAutoloader() {
    if ($this->_autoloader_registered) {
      return;
    }
    spl_autoload_register(array($this, '_loadClass'), TRUE);
    $this->_autoloader_registered = TRUE;
  }

  /**
   * Auto-loader for classes.
   */
  function _loadClass($class) {
    $class_prefix = 'me_twomice_civicrm_aggregatehouseholdcontributions_';
    $class_prefix_length = strlen($class_prefix);
    if (
      // Only load classes that clearly belong to this extension.
      0 === strncmp($class, $class_prefix, $class_prefix_length) &&
      // Do not load PHP 5.3 namespaced classes.
      // (in a future version, maybe)
      FALSE === strpos($class, '\\')
    ) {
      $path = dirname( __FILE__ ) . DIRECTORY_SEPARATOR;
      $file = strtr(substr($class, $class_prefix_length), '_', '/') . '.php';
      require_once ($path . $file);
    }
  }

  /**
   * For a given filterSet name, create the filterSet object if necessary, 
   * and return it.
   */
  function _getFilterSet($filter_set_name) {
    static $filter_sets_cache = array();
    if (!array_key_exists($filter_set_name, $filter_sets_cache)) {
      $filter_set_class_name = "me_twomice_civicrm_aggregatehouseholdcontributions_FilterSet_". ucfirst($filter_set_name);
      $filter_sets_cache[$filter_set_name] = new $filter_set_class_name;
    }
    return $filter_sets_cache[$filter_set_name];
  }

}
