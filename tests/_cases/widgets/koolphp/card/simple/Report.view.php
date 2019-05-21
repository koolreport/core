<?php
    use \koolreport\widgets\koolphp\Card;
?>
<html>
    <head>
        <title>Test Card</title>
        
    </head>
    <body>
        <h1>Test Card</h1>

        <div class="row">
            <div class="col-md-3">
                <?php
                Card::create(array(
                    "value"=>1249,
                    "baseValue"=>1500,
                    "indicator"=>"different",
                    "format"=>array(
                        "value"=>array(
                            "prefix"=>"$"
                        ),
                        "indicator"=>array(
                            "suffix"=>"",
                        )
                    ),
                    "cssStyle"=>array(
                        "negative"=>"color:#ddd",
                        "positive"=>"color:#0f0",
                        "indicator"=>"font-size:16px",
                        "card"=>"min-height:300px;",
                        "value"=>"color:blue",
                        "title"=>"color:green",
                    ),
                    "cssClass"=>array(
                        "negative"=>"test-negative",
                        "positive"=>"test-positive",
                        "indicator"=>"test-indicator",
                        "card"=>"test-card",
                        "value"=>"test-value",
                        "title"=>"test-title",
                        "upIcon"=>"glyphicon glyphicon-heart",
                        "downIcon"=>"glyphicon glyphicon-remove"
                    ),
                    "title"=>"Sale Amount",
                    "indicatorTitle"=>"If you compare to previous value of {value}"
                ));
                ?>
            </div>
            <div class="col-md-3">
            <?php
            Card::create(array(
                "title"=>"Sale Amount",
                "value"=>$this->src("automaker")->query("select sum(amount) from payments"),
                "baseValue"=>$this->src("automaker")->query("select sum(amount) from payments"),
            ));
            ?>
            </div>
        </div>
    </body>
</html>