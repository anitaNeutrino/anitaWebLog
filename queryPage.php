<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="generator" />
<body bgColor=#BBDDFF><font face="Arial">

<title>ANITA Run Database</title>

</head>
<body>

<h2>ANITA Run Database</h2>
<p>Search the ANITA II run database on this page.</p> 

<p>If you wish to add comments about specific runs, enter the information <a href="logForm.html">here</a>.  Please check the current log for the run first though!

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
	  <td><input name =firstRun size=4 maxlength=4></td>
	</tr>
	<tr>
	  <td>Last run</td>
	  <td><input name =lastRun size=4 maxlength=4></td>
	</tr>

      </table>
    </td>

    <td rowspan="3">
      <table cellpadding=0 cellspacing=0 border=0 width="200" allign left>
        <tr>
	  <td>Search run comments</td>
	</tr>
	<tr>
	  <td><input name =textSearch size=22></td>
	</tr>
      </table>
    </td>

    <td rowspan="3">
      <table cellpadding=0 cellspacing=0 border=0 width="200" allign left>
        <tr>
	  <td>Shifter Name</td>
	</tr>
	<tr>
	  <td><input name =theShifter size=22></td>
	</tr>
      </table>
    </td>

  </tr>
  </table>
  <br>
  <input type="submit" value="Get Info">
</form>


<?php
$firstRun = $_POST["firstRun"];
$lastRun = $_POST["lastRun"];
$textSearch = $_POST["textSearch"];
$theShifter = $_POST["theShifter"];

$textArray = explode(" ",$textSearch);
$textArraySize = count($textArray);

$dbhost = "localhost";
$dbuser = "anita";
$dbpass = "AniTa08";
$dbName = "anita";

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
  $firstRun=2000;
  $lastRun=5000;
}
if(!$lastRun){
  $lastRun = $firstRun;
}
if(!$firstRun){
  $firstRun = $lastRun;
}
if($firstRun > $lastRun){
  die('make sure first run is earlier than last run!');
}
if($theShifter){
  $findShifter=$theShifter;
}

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



  pg_connect($dbhost,$dbuser,$dbpass) or die(pg_error());
  pg_select_db($dbName) or die(pg_error());
$runsFound=0;
for($runNumber = $firstRun;$runNumber < $lastRun+1;$runNumber++){

  $result = pg_query("SELECT * FROM runTable WHERE runNumber=$runNumber");
  $row = pg_fetch_array($result);

  $location = $row['location'];
  $startTime = $row['startTime'];
  $endTime = $row['endTime'];
  $firstEvent = $row['firstEvent'];
  $lastEvent = $row['lastEvent'];
  $shifterName = $row['shifterName'];
  $runDescription = $row['runDescription'];
  $logName = $row['logName'];
  $logComment = $row['logComment'];

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


  }

  if(!$theShifter){
    $findShifter=$shifterName;
  }
    


  if(!$row){
  }
  else if($foundComment==1 && strtolower($findShifter)==strtolower($shifterName)){
    $runsFound++;
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

if($runsFound==0){
  echo "
  <tr>
    <td width=3%>$firstRun</td>
    <td width=3%></td>
    <td width=12%>No relevent runs</td>
    <td width=12%>relax your search</td>
    <td width=6%></td>
    <td width=6%></td>
    <td width=4%></td>
    <td width=15%></td>
    <td width=5%></td>
    <td width=15%></td>
  </tr>";
  if(strcmp($firstRun,$lastRun)){
    echo"
  <tr>
    <td width=3%>$lastRun</td>
    <td width=3%></td>
    <td width=12%></td>
    <td width=12%></td>
    <td width=6%></td>
    <td width=6%></td>
    <td width=4%> </td>
    <td width=15%></td>
    <td width=5%> </td>
    <td width=15%></td>
  </tr>
 
  ";
  }
}

echo "</table>";
?>

</body>
</html>
