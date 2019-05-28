<?php
use \koolreport\d3\ColumnChart;
use \koolreport\d3\BarChart;
use \koolreport\d3\PieChart;
use \koolreport\d3\DonutChart;
$category_amount = array(
    array("category"=>"Books","sale"=>32000,"cost"=>20000,"profit"=>12000),
    array("category"=>"Accessories","sale"=>43000,"cost"=>36000,"profit"=>7000),
    array("category"=>"Phones","sale"=>54000,"cost"=>39000,"profit"=>15000),
    array("category"=>"Movies","sale"=>23000,"cost"=>18000,"profit"=>5000),
    array("category"=>"Others","sale"=>12000,"cost"=>6000,"profit"=>6000),
);
?>
<html>
    <head>
        <title>Test D3 Chart</title>
    </head>
    <body>
        <h1>Test D3 Chart</h1>
        <div id="test"></div>
        <?php
        ColumnChart::create(array(
            "dataStore"=>array(
                array("name"=>"Tuan","age"=>35,"salary"=>45),
                array("name"=>"Dong","age"=>20,"salary"=>50)
            ),
            "columns"=>array(
                "name"=>array(
                    "label"=>"Name",
                ),
                "age"=>array(
                    "label"=>"Age"
                ),
                "salary"=>array(
                    "label"=>"Salary"
                ),
            ),
            "options"=>array(
                "axis"=>array(
                    "y"=>array(
                        "tick"=>array(
                            "format"=>"function(){return d3.format('$,')}()"
                        )
                    )
                )
            )
        ));
        ?>

        <?php
        PieChart::create(array(
            "dataStore"=>$category_amount,
            "title"=>"Cost of Good Sold",
            "columns"=>array(
                "category",
                "cost"=>array(
                    "type"=>"number",
                    "prefix"=>"$",
                )
            ),
        ));
        ?>
    </body>
</html>