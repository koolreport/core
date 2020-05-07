<?php
    use \koolreport\widgets\koolphp\Table;

?>
<html>
    <head>
        <title>Test creating running total</title>
    </head>
    <body>
        <h1>Test Creating running total</h1>
        <p>Make the running total of subgroup as well as running total of grand total</p>
        <?php
        Table::create([
            "name"=>"myTable",
            "dataSource"=>$this->src("automaker")->query("
                select status, priceEach from orders
                join orderdetails on orderdetails.orderNumber = orders.orderNumber
                limit 2000
            "),
            "grouping"=>array(
                "status"=>array(
                    "calculate"=>array(
                        "{sumPrice}"=>array("sum","priceEach")
                    ),
                    "top"=>"<b>Status {status}</b>",
                    "bottom"=>"<b>Sum Price: {sumPrice}</b>",
                )
            ),
            "columns"=>array(
                "status","priceEach",
                array(
                    "label"=>"Running Category",
                    "value"=>function ($row) use (&$category,&$category_total) {
                        if ($row["status"]!=$category) {
                            $category = $row["status"];
                            $category_total = 0;
                        }
                        $category_total += $row["priceEach"];
                        return $category_total;
                    }
                ),
                array(
                    "label"=>"Running Total",
                    "value"=>function ($row) use (&$total) {
                        $total += $row["priceEach"];
                        return $total;
                    }
                )
            )
        ]);
        ?>
    </body>
</html>