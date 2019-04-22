<?php
require_once "../../../../autoload.php";
require_once "Report.php";

$report = new Report;
//$report->run()->render();
$report->run()->export()
->pdf(array(
    "format"=>"A4",
    "orientation"=>"portrait",
    //"zoom"=>2
))
->toBrowser("test.pdf");