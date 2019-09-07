<?php
require_once "../../DbReport.php";

use \koolreport\processes\Join;

class Report extends \koolreport\KoolReport
{
    use \koolreport\clients\Bootstrap;

    protected $data;
    public function settings()
    {
        return array(
            "dataSources"=>array(
                "source1"=>array(
                    "class"=>'\koolreport\datasources\CSVDataSource',
                    'filePath'=>dirname(__FILE__)."/source1.csv",
                ),
                "source2"=>array(
                    "class"=>'\koolreport\datasources\CSVDataSource',
                    'filePath'=>dirname(__FILE__)."/source2.csv",
                )
            )
        );
    }   
    protected function setup()
    {
        
        $source1 = $this->src("source1");
        $source2 = $this->src("source2");
        $join = new Join($source1, $source2, array("id"=>"id"));
        $join->pipe($this->dataStore("data"));
    }
}