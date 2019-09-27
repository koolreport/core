<?php

require_once "../../../../autoload.php";

use \koolreport\processes\JsonColumn;

$src = new \koolreport\datasources\ArrayDataSource([
    "data"=>[
        ["key"=>1,"value"=>'{"name":"Peter","age":35}'],
        ["key"=>1,"value"=>'{"name":"David","age":32}'],
    ]
]);

$store = $src->pipe(new JsonColumn(["value"]))
->pipe(new \koolreport\core\DataStore);

$store->requestDataSending();

\koolreport\widgets\koolphp\Table::create([
    "dataSource"=>$store,
    "columns"=>[
        "key",
        "value"=>[
            "formatValue"=>function($value)
            {
                return $value["age"];
            }
        ]
    ]
]);

var_dump([
    "a"=>12,
    "b"
]);
