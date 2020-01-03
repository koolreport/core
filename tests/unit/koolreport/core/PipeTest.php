<?php
namespace koolreport\core;

use \koolreport\processes\Limit;
use \koolreport\processes\Group;
use \koolreport\processes\Sort;
use \koolreport\core\Utility;

class PipeTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;
    
    // tests
    public function testPipeIf()
    {
        $start = new Limit(array(0));
        
        $node = $start->pipeIf(
            true,
            function ($n) {
                return $n->pipe(new Group(array()));
            },
            function ($n) {
                return $n->pipe(new Sort(array()));
            }
        );
        $this->assertEquals(Utility::getClassName($node), "Group");


        $node = $start->pipeIf(
            false,
            function ($n) {
                return $n->pipe(new Group(array()));
            },
            function ($n) {
                return $n->pipe(new Sort(array()));
            }
        );
        $this->assertEquals(Utility::getClassName($node), "Sort");


        $node = $start->pipeIf(
            false,
            function ($n) {
                return $n->pipe(new Group(array()));
            }
        );
        $this->assertEquals(Utility::getClassName($node), "Limit");

    }

    public function testPipeTree()
    {
        $start = new Limit(array(0));
        $start->pipeTree(
            function ($node) {
                $node->pipe(new Group())->pipe(new DataStore);
            },
            function ($node) {
                $node->pipe(new Sort())->pipe(new DataStore);
            }
        );
    }
}