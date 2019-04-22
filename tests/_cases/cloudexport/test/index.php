<?php
require_once "../../../../autoload.php";
require_once "MyReport.php";

$report = new MyReport;
$report->run()
->cloudExport("MyReportPDF")
->chromeHeadlessio("b272431244d5c8d0061f6f30da451c3f99337348b92bbab2cd6f34423d0dd43a")
->pdf()
->toBrowser("myreport.pdf");