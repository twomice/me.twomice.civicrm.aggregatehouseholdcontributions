<?php

/**
 * Documentation for API of PHPUnit_Extensions_SeleniumTestCase:
 * http://release.seleniumhq.org/selenium-core/1.0.1/reference.html
 * This also seems to be helpful for method names at the bottom of the page:
 * http://docs.tadiavo.com/phpunit/www.phpunit.de/pocket_guide/3.1/en/selenium.html
 */
class AggHouseContrib extends PHPUnit_Extensions_SeleniumTestCase {
  var $config;
  var $_sleepBeforeAssert = 0;

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

  public function setFilterTotalValues() {
    $this->click('is_filter_total');
    $this->select('total_contribution_date_relative', 'value=0');
    $this->type('total_contribution_date_from', '1/1/2014');
    $this->type('total_contribution_date_to', '1/1/2015');
    $this->select('total_contribution_total_op', 'value=gte');
    $this->type('total_contribution_total_value', '1000');
  }

  public function setFilterAnyValues() {
    $this->click('is_filter_total');
    $this->select('any_contribution_date_relative', 'value=0');
    $this->type('any_contribution_date_from', '1/1/2014');
    $this->type('any_contribution_date_to', '1/1/2015');
    $this->select('any_contribution_amount_op', 'value=gte');
    $this->type('any_contribution_amount_value', '1000');
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

  public function setColumnTotalValues($setting_type) {
    $this->click('//input[@name="total_contribution_column_filter"][@value="2"]');
    if ($setting_type == 'custom') {
      $this->select('column_total_contribution_date_relative', 'value=0');
      $this->type('column_total_contribution_date_from', '1/1/2014');
      $this->type('column_total_contribution_date_to', '1/1/2015');
      $this->select('column_total_contribution_amount_op', 'value=lte');
      $this->type('column_total_contribution_amount_value', '800');
    }
    elseif ($setting_type == 'copy') {
      $this->click('copy_settings_from_filter_total');
      // FIXME: It should not be necessary to set op and value like this, but
      // at the moment the "copy" button is not doing it:
      $this->select('column_total_contribution_amount_op', 'value=gte');
      $this->type('column_total_contribution_amount_value', '1000');
    }
  }

  public function setColumnLargestValues($setting_type) {
    $this->click('//input[@name="largest_contribution_column_filter"][@value="2"]');
    if ($setting_type == 'custom') {
      $this->select('column_largest_contribution_date_relative', 'value=0');
      $this->type('column_largest_contribution_date_from', '1/1/2014');
      $this->type('column_largest_contribution_date_to', '1/1/2015');
      $this->select('column_largest_contribution_amount_op', 'value=lte');
      $this->type('column_largest_contribution_amount_value', '800');
    }
    elseif ($setting_type == 'copy') {
      $this->click('copy_settings_from_filter_largest');
      // FIXME: It should not be necessary to set op and value like this, but
      // at the moment the "copy" button is not doing it:
      $this->select('column_largest_contribution_amount_op', 'value=gte');
      $this->type('column_largest_contribution_amount_value', '1000');
    }
  }

  public function setColumnFirstValues($setting_type) {
    $this->click('//input[@name="first_contribution_column_filter"][@value="2"]');
    if ($setting_type == 'custom') {
      $this->select('column_first_contribution_date_relative', 'value=0');
      $this->type('column_first_contribution_date_from', '1/1/2014');
      $this->type('column_first_contribution_date_to', '1/1/2015');
      $this->select('column_first_contribution_amount_op', 'value=lte');
      $this->type('column_first_contribution_amount_value', '800');
    }
    elseif ($setting_type == 'copy') {
      $this->click('copy_settings_from_filter_first');
      // FIXME: It should not be necessary to set op and value like this, but
      // at the moment the "copy" button is not doing it:
      $this->select('column_first_contribution_amount_op', 'value=gte');
      $this->type('column_first_contribution_amount_value', '1000');
    }
  }

  public function setColumnLastValues($setting_type) {
    $this->click('//input[@name="last_contribution_column_filter"][@value="2"]');
    if ($setting_type == 'custom') {
      $this->select('column_last_contribution_date_relative', 'value=0');
      $this->type('column_last_contribution_date_from', '1/1/2014');
      $this->type('column_last_contribution_date_to', '1/1/2015');
      $this->select('column_last_contribution_amount_op', 'value=lte');
      $this->type('column_last_contribution_amount_value', '800');
    }
    elseif ($setting_type == 'copy') {
      $this->click('copy_settings_from_filter_last');
      // FIXME: It should not be necessary to set op and value like this, but
      // at the moment the "copy" button is not doing it:
      $this->select('column_last_contribution_amount_op', 'value=gte');
      $this->type('column_last_contribution_amount_value', '1000');
    }
  }

  public function assertResults($test_name) {
    sleep($this->_sleepBeforeAssert);
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
      // "total" column
      $total = $this->getText("css=tr#crm-report_{$row_id} td:nth-child(2)");
      // "first" column
      $first = $this->getText("css=tr#crm-report_{$row_id} td:nth-child(3)");
      // "last" column
      $last = $this->getText("css=tr#crm-report_{$row_id} td:nth-child(4)");
      // "largest" column
      $largest = $this->getText("css=tr#crm-report_{$row_id} td:nth-child(5)");
      // link inside "display name" column
      $name_full = $this->getText("css=tr#crm-report_{$row_id} td:nth-child(1) a");
      // 'name' is obfuscated as [first three]...[last three]
      $name = substr($name_full, 0, 3) . '...' . substr($name_full, -3);

      foreach ($assert_row_values as $value_name) {
        $this->assertEquals($values[$value_name], $$value_name, "In row {$row_id}, {$value_name} is '{$$value_name}' but should be '{$values[$value_name]}'.");
      }
    }
  }

