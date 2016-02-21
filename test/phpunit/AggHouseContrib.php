<?php

/**
 * Documentation for API of PHPUnit_Extensions_SeleniumTestCase:
 * http://release.seleniumhq.org/selenium-core/1.0.1/reference.html
 * This also seems to be helpful for method names at the bottom of the page:
 * http://docs.tadiavo.com/phpunit/www.phpunit.de/pocket_guide/3.1/en/selenium.html
 */
class AggHouseContrib extends PHPUnit_Extensions_SeleniumTestCase {
  var $config;

  public function setUp() {
    global $config;
    $this->config = $config;

    $this->setBrowser('firefox');
    $this->setBrowserUrl($this->config['base_url']);


  }

  public function tearDown() {
    $this->stop();
    return;
  }

  public function _login() {
    $this->open('/user');
    $this->assertElementPresent('edit-submit');
    $this->type('edit-name', $this->config['test_user_name']);
    $this->type('edit-pass', $this->config['test_user_pass']);
    $this->clickAndWait('edit-submit');
    $this->assertText('css=h1', $this->config['test_user_name']);
    return;
  }

  public function startReportSetup() {
    $this->_login();
    $this->open($this->config['report_url']);
    $this->setDisplayColumns();

    if (array_key_exists('gid_value_option_locator', $this->config) && $this->config['gid_value_option_locator'] > 0) {
      $this->addSelection('gid_value', $this->config['gid_value_option_locator']);
    }
  }

  public function setDisplayColumns() {
    $this->click('fields_display_name');
    $this->click('fields_total_contribution');
    $this->click('fields_first_contribution');
    $this->click('fields_last_contribution');
    $this->click('fields_largest_contribution');
  }

  public function setFilterFirstValues() {
    $this->click('is_filter_first');
    $this->select('first_contribution_date_relative', 'value=0');
    $this->type('first_contribution_date_from', '1/1/2014');
    $this->type('first_contribution_date_to', '1/1/2015');
    $this->select('first_contribution_amount_op', 'value=gte');
    $this->type('first_contribution_amount_value', '1000');
  }

  public function setFilterLastValues() {
    $this->click('is_filter_last');
    $this->select('last_contribution_date_relative', 'value=0');
    $this->type('last_contribution_date_from', '1/1/2014');
    $this->type('last_contribution_date_to', '1/1/2015');
    $this->select('last_contribution_amount_op', 'value=gte');
    $this->type('last_contribution_amount_value', '1000');
  }

  public function setFilterLargestValues() {
    $this->click('is_filter_largest');
    $this->select('largest_contribution_date_relative', 'value=0');
    $this->type('largest_contribution_date_from', '1/1/2014');
    $this->type('largest_contribution_date_to', '1/1/2015');
    $this->select('largest_contribution_amount_op', 'value=gte');
    $this->type('largest_contribution_amount_value', '1000');
  }

  public function assertResults($test_name) {
//    sleep(60);
    $this->assertArrayHasKey($test_name, $this->config['results'], "Expected results for test '$test_name' not found in \$this->config['results'].");

    
    $expected = $this->config['results'][$test_name];

    // Assert row_count.
    if ($this->isElementPresent('//th[text()[contains(.,"Total Row(s)")]]/../td')) {
      $row_count = $this->getText('//th[text()[contains(.,"Total Row(s)")]]/../td');
    }
    else {
      $row_count = $this->getText('//th[text()[contains(.,"Row(s) Listed")]]/../td');
    }

    $this->assertEquals($expected['row_count'], $row_count, "Row count should be {$expected['row_count']} but is $row_count.");

    $assert_row_values = array(
      "name",
      "total",
      "first",
      "last",
      "largest",
    );
    foreach ($expected['rows'] as $row_id => $values) {
      $name = $this->getText("css=tr#crm-report_{$row_id} td.crm-report-civicrm_contact_display_name a");
      $total = $this->getText("css=tr#crm-report_{$row_id} td.crm-report-civireport_tmp_column_total_total_contribution");
      $first = $this->getText("css=tr#crm-report_{$row_id} td.crm-report-civireport_tmp_column_first_first_contribution");
      $last = $this->getText("css=tr#crm-report_{$row_id} td.crm-report-civireport_tmp_column_last_last_contribution");
      $largest = $this->getText("css=tr#crm-report_{$row_id} td.crm-report-civireport_tmp_column_largest_largest_contribution");

      foreach ($assert_row_values as $value_name) {
        $this->assertEquals($values[$value_name], $$value_name, "In row {$row_id}, {$value_name} is '{$$value_name}' but should be '{$values[$value_name]}'.");
      }
    }
  }

