<?php

class me_twomice_civicrm_aggregatehouseholdcontributions_FilterSet extends CRM_Report_Form {
  var $_filter_criteria_fields = array();
  var $_column_criteria_fields = array();
  var $_scope_fields = array();
  var $_name = '';

  function __construct() {
    $this->_filter_criteria_fields = array(
        $this->_name .'_contribution_date' => array(
        'name' => 'receive_date',
        'dbAlias' => 'receive_date',
        'base_title' => 'date',
        'title' => $this->_buildFilterCriteriaFieldLabel('date'),
        'type' => CRM_Utils_Type::T_DATE,
        'operatorType' => CRM_Report_Form::OP_DATE,
        'grouping' => $this->_name . '-filters',
//        'pseudofield' => $pseudofield,
      ),
      $this->_name .'_contribution_financial_type_id' => array(
        'name' => 'financial_type_id',
        'dbAlias' => 'financial_type_id',
        'base_title' => 'type',
        'title' => $this->_buildFilterCriteriaFieldLabel('type'),
        'operatorType' => CRM_Report_Form::OP_MULTISELECT,
        'type' => CRM_Utils_Type::T_INT,
        'options'      => CRM_Contribute_PseudoConstant::financialType(),
        'grouping' => $this->_name . '-filters',
//        'pseudofield' => $pseudofield,
      ),
      $this->_name .'_contribution_page_id' => array(
        'name' => 'contribution_page_id',
        'dbAlias' => 'contribution_page_id',
        'base_title' => 'page',
        'title' => $this->_buildFilterCriteriaFieldLabel('page'),
        'operatorType' => CRM_Report_Form::OP_MULTISELECT,
        'type' => CRM_Utils_Type::T_INT,
        'options'      => CRM_Contribute_PseudoConstant::contributionPage(),
        'grouping' => $this->_name . '-filters',
//        'pseudofield' => $pseudofield,
      ),
      $this->_name .'_contribution_status_id' => array(
        'name' => 'contribution_status_id',
        'dbAlias' => 'contribution_status_id',
        'base_title' => 'status',
        'title' => $this->_buildFilterCriteriaFieldLabel('status'),
        'operatorType' => CRM_Report_Form::OP_MULTISELECT,
        'type' => CRM_Utils_Type::T_INT,
        'options'      => CRM_Contribute_PseudoConstant::contributionStatus(),
        'default' => '1',
        'grouping' => $this->_name . '-filters',
//        'pseudofield' => $pseudofield,
      ),
      $this->_name .'_contribution_campaign_id' => array(
        'name' => 'campaign_id',
        'dbAlias' => 'campaign_id',
        'base_title' => 'campaign',
        'title' => $this->_buildFilterCriteriaFieldLabel('campaign'),
        'operatorType' => CRM_Report_Form::OP_MULTISELECT,
        'type' => CRM_Utils_Type::T_INT,
        'options'      => CRM_Campaign_BAO_Campaign::getCampaigns(NULL, NULL, NULL, FALSE),
        'grouping' => $this->_name . '-filters',
//        'pseudofield' => $pseudofield,
      ),
      $this->_name .'_contribution_source' => array(
        'name' => 'source',
        'dbAlias' => 'source',
        'base_title' => 'source',
        'title' => $this->_buildFilterCriteriaFieldLabel('source'),
        'operatorType' => CRM_Report_Form::OP_STRING,
        'type' => CRM_Utils_Type::T_STRING,
        'grouping' => $this->_name . '-filters',
//        'pseudofield' => $pseudofield,
      ),
      $this->_name .'_contribution_amount' => array(
        'name' => 'total_amount',
        'dbAlias' => 'total_amount',
        'base_title' => 'amount',
        'title' => $this->_buildFilterCriteriaFieldLabel('amount'),
        'type' => CRM_Utils_Type::T_MONEY,
        'grouping' => $this->_name . '-filters',
//        'pseudofield' => $pseudofield,
      ),
    );

    $this->_buildColumnCriteriaFields();
  }

  function _buildFilterCriteriaFieldLabel($base_title) {
    return "{$this->_name} contribution: qualifying $base_title";
  }
  function _buildColumnCriteriaFieldLabel($base_title) {
    return ucfirst($base_title);
  }

  function _buildColumnCriteriaFields() {
    foreach($this->_filter_criteria_fields as $key => $field) {
      $field['grouping'] = 'column-filters';
      $field['title'] = $this->_buildColumnCriteriaFieldLabel($field['base_title']);
      $this->_column_criteria_fields[$key] = $field;
    }
  }
}