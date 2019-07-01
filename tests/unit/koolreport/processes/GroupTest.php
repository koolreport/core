<?php 

namespace koolreport\processes;

class GroupTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;
    
    // tests
    public function testNormal()
    {
        $source = new \koolreport\datasources\ArrayDataSource(array(
            "data"=>array(
                array("category"=>"Car","amount"=>30,"area"=>"A1"),
                array("category"=>"Car","amount"=>12,"area"=>"B2"),
            )
        ));

        $store = $source->pipe(new Group([
            "by"=>"category",
            "sum"=>"amount"
        ]))
        ->pipe(new \koolreport\core\DataStore)
        ->requestDataSending();

        $rows = $store->toArray();

        $this->assertArraySubset(array(
            "category"=>"Car",
            "amount"=>42,
        ),$rows[0]);
    }

    public function testGroupFunction()
    {
        $source = new \koolreport\datasources\ArrayDataSource(array(
            "data"=>array(
                array("category"=>"Car","amount"=>30,"area"=>"A1"),
                array("category"=>"Car","amount"=>12,"area"=>"B2"),
            )
        ));

        $store = $source->pipe(new Group([
            "by"=>"category",
            "custom" => function ($row, $result, $c) {
                if ($c==1) {
                    return $result;
                }
                $result["area"] .= ",".$row["area"];
                return $result;
            }
        ]))
        ->pipe(new \koolreport\core\DataStore)
        ->requestDataSending();

        $rows = $store->toArray();

        $this->assertArraySubset(array(
            "category"=>"Car",
            "area"=>"A1,B2",
        ),$rows[0]);
    }
}