<?php

class Report extends \koolreport\KoolReport
{
    function settings()
    {
        return array(
            "dataSources"=>array(
                "array"=>array(
                    "class"=>"/koolreport/datasources/ArrayDataSource",
                    "dataFormat"=>"associate",
                    "data"=>array(
                        array("name"=>"Peter","age"=>1),
                        array("name"=>"Peter","age"=>2),
                        array("name"=>"Michael","age"=>5),
                    )
                )
            )
        );
    }    
    function setup()
    {
        $this->src("array")->pipe($this->dataStore("result"));
        $this->src("array")->pipe($this->dataStore("abc"));
    }
}