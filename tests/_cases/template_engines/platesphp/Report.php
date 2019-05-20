<?php

require_once dirname(__FILE__)."/../../../../autoload.php";


class Report extends \koolreport\KoolReport
{
    use \koolreport\clients\Bootstrap;

    use \koolreport\platesphp\Engine;
    protected function platesInit()
    {
        return League\Plates\Engine::create(dirname(__FILE__).'/templates');
    }


    protected function settings()
    {
        return array(
            "dataSources"=>array(
                "data"=>array(
                    "class"=>'\koolreport\datasources\CSVDataSource',
                    'filePath'=>dirname(__FILE__)."/data.csv",
                ),
            )
        );
    }

    protected function setup()
    {
        $this->src("data")->pipe($this->store("all"));
    }


}