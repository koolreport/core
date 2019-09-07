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
                array("client"=>"aaa","item"=>"flip","price"=>999,"hehe"=>1),
                array("client"=>"aaa","item"=>"flip","price"=>999,"hehe"=>1),
                array("client"=>"aaa","item"=>"flip","price"=>888,"hehe"=>1),
                array("client"=>"aaa","item"=>"flip","price"=>888,"hehe"=>1),
                array("client"=>"aaa","item"=>"flip","price"=>999,"hehe"=>1),
                array("client"=>"bbb","item"=>"flip","price"=>888,"hehe"=>1),
                array("client"=>"bbb","item"=>"flip","price"=>888,"hehe"=>1),
                array("client"=>"bbb","item"=>"flip","price"=>999,"hehe"=>1),
                array("client"=>"ccc","item"=>"flip","price"=>888,"hehe"=>1),
                array("client"=>"ccc","item"=>"flip","price"=>888,"hehe"=>1),
                array("client"=>"ccc","item"=>"flip","price"=>999,"hehe"=>1),
            ),
            "grouping"=>array(
                "client"=>array(
                    "top"=>"<b>Client {client}</b>",
                ),
                "item"=>array(
                    "top"=>"<b>Item {item}</b>",
                )
            ),
            "columns"=>array("price","hehe"),
            "removeDuplicate"=>array("price")
        ));
        ?>

    </body>
</html>