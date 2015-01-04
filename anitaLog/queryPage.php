<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="generator" />
<body bgColor=#BBDDFF><font face="Arial">

<title>ANITA-3 Run Database</title>

</head>
<body>

<h2>ANITA-3 Calibration/Testing Run Database</h2>
<p>Search the ANITA-3 run database on this page.</p> 

<p>If you wish to add comments about specific runs, enter the information <a href="logForm.html">here</a>.</p>

<hr />

<form action="queryPage.php" method="post">

  <p>Which runs would you like information about (leave all fields blank to select all)?<br>
  </p>

  <table cellpadding=0 bgcolor=#BBDDFF cellspacing=0 border=0 width="600" allign center>
  <tr>

    <td rowspan="3">
      <table cellpadding=0 cellspacing=0 border=0 width="200" allign left>

        <tr>
	  <td>First run</td>
	  <td><input name =firstRun size=10 maxlength=10 value="<?php echo $_POST["firstRun"]; ?>"></td>
	</tr>
	<tr>
	  <td>Last run</td>
	  <td><input name =lastRun size=10 maxlength=10 value="<?php echo $_POST["lastRun"]; ?>"></td>
	</tr>
      </table>
    </td>

    <td rowspan="3">
      <table cellpadding=0 cellspacing=0 border=0 width="200" allign left>
        <tr>
	  <td>Search run comments</td>
	</tr>
	<tr>
	  <td><input name =textSearch size=22 value="<?php echo $_POST["textSearch"];?>"></td>
	</tr>
      </table>
    </td>

    <td rowspan="3">
      <table cellpadding=0 cellspacing=0 border=0 width="200" allign left>
        <tr>
	  <td>Shifter Name</td>
	</tr>
	<tr>
	  <td><input name =theShifter size=22 value="<?php echo $_POST["theShifter"];?>"></td>
	</tr>
      </table>
    </td>

  </tr>
  </table>

<p>Select location of run?<br /></p>
<input type="checkbox" name="location[]" value="Antarctica" checked="checked"/>Antarctica<br />
<input type="checkbox" name="location[]" value="Palestine" />Palestine/Hawaii<br /> 

<p>Run order?<br /></p>
<input type="radio" name="order" value="Ascending" />Ascending<br /> 
<input type="radio" name="order" value="Descending" checked="checked"/>Descending<br />

<input type="submit" value="Get Info">
</form>


<?php
error_reporting(E_ALL);
$firstRun = $_POST["firstRun"];
$lastRun = $_POST["lastRun"];
$textSearch = $_POST["textSearch"];
$theShifter = $_POST["theShifter"];

$order = $_POST["order"];
$aloc = array($_POST["location"]);
$antLoc = 0;
if(array_key_exists(0, $aloc)){
    $antLoc = $aloc[0];
}
$palLoc = 0;
if(array_key_exists(1, $aloc)){
    $palLoc = $aloc[1];
}

$textArray = explode(" ",$textSearch);
$textArraySize = count($textArray);

$dbhost = "localhost";
$dbuser = "anita";
$dbpass = "IceRadi0";
$dbName = "runLog";

$selRunNumber = " ";
$location = " ";
$startTime = " ";
$endTime = " ";
$firstEvent = " ";
$lastEvent = " ";
$shifterName = " ";
$runDescription = " ";
$logName = " ";
$logComment = " ";

if(!$firstRun && !$lastRun){
    $firstRun=1;
    $lastRun=19999;
}

if(!$lastRun){
    //  $lastRun = $firstRun;
    $lastRun = 19999;
}
if(!$firstRun){
    //  $firstRun = $lastRun;
    $firstRun = 0;
}
if($firstRun > $lastRun){
    die('Make sure first run is earlier than last run!');
}
if($theShifter){
    $findShifter=$theShifter;
}

$link = mysql_connect('localhost', 'anita', 'IceRadi0') or die('Error! Could not connect to server');
$db_selected = mysql_select_db('runLog', $link) or die('Error! Could not find database');
$runsFound=0;
$startRun=0;
$endRun=0;
$iterater=0;
if($order=="Ascending"){
    $startRun=$firstRun;
    $endRun=$lastRun;
    $iterator=1;
}
else{
    $startRun=$lastRun;
    $endRun=$firstRun;
    $iterator=-1;
}

$runNumber = $startRun-$iterator;
while($runNumber!=$endRun){
    $runNumber += $iterator;
    $result = mysql_query("SELECT * FROM runTable WHERE run=".$runNumber);
    if($result != FALSE){
        $row = mysql_fetch_array($result, MYSQL_NUM);
        $location = $row[1];
        $startTime = $row[2];
        $endTime = $row[3];
        $firstEvent = $row[4];
        $lastEvent = $row[5];
        $shifterName = $row[6];
        $runDescription = $row[7];
        $logName = $row[8];
        $logComment = $row[9];

        // Require location matching
        if($location==$antLoc){
        }
        else if($location==$palLoc){
        }
        else{
            continue;
        }

        $foundComment=1;
	
        if($textSearch){

            for($textElement=0;$textElement<$textArraySize;$textElement++){
		$foundCommentArray[$textElement]=0;
	    }

            $descriptionArray = explode(" ",$runDescription);
            $commentArray = explode(" ",$logComment);

            $arraySize = count($descriptionArray);
            for($arrayElement=0;$arrayElement<$arraySize;$arrayElement++){
		for($textElement=0;$textElement<$textArraySize;$textElement++){
		    if(strtolower($textArray[$textElement])==strtolower($descriptionArray[$arrayElement])){
        		$foundCommentArray[$textElement]=1;
		    }
		}
            }

            $arraySize = count($commentArray);
            for($arrayElement=0;$arrayElement<$arraySize;$arrayElement++){
		for($textElement=0;$textElement<$textArraySize;$textElement++){
		    if(strtolower($textArray[$textElement])==strtolower($descriptionArray[$arrayElement])){
         		$foundCommentArray[$textElement]=1;
		    }
		}
            }

	    
            for($textElement=0;$textElement<$textArraySize;$textElement++){
		if($foundCommentArray[$textElement]==0){
         	    $foundComment=0;
		}
            }


	} // if($textSearch)

	if(!$theShifter){
            $findShifter=$shifterName;
	}
	
	if(!$row){
	}
	else if($foundComment==1 && strtolower($findShifter)==strtolower($shifterName)){
            $runsFound++;
	    if($runsFound==1){
		echo "<hr />";

		echo "<table cellpadding=10 cellspacing=1 border=1 width=100%>
  <tr>
    <td width=3%>Run</td>
    <td width=5%>Loc.</td>
    <td width=12%>Start Time</td>
    <td width=12%>End Time</td>
    <td width=6%>First Event</td>
    <td width=6%>Last Event</td>
    <td width=4%>Shifter</td>
    <td width=15%>Shift Details</td>
    <td width=5%>Log Name</td>
    <td width=15%>Log Entries</td>
  </tr>";
	    }

	    echo "
  <tr>
    <td width=3%>$runNumber</td>
    <td width=3%> $location</td>
    <td width=12%> $startTime</td>
    <td width=12%> $endTime</td>
    <td width=6%> $firstEvent</td>
    <td width=6%> $lastEvent</td>
    <td width=4%> $shifterName</td>
    <td width=15%> $runDescription</td>
    <td width=5%> $logName</td>
    <td width=15%> $logComment</td>
  </tr>";
	}
    }
}

if($runsFound==0){
    echo "<p>Sorry. No runs found matching your search criteria.<p/>";
}

echo "</table>";
?>

</body>
</html>
