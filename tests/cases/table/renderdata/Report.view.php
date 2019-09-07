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
            "name"=>"myTable",
            "dataSource"=>$this->src("automaker")->query("select * from customers")
        ]);
        ?>
    </body>
</html>