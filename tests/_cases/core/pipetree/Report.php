<?php

use \koolreport\processes\Group;

class Report extends \koolreport\KoolReport
{
    public function settings()
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
        $this->src("data")
        ->pipeTree(
            function ($node) {
                $node->pipe($this->store("all"));
            },
            function ($node) {
                $node->pipe(new Group(array(
                    "by"=>"cat",
                    "avg"=>"price",
                )))->pipe($this->store("group"));                
            }
        );
    }
}