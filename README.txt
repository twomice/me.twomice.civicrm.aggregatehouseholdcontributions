Aggregated Household Contributions

Provides a report template for analyzing contributions aggregated at the
household level.

===================
BACKGROUND

Most organizations that keep track of Household relationships in CiviCRM
eventually find that their contribution records are spread across a mix of
individuals and households: some contributions attributed to the  Smith
Household, others to John Smith, and still others to Mary Smith. But when
you've taken the time to keep good Household records, you probably want to
relate to your contacts as households at times, especially in your fundraising
campaigns, where it can really matter, for example, how much the entire
household has given in the past year.

The Aggregated Household Contribution report offers a way to report accurately
on exactly that type of question.

===================
HOW IT WORKS

The primary intent of this report is to present all individual contacts in a
household, along with the household contact itself, as one entity, which we call
an Aggregated Household. All contributions for those various individual contacts
are then attributed to that Aggregated Household. All of John Smith's
contributions, Mary Smith's contributions, and the Smith Household's
contributions are attributed to the Aggregated Household known as "The Smith
Household". Individual contacts who don't belong to a household are counted as
their own Aggregated Household entity, named after the individual, and are
treated like any other Aggregated Household. Once this aggregation is done, this
report will apply its filters to determine which Aggregated Households will
appear in the report output.

===================
UNIQUE FEATURES

This report presents two unique features: Contribution filter sets, and options
to determine aggregate column values.

--------------------
1. Contribution filter sets:
After the household aggregation has been performed (see HOW IT WORKS), this
report applies its filters. There are the usual filters for groups and tags, and
additionally five sets of special filters related to the contributions
themselves: Total, First, Last, Largest, and Any.

1.a. Total
Use the Total filter to limit the report based on the total amount of
contributions by each Aggregated Household. There are several fields labeled
"Total contribution: qualifying [attribute]," where [attribute] is date, status,
contribution type, etc. Use these fields to limit the contributions that should
be totaled up. Then use the last field, "Total contribution: total," to ensure
that the totaled amount meets a certain range. For example, the following Total
filter settings will limit the report to Aggregate Households which have given
more than $5000 in completed contributions in the past year:

  "Total contribution: qualifying date": Last 12 Months
  "Total contribution: qualifying status": Completed
  "Total contribution: total": [Is greater than:] 5000

1.b. First
Use the First filter to limit the report based on a first contribution by each
Aggregated Household. Similar to the Total filter, there are several fields
labeled "First contribution: [attribute]", used to limit the contributions which
should be considered before picking a "first" one. Unlike the Total filter,
which simply calculates a total and then compares it to a given criteria, the
use of the First filter can be considered in three distinct ways:

  a. Limit the report to Aggregate Households where the first ever contribution
     meets all the First filter criteria;
  b. Limit the report to Aggregate Households where the first contribution
     *WITHIN A CERTAIN DATE RANGE* meets the First filter criteria; or
  c. Limit the report to Aggregate Households where the first contribution
     *WITHIN A CERTAIN AMOUNT RANGE* meets the First filter criteria.

This distinction is indicated by the "First Contribution filter scope" field,
which has the corresponding options:

  a. First contribution ever meets these criteria
  b. First contribution meeting these criteria was within this date range
  c. First contribution meeting these criteria was within this amount range

