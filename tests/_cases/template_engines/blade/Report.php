<?php

require_once dirname(__FILE__)."/../../../../autoload.php";


class Report extends \koolreport\KoolReport
{
    use \koolreport\clients\Bootstrap;

    use \koolreport\blade\Engine;
    protected function bladeInit()
    {
        $views = __DIR__."/views";
        $cache = __DIR__."/cache";
        return new \Jenssegers\Blade\Blade($views, $cache);
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