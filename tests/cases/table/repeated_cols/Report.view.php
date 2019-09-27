<?php
    use \koolreport\widgets\koolphp\Table;
?>
<html>
    <head>
        <title>Test Repeating Cols</title>
    </head>
    <body>
        <h1>Test Repeated Cols and Json Data</h1>
        <?php
        Table::create([
            "dataSource"=>array(
                array("name"=>"Michael","address"=>["country"=>"US","state"=>"New york"]),
                array("name"=>"David","address"=>["country"=>"US","state"=>"New york"]),
                array("name"=>"Peter","address"=>["country"=>"US","state"=>"New york"]),
            ),
            "columns"=>array(
                "#",
                "name",
                ["name"],
                "address",
                [
                    "address",
                    "label"=>"Country",
                    "formatValue"=>function($value)
                    {
                        return $value["country"];
                    }
                ]
            )
        ]);
        ?>
    </body>
</html>