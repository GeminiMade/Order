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
$ord = $_GET['ORD'];

if (isset($_GET['Action'])){
    if (trim($_GET['Action']) == 'DIP'){
        $turn = $_GET['Turn'];
   
        
        
        $s = "delete from oeorhp where ihturn = $turn and IHord# = $ord";
        $r = db2_exec($con, $s);
        $s = "delete from oeordp where idturn = $turn and Idord# = $ord";
        $r = db2_exec($con, $s);
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
        $data['XS'] = $s;
        $data['XSM'] = db2_stmt_errormsg();
        
    }
    if (trim($_GET['Action']) == 'PPACK'){     // PRINT PACKING LIST
        $ord = $_GET['ORD'];
        if (strlen(trim($ord)) == 7) $ord = '0' . $ord; 
        $s = "call setPick ('$ord')";
        db2_exec($con, $s);
        $data['XS'] = $s;
        $data['XSM'] = db2_stmt_errormsg();
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
        $data['XS'] = $s;
        $data['XSM'] = db2_stmt_errormsg();
         sleep(2);
    }
    if (trim($_GET['Action']) == 'PRTPKL'){     // Create PACKING LIST
        $pkl = $_GET['PKL'];
        $u = $_GET['U'];
        $u2 = trim(strtoupper($u)) . '        ';
        $u3 = substr($u2,0,10);
        if (strlen(trim($pkl)) == 7) $pkl = '0' . $pkl;
        $s = "call RESENDPL ('$pkl', '$u3')";
        db2_exec($con, $s);
        $data['XS'] = $s;
        $data['XSM'] = db2_stmt_errormsg();
        // sleep(2);
    }
    
    
    if (trim($_GET['Action']) == 'DPK'){   // Remove a packing list
        $pkl = $_GET['PKL'];
        $s = "Update oeordt set ODPKL# = 0 where ODPKL# = $pkl and ODORD# = $ord with NC";
        db2_exec($con, $s);
         
        $s = "Delete from OEPKLH where SDPKL# = $pkl with NC";
        db2_exec($con, $s);
        
        $s = "Delete from OEPKLD where PKPKL# = $pkl and PLORD# = $ord with NC";
        db2_exec($con, $s);
        
        $s = "Update oeorhp set IHPKL# = 0 where IHPKL# = $pkl and IHORD# = $ord with NC";
        db2_exec($con, $s);
        
        $s = "DELETE FROM SHIPHD WHERE CHPKL# = $pkl and CHORD# = $ord with NC";
        db2_exec($con, $s);
        $data['SHIPHD'] = $s;
        $data['SHIPHDM'] = db2_stmt_errormsg();
        
    }

    
}

