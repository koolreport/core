<?php namespace koolreport;

class KoolReportTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;
    
    protected function _before()
    {
    }

    protected function _after()
    {
    }

    // tests
    public function testGetArray()
    {
        $data = array(
            array("name"=>"Peter","age"=>1),
            array("name"=>"Peter","age"=>2),
            array("name"=>"Michael","age"=>5),
        );
        $report = new class extends \koolreport\KoolReport
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
            }
        };
        $this->assertEquals(
            array(
                "result"=> $data
            ),
            $report->run()->toArray()
        );
    }
}