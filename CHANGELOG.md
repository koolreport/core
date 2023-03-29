# Change Log

## Version 6.1.0
1. Fix dynamic properties in PHP 8.2.
2. Fix mull meta column key in core/Table widget.
3. Add event "init", "drawing" and "drawed" to google chart
4. Add public `viewDir` property for KoolReport class to find view files from that directory.
5. Add public `renderingVariables` property for KoolReport class to use in view files.

## Version 6.0.1
1. Fix forced string cast of report's "assets" setting.

## Version 6.0.0

1. Improve Table's client function handleRemoveDuplicate for much faster rendering.
2. Add closeCursor to statement in PdoDataSource.
3. Fix the string function with null parameter in PHP8.1
4. Upgrade jquery to 3.5.0
5. Add `Shuffle` process to randomize data order.

## Version 5.6.2

1. Fix: MySQL, PostgreSQL, SQLServer data sources when binding array with more than 10 values.
2. Fix: bug with DataStore's offsetGet method.

## Version 5.6.1

1. Fix DataStore's generator method.
2. Fix DataStore's deprecated methods in PHP 8.1.

## Version 5.6.0

1. Added: `AssetManager` ability to load resources with absolute paths.
2. Improved: `PDODataSource`, `MySQLDataSource`, `PostgreSQLDataSource`, `SQLSRVDataSource`, `OracleDataSource` to be able to bind params with any orders, duplicated names, array params for WHERE IN (only `PDODataSource` supports this before).
3. Added: SQL-like `like` and `not like` operator for `Filter` process.
4. Add `useGenerator` method for report.
5. Add `charset` and `session_mode` properties for OracleDataSource.

## Version 5.5.0

1. Added: PDODataSource is able to execute other statement like update, delete with `execute()` method
2. Added: Forward process
3. Added: Multiple "role" => "annotation" columns for Google chart
4. Added: Hebrew language file for Table widget
5. Added: Alias `rowspan` and `groupCellsInColumns` for `removeDuplicate` of Koolphp Table widget
6. Added: make `removeDuplicate` work with both column names and column orders

## Version 5.1.0

1. Fixed: Calculate correctly the document root folder
2. Added: Add port option to PostgreSQLDataSource
3. Added: Ability to change version of Google Charts
4. Added: Ability to set language locale for Google Charts
5. Added: Ability to render empty sub report for later updating from client
6. Improved: Calculate correctly the document root folder
7. Added: port option to PostgreSQLDataSource
8. Fixed: uksort call in various datasources to work with PHP 8.x
9. Added: fetchData method to OracleDataSource, PostgreSQLDataSource, SQLSRVDataSource

## Version 5.0.1

1. Improved: `Filter` process to compare when string of number is compared to number, we will use number comparison.

## Version 5.0.0

1. Google Chart: Only redraw when chart is loaded.
2. `Count`: Adding new Count process to count rows with condition.
3. `DataStore`: Add method `getScalar()`
4. `Filter`: Adding operator `<>`
5. `TypeAssure`: New type conversion process

## Version 4.7.1

1. Fix version number of KoolReport to make sure new resource is generated correctly.

## Version 4.7.0

1. `Table`: Adding `avg` method to row group
2. `ResourceManager`: Enhance way to export asset folder and generate asset url
3. `Table`: Fix issue with rowClick activated when clicked on header
4. `Google Charts`: Fix error caused by empty options while using new version of Google Charts.

## Version 4.5.1

1. `ResourceManager`: Fix issue with load balancing

## Version 4.5.0

1. `DataStore`: Adding `distinct()` method to get the distinct values of a column
2. `KoolReport`: Adding `toArray()` method to get data in array from report
3. `KoolReport`: Adding `toJson()` method to get data in json from report
4. `KoolReport`: Adding `getXml()` method to return xml representing all report in xml
5. `ExistedPdoDataSource`: Adding this new datasource to use in case we have existing pdo connection instead of raw connection settings.
6. `JsonSpread`: New process to spread json string into multiple columns.

