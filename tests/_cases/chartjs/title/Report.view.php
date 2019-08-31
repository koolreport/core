<?php
    use \koolreport\chartjs\ColumnChart;
?>

<html>
    <head>
        <title>Test title of chartjs</title>
    </head>
    <body>
        <h1>Test title of chartjs</h1>
        <?php
        \koolreport\chartjs\ColumnChart::create([
            "title"=>"Abc",
            "options"=>[
                "title"=>[
                    "display"=>true,
                    "text"=>"Any text",
                    "top"=>"50px",
                    "fontFamily"=>"'Raleway', sans-serif",
                    "fontSize"=>21,
                ]
            ],
            "dataSource"=>[
                ["name"=>"Peter","age"=>35],
                ["name"=>"David","age"=>25],
            ],
        ]);
        ?>
    </body>
</html>