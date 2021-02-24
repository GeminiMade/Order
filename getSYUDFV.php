<?php
 
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT, GET, POST");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header("Content-Type: application/json");
error_reporting(E_ALL);
ini_set('details_errors', TRUE);
ini_set('details_startup_errors', TRUE);
ini_set('memory_limit', '-1');
ini_set('time_limit', '280');
ignore_user_abort();
date_default_timezone_set('America/Chicago');
include "../GConB.php";
$table = $_GET['TABLE'];
$field = $_GET['FIELD'];
$s = " SELECT * FROM syudfv WHERE UVFILN = '$table' and UVFLDN = '$field'
 ORDER BY UVSEQ# ";
$r = db2_exec($con, $s);
while ($row = db2_fetch_assoc($r)){
    $x['id'] = $row['UVFLDV'];
    $x['text'] = $row['UVFLDV'];
    $data['root'][] = $x;
}
$data['success'] = true;
echo $_GET['callback'] . '(' . json_encode($data). ')';