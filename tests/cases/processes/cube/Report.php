<?php
require_once "../../DbReport.php";

use \koolreport\processes\Group;
use \koolreport\cube\processes\Cube;

class Report extends \koolreport\KoolReport
{
    use \koolreport\clients\Bootstrap;

    protected $data;
    public function settings()
    {
        return array(
            "dataSources"=>array(
                "data"=>array(
                    "class"=>'\koolreport\datasources\CSVDataSource',
                    'filePath'=>dirname(__FILE__)."/data.csv",
                )
            )
        );
    }   
    protected function setup()
    {
        $this->data = array("a","b","c");
        $this->src("data")
        ->pipe(new Cube())
        ->pipe(Group::process(array(
            "by"=>array("cat","sub"),
            "sum"=>"price",
            "caseSensitive"=>false
        )))
        ->pipe($this->dataStore("data"));
    }
}