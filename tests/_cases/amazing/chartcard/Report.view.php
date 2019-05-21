<?php
use \koolreport\amazing\ChartCard;
?>
<html>
    <head>
        <title>Test ChartCard</title>
    </head>
    <body>
        <h1>Test ChartCard</h1>
        <div class="row">
            <div class="col-md-3 offset-md-4">
                <?php
                ChartCard::create(array(
                    "title"=>"Member online",
                    "value"=>2000,
                    "format"=>array(
                        "value"=>array(
                            "prefix"=>"$"
                        )
                    ),
                    "chart"=>array(
                        "type"=>"area",
                        "dataSource"=>array(
                            array("month","amount"),
                            array("Jan",120),
                            array("Feb",80),
                            array("Mar",100),
                            array("Apr",110),
                            array("May",150),
                            array("Jun",130),
                            array("Jul",120),
                            array("Aug",80),
                            array("Sep",100),
                            array("Oct",110),
                            array("Nov",150),
                            array("Dec",130),
                        ),
                        "columns"=>array(
                            "month",
                            "amount"=>array(
                                "label"=>"Amount",
                                "prefix"=>"$",
                                "type"=>"number"
                            )
                        )
                    ),
                    "cssClass"=>array(
                        "card"=>"test-card",
                        "value"=>"test-value",
                        "icon"=>"icon-people",
                        "title"=>"test-title",
                    ),
                    "cssStyle"=>array(
                        "card"=>"test:1",
                        "value"=>"test:1",
                        "icon"=>"test:1",
                        "title"=>"test:1",
                    ),
                ));
                ?>            
            </div>
            <div class="col-md-3">
            <?php
                ChartCard::create(array(
                    "value"=>$this->src("automaker")->query("select sum(amount) from payments"),
                    "preset"=>"info",
                    "title"=>"Sale amount",
                    "chart"=>array(
                        "dataSource"=>array(
                            array("month","amount"),
                            array("Jan",120),
                            array("Feb",80),
                            array("Mar",100),
                            array("Apr",110),
                            array("May",150),
                            array("Jun",130),
                            array("Jul",120),
                            array("Aug",80),
                            array("Sep",100),
                            array("Oct",110),
                            array("Nov",150),
                            array("Dec",130),
                        ),
                    )
                ));
            ?>
            </div>
        </div>

    </body>
</html>