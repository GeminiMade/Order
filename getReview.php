<html>
<head>
<link rel=stylesheet type="text/css" href="../../../IVGeneral.css">
<link rel=stylesheet type="text/css" href="../../../IVMenu.css">
<link rel=stylesheet type="text/css" href="../../../HDSQuickLink.css">
<link rel=stylesheet type="text/css" href="../../../HDSTabs.css">

</head>
<body>
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
$pgid = 'MW';
// get the program option security
$s = "Select * from SYPGMS where SPPGID = '$pgid' and SPUSER = '$user'";
$r = db2_exec($con, $s);
$row = db2_fetch_assoc($r);

$sc = "Select * from sycusf where cuuser = '$userid'";
$rc = db2_exec($con, $sc);
$rowc = db2_fetch_assoc($rc);
$commonuser = $rowc['CUCUSR'];

if (trim($row['SPPGID']) == '') {
    $s = "Select * from SYPGMS where SPPGID = '$pgid' and SPUSER = '$commonuser'";
    $r = db2_exec($con, $s);
    $row = db2_fetch_assoc($r);
}


$opt01 = $row['SPOP01'];
$opt02 = $row['SPOP02'];
$opt03 = $row['SPOP03'];
$opt04 = $row['SPOP04'];
$opt05 = $row['SPOP05'];
$opt06 = $row['SPOP06'];
$opt07 = $row['SPOP07'];
$opt08 = $row['SPOP08'];
$opt09 = $row['SPOP09'];
$opt10 = $row['SPOP10'];
$opt11 = $row['SPOP11'];
$opt12 = $row['SPOP12'];
$opt13 = $row['SPOP13'];
$opt14 = $row['SPOP14'];
$opt15 = $row['SPOP15'];

 

    $s = "SELECT F1.ODORD# as ODORD, F1.ODWH, max(F2.BASE_DATE) as BD, min(F1.ODORL#) as LINE, max(F1.ODORL#) as MLINE,  
    max(F2.PROJE00001) as PD,                                   
    max(f_datcnv(F2.MUST_SHIP)) as MS , f_lastsca4(f1.odord#, f1.odwh) as LS,
    ODORST, ODSHPS,ODSVSV, ODSVDS,   sdpklp         
     FROM oeordt f1 
    left join order_adds f2 on f1.odorl# =f2.odorl# and f1.odord# = f2.odord#
    left join oepklh on f1.odpkl# = sdpkl#  
    WHERE f1.odord# = $ord 
    group by f1.odord#, odwh, ODORST, ODSHPS, ODSVSV, ODSVDS,  sdpklp
    order by ODWH";
$r = db2_exec($con, $s);
 
// var_dump($s,db2_stmt_errormsg());
echo "<table border = '1' class='contenttable'>";
echo "<tr>";
echo "<th class='colhdr'>Actions</th>";
echo "<th class='colhdr'>Whs</th>";
echo "<th class='colhdr'>Status</th>";
echo "<th class='colhdr'>Hold<br>Code</th>";
echo "<th class='colhdr'>Base<br>Date</th>";
echo "<th class='colhdr'>Projected<br>Date</th>";
echo "<th class='colhdr'>Must Ship<br>Date</th>";
echo "<th class='colhdr'>ShipVia</th>";
echo "<th class='colhdr'>Shipped</th>";
echo "<th class='colhdr'>Last Tops<br>Scan</th>";
echo "<th class='colhdr'>Pick Ticket</th>";
echo "<th class='colhdr'>Packing List</th>";
echo "<th class='colhdr'>Ship With</th>";
echo "</tr>";
while ($row = db2_fetch_assoc($r)) {
    $line = $row['LINE'];
    $mline = $row['MLINE'];
    $whs = $row['ODWH'];
    // GET THE ORDER HEADER DATA
    $sh = "Select * from OEORhd where oeORD# = $ord ";
    $rh = db2_exec($con, $sh);
    $rowh = db2_fetch_assoc($rh);
    // get the last scan description
    $ls = $row['LS'];
    $sls = "Select * from gmtop02 where GMWKSTN2 = '$ls' and GMWH2 = '$whs'";
    $rls = db2_exec($con, $sls);
    $rowls = db2_fetch_assoc($rls);
    $lsd = $rowls['GMWKSDSC2'];
    
    $s1 = "Select * from OEORDP where IDORD# = $ord and IDORL# = $line order by IDTURN desc";
    $r1 = db2_exec($con, $s1);
    
    $row1 = db2_fetch_assoc($r1);
    $turn = intval($row1['IDTURN']);
    $s2 = "Select * from OEORHP where IHTURN = $turn";
    $r2 = db2_exec($con, $s2);
    
    // var_dump($s2, db2_stmt_errormsg());
    $row2 = db2_fetch_assoc($r2);
    $pkl = intval($row2['IHPKL#']);
    $row['TURN'] = $turn;
    $row['PKL'] = $pkl;
    $s4 = "Select F_GETODU($ord, $mline, 'PAC') as PTLINK, F_GETODU($ord, $line, 'PKL') as PLLINK from sysibm.sysdummy1";
    $r4 = db2_exec($con, $s4);
    $row4 = db2_fetch_assoc($r4);
    // set status
    $hold = $rowh['OEHOLD'];
    $fl05 = $rowh['OEFL05'];
    
    $status = 'New';
    $row['HOLD'] = 'N';
    if (trim($hold) !== '') {
        $status = "on $hold hold";
        $row['HOLD'] = 'Y';
    }
    if ($fl05 == ' ') {
        $status = 'Chedit check not done';
        $row['HOLD'] = 'Y';
    }
    if ($fl05 !== '2') {
        $status = 'On credit hold';
        $row['HOLD'] = 'Y';
    }
    $stat = $row['ODORST'];
    if ($stat == 'C') {
        $status = 'Closed';
    } else {
        if ($turn > 0 and $pkl == 0) {
            $status = 'Pick Ticket Created';
        } else {
            if ($turn > 0 and $pkl > 0) {
                if ($row['SDPKLP'] !== 'Y') {
                    $status = 'Packing List Created - not printed';
                } else {
                    $status = 'Packing List Created - and printed';
                }
            } else {
                if (trim($turn) == '') {
                    $status = 'Open';
                }
            }
        }
    }
    
    $s3 = "Select f_GETODU($ord, $line, 'DATSHP') as VAL from sysibm.sysdummy1";
    $r3 = db2_exec($con, $s3);
    $data['SQL3' . $whs] = $s3;
    $data['SQLM3' . $whs] = db2_stmt_errormsg();
    $row3 = db2_fetch_assoc($r3);
    $row['DATSHP'] = $row3['VAL'];
    $s3 = "Select f_GETODU($ord, $line, 'DHOLD') as VAL from sysibm.sysdummy1";
    $r3 = db2_exec($con, $s3);
    $data['SQL4' . $whs] = $s3;
    $data['SQLM4' . $whs] = db2_stmt_errormsg();
    $row3 = db2_fetch_assoc($r3);
    $row['DHOLD'] = trim($row3['VAL']);
    $row['PKLP'] = $row['SDPKLP'];
    $row['LINE'] = $row['LINE'];
    $row['MLINE'] = $row['MLINE'];
    if (trim($row['DHOLD']) !== '')
        $status = 'Production Hold';
    
    $row['STAT'] = $status;
    
    $pllink = '/WWW/ZENDPHP7/HTDOCS' . trim($row4['PLLINK']);
    $plurl = 'http://erpdc:10080' . trim($row4['PLLINK']);
    $ptlink = '/WWW/ZENDPHP7/HTDOCS/TEST/PIC/' . trim($row['ODORD']) . '_' . trim($row['TURN']) . '.pdf';
    $pturl = 'http://erpdc:10080/TEST/PIC/' . trim($row['ODORD']) . '_' . trim($row['TURN']) . '.pdf';
    $row['PTLINK'] = '/TEST/PIC/' . trim($row['ODORD']) . '_' . trim($row['TURN']) . '.pdf';
    $row['PLLINK'] = trim($row4['PLLINK']);
    $plexists = file_exists(trim($pllink));
    $ptexists = file_exists(trim($ptlink));
    $row['PLE'] = $plexists;
    
    $row['PTE'] = $ptexists;
    if ($ptexists) {
        $pick = "<a href='$pturl' target='_blank'>" . $row['TURN'] . "</a>";
    } else {
        $pick = $row['TURN'];
    }
    if ($plexists) {
        $pack = "<a href='$plurl' target='_blank'>" . $row['PKL'] . "</a>";
    } else {
        $pack = $row['PKL'];
    }
    $svsv = trim($row['ODSVSV']);
    $sviaurl = "chgSvia.php?DB=TS&ORD=$ord&WHS=$whs&SVIA=$svsv";
   
    
    $pt1 = true;
    $pt2 = false;
    $pl1 = false;
    $pl2 = false;
    $pl3 = false;
    $h1 = false;
    $h2 = false;
    
    $turn = $row['TURN'];
    $dhold = trim($row['DHOLD']);
    $whs = $row['ODWH'];
    // if line is on hold do not allow any actions
    if (trim($dhold) == '') {
        $holdtitle = "Detail Line Hold Code";
        // if the packing has not been created - show the pick ticket options
        if ((trim($pkl) == "" or trim($pkl) == "0") and $opt09 == 'Y') {
            $svia = "<a href='#' onclick='window.open(\"$sviaurl\", \"Change Svia\", \"width=400,height=400\")'>" . trim($row['ODSVDS']) . '(' . $row['ODSVSV'] . ')</a>';
            
            if (trim($turn) == '' or trim($turn) == "0") {
                $pt2 = false;
            } else {
                $pt2 = true;
                $h1 = true;
                $h2 = false;
            }
            $pl1 = true;
        } else {
            $svia = trim($row['ODSVDS']) . '(' . $row['ODSVSV'] . ')';
            if (trim($turn) == '')
                $pt2 = false;
            $pl2 = true;
            $pl3 = true;
            $h1 = false;
            $h2 = false;
        } // end packing list
    } else {
        $pt1 = false;
        $h2 = true;
        $h1 = false;
        $holdtitle = "No actions are allowed line is on hold";
    } // end dhold
    if (trim($turn) == '' or trim($turn) == "0") {
        $pt1click = "maintActions.php?DB=TS&ORD=$ord&Action=PPACK";
    } else {
        $pt1click = "maintActions.php?DB=TS&ORD=$ord&TURN=$turn&Action=RPPACK";
    }
    $pt2click = "maintActions.php?DB=TS&ORD=$ord&TURN=$turn&Action=DIP";
    $pl1click = "getReasonPkl.php?DB=TS&ORD=$ord&WHS=$whs&U=$user&TURN=$turn&Action=CRTPKL";
    $pl2click = "maintActions.php?DB=TS&ORD=$ord&PKL=$pkl&U=$user&Action=PRTPKL";
    $pl3click = "maintActions.php?DB=TS&ORD=$ord&PKL=$pkl&Action=DPK";
    $h1click = "getReason.php?DB=TS&ORD=$ord&WHS=$whs&Action=HOLD";
    $h2click = "maintActions.php?DB=TS&ORD=$ord&WHS=$whs&TURN=$turn&Action=HOLDREL";
    $qtclick = "QuickTops.php?DB=TS&ORD=$ord&WHS=$whs";
    $msclick = "ManShip.php?DB=TS&ORD=$ord&WHS=$whs";
    
    $ss = "SELECT sum( odqord - odqstd ) as OPN, sum(ODQSTC) as STC FROM oeordt WHERE odwh = 
$whs and odord# = $ord                                         ";
    $rss = db2_exec($con, $ss);
    $rowss = db2_fetch_assoc($rss);
    $shipped = false;
     
    if (trim($rowss['OPN']) == 0) {
        $shipped = true; 
        $row['STAT'] = 'Shipped/Invoiced ';
    }
    if (trim($rowss['STC']) > 0) {
        
        $row['STAT'] = 'Shipped Not Invoiced ';
    }
       
     // Ship with condition for this line.
     $sw = "Select * from OEOUDT where ouord = $ord and ouline = $line and OUFLDN = 'TOWHS'";
     $rw = db2_exec($con, $sw);
     $roww = db2_fetch_assoc($rw);
     if ($roww['OUFLDR'] > 0) {
         $shipwith = '  Warehouse <b>' . intval($roww['OUFLDR']) . '</b>';
         $sw = "Select * from OEOUDT where ouord = $ord and ouline = $line and OUFLDN = 'STREAS'";
         $rw = db2_exec($con, $sw);
         $roww = db2_fetch_assoc($rw);
         $shipwith .= '<br>  Code: <b>' . $roww['OUFLDV'] . "</b>";
         $sw = "Select * from OEOUDT where ouord = $ord and ouline = $line and OUFLDN = 'STRSOC'";
         $rw = db2_exec($con, $sw);
         $roww = db2_fetch_assoc($rw);
         $shipwith .= '<br> Note: <b>' . $roww['OUFLDV'] . '</b>';
     } else $shipwith = '&nbsp';
    
    $actions = '';
    if (trim($row['LS']) !== 'SH') {
        if ($pt1 and $opt01 == 'Y')
            $actions .= " <img src='../../../icons/greenRpt.png' onclick='window.open(\"$pt1click\", \"pt\", \"width=300,height=300\")' title='Create/Print Pick Ticket'></img>";
        if ($pt2  and $opt07 == 'Y')
            $actions .= "<img src='../../../icons/cancelgreen.png' onclick='window.open(\"$pt2click\", \"pt\", \"width=300,height=300\")' title = 'Delete Pick Ticket'></img>";
        if ($pl1 and $opt02 == 'Y')
            $actions .= "<img src='../../../icons/redRpt.png' onclick='window.open(\"$pl1click\", \"pl\", \"width=300,height=300\")' title = 'Create/Print Packing List'></img>";
        if ($pl2  and $opt08 == 'Y')
            $actions .= "<img src='../../../icons/redRpt.png' onclick='window.open(\"$pl2click\", \"pl\", \"width=300,height=300\")' title='Print Packing List'></img>";
        if ($pl3 and $opt08 == 'Y')
            $actions .= "<img src='../../../icons/cancelRed.png' onclick='window.open(\"$pl3click\", \"pl\", \"width=300,height=300\")' title='Delete Packing List'></img>";
        if ($h1 and $opt03 == 'Y')
            $actions .= "<img src='../../../icons/hold.png' onclick='window.open(\"$h1click\", \"hl\", \"width=300,height=300\")' title='Prod Hold'></img>";
        if ($h2 and $opt04 == 'Y')
            $actions .= "<img src='../../../icons/holdRel.png' onclick='window.open(\"$h2click\", \"hl\", \"width=300,height=300\")' title='Release Hold'></img>";
        if ($opt11 == 'Y')
        $actions .= "<img src='../../../icons/dependents.gif' onclick='window.open(\"$qtclick\", \"Tops\", \"width=300,height=300\")' alt='Quick Tops Entry'></img>";
    } else {
        if (! $shipped and $opt10 == 'Y') {
            $actions .= "<img src='../../../icons/go.gif' onclick='window.open(\"$msclick\", \"Tops\", \"width=300,height=300\")' title='Manual Ship'></img>";
        }
    }
    echo "<tr>";
    echo "<td class='colalph'>" . $actions . "</td>";
    echo "<td class='colnmbr'>" . $row['ODWH'] . "</td>";
    echo "<td   style='word-wrap: break-word; width: 150px;font-size: 9pt;' >" . $row['STAT'] . "</td>";
    echo "<td class='colalph' title ='$holdtitle'>" . $row['DHOLD'] . "</td>";
    echo "<td class='colalph'>" . $row['BD'] . "</td>";
    echo "<td class='colalph'>" . $row['PD'] . "</td>";
    echo "<td class='colalph'>" . $row['MS'] . "</td>";
    echo "<td class='colalph'>$svia</td>";
    echo "<td class='colalph'>&nbsp</td>";
    echo "<td class='colalph'>$lsd (" . $row['LS'] . ")</td>";
    echo "<td class='colalph'>" . $pick . "</td>";
    echo "<td class='colalph'>" . $pack . "</td>";
    echo "<td class='colalph'>" . $shipwith . "</td>";
    echo "</tr>";
}

echo "</table>";

$relUrl = "RlsCr2.php?DB=" . $_GET['DB'] . "&ORD=$ord";
if ($fl05 == '1') {
    echo "<img src='../../../icons/gball.jpg' onclick='window.open(\"$relUrl\")' alt='Release Credit Hold'></img>";
}
?>
</body>
</html>

