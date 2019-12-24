<?php

class ExistedPdoDataSource extends \koolreport\datasources\PdoDataSource
{
    protected function onInit()
    {
        $this->connection = Utility::get($this->params,"pdo");
    }
}