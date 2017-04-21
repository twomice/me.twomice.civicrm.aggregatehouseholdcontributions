# Donor Detail with Contributions Aggregated for Households

## SUMMARY

A report template for analyzing Individual and Household contributors, with donations aggregated ("rolled up") to the Household level, also offers uniquely robust filter and display/export options to meet the needs of fundraising professionals.

## BACKGROUND

Many organizations that use Household contacts and Individual contacts in CiviCRM will eventually have contribution spread across a mix of Households and Individuals.

Although volunteer and event management will often focus on an Individual, fundraising professionals often want to consider giving history of couples and analyze giving history at the Household level for fundraising activities such as:

* Prospecting, Solicitation and Cultivation list building and segmentation;
* Recognition of contributors such as in annual report honor rolls; and
* Acknowledgment such as annual recap receipts. 

Along with the need to roll up contributions for Households, fundraising professionals may also be looking for multifaceted options to filter, display, and export giving history with fundraising best practices in mind.

## HOW IT WORKS

While the unique functionality of the report is seamless to the user, it’s important to understand that this report first performs aggregation, then applies filtering and display options.

### Aggregation
The report is recognizing each Household and its Individual household members or head(s) as one entity, which we call an Aggregated Household. Also, any Individual contact who is not part of a Household is treated as their own Aggregated Household.

#### Examples of Aggregated Households
* A typical household case is the Pat & Kelly Smith Household. Pat Smith's contributions, Kelly Smith's contributions, and the contributions of the Pat & Kelly Smith Household are all reported as given by one Aggregated Household: "Pat & Kelly Smith Household".
* An Individual without a Household relationship, John Doe, is treated and displayed as one Aggregated Household, "John Doe".

#### Notes on aggregation

1. **Aggregating relationships:** Only relationships of the types "Household member of/is" and “Head of Household for/is,” which are active at the time the report is run, are used to aggregate Individuals into Households. You may need to create or edit relationships between your contacts to fit this specification.

1. **Multiple Households per Individual:** If an Individual has an aggregating relationship to two (or more) Household records, that Individual's contributions will be reported as given by all of those Aggregated Households. Although this may be a rare case, it is important to note that gifts could be “double counted” in this way.

1. **Households as a whole:** Fundraisers cannot use this report to produce results that break out Individuals who are members of Households. Individual names and information will be shown in results only for Individuals who have no aggregating relationships.

### Filtering and display options
After performing aggregation, the report applies setting defined in the report criteria for Filters, Columns, and special Aggregate Columns, to determine which Aggregated Households and what information to display in the report output.

The Filters and the Aggregate Columns criteria both include "Criteria Sets" of criteria to select and further define. These Criteria Sets include: Total Contribution; First Contribution; Last Contribution; Largest Contribution and Any Contribution, and their multifaceted options for further definition offer tools to follow best practices in fundraising.

#### Columns 
These include typical display columns, as well as columns showing amounts for the Criteria Sets noted above.

1. **From Household:** The typical display columns will pull from the Household contact record, when there is one, and from the Individual contact record for Individuals without aggregating relationships. This is important to remember for fields like "Is Deceased," which will always show “no” for a Household, even if a _member_ of that Household is marked as deceased. 

1. **Sets further refined on Aggregate Column Tab:** When any of the Columns labeled Total, First, Last, or Largest are selected for display, the Aggregate Columns tab provides options to determine criteria for the values displayed in these Columns. This is explained in the section "Aggregate Column values," below. 

#### Filters
This report provides five special sets of options for Filters, and special handling for Tag and Group filters:

1.  **Apply "Total Contribution" filter:** Checking this filter option enables an additional collection of filter options to limit the report based on the **total amount** of contributions by each Aggregated Household. These include date, type, page, status, campaign, source, and qualifying amount. Use these fields to limit the contributions that should be totaled up before filtering. Use the last field, "Total contribution: total," to define  limiting criteria against which the totaled amount will be compared; Aggregated Households whose totaled amounts do not match this criteria will be excluded from the report output.  
&nbsp;  
**Example:** 
The following "Total Contribution" filter settings will limit the report to Aggregate Households who have given a total of at least $1,000 in completed contributions in the previous year:
  * Total contribution: qualifying date: Previous year
  * Total contribution: qualifying status: Completed
  * Total contribution: total: Is greater than or equal to: 1,000

1. **Apply "First Contribution" filter:"** Checking this filter option enables an additional collection of filter options to limit the report based on the **"first" contribution** by each Aggregated Household. The criteria here should be fairly self-explanatory. However, unlike the "Total Contribution" filter (above) and "Any Contribution" filter (below), this filter provides the special criteria **“First contribution" filter scope**, which can be used to define the meaning of "first" contribution, in three distinct ways.  
&nbsp;  
**Examples:**
For the examples below, different results are obtained with these filters:
  * First Contribution: qualifying date: “previous year”
  * First Contribution: qualifying campaign: “Annual Gala”
  * First Contribution: qualifying amount: “Is greater than or equal to $1,000”
  * And one of the following First contribution: filter scopes …

     1. **First contribution ever meets these criteria:** Limits results to Aggregated Households whose _first gift ever_ matches all the other criteria defined above in the first contribution set. 
