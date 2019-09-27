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
                array("name"=>"Michael","2"=>"abc"),
                array("name"=>"David","2"=>"abc"),
                array("name"=>"Peter","2"=>"abc"),
            ),
            "columns"=>array(
                "#",
                "name",
                ["name"],
                [
                    "name",
                    "label"=>"Calculated",
                    "value"=>function($row) {
                        return 100000;
                    },
                    "type"=>"number",
                    "formatValue"=>function($value ){}
                ]
            )
        ]);
        ?>
    </body>
</html>