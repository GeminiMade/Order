<?php

error_reporting(E_ALL);
ini_set('details_errors', TRUE);
ini_set('details_startup_errors', TRUE);
ini_set('memory_limit', '-1');
include "../GConC.php";

if(isset($_POST['submit']))
{ $ord = $_POST['ORD'];} else $ord = 0;


?>
<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <input type="number" name="ORD" value="<?php echo $ord;?>"><br>
    <input type="submit" name="submit" value="Change Order"><br>
    </form>
<?php 
$s = "Select * from oeordt where odord# = $ord and ODPCLS not in ('CHNG')";
$r = db2_exec($con, $s);
While ($row = db2_fetch_assoc($r)){
    
//    echo "<hr>";
echo "<br><br>";
//    echo "<br><b>Item Description </b>";
    Echo "<br>" . $row['ODIMDS'];
 
    $line = $row['ODORL#'];
    // feature lines
//    Echo "<br><b>Item Features</b>";
    $s2 = "SELECT *  FROM hdmfdt                
join hdimst on imitem = oooptn  
WHERE OOORD# = $ord  and OOORL#= $line ";
    $r2 = db2_exec($con, $s2);
    while ($rowo = db2_fetch_assoc($r2)){
        echo "<br>" . $rowo['IMIMDS'];
    }
    
    
    $s1 = "SELECT * FROM oeocmt WHERE OCORD# = $ord and OCORL# = $line and OCCMNT <> ' ' and OCDOCT = 'ACK'";
    $r1 = db2_exec($con, $s1);
    $print = false;
    $strgrp = false;
    while ($rowc = db2_fetch_assoc($r1)){
        $cmt = $rowc['OCCMNT'];
      
        // print lines after copy and before copy end if copy start is found
        if (substr($cmt, 0,10) == '***COPYSTA'){
         //   echo "<br><b> --- Copy --- </b>";
            $strgrp = true;
        }  // end copy start 
        // copy end line 
      
        if (substr($cmt, 0,10) == '***COPYEND'){
            $print = false;
            $strgrp = false;
        }
        
        // print lines after Logo and before logo end if copy start is found
        if (substr($cmt, 0,10) == '***LOGOSIZ'){
     //       echo "<br><b> --- Logo --- </b>";
            $strgrp = true;
        }  // end copy start
        // copy end line
        
        if (substr($cmt, 0,10) == '***LOGOEND'){
            $print = false;
            $strgrp = false;
        }
        
        
        
        if ($print){
            echo "<br>" . $rowc['OCCMNT'];
        }
        if (!$strgrp){
            $print = false;
        } else {
            $print = true;
        }
        
        
        
    } // end of comment loop
    
    
    
}
echo "<br><br>";