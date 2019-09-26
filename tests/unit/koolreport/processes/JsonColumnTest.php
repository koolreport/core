<?php namespace koolreport\processes;

class JsonColumnTest extends \Codeception\Test\Unit
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
    public function testConvertToArray()
    {
        $src = new \koolreport\datasources\ArrayDataSource([
            "data"=>[
                ["key"=>1,"value"=>'{"name":"Peter","age":35}'],
                ["key"=>1,"value"=>'{"name":"David","age":32}'],
            ]
        ]);

        $store = $src->pipe(new JsonColumn(["value"]))
        ->pipe(new \koolreport\core\DataStore);

        $store->requestDataSending();
        $this->assertEquals("Peter",$store[0]["value"]["name"]);
    }
}