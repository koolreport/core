<?php
    use \koolreport\widgets\koolphp\Table;
?>
<html>
    <head>
        <title>Test group with removeDuplicate</title>
    </head>
    <body>
        <h1>Test group with removeDuplicate</h1>

        <?php
        Table::create(array(
            "dataSource"=>array(
                array("client"=>"aaa","item"=>"flip","price"=>999),
                array("client"=>"aaa","item"=>"flip","price"=>999),
                array("client"=>"aaa","item"=>"flip","price"=>999),
                array("client"=>"bbb","item"=>"flip","price"=>999),
                array("client"=>"bbb","item"=>"flip","price"=>999),
                array("client"=>"bbb","item"=>"flip","price"=>999),
                array("client"=>"ccc","item"=>"flip","price"=>999),
                array("client"=>"ccc","item"=>"flip","price"=>999),
                array("client"=>"ccc","item"=>"flip","price"=>999),
            ),
            "grouping"=>array(
                "client"=>array(
                    "top"=>"<b>Client {client}</b>",
                )
            ),
            "columns"=>array("item","price"),
            "removeDuplicate"=>array("item")
        ));
        ?>

    </body>
</html>