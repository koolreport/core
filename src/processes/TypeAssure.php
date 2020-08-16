<?php

namespace koolreport\processes;

class TypeAssure extends \koolreport\core\Process
{
    protected function onInput($row)
    {
        foreach($row as $k=>$v) {
            if(gettype($v)==="string" && $this->metaData["columns"][$k]["type"]==="number") {
                $row[$k] = (float)$v;
            }
        }
        $this->next($row);
    }
} 