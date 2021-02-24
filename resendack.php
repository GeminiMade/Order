<?php
error_reporting(E_ALL);
ini_set('details_errors', TRUE);
ini_set('details_startup_errors', TRUE);
ini_set('memory_limit', '-1');
ini_set('time_limit', '280');
ignore_user_abort();
date_default_timezone_set('America/Chicago');
include "../GConB.php";
$ord = $_GET['ORD'];
$u1 = $user . '          '; 
$u2 = substr($u1,0,10);

$s = "Call ResendAck1 ('$ord'. '$u2')";
db2_exec($con, $s);

var_dump($s, db2_stmt_errormsg());

//<script>window.opener.reload()</script>;
//<script>window.close()</script>;


?>
<script>window.opener.reload()</script>;
<script>window.close()</script>;