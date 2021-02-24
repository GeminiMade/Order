<?php
// release 1.1.0 Test 
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
$ord = $_GET['ORD'];
$s = " SELECT * FROM oeefrc  
WHERE EFORD# = $ord order by EFSeq ";
$r = db2_exec($con, $s);
while ($row = db2_fetch_assoc($r)){
    $x['EFCUST'] = $row['EFCUST'];
    $x['EFCONT'] = $row['EFCONT'];
    $x['EFSEQ'] = $row['EFSEQ'];
    $x['EFDOCT'] = $row['EFDOCT'];
    $x['EFRNAM'] = trim($row['EFRNAM']);
     
    $dt = $row['EFDOCT'];
    $s1 = "Select LOGMSG, DATE(ts) as DTE, TIME(ts) as TME from oblog where DT = '$dt' and cust = $ord order by ts desc 
fetch first 1 row only";
    $r1 = db2_exec($con, $s1);
    $row1 = db2_fetch_assoc($r1);
    $email = trim($row['EFEMAL']);
    if ($dt == 'ACK') {
        $emlink = "<a href = '../resendack.php?DB=$db&ORD=$ord' target = '_blank'> $email </a>";
    } else {
        $emlink = $email;
    }
    $x['EFEMAL']  = $emlink . '<br>' . trim($row1['LOGMSG']) . '<br>' . $row1['DTE'] . ' ' . $row1['TME'];
         
    
    
    $data['root'][] = $x;
}
$data['success'] = true;
echo $_GET['callback'] . '(' . json_encode($data). ')';