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
$whs = $_GET['WHS'];
$db = $_GET['DB'];
$u = $_GET['U'];
 


 





if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $dareason1 = $_POST["reason1"];
    $dareason2 = $_POST["reason2"];
   
    $s = "Select * from oeordt where ODORD# = $ord and ODWH = $whs";
    $r = db2_exec($con, $s);
   // var_dump($s, db2_stmt_errormsg());
    while ($row = db2_fetch_assoc($r)) {
        $line = $row['ODORL#'];
        
        
            $text2 = 'Production Hold ' . date_create()->format('Y-m-d H:i:s') . ' By ' . $user;
            $s1 = "Call AddCmnt ($ord, $line, 'LOG', '$text2')";
            $r = db2_exec($con, $s1);
            var_dump($s1, db2_stmt_errormsg());
            
            if (trim($dareason1) !== '') {
                $s1 = "Call AddCmnt ($ord, 999, 'INT', '$dareason1')";
                $r = db2_exec($con, $s1);
                $s1 = "Call AddCmnt ($ord, 999, 'PKL', '$dareason1')";
                $r = db2_exec($con, $s1);
               // var_dump($s1, db2_stmt_errormsg());
            }
            if (trim($dareason2) !== '') {
                $s1 = "Call AddCmnt ($ord, 999, 'INT', '$dareason2')";
                $r = db2_exec($con, $s1);
                $s1 = "Call AddCmnt ($ord, 999, 'PKL', '$dareason2')";
                $r = db2_exec($con, $s1);
                // var_dump($s1, db2_stmt_errormsg());
            }
 
            
         
            
            
            $u2 = trim(strtoupper($u)) . '        ';
            $u3 = substr($u2,0,10);
            if (strlen(trim($turn)) == 7) $turn = '0' . $turn;
            $s = "call RESENDPL ('$turn', '$u3')";
            db2_exec($con, $s);
 
            
        
    }
    //   
    echo "<script>
window.close();
 window.opener.location.reload();
</script>";
}
$turn = 0;

 
    $s = "SELECT IDTURN FROM OEORDP LEFT JOIN OEORDT ON IDORD# =           
ODORD# AND IDORL# = ODORL# WHERE IDORD# = $ord AND ODWH = $whs ";
    $r = db2_exec($con, $s);
    $row = db2_fetch_assoc($r);
    $turn = $row['IDTURN'];
    
    $turnText = "<br>Packing List#: $turn";
 


echo "<h2>Add Packing List Comment for<br>
 Order: $ord<br>
 Warhehouse: $whs
$turnText</h2>";
$url = "getReason.php?DB=$db&ORD=$ord&WHS=$whs";
?>

<form method="post" action="<?php echo htmlspecialchars($url);?>">
	Reason 1: <input type="text" name="reason1" size="60"> 
	Reason 2:  <input type="text" name="reason1" size="60"> 
	<input type="hidden" name="turn" value="<?php echo $turn;?>"> 
	<input type="submit" name="submit" value="Submit">
</form>