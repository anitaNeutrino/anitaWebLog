<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="generator" />
<body bgColor=#BBDDFF><font face="Arial">

</head>

<body>
<p> Click <a href="queryPage.html">here</a> to return to the query page</p>
<br>
Unless you want/need to fill another <a href="logForm.html">log form</a>
<br>
<br>
Any errors:<br>

<?php

$runNumber = $_POST["runNumber"];
#//$lastNumber = $_POST["lastNumber"];
$name = $_POST["name"];
$comment = $_POST["comment"];

//$thingsToWrite = "\n\nLog Form:
//Run: $runNumber
//Logged by: $logName
//Comments: 
//$comment\n";


if(!$runNumber){
  die('you must enter a run number!');
}

//now to make the file

#open the sql database
#$dbhost = "localhost";
#$dbuser = "anita";
#$dbpass = "AniTa08";
#$dbName = "runLog";

//pg_connect($dbhost,$dbuser,$dbpass) or die (pg_error());
//pg_select_db($dbName) or die(pg_error());
$link = mysqli_connect('localhost', 'anita', 'PoorPass') or die('Could not connect to server');
$db_selected = mysqli_select_db($link,'runLog');

$result = mysqli_query($link,"SELECT * FROM runTable WHERE run=".$runNumber);
$row = mysqli_fetch_array($result, MYSQLI_NUM);

if(!$row) die("there is no entry for run '$runNumber'");

#$logName = $row['logName'];
#$logComment = $row['logComment'];
$logName = $row[8];
$logComment = $row[9];

if($logComment){
  $newLoggedComments = $logComment." ".$comment;
  if(strcasecmp($logName,$name)!=0){
    $newLoggedNames = $logName.", ".$name;
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
mysqli_query($link,"UPDATE `runTable` SET `commenter`='$newLoggedNames' WHERE `run`='$runNumber'") or die("Update failed.");
$r2 = mysqli_query($link,"UPDATE `runTable` SET `comments`='$newLoggedComments' WHERE `run`='$runNumber'") or die("Update failed!");

?>

</body>
</html>
