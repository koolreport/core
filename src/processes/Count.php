<?php

namespace koolreport\processes;

use \koolreport\core\Process;
use \koolreport\core\Utility;

class Count extends Process
{
    protected $counters = array();
    protected $funcs = array();
    /**
     * Handle on initiation
     *
     * @return null
     */
    protected function onInit()
    {
        if($this->params!==null) {
            foreach($this->params as $cKey=>$countFunc) {
                $this->counters[$cKey] = 0;
                $this->funcs[$cKey] = $countFunc;
            }
        }
        $this->counters["{{all}}"] = 0;
        $this->funcs["{{all}}"] = function($row) {
            return true;
        };
    }

    /**
     * Handle on meta received
     * 
     * @param array $metaData The meta data
     * 
     * @return array New meta data
     */
    protected function onMetaReceived($metaData)
    {
        $metaData = array(
            "columns"=>array()
        );
        if($this->params !== null) {
            $keys = array_keys($this->params);
            foreach($keys as $key) {
                $metaData["columns"][$key] = array(
                    "type"=>"number"
                );
            }
        }
        $metaData["columns"]["{{all}}"] = array(
            "type"=>"number"
        );
        return $metaData;
    }
    
    /**
     * Handle on input start
     * 
     * @return null
     */
    protected function onStartInput()
    {
        //Reset all counters
        foreach($this->counters as $k=>$v) {
            $this->counters[$k] = 0;
        }
    }
    /**
     * Handle on data input
     *
     * @param array $data The input data row
     *
     * @return null
     */
    protected function onInput($row)
    {
        foreach($this->funcs as $k=>$func) {
            if($func($row)) {
                $this->counters[$k]++;
            }
        }
    }
    /**
     * Handle on input end
     * 
     * @return null
     */
    protected function onInputEnd()
    {
        $this->next($this->counters);
    }
}