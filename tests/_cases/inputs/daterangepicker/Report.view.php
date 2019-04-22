<?php
    use \koolreport\inputs\DateRangePicker;
?>

<html>
    <head>
        <title>DateRangePicker</title>
    </head>
    <body>
        <h1>DateRangePicker</h1>
        <p class="lead">
            Should display DateRangePicker
        </p>
        <div class="row">
            <div class="col-md-4">
                <h4>maxSpan</h4>
                <?php
                DateRangePicker::create(array(
                    "name"=>"dateRange",
                    "options"=>array(
                        "maxSpan"=>array(
                            "days"=>7
                        )
                    ),
                    "ranges"=>array()
                ));
                ?>         
            </div>
        </div>

    </body>
</html>