&nbsp;
**Scope Example:** With three filters above, this scope will find all those whose first gift ever was \$1,000 or more and was made for the "Annual Gala" Campaign last year (but will exclude, for example, those who made a gift before last year, or whose first gift was not \$1,000 or more or for the Annual Gala.)

     2. **First contribution meeting these criteria was within this date range:** Limits results to Aggregated Households whose _first gift matching all the other criteria_ fell within the specified _date_ criteria.
&nbsp;
**Scope Example:** With three filters above, this scope will find all those whose first \$1,000 or larger Annual Gala gift was made last year (but will exclude those with a gift of \$1,000 or more for the same Campaign in a previous year.)

     3. **First contribution meeting these criteria was within this amount range:** Limits results to Aggregated Households whose _first gift matching all the other criteria_ fell within the specified _amount_ criteria. 
&nbsp;
**Scope Example:** With three filters above, this scope will find all those whose first gift for the Gala last year was \$1,000 or more (but will exclude, for example, those who sent a \$50 gift coded to the Annual Gala campaign last year to hold seats, then later last year made a \$1,000 gift at the Gala.)

     1. **Apply "Last Contribution" filter:** Checking this filter option enables an additional collection of filter options to limit the report based on a **"last" contribution** by each Aggregated Household. Options for defining “last” are comparable to defining "first" in the "First Contribution" filter.

     1. **Apply "Largest Contribution" filter set:** Checking this filter option enables an additional collection of filter options to limit the report based on a **"largest" contribution** by each Aggregated Household. Options for defining “largest” are comparable to defining "first" in the "First Contribution" filter.

     1. **Apply "Any Contribution" filter set:** Checking this filter option enables an additional collection of filter options to limit the report based on any single contribution by each Aggregated Household. As with the "Total Contribution" filter, there is no "filter scope" criteria in this filter's options.

##### Tags
The operators for the "Tags" filter have special meaning due to the way in which aggregation is performed:

1. **Is one of**: Will include an Aggregated Household only if _one or more_ contacts within an Aggregated Household have any of the specified Tags.

1. **Is not one of**: Will include an Aggregated Household only if _none_ of the contacts within an Aggregated Household have any of the selected Tags. If even one of the selected tags has been applied to even one of the Individual or Household contacts, the Aggregated Household is excluded.

##### Groups
Likewise, the operators for the "Groups" filter have special meaning due to the way in which aggregation is performed:

1. **Is one of**: Will include an Aggregated household only if _one or more_ contacts within an Aggregated Household are in any of the selected Groups.

1. **Is not one of**: Will include an Aggregated Household only if _none_ of the contacts within an Aggregated Household are in any of the selected Groups. If even one of the selected Groups contains even one of the Individual or Household contacts, the Aggregated Household is excluded.

#### Aggregate Columns criteria 
As described above, the Columns criteria include options to display typical CiviCRM report columns as well as calculated columns for dates and amounts for Total, First, Last and Largest contributions. When any of these calculated columns selected for display, the Aggregate Columns criteria will then provide options to further define rules for calculation these values.  The criteria options which may appear are these:

1. **"Total contribution" column** provides these options:
    1. **Total of all contributions ever** Selecting this option will cause the Total Contribution column to display the total of all contributions ever given by the Aggregated Household, without regard to any other criteria such as type or status (so it may include in-kind, fees, incomplete, refunded, cancelled, etc.)
    1. **Use custom settings** Selecting this option opens up further options to either:
        * **Copy settings from filter** to calculate display amount with the same criteria defined on the "Total Contribution" Filter. OR
       * Define different criteria to calculate the displayed amount, selecting from same options available in filters.

1. **"First contribution" column** provides the same options as described for **"Total Contribution" column**. You may configure these options to cause the "First Contribution Amount" column to display either:
    * The amount of the _first contribution ever_ by date, without regard to any other criteria, OR 
    * Use custom settings either copied from the "First Contribution" filter or in the criteria here. 
1. **"Last contribution" column** provides the same options as described for **"Total Contribution" column**. You may configure these options to cause the "Last Contribution Amount" column to display either:
    * The amount of the _last contribution ever_ by date, without regard to any other criteria, OR 
    * Use custom settings either copied from the "Last Contribution" filter or in the criteria here. 
1. **"Largest contribution" column** provides the same options as described for **"Total Contribution" column**. You may configure these options to cause the "Largest Contribution Amount" column to display either:
    * The amount of the _largest contribution ever_ by amount, without regard to any other criteria, OR 
    * Use custom settings either copied from the "Largest Contribution" filter or in the criteria here. 
