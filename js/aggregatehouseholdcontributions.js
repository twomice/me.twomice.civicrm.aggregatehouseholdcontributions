CRM.$(function($) {

  filterSets = [
    'total',
    'first',
    'last',
    'largest',
    'any'
  ];

  $('div#report-tab-set-filters input#total_contribution_amount_value').closest('table').attr('id', 'set-filters-original');
  for (i in filterSets) {

    // Check how many column-filter fields exist for this set. If there are none,
    // don't bother creating the table to hold them.
    var movedElementsLength = $('select[id^="column_'+ filterSets[i] +'_"]').length;
    if (movedElementsLength) {
      // Create the table to hold the column-filter fields for this set.
      $('div#set-aggregate-column-filters').append('<table id="set-aggregate-column-filters-'+ filterSets[i] +'" class="report-layout"><thead></thead><tbody></tbody></table>');
      // Within the table, create a button, "copy settings from filter".
      $('div#set-aggregate-column-filters table#set-aggregate-column-filters-'+ filterSets[i] +' tbody').append('<tr><td colspan="3"><button id="copy_settings_from_filter_' + filterSets[i] + '">Copy settings from filter</button></td></tr>');
      // Define a click handler for the button.
      $('button#copy_settings_from_filter_' + filterSets[i]).click({'setName': filterSets[i]}, aggregatedHouseholds_copy_filter_settings);
      // Move the "use custom" radio button into the new table header.
      $('input[name="'+ filterSets[i] +'_contribution_column_filter"]').closest('tr').appendTo('table#set-aggregate-column-filters-'+ filterSets[i] +' thead');
      // Add 'report-contents' class for the "use custom" field label td.
      $('input[name="'+ filterSets[i] +'_contribution_column_filter"]').closest('tr').find('td:first').addClass('report-contents');
      // Set colspan to 2 for the td containing the "use custom" field, so it
      // doesn't change dimensions when the tbody is shown/hidden.
      $('input[name="'+ filterSets[i] +'_contribution_column_filter"]').closest('td').attr('colspan', 2);
      // Define a change handler for the "use custom" field.
      $('input[name="'+ filterSets[i] +'_contribution_column_filter"]').change({'setName': filterSets[i]}, aggregatedHouseholds_toggleColumnFilterVisibility);
      // Fire the change handler, so any existing value will have an immediate
      // effect on the display.
      $('input[name="'+ filterSets[i] +'_contribution_column_filter"]').change();
      // Move all column-filter fields into the new table for this set.
      $('select[id^="column_'+ filterSets[i] +'_"]').closest('tr').appendTo('table#set-aggregate-column-filters-'+ filterSets[i] +' tbody');

      // Define a change-handler for the "display column" check-box for this column.
      $('input#fields_' + filterSets[i] + '_contribution').change({'setName': filterSets[i]}, aggregatedHouseholds_toggleColumnFilterAvailability)
      // Fire the change handler, so any existing value will have an immediate
      // effect on the display.
      $('input#fields_' + filterSets[i] + '_contribution').change()
    }

    // Create a table to hold the filter group for fields in this filter set.
    $('table#set-filters-original').before('<table id="set-filters-'+ filterSets[i] +'" class="report-layout"><thead></thead><tbody></tbody></table>');
    // Move this set's "is filter" checkbox field into the table.
    $('input[name="is_filter_'+ filterSets[i] +'"]').closest('tr').appendTo('table#set-filters-'+ filterSets[i] +' thead');
    // Move the checkbox into the first td in the row.
    $('input[name="is_filter_'+ filterSets[i] +'"]').prependTo($('input[name="is_filter_'+ filterSets[i] +'"]').closest('tr').find('td:first'))
    // Remove the now-empty td.
    $('input[name="is_filter_'+ filterSets[i] +'"]').closest('td').next().remove()
    // Set colspan for the first td to 3, so that it spans the whole table.
    $('input[name="is_filter_'+ filterSets[i] +'"]').closest('td').attr('colspan', '3');
    // Set a change handler for the checkbox, to toggle the "copy settings" button.
    $('input[name="is_filter_'+ filterSets[i] +'"]').change({'setName': filterSets[i]}, aggregatedHouseholds_toggleColumnFilterCopySettingsButton);
    // Set another change handler for the checkbox, to toggle visibility of fields related to the checkbox.
    $('input[name="is_filter_'+ filterSets[i] +'"]').change({'setName': filterSets[i]}, aggregatedHouseholds_toggleColumnFilterVisibility);
    // Fire the change handlers, so any existing value will have an immediate
    // effect on the display.
    $('input[name="is_filter_'+ filterSets[i] +'"]').change();
    // Move all filter fields into the new table for this set.
    $('select[name^="'+ filterSets[i] +'_contribution_"]').closest('tr').appendTo('table#set-filters-'+ filterSets[i] +' tbody');
  }
});

