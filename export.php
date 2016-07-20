<?php
 error_reporting( E_ALL ); 
 ini_set('display_errors', 'on');
require_once 'functions.php';

$datasets=getAllDatasetTasks();

?>
<html>
<head></head>
<body>
<?php 
foreach($datasets as $ds)
{
$dataset_id = $ds["dataset_id"];
$dataset_name = $ds["dataset_name"];
$dataset_type = $ds["dataset_type"];
$task_id = $ds["task_id"];
$task_name = $ds["task_name"];

echo "<a href='exportDataset.php?dataset_id=$dataset_id&task_id=$task_id'>$dataset_name $task_name ($dataset_type) </a><br>";
}

?>
</body>