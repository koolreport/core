<?php
    use \koolreport\widgets\google\ColumnChart;
    use \koolreport\widgets\google\BarChart;
    use \koolreport\widgets\google\Gauge;
    use \koolreport\widgets\google\Timeline;
?>
<html>
    <head>
        <title>Test Trendlines</title>
    </head>
    <body>
        <h1>Test trendlines</h1>
        <p class="lead">
            Should show trendlines
        </p>
        <?php
        ColumnChart::create(array(
            "dataSource"=>array(
                array("cat","amount"),
                array(1,3),
                array(2,8),
                array(3,10),
                array(4,15),
                array(5,18),
            ),
            "columns"=>array("cat","amount"),
            "options"=>array(
                "trendlines"=> array(
                    0=>array()
                )
            )
        ));
        ?>
    </body>
</html>