  public function testFilterTotal() {
    $this->startReportSetup();
    $this->setFilterTotalValues();
    $this->clickAndWait('_qf_aggregatehouseholdcontributions_submit');
    $this->assertResults(__FUNCTION__);
  }

  public function testFilterAny() {
    $this->startReportSetup();
    $this->setFilterAnyValues();
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

  public function testFilterTotalAndColumnTotalWithCustomSettings() {
    $this->startReportSetup();
    $this->setFilterTotalValues();
    $this->setColumnTotalValues('custom');
    $this->clickAndWait('_qf_aggregatehouseholdcontributions_submit');
    $this->assertResults(__FUNCTION__);
  }

  public function testFilterTotalAndColumnTotalWithCopySettings() {
    $this->startReportSetup();
    $this->setFilterTotalValues();
    $this->setColumnTotalValues('copy');
    $this->clickAndWait('_qf_aggregatehouseholdcontributions_submit');
    $this->assertResults(__FUNCTION__);
  }

  public function testFilterLargestWithScopeEverAndColumnLargestWithCustomSettings() {
    $this->startReportSetup();
    $this->setFilterLargestValues();
    $this->select('largest_contribution_scope_value', 'value=1');
    $this->setColumnLargestValues('custom');
    $this->clickAndWait('_qf_aggregatehouseholdcontributions_submit');
    $this->assertResults(__FUNCTION__);
  }

  public function testFilterLargestWithScopeDateAndColumnLargestWithCustomSettings() {
    $this->startReportSetup();
    $this->setFilterLargestValues();
    $this->select('largest_contribution_scope_value', 'value=2');
    $this->setColumnLargestValues('custom');
    $this->clickAndWait('_qf_aggregatehouseholdcontributions_submit');
    $this->assertResults(__FUNCTION__);
  }

  public function testFilterLargestWithScopeAmountAndColumnLargestWithCustomSettings() {
    $this->startReportSetup();
    $this->setFilterLargestValues();
    $this->select('largest_contribution_scope_value', 'value=3');
    $this->setColumnLargestValues('custom');
    $this->clickAndWait('_qf_aggregatehouseholdcontributions_submit');
    $this->assertResults(__FUNCTION__);
  }

  public function testFilterLargestWithScopeEverAndColumnLargestWithCopySettings() {
    $this->startReportSetup();
    $this->setFilterLargestValues();
    $this->select('largest_contribution_scope_value', 'value=1');
    $this->setColumnLargestValues('copy');
    $this->clickAndWait('_qf_aggregatehouseholdcontributions_submit');
    $this->assertResults(__FUNCTION__);
  }

  /**
   * @group fatal
   */
  public function testFilterLargestWithScopeDateAndColumnLargestWithCopySettings() {
    $this->startReportSetup();
    $this->setFilterLargestValues();
    $this->select('largest_contribution_scope_value', 'value=2');
    $this->setColumnLargestValues('copy');
    $this->clickAndWait('_qf_aggregatehouseholdcontributions_submit');
    $this->assertResults(__FUNCTION__);
  }

  public function testFilterLargestWithScopeAmountAndColumnLargestWithCopySettings() {
    $this->startReportSetup();
    $this->setFilterLargestValues();
    $this->select('largest_contribution_scope_value', 'value=3');
    $this->setColumnLargestValues('copy');
    $this->clickAndWait('_qf_aggregatehouseholdcontributions_submit');
    $this->assertResults(__FUNCTION__);
  }

  public function testFilterFirstWithScopeEverAndColumnFirstWithCustomSettings() {
    $this->startReportSetup();
    $this->setFilterFirstValues();
    $this->select('first_contribution_scope_value', 'value=1');
    $this->setColumnFirstValues('custom');
    $this->clickAndWait('_qf_aggregatehouseholdcontributions_submit');
    $this->assertResults(__FUNCTION__);
  }

  public function testFilterFirstWithScopeDateAndColumnFirstWithCustomSettings() {
    $this->startReportSetup();
    $this->setFilterFirstValues();
    $this->select('first_contribution_scope_value', 'value=2');
    $this->setColumnFirstValues('custom');
    $this->clickAndWait('_qf_aggregatehouseholdcontributions_submit');
    $this->assertResults(__FUNCTION__);
  }

  public function testFilterFirstWithScopeAmountAndColumnFirstWithCustomSettings() {
    $this->startReportSetup();
    $this->setFilterFirstValues();
    $this->select('first_contribution_scope_value', 'value=3');
    $this->setColumnFirstValues('custom');
    $this->clickAndWait('_qf_aggregatehouseholdcontributions_submit');
    $this->assertResults(__FUNCTION__);
  }

  public function testFilterFirstWithScopeEverAndColumnFirstWithCopySettings() {
    $this->startReportSetup();
    $this->setFilterFirstValues();
    $this->select('first_contribution_scope_value', 'value=1');
    $this->setColumnFirstValues('copy');
    $this->clickAndWait('_qf_aggregatehouseholdcontributions_submit');
    $this->assertResults(__FUNCTION__);
  }

  public function testFilterFirstWithScopeDateAndColumnFirstWithCopySettings() {
    $this->startReportSetup();
    $this->setFilterFirstValues();
    $this->select('first_contribution_scope_value', 'value=2');
    $this->setColumnFirstValues('copy');
    $this->clickAndWait('_qf_aggregatehouseholdcontributions_submit');
    $this->assertResults(__FUNCTION__);
  }

  public function testFilterFirstWithScopeAmountAndColumnFirstWithCopySettings() {
    $this->startReportSetup();
    $this->setFilterFirstValues();
    $this->select('first_contribution_scope_value', 'value=3');
    $this->setColumnFirstValues('copy');
    $this->clickAndWait('_qf_aggregatehouseholdcontributions_submit');
    $this->assertResults(__FUNCTION__);
  }

  public function testFilterLastWithScopeEverAndColumnLastWithCustomSettings() {
    $this->startReportSetup();
    $this->setFilterLastValues();
    $this->select('last_contribution_scope_value', 'value=1');
    $this->setColumnLastValues('custom');
    $this->clickAndWait('_qf_aggregatehouseholdcontributions_submit');
    $this->assertResults(__FUNCTION__);
  }

  public function testFilterLastWithScopeDateAndColumnLastWithCustomSettings() {
    $this->startReportSetup();
    $this->setFilterLastValues();
    $this->select('last_contribution_scope_value', 'value=2');
    $this->setColumnLastValues('custom');
    $this->clickAndWait('_qf_aggregatehouseholdcontributions_submit');
    $this->assertResults(__FUNCTION__);
  }

  public function testFilterLastWithScopeAmountAndColumnLastWithCustomSettings() {
    $this->startReportSetup();
    $this->setFilterLastValues();
    $this->select('last_contribution_scope_value', 'value=3');
    $this->setColumnLastValues('custom');
    $this->clickAndWait('_qf_aggregatehouseholdcontributions_submit');
    $this->assertResults(__FUNCTION__);
  }

  public function testFilterLastWithScopeEverAndColumnLastWithCopySettings() {
    $this->startReportSetup();
    $this->setFilterLastValues();
    $this->select('last_contribution_scope_value', 'value=1');
    $this->setColumnLastValues('copy');
    $this->clickAndWait('_qf_aggregatehouseholdcontributions_submit');
    $this->assertResults(__FUNCTION__);
  }

  public function testFilterLastWithScopeDateAndColumnLastWithCopySettings() {
    $this->startReportSetup();
    $this->setFilterLastValues();
    $this->select('last_contribution_scope_value', 'value=2');
    $this->setColumnLastValues('copy');
    $this->clickAndWait('_qf_aggregatehouseholdcontributions_submit');
    $this->assertResults(__FUNCTION__);
  }

  public function testFilterLastWithScopeAmountAndColumnLastWithCopySettings() {
    $this->startReportSetup();
    $this->setFilterLastValues();
    $this->select('last_contribution_scope_value', 'value=3');
    $this->setColumnLastValues('copy');
    $this->clickAndWait('_qf_aggregatehouseholdcontributions_submit');
    $this->assertResults(__FUNCTION__);
  }

}