Examples:
  a. To limit the report to Aggregated Households whose first ever completed
     contribution was for less than $100 and was in the year 2004, apply these
     filter settings:
       First contribution: qualifying date: [Date range of 2004]
       First contribution: qualifying status: Completed
       First contribution: qualifying amount: [ Is less than ] $100
       "First Contribution" filter scope: First contribution ever meets these
         criteria

     This will exclude Aggregated Households whose first ever contribution was
     for $100 or more, and those whose first ever contribution was not in 2004.

  b. To limit the report to Aggregated Households whose first completed
     contribution for less than $100 was in 2004, apply these filter settings:
       First contribution: qualifying date: [Date range of 2004]
       First contribution: qualifying status: Completed
       First contribution: qualifying amount: [ Is less than ] $100
       "First Contribution" filter scope: First contribution contribution
         meeting these criteria was within this amount range

     This will allow Aggregate Households whose first ever completed
     contribution was before 2004, as long as their first completed contribution
     for less than $100 was in 2004.

  c. To limit the report to Aggregated Households whose first completed
     contribution in 2004 was for less than $100, apply these filter settings:
       First contribution: qualifying date: [Date range of 2004]
       First contribution: qualifying status: Completed
       First contribution: qualifying amount: [ Is less than ] $100
       "First Contribution" filter scope: First contribution contribution
         meeting these criteria was within this amount range

     This will allow Aggregate Households whose first ever completed
     contribution was more than $100, as long as their first completed
     contribution in 2004 was less than $100.

1.c.  Last
Use the Last filter to limit the report based on a last contribution by each
Aggregated Household. Usage is comparable to the First filter (See "1.b.
First").

1.d.  Largest
Use the Largest filter to limit the report based on a largest contribution by
each Aggregated Household. Usage is comparable to the First filter (See "1.b.
First").

1.e. Any
Use the Any filter to limit the report based on any single contribution by each
Aggregated Household. Usage is comparable to the contribution filters in stock
CiviCRM reports. For example, to limit the report to Aggregated Households who
have every completed one contribution of more than $10,000, apply these filter
settings:
  Any contribution: qualifying status: Completed
  Any contribution: qualifying amount: [ Is greater than ] 10,000


--------------------
2. Determine aggregate column values
As is true of most CiviCRM reports, this report allows selection of any of a
number of columns for display. Most of these are rather straightforward,
displaying the value of a specific field (Last Name, Postal Code, etc.) for any
given contact. This report also provides four additional columns which display
calculated values for each Aggregated Household: Total contribution, First
contribution, Last contribution, and Largest contribution.

For each of these four columns, calculation of the displayed value is affected
by configurable rules under the report criteria section labeled "Determine
aggregate column values," where each column may be set to display either the
amount "for all contributions, ever," or an amount based only on contributions
that meet specified criteria.

(Note that the "Determine aggregate column values" settings for each column is 
hidden unless that columns is selected for display, and the "Determine aggregate
column values" section itself is hidden entirely unless at least one of the four
columns is selected for display.)

Examples:
  a. To cause the "First contribution" column to display the amount of the
     earliest contribution attributed to each Aggregated Household (regardless
     of status, date, amount, campaign, or any other criteria), select the radio
     button labeled "Total of all contributions ever" under "Total contribution
     column" in the "Determine aggregate column values" section.

  b. To cause the "First contribution" column to display the first completed
     contribution over $100 in the year of 2004, select the radio button labeled
     "Use custom settings" under "First contribution column" in the "Determine
     aggregate column values" section. A collection of criteria fields will be
     displayed below the radio button. Apply these filter settings::
       Date: [Date range of 2004]
       Status: Completed
       Amount: [ Is less than ] $100

When a display column's corresponding filter is in use, the "Use custom
settings" criteria fields for that column will include a button labeled "Copy
settings from filter."  Click this button to copy all field values from the
corresponding filter criteria into the "Use custom settings" for this column.
This will have the effect of displaying the amount most relevant to the
corresponding filter.

Example:
  When using the Total filter to limit the report to Aggregate Households which
  have given more than $5000 in completed contributions in the past year (see
  "1. Contribution filter sets" > "1.a. Total", above), follow these steps to
  include a Total column which will display the total amount of completed
  contributions in the past year:
    1. Select the "Total" column for display.
    2. Select the radio button labeled "Use custom settings" under "Total
       contribution column" in the "Determine aggregate column values" section.
    3. Click the button labeled "Copy settings from filter" and observed that
       the values of the "Use custom settings" criteria fields now exactly match
       those of the Total filter criteria fields.

  These settings will cause the "Total" column to display the total amount of
  contributions which match the Total filter.
