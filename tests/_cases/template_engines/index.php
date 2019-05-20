<?php
require_once "platesphp/Report.php";

$report = new Report;
$report->run()->render("report");