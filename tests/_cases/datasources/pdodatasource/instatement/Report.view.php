<?php
use \koolreport\widgets\koolphp\Table;
?>

<html>
    <head>
        <title>Test PdoDataSource</title>
    </head>
    <body>
        <h1>Test PdoDataSource</h1>
        <p>
            It should return well with IN operator of SQL,
            we try to bind array to the sql statement.
        </p>
        <?php
        Table::create(array(
            "dataSource"=>$this->src('automaker')->query("
                SELECT * from employees
                WHERE lastName in (:lastName)
            ")->params(array(
                ":lastName"=>array("Murphy","Firrelli")
            ))
        ));
        ?>
    </body>
</html>