<?php

class MyReport extends \koolreport\KoolReport
{
    //Register cloud export service in your report
    use \koolreport\cloudexport\Exportable;
}