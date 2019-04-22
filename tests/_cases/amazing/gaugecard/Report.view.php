<?php
use \koolreport\amazing\GaugeCard;

?>
<html>
    <head>
        <title>Test GaugeCard</title>
    </head>
    <body>
        <h1>Test GaugeCard</h1>
        <div class="row">
            <div class="col-md-3">
                <?php
                GaugeCard::create(array(
                    "title"=>"income",
                    "value"=>2000,
                    "baseValue"=>3000,
                    "indicator"=>"percentComplete",
                    "preset"=>"info",
                    "format"=>array(
                        "value"=>array(
                            "prefix"=>"$"
                        )
                    ),
                    "gauge"=>array(
                        "animationSpeed"=>32
                    ),
                    "indicatorThreshold"=>50,
                    "cssClass"=>array(
                        "card"=>"test-card",
                        "value"=>"test-value",
                        "icon"=>"test-icon",
                        "title"=>"test-title",
                        //"nagative"=>"test-negative",
                        //"positive"=>"test-positive",
                    ),
                    "cssStyle"=>array(
                        "card"=>"test:1",
                        "value"=>"test:1",
                        "icon"=>"test:1",
                        "title"=>"test:1",
                        //"negative"=>"negative:1",
                        //"positive"=>"positive:1",
                    ),
                ));
                ?>            
            </div>
        </div>

    </body>
</html>