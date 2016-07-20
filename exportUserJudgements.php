<?php
 error_reporting( E_ALL ); 
 ini_set('display_errors', 'on');
require_once 'functions.php';

//echo insertUnit(1);

writeResultsToCSVByUser($_GET["user_id"], "output/file.csv");
?>
<html>
<head></head>
<body>
<a href="output/file.csv"> Download results </a>
</body>