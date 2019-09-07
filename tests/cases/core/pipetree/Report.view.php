<?php
use \koolreport\widgets\koolphp\Table;
?>
<html>
    <head>
        <title>Test pipeTree</title>
    </head>
    <body>
        <h1>Test pipeTree</h1>
        <h4>All</h4>
        <?php
        Table::create(array(
            "dataSource"=>$this->store("all")
        ));
        ?>
        <h4>Group</h4>
        <?php
        Table::create(array(
            "dataSource"=>$this->store("group")
        ));
        ?>
    </body>
</html>