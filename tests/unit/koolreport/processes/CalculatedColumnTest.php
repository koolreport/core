<?php namespace koolreport\processes;

class CalculatedColumnTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    // tests
    public function testNullToZero()
    {
        $source = new \koolreport\datasources\ArrayDataSource;
        $source->load(
            array(
                array("amount"=>3,"quantity"=>null)
            )
        );
        $result = $source
        ->pipe(new \koolreport\processes\ColumnMeta(array(
            "amount"=>array("type"=>"number"),
            "quantity"=>array("type"=>"number"),
        )))
        ->pipe(new \koolreport\processes\CalculatedColumn(array(
            "total"=>"{amount}*{quantity}"
        )))->pipe(new \koolreport\core\DataStore);

        $result->requestDataSending();

        $this->assertEquals($result->get(0,"total"),0);
    }
}