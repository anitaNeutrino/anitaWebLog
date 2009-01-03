<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="generator" />
<body bgColor=#BBDDFF><font face="Arial">

<title>UPDATE THE STUFF!</title>

</head>
<body>
hello
<br>


<?php

$newline = "<br>";

#open the sql database
$dbhost = "localhost";
$dbuser = "anita";
$dbpass = "AniTa08";
$dbname = "runLog";

$conn = pg_connect($dbhost,$dbuser,$dbpass) or die (pg_error());

pg_select_db($dbname) or die(pg_error());

#remember to update the text file
#$filename = "/home/mottram/work/anitaLog/runLog/runLog.txt";
#$filename = "/unix/www/users/mottram/public_html/anitaLog/runLog/runLog.txt";
$filename = "runLog.txt";

$filehandle = fopen ($filename,"r") or die(pg_error());

while (!feof($filehandle)){
  $theData = fgets($filehandle);
  $theArray = explode("\t",$theData);
  #echo $newline." ".$theArray[0];

  $theArray[7]=str_replace("'","",$theArray[7]);

#the table is to be called runTable
  $query = sprintf("SELECT * FROM runTable WHERE runNumber='$theArray[0]'");
  
  $result = pg_query($query);
  
  if(!$result){
    die('invalid query');
  }
  $foundEntry=0;

  if($row = pg_fetch_array($result)){
    $foundEntry++;
    #echo "oh dear ".$theArray[0];
#the query exists then all we need to do is check for the root file ... if this exists IN BOTH THE DATABASE AND THE TEXT FILE also then can pass onto next entry
    if($row['startTime']=="NO ROOT FILE")
      {
	if($theArray[2]=="NO ROOT FILE")
	  {#do nothing
	     }	
	else
	  {
#this means a root file has been found, but nothing has been entered into the database so enter relevent info into the database
	    pg_query("UPDATE runTable SET startTime='$array[2]' WHERE runNumber='$array[0]'") or die('error updating starttime');
	    pg_query("UPDATE runTable SET endTime='$array[3]' WHERE runNumber='$array[0]'") or die('error updating endtime');
	    pg_query("UPDATE runTable SET firstEvent='$array[4]' WHERE runNumber='$array[0]'") or die('error updating firstevent');
	    pg_query("UPDATE runTable SET lastEvent='$array[5]' WHERE runNumber='$array[0]'") or die('error updating lastevent');
	  }
      }
    else
      {#root file data has already been entered
	 }
  }
  else{
  //if($foundEntry<3){
    echo "new entry: ".$theArray[0]." ".$theArray[6]." ".$theArray[7].$newline;
    {#this means there is entry in the database yet, set all values
       pg_query("INSERT INTO runTable (id,runNumber,location,startTime,endTime,firstEvent,lastEvent,shifterName,runDescription) VALUES('NULL','$theArray[0]','$theArray[1]','$theArray[2]','$theArray[3]','$theArray[4]','$theArray[5]','$theArray[6]','$theArray[7]')") or die(pg_error());
    }  
  }
}
fclose($filehandle);

pg_close($conn);

?>

</body>
</html>
