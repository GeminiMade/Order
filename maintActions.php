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

if (isset($_GET['Action'])){
    if (trim($_GET['Action']) == 'DIP'){
        $turn = $_GET['TURN'];
        
        
        
        $s = "delete from oeorhp where ihturn = $turn and IHord# = $ord";
        $r = db2_exec($con, $s);
        var_dump($s, db2_stmt_errormsg());
        $s = "delete from oeordp where idturn = $turn and Idord# = $ord";
        $r = db2_exec($con, $s);
        var_dump($s, db2_stmt_errormsg());
    }
    
    if (trim($_GET['Action']) == 'RPPACK'){    // REPRINT PACKING LIST
        $ord = $_GET['ORD'];
        $turn = $_GET['Turn'];
        if (strlen(trim($ord)) == 7) $ord = '0' . $ord;
        $tlen = strlen(trim($turn));
        if ($tlen == 7) $turn = '00' . $turn;
        if ($tlen == 8) $turn = '0' . $turn;
        $s = "call resendpic ('$ord', '$turn', 'N')";
        db2_exec($con, $s);
        var_dump($s, db2_stmt_errormsg());
        
    }
    if (trim($_GET['Action']) == 'PPACK'){     // PRINT PACKING LIST
        $ord = $_GET['ORD'];
        if (strlen(trim($ord)) == 7) $ord = '0' . $ord;
        $s = "call setPick ('$ord')";
        db2_exec($con, $s);
        var_dump($s, db2_stmt_errormsg());
        // this is a submitted job so lets delay the reload for x seconds
        
        
        sleep(8);
    }
    if (trim($_GET['Action']) == 'CRTPKL'){     // Create Pick Ticket
        
        $ord = $_GET['ORD'];
        $whs = $_GET['WHS'];
        $u = $_GET['U'];
        $u2 = trim(strtoupper($u)) . '        ';
        $u3 = substr($u2,0,10);
        if (strlen(trim($ord)) == 7) $ord = '0' . $ord;
        $s = "call CRTPKL ('$ord', '$whs', '$u3')";
        db2_exec($con, $s);
        var_dump($s, db2_stmt_errormsg());
        sleep(2);
    }
    if (trim($_GET['Action']) == 'PRTPKL'){     // Print PACKING LIST
        $pkl = $_GET['PKL'];
        $u = $_GET['U'];
        $u2 = trim(strtoupper($u)) . '        ';
        $u3 = substr($u2,0,10);
        if (strlen(trim($pkl)) == 7) $pkl = '0' . $pkl;
        $s = "call RESENDPL ('$pkl', '$u3')";
        db2_exec($con, $s);
        var_dump($s, db2_stmt_errormsg());
        // sleep(2);
    }
    
    
    if (trim($_GET['Action']) == 'DPK'){   // Remove a packing list
        $pkl = $_GET['PKL'];
        $s = "Update oeordt set ODPKL# = 0 where ODPKL# = $pkl and ODORD# = $ord with NC";
        db2_exec($con, $s);
        var_dump($s, db2_stmt_errormsg());
        $s = "Delete from OEPKLH where SDPKL# = $pkl with NC";
        db2_exec($con, $s);
        var_dump($s, db2_stmt_errormsg());
        $s = "Delete from OEPKLD where PLPKL# = $pkl and PLORD# = $ord with NC";
        db2_exec($con, $s);
        var_dump($s, db2_stmt_errormsg());
        $s = "Update oeorhp set IHPKL# = 0 where IHPKL# = $pkl and IHORD# = $ord with NC";
        db2_exec($con, $s);
        var_dump($s, db2_stmt_errormsg());
        $s = "DELETE FROM SHIPHD WHERE CHPKL# = $pkl and CHORD# = $ord with NC";
        db2_exec($con, $s);
      
      
        var_dump($s, db2_stmt_errormsg());
    }
    
    if (trim($_GET['Action']) == 'HOLD'){   // Prod Hold
        $whs = $_GET['WHS'];
        
        $reason = '';
        echo "<script>myFunction($reason)</script>";
        Echo"Reason:  $reason";
        
        
        $s = "Select * from oeordt where ODORD# = $ord and ODWH = $whs";
        $r = db2_exec($con, $s);
        var_dump($s, db2_stmt_errormsg());
        while($row = db2_fetch_assoc($r)){
            $line = $row['ODORL#'];
            $s1 = "merge into oeoudt as s using table(values($ord, $line, 'DHOLD', 'PROD')) as T
            (OUORD, OULINE, OUFLDN, OUFLDV)
            on s.ouord = t.ouord and s.ouline = t.ouline and s.oufldn = t.oufldn
            when matched then update set oufldv = t.oufldv
            when not matched then insert (ouord, ouline, oufldn, oufldv, oufldd) 
            values(t.ouord, t.ouline, t.oufldn, t.oufldv, current date) with NC";
            db2_exec($con, $s1);
            var_dump($s1, db2_stmt_errormsg());
        }
       
        
    }
    if (trim($_GET['Action']) == 'HOLDREL'){   // Release Prod Hold
        $whs = $_GET['WHS'];
     
        $s = "Select * from oeordt where ODORD# = $ord and ODWH = $whs";
        $r = db2_exec($con, $s);
        while($row = db2_fetch_assoc($r)){
            $line = $row['ODORL#'];
            $s1 = "Update OEOUDT set OUFLDV = ' ' where OUFLDN ='DHOLD' and
            OUORD = $ord and OULINE = $line WITH NC";
            db2_exec($con, $s1);
            var_dump($s1, db2_stmt_errormsg());
        }
    }
    
}
?>
<script>
// window.close();
window.opener.location.reload();
 
function myFunction(reason) {
  var reason = prompt("Reason for placing on Production Hold", "Production Hold");
 
}
</script>