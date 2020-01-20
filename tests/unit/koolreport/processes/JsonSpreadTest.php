<?php namespace koolreport\processes;

class JsonSpreadTest extends \Codeception\Test\Unit
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
    public function testEnterColumnName()
    {
        $src = new \koolreport\datasources\ArrayDataSource([
            "data"=>[
                ["key"=>1,"value"=>'{"name":"Peter","age":35}'],
                ["key"=>1,"value"=>'{"name":"David","age":32}'],
            ]
        ]);

        $store = $src->pipe(new JsonSpread(["value"]))
        ->pipe(new \koolreport\core\DataStore);

        $store->requestDataSending();
        $this->assertEquals("Peter",$store[0]["value.name"]);
        $this->assertEquals("32",$store[1]["value.age"]);
        $this->assertEquals("number",$store->meta()["columns"]["value.age"]["type"]);
        $this->assertEquals("string",$store->meta()["columns"]["value.name"]["type"]);
    }

    // tests
    public function testEnterDetailSubColumns()
    {
        $src = new \koolreport\datasources\ArrayDataSource([
            "data"=>[
                ["key"=>1,"value"=>'{"name":"Peter","age":35}'],
                ["key"=>1,"value"=>'{"name":"David","age":32}'],
            ]
        ]);

        $store = $src->pipe(new JsonSpread(["value"=>["age"]]))
        ->pipe(new \koolreport\core\DataStore);

        $store->requestDataSending();
        $this->assertEquals("32",$store[1]["value.age"]);
        $this->assertEquals("number",$store->meta()["columns"]["value.age"]["type"]);
        $this->assertArrayNotHasKey("value.name",$store[0]);
        $this->assertArrayNotHasKey("value.name",$store->meta()["columns"]);
    }

}