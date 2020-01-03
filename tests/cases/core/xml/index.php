<?php
require_once "../../../../autoload.php";
require_once "Report.php";
header("Content-type: text/xml");
$report = new Report;
echo $report->run()->getXml();