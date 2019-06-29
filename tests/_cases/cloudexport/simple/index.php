<?php
require_once "../../../../autoload.php";
require_once "Report.php";

$report = new Report;
//$report->run()->render();
$report->run()->cloudExport()
->chromeHeadlessio("f4d94ae93b306412b7ac8c4e5809bb8a526772bdcdf72f7afefd812f19c8826a")
->pdf(array(
    "displayHeaderFooter"=>true,
    "headerTemplate"=>"<div style='font-size:14px !important'>Sale Report</div>",
    "footerTemplate"=>"<span class='pageNumber'  style='font-size:14px !important'></span>",
))
->toBrowser("test.pdf");