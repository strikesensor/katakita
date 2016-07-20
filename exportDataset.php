<?php
 error_reporting( E_ALL ); 
 ini_set('display_errors', 'on');
require_once 'functions.php';

//echo insertUnit(1);
$dataset_id = $_GET["dataset_id"];
$task_id = $_GET["task_id"];

$result = getTypeforDatasetId($dataset_id);
$type = $result["type"];

writeResultsToCSVText($dataset_id, null,$task_id, $type , "output/file.csv");
?>
<html>
<head></head>
<body>
<a href="output/file.csv"> Download results </a>
</body>