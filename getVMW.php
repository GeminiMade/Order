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
$s = "Select OEORHD.*, HDCUST.*, HDHLCD.*, HDSLSM.*, hdotyp.*,  f_datcnv(oebdte) as OEBDTE,
f_datcnv(oerqdt) as OERQDT, f_datcnv(OEDTE2) as OEDTE2, f_getoeu(OEORD#, 'BASDTE') as BD, 
f_getoeu(OEORD#, 'PRJDTE') as PD, f_getoeu(OEORD#, 'DOLADD') as DOLADDS,  
f_getoeu(OEORD#, 'PCADDS') as PCADDS, f_getoeu(OEORD#, 'WHSADD') as WHSADDS,
f_getoeu(OEORD#, 'OPTADD') as OPTADDS, OEORRF,OEUDF2,
f_getoeu(OEORD#, 'WHSE') as RPTWHS, f_getoeu(OEORD#, 'PRDTYP') as PRDTYP, OEDSHP 
from OEORHD 
left join hdcust on oeshto  = cmcust
left join hdhlcd on oehold = hchlcd
left join hdslsm on OESLSM = SMSLSM
left join hdotyp on otapid = 'OE' and ototcd = oeorty
where oeord# = $ord";

$r = db2_exec($con, $s);
// var_dump($s, db2_stmt_errormsg());
while ($row = db2_fetch_assoc($r)){
    
    $x['OEORD'] = $row['OEORD#'];
    if ($row['OEBLTO'] !== $row['OESHTO']){
    $x['CUST'] = $row['OEBLTO'] . '/' . $row['OESHTO'];
    } else {
        $x['CUST'] = $row['OESHTO'];
    }
    $x['CMCNA1'] = $row['CMCNA1'];
    $adr = trim($row['CMCNA2']);
    if (trim($row['CMCNA3']) !== ''){
        $adr .= '<br>' . trim($row['CMCNA3']);
    }
    if (trim($row['CMCNA4']) !== ''){
        $adr .= '<br>' . trim($row['CMCNA4']);
    }
    $adr .= trim($row['CMCCTY']) . ',  ' . $row['CMST'] . '  ' . $row['CMZIP'];
    $x['ADR'] = $adr;
    $x['HOLD'] =  "(" . $row['OEHOLD'] . ') ' . trim($row['HCDESC']);
    $crflg = "(" . $row['OEFL05'] . ') ';
    if ($row['OEFL05']  ==  '0') $crflg .= 'Not on credit hold';
        if ($row['OEFL05']  == '1') $crflg .= 'On credit hold';
            if ($row['OEFL05']  ==  '2') $crflg .= 'Order released over credit limit';
                if ($row['OEFL05']  ==  '3') $crflg .= 'Credit';
                    if ($row['OEFL05']  ==   '4') $crflg .= 'Deposit';
                        if ($row['OEFL05']  ==   '5') $crflg .= 'Past due';
                            if ($row['OEFL05']  ==  '6') $crflg .= 'Deposit & past due';
                                    if ($row['OEFL05']  ==  '7') $crflg .= 'COD apply credit';
                                    
   if (trim($row['OEHOLD']) !== '' or $row['OEFL05'] !== '2') {
       $ohold = true;
   } else $ohold = false;
                                    
   $dshp = $row['OEDSHP'];
   $dshpadr = ' ';
   $dval = 'NA';
   $dshpvalmsg = '';
   $cust = $row['OESHTO'];
   if (trim($dshp) !== '' and trim($dshp) !== '0' ){
       
       
       $sd = "Select  * from hddshp where DSVCF = 'C' and DSVNCS = $cust and DSNMBR = $dshp";
       $rd = db2_exec($con, $sd);
       $rowd = db2_fetch_assoc($rd);
       $dshpadr = $rowd['DSNAME'];
       if (trim($rowd['DSADR1']) !== ''){
           $dshpadr .= '<br>' . $rowd['DSADR1'];
       }
       if (trim($rowd['DSADR2']) !== ''){
           $dshpadr .= '<br>' . $rowd['DSADR2'];
       }
       if (trim($rowd['DSADR3']) !== ''){
           $dshpadr .= '<br>' . $rowd['DSADR3'];
       }
       $dshpadr .= '<br>' . trim($rowd['DSCITY']) . ', ' . $rowd['DSST'] . '   ' . $rowd['DSZIP'] ;
       if (trim($rowd['DSCTRY']) !== 'US' and trim($rowd['DSCTRY']) !== ''){
           $dshpadr .= '<br>Country Code: ' . $rowd['DSCTRY'];
       }
       $sdv = "Select * from hddshpu where DUVCF = 'C' and DUVNCS = $cust and DUNMBR = $dshp and DUFLDN = 'ADRVAL'";
       $rdv = db2_exec($con, $sdv);
       $rowdv = db2_fetch_assoc($rdv);
       $dval = trim($rowdv['DUFLDV']);
       if ($dval == '2'){
           $sdvm = "Select * from hddshpu where DUVCF = 'C' and DUVNCS = $cust and DUNMBR = $dshp and DUFLDN = 'ADRMSG'";
           $rdvm = db2_exec($con, $sdvm);
           $rowdvm = db2_fetch_assoc($rdvm);
           $dshpvalmsg = trim($rowdvm['DUFLDV']);
       }
       if (trim($dval) == ''){
           $dval = 'NC';
       }
       
   }
   
   $db = $_GET['DB'];
   $sc = "Select * from hdcust_Ext where cmcust = $cust and FLD = 'ADRVAL'";
   $rc = db2_exec($con, $sc);
   $rowc = db2_fetch_assoc($rc);
   $cval = 'NA';
   $cval = $rowc['FLDVAL'];
  
   
   $sc = "Select * from hdcust_Ext where cmcust = $cust and FLD = 'ADRMSG'";
   $rc = db2_exec($con, $sc);
   $rowcm = db2_fetch_assoc($rc);
 
   $cvalmsg = trim($rowcm['FLDVAL']);
   
   $x['CVAL'] = $cval;
   $x['CVALMSG'] = $cvalmsg;
   $x['CRFGL'] = $crflg;                                 
   $slsm = '(' . $row['OESLSM'] . ') ' . trim($row['SMSNA1']); 
   $x['OESLSM'] = $slsm;
   $x['OECTRM'] = $row['OECTRM'];
   $x['OEBDTE'] = $row['OEBDTE'];
   $x['OERQDT'] = $row['OERQDT'];
   $x['OEDTE2'] = $row['OEDTE2'];
   $x['OEDTE3'] = $row['OEDTE3'];
   $x['OEDTE4'] = $row['OEDTE4'];
   $x['RPTCLS'] = $row['PRDTYP'];
   $x['RPTWHS'] = $row['RPTWHS'];
   $x['OEORRF'] = $row['OEORRF'];
   $x['OEUDF2'] = $row['OEUDF2'];
   $x['OPTADDS'] = $row['OPTADDS'];
   $x['PCADDS'] = $row['PCADDS'];
   $x['DOLADDS'] = $row['DOLADDS'];
   $x['WHSADDS'] = $row['WHSADDS'];
   $x['OESVSV'] = $row['OESVSV'];
   $x['OESVDS'] = $row['OESVDS'];
   $x['OEFL05'] = $row['OEFL05'];
   $x['BD'] = $row['BD'];
   $x['PD'] = $row['PD'];
   $x['OECONT'] = $row['OECONT'];
   $x['DSHP'] = $dshp;
   $x['DSHPADR'] = $dshpadr;
   $x['DVAL'] = trim($dval);
   $x['DVALM'] = $dshpvalmsg;
   $x['OESHTO'] = $row['OESHTO'];
   $title = '('. $row['OEORTY'] .')' . ' ' . trim($row['OTDESC']);
   if ($row['OEORST'] == 'O') {
       if ($ohold){
       $title .= '<sec style="color: red;"> Order Status: On Hold </sec>';
       } else {
           $title .= '<sec style="color: #ffe773;"> Order Status: Open </sec>';
       }
   }
   if ($row['OEORST'] == 'C') {
       $title .= '<sec style="color: #ffd300;"> Order Status: Closed</sec>';
   }
   $x['TITLE'] = "<div style = 'Font-size: 20px; '>Order Number: $ord -- $title  </div>" ;
    
    $data['root'][] =  $x;
    
}




$data['success'] = true;
echo $_GET['callback'] . '(' . json_encode($data). ')';
// OEORD. OESHTO, CMCNA1, OEHOLD, OEBDTE, OERQDT, OEDTE2, OEUDF2, OEORRF, OECTRM, OESLSM, OECONT, OESVSV, OESVDS