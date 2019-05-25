<?php

require_once dirname(__FILE__)."/../../../../autoload.php";


class Report extends \koolreport\KoolReport
{
    use \koolreport\clients\Bootstrap;

    use \koolreport\twig\Engine;
    protected function twigInit()
    {
        $loader = new \Twig\Loader\FilesystemLoader(dirname(__FILE__).'/views');
        $twig = new \Twig\Environment($loader);
        return $twig;
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