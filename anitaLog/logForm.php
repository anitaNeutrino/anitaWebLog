<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="generator" />
<body bgColor=#BBDDFF><font face="Arial">
<meta HTTP-EQUIV="REFRESH" content="3; url=http://www.hep.ucl.ac.uk/~mottram/anitaLog/queryPage.php">
</head>

<body>
You will now be redirected back to the query page.  Please check your log entry!
<br>
Unless you want/need to fill another <a href="logForm.html">log form</a>
<br>
<br>
Any errors:<br>

<?php

$runNumber = $_POST["runNumber"];
$lastNumber = $_POST["lastNumber"];
$name = $_POST["name"];
$comment = $_POST["comment"];

$thingsToWrite = "\n\nLog Form:
Run: $runNumber
Logged by: $logName
Comments: 
$comment\n";


if(!$runNumber){
  die('you must enter a run number!');
}

//now to make the file

#open the sql database
$dbhost = "localhost";
$dbuser = "anita";
$dbpass = "AniTa08";
$dbName = "runLog";

pg_connect($dbhost,$dbuser,$dbpass) or die (pg_error());
pg_select_db($dbName) or die(pg_error());

$result = pg_query("SELECT * FROM runTable WHERE runNumber=$runNumber") or die(pg_error());

$row = pg_fetch_array($result);

if(!$row) die("there is no entry for run '$runNumber'");

$logName = $row['logName'];
$logComment = $row['logComment'];

if($logComment){
  $newLoggedComments = $logComment." ".$comment;
  if(strcasecmp($logName,$name)!=0){
    $newLoggedNames = $logName." ".$name;
  }
  else{
    $newLoggedNames = $logName;
  }
}
else{
  $newLoggedComments = $comment;
  $newLoggedNames = $name;
}

#$newLoggedNames = '';
#$newLoggedComments = '';


#finally submit this to the database
pg_query("UPDATE runTable SET logName='$newLoggedNames' WHERE runNumber='$runNumber'") or die(pg_error());
pg_query("UPDATE runTable SET logComment='$newLoggedComments' WHERE runNumber='$runNumber'") or die(pg_error());



?>

</body>
</html>
