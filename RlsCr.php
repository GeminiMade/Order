<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT, GET, POST");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header("Content-Type: application/json");

ini_set('memory_limit', '-1');
ini_set('time_limit', '280');
ignore_user_abort();
date_default_timezone_set('America/Chicago');
include "../GConC.php";
$ord = $_GET['ORD'];
if (strlen(trim($ord)) == 7) $ord = '0' . $ord;
$usr = $user . '        ';
$usr = substr($usr,0,10);

$s = "CALL RLSCRHLD ('$ord','$usr') ";
$r = db2_exec($con, $s);
//var_dump($s, db2_stmt_errormsg());
$msg = db2_stmt_errormsg();
if (trim($msg) !== ''){
    $data['failure'] = true; 
    $data['MSG']  = $msg;
} else {
    $data['success'] = true;
}

echo json_encode($data);
