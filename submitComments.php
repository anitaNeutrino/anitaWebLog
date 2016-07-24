<?php
$runNumber = $_POST["runNumber"];
$name = $_POST["commentsBy"];
$comment = $_POST["comment"];

if(!is_numeric($runNumber)){
    die("Error! Run input must be numeric.");
}

//$name = mysqli_real_escape_string($name);
//$comment = mysqli_real_escape_string($comment);

if(strlen($name)==0){
    die('Error! Name input blank.');
}
if(strlen($comment)==0){
    die('Error! Comment input blank.');
}

$tableName = "runTable";
$dbhost = "localhost";
$dbuser = "anita";
$dbpass = "S0uthP0l3";
$dbName = "anita";


$link = mysqli_connect($dbhost, $dbuser, $dbpass) or die('Error! Could not connect to server');
$db_selected = mysqli_select_db($link,$dbName) or die('Error! Could not connect to database');


$result = mysqli_query($link,"SELECT * FROM runTable WHERE run=".$runNumber);
$row = mysqli_fetch_array($result, MYSQLI_NUM);

// Shouldn't get this one
if(!$row) die("Error! There is no entry for run $runNumber");

$logComment = $row[8];

if($logComment){
    $newLoggedComments = str_replace("</table>", "<tr><td>", $logComment);
    $newLoggedComments = $newLoggedComments.$name."</td><td>".$comment."</td></tr></table>";
}
else{
    $newLoggedComments = "<table style=\"table-layout: fixed; word-wrap:break-word; border-spacing:0.5em;\" cellspacing=1 width=100%><tr><td width=25%>".$name."</td><td width=75%>".$comment."</td></tr></table>";
}

$newLoggedComments = mysqli_real_escape_string($newLoggedComments);

#finally submit this to the database
$r = mysqli_query($link,"UPDATE `runTable` SET `comments`='$newLoggedComments' WHERE `run`='$runNumber'") or die("Error! Could not complete SQL update.");
?>
