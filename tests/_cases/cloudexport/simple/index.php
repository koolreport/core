<?php
require_once "../../../../autoload.php";
require_once "Report.php";

$report = new Report;
//$report->run()->render();
$report->run()->cloudExport()
->chromeHeadlessio("b272431244d5c8d0061f6f30da451c3f99337348b92bbab2cd6f34423d0dd43a")
->pdf(array(
    "displayHeaderFooter"=>true,
    "headerTemplate"=>"<div style='font-size:14px !important'>Sale Report</div>",
    "footerTemplate"=>"<span class='pageNumber'  style='font-size:14px !important'></span>",
))
->toBrowser("test.pdf");