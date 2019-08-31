<?php
    use \koolreport\widgets\koolphp\Table;
?>
<div class="report-content">
    <div class="text-center">
        <h1>Table Footer Function</h1>
        <p class="lead">
            This test setting function for footer
        </p>
    </div>

    <h2>Now</h2>
    <?php
    Table::create(array(
        "dataSource"=>$this->dataStore("payments"),
        "showFooter"=>"bottom",
        "columns"=>array(
            "year",
            "amount"=>array(
                "footer"=>function($store) {
                    return number_format($store->sum("amount"));
                }
            )
        )
    ));
    ?>

    <style>
        .darker
        {
            background:#ccc;
        }
    </style>
</div>