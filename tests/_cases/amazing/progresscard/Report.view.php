<?php
use \koolreport\amazing\ProgressCard;
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
                ProgressCard::create(array(
                    "title"=>"Member online",
                    "preset"=>"primary",
                    "value"=>2000,
                    "baseValue"=>3000,
                    "infoText"=>"This is chart from KoolReport",
                    "format"=>array(
                        "value"=>array(
                            "prefix"=>"$"
                        )
                    ),
                    "cssClass"=>array(
                        "icon"=>"icon-people"
                    )
                ));
                ?>
            </div>
        </div>

    </body>
</html>