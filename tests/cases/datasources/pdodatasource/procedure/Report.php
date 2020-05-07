<?php

class Report extends DbReport
{
    function setup()
    {
        $this->src("automaker")->query("
            DROP PROCEDURE IF EXISTS GetEmployees;
            CREATE PROCEDURE GetEmployees()
            BEGIN
                UPDATE offices SET city='San' WHERE officeCode=1;
                SELECT *  FROM offices;
            END;
        ");
    }
}
