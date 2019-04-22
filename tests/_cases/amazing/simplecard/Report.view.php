<?php
use \koolreport\amazing\SimpleCard;
?>
<html>
    <head>
        <title>Test Amazing Card</title>
    </head>
    <body>
        <h1>Test Amazing Card</h1>
        <div class="row">
            <div class="col-md-3">
                <?php
                SimpleCard::create(array(
                    "value"=>2000,
                    "title"=>"income",
                    "format"=>array(
                        "value"=>array(
                            "prefix"=>"$"
                        )
                    ),
                    "cssClass"=>array(
                        "card"=>"test-card",
                        "value"=>"test-value",
                        "icon"=>"test-icon",
                        "title"=>"test-title"
                    ),
                    "cssStyle"=>array(
                        "card"=>"test:1",
                        "value"=>"test:1",
                        "icon"=>"test:1",
                        "title"=>"test:1"
                    ),
                ));
                ?>            
            </div>
        </div>

    </body>
</html>