function aggregatedHouseholds_toggleColumnFilterAvailability(event) {
  var el = cj(this);
  var type = el.attr('type')

  var filterSetName = event.data.setName;

  var table = cj('table#set-aggregate-column-filters-' + filterSetName);
  if (type == 'checkbox' && el.is(':checked')) {
    table.addClass('aggregate-households-visible');
    table.show();
  }
  else {
    table.removeClass('aggregate-households-visible');
    table.hide();
  }

  var count = cj('div#set-aggregate-column-filters table.aggregate-households-visible').length
  if(count > 0) {
    cj('h3#set-aggregate-column-filters-header').show();
    cj('div#set-aggregate-column-filters-noop-help').hide();
  }
  else {
    cj('h3#set-aggregate-column-filters-header').hide();
    cj('div#set-aggregate-column-filters-noop-help').show();
  }
}


/**
 * Toggle display of the filter fields for the changed checkbox.
 */
function aggregatedHouseholds_toggleColumnFilterVisibility(event) {
  var el = cj(this);
  var type = el.attr('type')

  var filterSetName = event.data.setName;

  var tbody = el.closest('table').find('tbody');
  if ((type == 'checkbox' || el.val() == 2) && el.is(':checked')) {
    tbody.show();
  }
  else {
    tbody.hide();
  }

}

/**
 * Toggle display of the "Copy Settings from Filter" button for the changed checkbox.
 */
function aggregatedHouseholds_toggleColumnFilterCopySettingsButton(event) {
  var el = cj(this);

  var filterSetName = event.data.setName;
  var copyButtonTr = cj('button#copy_settings_from_filter_' + filterSetName).closest('tr')

  var tbody = el.closest('table').find('tbody');
  if (el.is(':checked')) {
    copyButtonTr.show();
  }
  else {
    copyButtonTr.hide();
  }
}

/**
 * Copy settings from filter to aggregate column display settings, for the
 * setName defined in event.data.setName.
 */
function aggregatedHouseholds_copy_filter_settings(event) {
  setName = event.data.setName;
  // Copy values for all select elements.
  cj('select[id^="' + setName + '_contribution_"]').each(function(idx, el){
    var column_field_id = 'column_'+ el.id
    var selector = 'select#' + column_field_id
    cj(selector).val(cj(el).val()).change();
  })

  // Copy values for all input elements.
  cj('input[id^="' + setName + '_contribution_"]').each(function(idx, el){
    var column_field_id = 'column_'+ el.id
    var selector = 'input#' + column_field_id
    cj(selector).val(cj(el).val()).change();
  })

  // Specifically set date_from and date_to (these are datepicker fields which
  // have dynamic IDs, so we can determine the aggregate-column-field ID just
  // by prepending 'column_' to the filter field ID.
  var from_date = cj('input[id^="' + setName + '_contribution_date_from_display_"]').val()
  cj('input[id^="column_' + setName + '_contribution_date_from_display_"]').val(from_date)
  var to_date = cj('input[id^="' + setName + '_contribution_date_to_display_"]').val()
  cj('input[id^="column_' + setName + '_contribution_date_to_display_"]').val(to_date)

  // Ensure the click event doesn't trigger its default behavior.
  event.preventDefault();
}