## Version 4.3.0

1. `PDODataSource`: Adding more options for PDO initiation
2. `Group`: Adding `custom` property to host an anonymous function for custom grouping
3. `Table`: Adding the missing `count` method
4. `DataStore`: Fix typos in toTableArray() function
5. `Table`: Fix the table footer position
6. `Table`: Column footer setting is now able to receive custom function
7. `PdoDataSource`: Remove the auto null to zero conversion
8. `CalculatedColumn`: Auto converse null to zero
9. `jQuery`: Upgrade to version 3.4.1
10. `FontAwesome`: Upgrade to 5.10.2
11. `Card`: Adding `href` to navigate.
12. `Table`:Fix rowspan issue when use grouping together with removeDuplicate
13. `DataStore`: Adding `insert()` method to insert row at any place.
14. `JsonColumn`: Adding JsonColumn process to turn column value into array
15. `ArrayColumn`: Adding array column type
16. `Table`: Adding custom column capability
17. `Utility`: Format array column type 

## Version 4.0.0

1. `Group`: Fix grouping issue
2. `Group`: Adding `caseSensitive` property to set whether grouping column should be grouped with case sensitive. Default value is `true`.
3. `Group`: Free memory on input end
4. `Join`: Fix the key generation
5. `Join`: Free memory on input end
6. `Card`: New widget to show information
7. `Timeline`: Fix the date time conversion to Javascript datetime
8. `MySQLDataSource`: Remove the auto-added parenthesis in IN operator
9. `SQLSRVDataSource`: Remove the auto-added parenthesis in IN operator
10. `PostgreSQLDataSource`: Remove the auto-added parenthesis in IN operator
11. `OracleDataSource`: Remove the auto-added parenthesis in IN operator
12. `Node`: Adding `pipeIf()` method to set condition to pipe.
13. `Node`: Adding `pipeTree()` method to pipe data from a node to series of nodes.
14. `KoolReport`: Adding `store()` that act like `dataSource()`
15. `KoolReport`: Capable of using template engine rather than default view of koolreport.
16. `DataStore` : Adding function `toTableArray()` to return data in table format
17. `Table`: Fix the issue of html special char in table
18. `autoload`: Able to load package class from both `koolreport/core/packages` and `koolreport` folder.
19. `MySQLDataSource`: Bind DataTables' server side params instead of escaping them
20. `SQRSRVDataSource`: Bind DataTables' server side params instead of escaping them
21. `PostgreSQLDataSource`: Bind DataTables' server side params instead of escaping them
22. `OracleDataSource`: Bind DataTables' server side params instead of escaping them
23. `Google Chart` : Make change in `prepareData()` that only non null floatval value is passed, leave null values alone (for case like LineChart's `interpolateNulls = true`)
24. `Map` : set `metaSent = false` when `is_nded = false`
25. `Transpose2` : Fix undefined column key for some rows when transposing

## Version 3.25.4

1. `DataStore`: Change `clone()` function name to `makeCopy()` to avoid reserved keywords `clone` in PHP 5.x
2. `Table`: Avoid duplication of `groupLevel()` function

## Version 3.25.3

1. `Table`: Fix Table warning in PHP 7.2+ when grouping is not set
2. `GoogleCharts`: Remove auto change pointer on select event

## Version 3.25.1

1. `Utility`: Fix the symbolic path

## Version 3.25.0

1. `Table`: Adding `css` and `cssStyle` options for group row.
2. `Table`: `"top"` and `"bottom"` template of row group now supports function
3. `Widget`: Fix the `standardizeDataSource()` issue when receiving data source by function
4. `KoolReport`: Convert code base to comply with PSR-2 standard
5. `DataStore`: Change output of `toJson()` method to return both data and meta data
6. `GoogleChart`: Fix the issue of inability to load multiple charts from different google chart package.
7. `KoolReport`: Avoid duplicate data sending when requestDataSending() and run() are both called.
8. `DataStore`: Adding method `clone()` to get a datastore cloned.
9. `Table`: Adding new property `sorting` to facilitate sorting on Table.

## Version 3.1.0

1. `ResourceManager`: Fix issue with publishing resources 
2. `Widget`: Fix the PHP5.4 incompatibility

## Version 3.0.0

1. `DateTimeFormat`: Deal with null value or wrong format of datetime
2. `Utility`: Deal with null value of datetime
3. `TimeBucket`: Avoid null date value
4. `DateTimeFormat`: Check null date value and do not convert if the datetime is in good shape
5. `Widget`: New loading method for Widget which will allow widget is able to load resource and initiate itself on-demand
6. `KoolReport`: Able to create event handler function in the report instead of using registerEvent() function.
7. `Widget`: Remove `registerResources()` and `renderResources()`
8. `Table`: Enhance the removeDuplicate feature, supporting paging and remove duplicate cell at the same time.
9.`Node`: Add function setEnded() in case we want to define a datastore with existed data and does not need data is piped to it.
10. `Widget`: Allow widget to initiate without creating a report to hold it, meaning you can freely create widget in your own application
11. `KoolReport`: Update the new way to calculate document root
12. `DataStore`: Allow DataStore to be used like an array, working with foreach.
13. `DataStore`: Add list of essential methods for array manipulation
14. `DataSource`: Adding static function `create()` to create a source without setting up a full report.
15. `CSVDataSource`: Allow datasource to convert string data to UTF8
16. `ResourceManager`: Enhance the way to public report assets folder
17. `Widget`: Adding `onReady` state to let user write custom function when widget is ready.
20. `KoolReport`: Adding client-side onDone() event to callback a function on all widgets are initiated.
21. `Gauge`: Update library library location
22. `ComboChart`: Adding `chartType` property for columns in `ComboChart` for setting the chart to display
23. `Widget`: Adding `themeBase` property to let theme define how widget to be rendered
24. `Widget`: Adding `withoutLoader` property to set where widget should render with or without KoolReport loader.
25. `Table`: Make pagination compatible with Bootstrap4
26. `Widget`: Adding `themeCssClass` property to let theme controls appearance of widget
27. `Widget`: Cover widgets in custom tag to increase client-side accessibility.
28. `Table`: Add new feature `Row Grouping` which allows multi-levels row group in Table.

## Version 2.78.0

1. Utility: Fix jsonEncode() to work with array contains javascript function
2. DataSource: Improve the parameter escape string
3. DataSource: Make `MySQLDataSource`, `PostgreSQLDateaSource`, `SQLSRVDataSource`, `OracleDataSource` share database connection to reduce response time and memory.
4. GoogleChart: When you user select item on chart, the selectedRow now can contain associate value beside the array of values.
5. Table: Return both array and associate rowData on the rowClick event.

## Version 2.75.0

1. Table: Add `responsive` property to Table widget
2. GoogleChart: Add `formatValue` to column so that user can do custom value format.
3. GoogleChart: Make width of GoogleChart default 100%
4. SubReport: Enhance the partial render
5. PdoDataSource: Fix issue with Oracle
6. KoolReport: Reduce reload settings() by saving to $reportSettings
7. KoolReport: src() will take the first datasource if name is not specify 
8. Utility: Add advanced jsonEncode() function to enable js function definition inside php array.
9. Adding version factor to KoolReport as well as its widget so that everytime we upgrade core library as well as package, the widget is able to create new assets folder with updated resource
10. Fix several minor bug
11. Adding `DifferenceColumn` process to calculate the difference between row and the previous one.
12. Adding `AppendRow` process to add custom row to data flow.

## Version 2.43.0

1. Google Chart:Fix issue with GoogleChart when working with numeric column but in string form.


## Version 2.42.0

1. Change namespace `::class` to use classname in string so that `KoolReport` is compatible with PHP 5.4
2. `PdoDataSource`: Fix `charset` issue with `PostgreSQL`

## Version 2.41.3

1. Adding `AccumulativeColumn` to generate accumulative column.
2. Fix Group process for not counting records correctly.
3. Enhance the `autoload` of KoolReport
4. A bundle of small fixes

## Version 2.32.8

1. Revert back to previous param binding of PDODataSource


## Version 2.31.7

1. Fix the bug of incorrect active report when subReport is used.
2. Widget: Adding default `dataSource` and backward `dataStore` property.
3. Widget: Able to set dataStore object, array data and even the adhoc process.
4. Table: Remove the `data` property and start using the `dataSource`
5. GoogleCharts: Remove `data` property and start using `dataSource` instead
6. DataStore: Adding `requestDataSending()` to manually request data piping from source.
7. Adding events `OnBeforeResourceAttached` and `OnResourceAttached`
8. Table: Add ability to set multilevel group headers.
9. PdoDataSource: Adding SQL Query error handling
10. CopyColumn: Change input format from `"orginal"=>"copy"` to `"copy"=>"orginal"`
11. DataStore: Function process() can accept a series of piping processes.
12. GoogleCharts: Add property `pointerOnHover` to set whether pointer will be used when user holds mouse over the item of chart.
13: GoogleCharts: Automatic set `pointerOnHover=>true` if there is `"itemSelect"` event is registered.
14. Table: Change "goPage" event to "pageChanged" event
15. Added `ColumnRename` process to rename column
16. Process: Adding static function `process()`


## Version 2.0.0

1. DataStore: Add process() function to further process data
2. PdoDataSource: Update the bindParams() function.
3. Table: Handle the case when there is no data
4. Table: Show custom messages
5. Widget: Able to load language/localization
6. PdoDataSource, MySQLDataSource, SQLSRVDataSource: Update parameter binding.
7. Add ability to contain sub report, supporting partial report rendering.
8. Widget: Enhance the template() function
9. Google Charts: Rewrite library to support ajax loading and work well with SubReport
10. Table: Support ajax loading.
11. Table: Adding client event handler capability.

## Version 1.72.8
1. DataStore: Fix the `get()` function
2. TimeBucket: Change month bucket format from `Y-n` to `Y-m` to support sorting.
3. DataStore: Add $offset parameter to the top() function.
4. DataStore: Add function min(), max(), sum(), avg()
5. Make KoolReport run debug() if could not find the view file.
6. Filter: Add `"in"` and  `"notIn"` operator 
7. DataStore::filter() Add "startWith","notStartWith", "endWith" and "notEndWith"
8. CalculatedColumn: Add row number column with key `{#}`
9. Table: New feature of pagination

## Version 1.61.5

1. Fix parameters bug in PDODataSource
2. Fix parameters bug in MySQLDataSource
3. Fix parameters bug in SQLSRVDataSource

## Version 1.61.2

1. Add html() method to Widget to allow return html of widget
2. Add $return to create() method of Widget to return html instead of echo html.
2. Add innerView() to KoolReport to allow rendering sub view
3. Add function get() to DataStore to get any value in table
4. Make Transpose process take the label as data if the label of column is available.
4. Fix the isEnd() function of Node
5. Fix Group to allow multiple sources to pipe to group process.
6. Return content on event `"OnRenderEnd"`
7. Allow cancel rendering using `"OnBeforeRender"` event
8. Add previous() function to Node for navigation
9. Fix the ProcessGroup to enable transferring data smoothly.
10. ResourceManager now will fire the OnResourceInit event
11. Table has `data` property to input data on fly.
12. Google chart has `data` property to input data on fly.
13. Add `filter()` function to DataStore to filter data base on condition.
14. Add `top()` and `topByPercent()` function to DataStore to get the top rows
15. Add `bottom()` and `bottomByPercent()` function to DataStore to get the bottom rows
16. Add `sort()` function to DataStore to get data sorted.
17. Add `"footer"=>"count"` to `Table` column settings.

## Version 1.47.3

1. New `AggregatedColumn` process
2. Table is now able to show footer
3. Make footer show on top with `showFooter` property
4. Add `footerText` property
5. Add `showHeader` to `Table` widget
6. Ability to set `cssStyle` for each columns. `cssStyle` can be string or array containing components `td`,`th` and `tf`.
7. Improve `DataSource` class
8. New process `Transpose` to tranpose column and row of data flow
9. Fix double quote issue of `PDODataSource`
10. The Node now has getReport() function which return the report.
11.Fix the Timeline google charts
12. Fix the Group process by removing the space in column name 
13. Add params() function to MySQLDataSource and MSSQLDataSource 

## Version 1.34.9

1. Fixed Fix Google Chart due to change in core library.
2. Add load() function for ArrayDataSource.
3. Add `formatValue` column settings in `\koolreport\widgets\koolphp\Table`.

## Version 1.32.8

1. Enhancment Table has align property for columns
2. Enhancment Adding event OnInit and OnInitDone to KoolReport
3. Enhancment Adding event OnBeforeSetup and OnSetup to KoolReport
4. Enhancment Adding function params() in PDODataSource to set params for SQL statement.
5. Enhancment Adding process Map which is versatile way to transform data.
6. Fixed Solve issue of empty data source given to table.
7. Fixed Solve the bug of missing row in ColumnsSort process.

## Version 1.27.6

1. Enhancment Move the ExcelDataSource from the core to separate package to reduce the size of core.
2. Enhancment Move MongoDataSource to separate package as well.
3. Enhancment Add namespace 'clients' to contain most common used clients library such as jQuery, Bootstrap
4. Enhancment Adding the colorScheme to Koolreport to manane color of charts and others.
5. Enhancment We now can create theme for KoolReport
6. Enhancment Enhance the Widget Asset Manager
7. Enhancment Add functionpublishAssetFolder() to KoolReport.
8. Enhancment Add the MySQLDataSource
9. Enhancment Add the SQLSRVDataSource
10. Enhancment Add ColumnsSort process to sort columns by name of label.
11. Enhancment The Sort process now can sort by custom comparison function.
12. Enhancment Add function debug() in KoolReport, this function will display all available data stores.
13. Fixed Fix Google Chart initiation bug,this bug is actually due to the change from Google library.

## Version 1.15.4

1. Enhancment Add ResourceManager to manage report's resources such as js, css and any others.
2. Enhancment Improve the loading of Google Charts library with new ResourceManager to avoid loading redundancy.
3. Enhancment Add event register and event handling mechanism.
4. Enhancment Add OnBeforeRun,OnRunEnd,OnBeforeRender,OnRenderEnd event.
5. Enhancment Allow to set full path for report's assets folder settings

## Version 1.11.4

1. Enhancment Add ReportDataSource to pull data from another report
2. Enhancment Allow to set "role" to columns for google chart
3. Enhancment Filter process is now allowed or operator
4. Enhancment Allow ValueMap to set custom function
5. Enhancment Make Google Charts responsive to the change of screen size
6. Enhancment Add saveTo() function to Node class
7. Enhancment Enhance the mechanism of google chart library loader
8. Enhancment Koolphp Table can remove duplicated value
9. Enhancment Add popStart() and pop() function to DataStore class which helps to reduce memory usage
10. Enhancment Enhance CSVDataSource and ExcelDataSource to reduce memory usage
11. Enhancment Allow CalculatedColumn to add custom function and set meta data on the fly.
12. Enhancment Make removeDuplicate of koolphp\Table be list of columns you want to remove duplicated data.
13. Fixed Fix autoload.php bug in loading packages
14. Fixed Fix OnlyColumn bug
15. Fixed Fix koolphp's Table bug when column header is number
16. Fixed Fix google charts duplicated chart id problem

## Version 1.0.0

1. Establish middle-ware structure of KoolReport
2. Build the most common datasource connectors
3. Build the most common data processes
4. Create PHP wrapper for Googe Charts library

## First brick

1. It was a nice day!
2. Paper and pencil
3. Two guys
4. In a garden