if (isset($_GET['records'])){
    $recs1 = json_decode($_GET['records']);
    $rec2 = json_decode(json_encode($recs1), true);
    $ord = $rec2['ODORD'];
    $whs = $rec2['ODWH']; 
    $bd = $rec2['BD'];
    $line = $rec2['LINE'];
    $PD = $rec2['PD'];
    $ms = $rec2['MS'];
    $ls = $rec2['LS'];
    $stat = $rec2['ODORST'];
    $rqdt = $rec2['ODRQDT'];
    $shps = $rec2['ODSHPS'];
    $svia = $rec2['ODSVSV'];
    $turn = $rec2['TURN'];
    $status = $rec2['STAT'];
    $datshp = $rec2['DATSHP'];
    $dhold = $rec2['DHOLD'];
    
    // update the ship via in the order detail
    $s = "Update OEORDT set ODSVSV= '$svia', ODRQDT = f_CDTOHD('$rqdt') where ODORD# = $ord and ODWH = $whs";
    $r = db2_exec($con, $s);
    $data['SQL1'] =  $s;
    $data['SQL1M'] = db2_stmt_errormsg();
  
    
    if (trim($dhold) == 'Blank') $dhold = ' ';
    // Change the Dhold 
    $s = "Merge into OEOUDT as t using table(values('$dhold', $ord, $line, 'DHOLD')) s 
(OUFLDV, OUORD, OULINE, OUFLDN) on t.OUORD = S.OUORD and t.OULINE = s.OULINE and t.OUFLDN = s.OUFLDN 
when matched then 
update set OUFLDV = s.OUFLDV 
When not matched then 
Insert (OUORD, OULINE, OUFLDN, OUFLDV) values(s.OUORD, s.OULINE, s.OUFLDN, s.OUFLDV) with NC";
    $r = db2_exec($con, $s);
    $data['SQL2'] =  $s;
    $data['SQL2M'] = db2_stmt_errormsg();
    $data['root'][] = $rec2;
} else {
$s = "SELECT F1.ODORD# as ODORD, F1.ODWH, max(F2.BASE_DATE) as BD, min(F1.ODORL#) as LINE, max(F1.ODORL#) as MLINE,  
max(F2.PROJE00001) as PD,                                   
max(f_datcnv(F2.MUST_SHIP)) as MS , f_lastsca2(f1.odord#) as LS,
ODORST, ODSHPS,ODSVSV, ODSVDS, F_datCnv(ODRQDT) as ODRQDT, sdpklp         
 FROM oeordt f1 
left join order_adds f2 on f1.odorl# =f2.odorl# and f1.odord# = f2.odord#
left join oepklh on f1.odpkl# = sdpkl#  
WHERE f1.odord# = $ord 
group by f1.odord#, odwh, ODORST, ODSHPS, ODSVSV, ODSVDS, ODRQDT, sdpklp
order by ODWH";
$r = db2_exec($con, $s);
$data['SQL1'] = $s;
$data['SQLM1'] = db2_stmt_errormsg();
while ($row = db2_fetch_assoc($r)){
    $line = $row['LINE'];
    $mline = $row['MLINE'];
    $whs = $row['ODWH'];
// GET THE ORDER HEADER DATA 
    $sh = "Select * from OEORhd where oeORD# = $ord ";
    $rh = db2_exec($con, $sh);
    $rowh = db2_fetch_assoc($rh);
    
$s1 = "Select * from OEORDP where IDORD# = $ord and IDORL# = $line order by IDTURN desc";
$r1 = db2_exec($con, $s1);
$data['SQL1' . $whs] = $s1;
$data['SQLM1' . $whs] = db2_stmt_errormsg();
$row1= db2_fetch_assoc($r1);
$turn = intval($row1['IDTURN']);
$s2 = "Select * from OEORHP where IHTURN = $turn";
$r2 = db2_exec($con, $s2);
$data['SQL2' . $whs] = $s2;
$data['SQLM2' . $whs] = db2_stmt_errormsg();
// var_dump($s2, db2_stmt_errormsg());
$row2= db2_fetch_assoc($r2);
$pkl = intval($row2['IHPKL#']);
$row['TURN'] = $turn;
$row['PKL'] = $pkl;
$s4 = "Select F_GETODU($ord, $mline, 'PAC') as PTLINK, F_GETODU($ord, $line, 'PKL') as PLLINK from sysibm.sysdummy1";
$r4 = db2_exec($con, $s4);
$data['SQL4' . $whs] = $s4;
$data['SQLM4' . $whs] = db2_stmt_errormsg();
$row4= db2_fetch_assoc($r4);
// set status 
$hold = $rowh['OEHOLD'];
$fl05 = $rowh['OEFL05'];

$status = 'New';
$row['HOLD'] = 'N';
if (trim($hold) !== '' ) {
    $status = "on $hold hold";
    $row['HOLD'] = 'Y';
}
if ($fl05 == ' '){
    $status = 'Chedit check not done';
    $row['HOLD'] = 'Y';
}
if ($fl05 !== '2'){
    $status =  'On credit hold';
    $row['HOLD'] = 'Y';
}
$stat = $row['ODORST'];
if ($stat == 'C'){
    $status = 'Closed';
} else {
    if ($turn >0 and $pkl == 0 ){
        $status = 'Pick Ticket Created';
    } else {
        if ($turn > 0 and $pkl > 0){
            if ($row['SDPKLP'] !== 'Y'){
            $status = 'Packing List Created - not printed';
            } else {
                $status = 'Packing List Created - and printed';
            }
        } else {
            if (trim($turn) == ''){
                $status = 'Open';
            }
        }
    }
}
    $row['STAT'] = $status;
    $s3 = "Select f_GETODU($ord, $line, 'DATSHP') as VAL from sysibm.sysdummy1";
    $r3 = db2_exec($con, $s3);
    $data['SQL3' . $whs] = $s3;
    $data['SQLM3' . $whs] = db2_stmt_errormsg();
    $row3= db2_fetch_assoc($r3);
    $row['DATSHP'] = $row3['VAL'];
    $s3 = "Select f_GETODU($ord, $line, 'DHOLD') as VAL from sysibm.sysdummy1";
    $r3 = db2_exec($con, $s3);
    $data['SQL4' . $whs] = $s3;
    $data['SQLM4' . $whs] = db2_stmt_errormsg();
    $row3= db2_fetch_assoc($r3);
    $row['DHOLD'] = trim($row3['VAL']);
    $row['PKLP'] = $row['SDPKLP'];
    $row['LINE'] = $row['LINE'];
    $row['MLINE'] = $row['MLINE'];
  
    
    $pllink =  '/WWW/ZENDPHP7/HTDOCS' . trim( $row4['PLLINK']);
    $ptlink = '/WWW/ZENDPHP7/HTDOCS/TEST/PIC/'. trim($row['ODORD']) . '_' . trim($row['TURN']) . '.pdf' ;
    $row['PTLINK'] = '/TEST/PIC/'. trim($row['ODORD']) . '_' . trim($row['TURN']) . '.pdf' ;
    $row['PLLINK'] = trim( $row4['PLLINK']);
    $plexists = file_exists(trim($pllink));
    $ptexists = file_exists(trim($ptlink));
    $row['PLE'] = $plexists;
    $row['PTE'] = $ptexists;
    
    $data['root'][] =  $row;
    
}

} // end records



$data['success'] = true;
echo $_GET['callback'] . '(' . json_encode($data). ')';
// ODORD,ODWH, BD, PD, MS, LS