  public function testFilterTotal() {
    $this->startReportSetup();

    $this->click('is_filter_total');
    $this->select('total_contribution_date_relative', 'value=0');
    $this->type('total_contribution_date_from', '1/1/2014');
    $this->type('total_contribution_date_to', '1/1/2015');
    $this->select('total_contribution_total_op', 'value=gte');
    $this->type('total_contribution_total_value', '1000');

    $this->clickAndWait('_qf_aggregatehouseholdcontributions_submit');
    $this->assertResults(__FUNCTION__);
  }

  public function testFilterAny() {
    $this->startReportSetup();

    $this->click('is_filter_total');
    $this->select('any_contribution_date_relative', 'value=0');
    $this->type('any_contribution_date_from', '1/1/2014');
    $this->type('any_contribution_date_to', '1/1/2015');
    $this->select('any_contribution_amount_op', 'value=gte');
    $this->type('any_contribution_amount_value', '1000');

    $this->clickAndWait('_qf_aggregatehouseholdcontributions_submit');
    $this->assertResults(__FUNCTION__);
  }
  
  public function testFilterFirstWithScopeEver() {
    $this->startReportSetup();
    $this->setFilterFirstValues();
    $this->select('first_contribution_scope_value', 'value=1');
    $this->clickAndWait('_qf_aggregatehouseholdcontributions_submit');
    $this->assertResults(__FUNCTION__);
  }

  public function testFilterFirstWithScopeDate() {
    $this->startReportSetup();
    $this->setFilterFirstValues();
    $this->select('first_contribution_scope_value', 'value=2');
    $this->clickAndWait('_qf_aggregatehouseholdcontributions_submit');
    $this->assertResults(__FUNCTION__);
  }

  public function testFilterFirstWithScopeAmount() {
    $this->startReportSetup();
    $this->setFilterFirstValues();
    $this->select('first_contribution_scope_value', 'value=3');
    $this->clickAndWait('_qf_aggregatehouseholdcontributions_submit');
    $this->assertResults(__FUNCTION__);
  }

  public function testFilterLastWithScopeEver() {
    $this->startReportSetup();
    $this->setFilterLastValues();
    $this->select('last_contribution_scope_value', 'value=1');
    $this->clickAndWait('_qf_aggregatehouseholdcontributions_submit');
    $this->assertResults(__FUNCTION__);
  }

  public function testFilterLastWithScopeDate() {
    $this->startReportSetup();
    $this->setFilterLastValues();
    $this->select('last_contribution_scope_value', 'value=2');
    $this->clickAndWait('_qf_aggregatehouseholdcontributions_submit');
    $this->assertResults(__FUNCTION__);
  }

  public function testFilterLastWithScopeAmount() {
    $this->startReportSetup();
    $this->setFilterLastValues();
    $this->select('last_contribution_scope_value', 'value=3');
    $this->clickAndWait('_qf_aggregatehouseholdcontributions_submit');
    $this->assertResults(__FUNCTION__);
  }

  public function testFilterLargestWithScopeEver() {
    $this->startReportSetup();
    $this->setFilterLargestValues();
    $this->select('largest_contribution_scope_value', 'value=1');
    $this->clickAndWait('_qf_aggregatehouseholdcontributions_submit');
    $this->assertResults(__FUNCTION__);
  }

  public function testFilterLargestWithScopeDate() {
    $this->startReportSetup();
    $this->setFilterLargestValues();
    $this->select('largest_contribution_scope_value', 'value=2');
    $this->clickAndWait('_qf_aggregatehouseholdcontributions_submit');
    $this->assertResults(__FUNCTION__);
  }

  public function testFilterLargestWithScopeAmount() {
    $this->startReportSetup();
    $this->setFilterLargestValues();
    $this->select('largest_contribution_scope_value', 'value=3');
    $this->clickAndWait('_qf_aggregatehouseholdcontributions_submit');
    $this->assertResults(__FUNCTION__);
  }

}
