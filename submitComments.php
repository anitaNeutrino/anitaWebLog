<?php
$runNumber = $_POST["runNumber"];
$name = $_POST["commentsBy"];
$comment = $_POST["comment"];

if(!is_numeric($runNumber)){
    die("Error! Run input must be numeric.");
}

//$name = mysql_real_escape_string($name);
//$comment = mysql_real_escape_string($comment);

if(strlen($name)==0){
    die('Error! Name input blank.');
}
if(strlen($comment)==0){
    die('Error! Comment input blank.');
}

$link = mysql_connect('localhost', 'anita', 'IceRadi0') or die('Error! Could not connect to server');
$db_selected = mysql_select_db('runLog', $link) or die('Error! Could not select database!');

$result = mysql_query("SELECT * FROM runTable WHERE run=".$runNumber);
$row = mysql_fetch_array($result, MYSQL_NUM);

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

$newLoggedComments = mysql_real_escape_string($newLoggedComments);

#finally submit this to the database
$r = mysql_query("UPDATE `runTable` SET `comments`='$newLoggedComments' WHERE `run`='$runNumber'") or die("Error! Could not complete SQL update.");
?>
