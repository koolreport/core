<?php
    use \koolreport\widgets\koolphp\Table;
?>
<html>
    <head>
        <title>Test Rendering Data At Client</title>
    </head>
    <body>
        <h1>Test Rendering Data At Client</h1>
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
                [
                    "label"=>"Country",
                    "value"=>function($row)
                    {
                        return $row["address"]["country"];
                    }
                ]
            )
        ]);
        ?>
    </body>
</html>