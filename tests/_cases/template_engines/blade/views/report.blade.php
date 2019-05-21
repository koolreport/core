<?php
    use \koolreport\widgets\koolphp\Table;
?>
<html>
<head>
    <title>Welcome to blade!</title>
</head>
<body>
    <h1>Welcome to blade!</h1>
    <?php
        Table::create(array(
            "dataSource"=>$report->dataStore("all")
        ));
    ?>
</body>
</html>