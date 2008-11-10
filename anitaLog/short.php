<body><html>
Click to go to the <a href="short.html">Next Short Form</a>
<br>
<?php

$adjustUTC=mktime(date("H")+8,date("i"),date("s"),date("m"),date("d"),date("y"));
$printtime=date("m/d/y, H:i:s",$adjustUTC);
$filetime=date("mdHis",$adjustUTC);
echo "Filename will be short$filetime.txt";

$smallfilename = "logs/short$filetime.txt";
$medfilename="logs/shiftCurrent.txt";

$thingstowrite="\n\nSHORT FORM:
Current UTC Time: $printtime
Name: $Shifter
Date: $Date
Time: $Time
Payload Time Offset: $payloadOff
PV Voltage (V): $pvvolt
PV Current (A): $pvcurr
+24 V Battery Voltage (V): $battvolt
Battery Current (A): $battcurr
Event Rate (Hz): $eventRate
CPU Ramdisk Status: $cpu
Number Events in Archived (Status): $noEventsArchived
HSK Data Current? $hsk
Comment: $hskComment
Is Data Flowing? $dataFlow
Comment: $dataFlowComment
TDRSS Status: $tdrss
SloMo Status: $slomo
Surf HK Scaler Rates (KHz): $scaler
ADDITIONAL COMMENTS:
$Comment\n";



// Let's see if the small file exists and make it if it isn't!.
$handleCreate = fopen($smallfilename, 'w') or die("can't open file");
fclose($handleCreate);
system("chmod a+w $smallfilename");

if (is_writable($smallfilename)) {
  if (!$handle = fopen($smallfilename, 'w')) {//change 'w' to 'a' to append file
    echo "Cannot open file ($smallfilename)";
    exit;
  }
  // Write $thingstowrite
  if (fwrite($handle, $thingstowrite) === FALSE) {
    echo "Cannot write to file ($smallfilename)";
    exit;
  }
  fclose($handle);  

  $myFile="$smallfilename";
  $fh=fopen($myFile,'r');
  $theData=fread($fh,filesize($myFile));
  fclose($fh);
  echo nl2br($theData);
  echo "<br>You can get the text file here: <a href='logs/short$filetime.txt'>Text File</a><br>";


 } else {
  echo "The file $smallfilename is not writable";
 }


 if ($First) {
   $handleCreatemed=fopen($medfilename,'w') or die("can't open file");
   fclose($handleCreatemed);
   system("chmod a+w $medfilename");
  }
  if (!$First) {
   $handleCreatemed=fopen($medfilename,'a') or die("can't open file");
   fclose($handleCreatemed);
   system("chmod a+w $medfilename");
  }

  if (is_writable($medfilename)) {

   if ($First) { // creating the file if it's the first from on shift
    if (!$medhandle=fopen($medfilename,'w')) {
     echo "Cannot open file ($medfilename)";
     exit;
    }
   }
   if (!$First) { // appending to the file if it's not the first
    if (!$medhandle=fopen($medfilename,'a')) {
     echo "Cannot open file ($medfilename)";
     exit;
    }
   }
    // Write $thingstowrite
    if (fwrite($medhandle,$thingstowrite)==FALSE) {
     echo "Cannot write to file ($medfilename)";
     exit;
    }
   
   fclose($medhandle);
  }
  else {
   echo "The file $medfilename is not writeable";
  }

 if ($Last) { 
  echo "<br><b>You can get a summary file of your shift here:</b> <a
href='logs/shiftCurrent.txt'>Summary Text File</a>.<br>(You can submit this
with your summary to the elog).<br>";
 system("cp logs/shiftCurrent.txt logs/shiftSummary$filetime.txt");
system("chmod a+w logs/shiftSummary$filetime.txt");
 }



?> 
Click to go to the <a href="short.html">Next Short Form</a>
</body></html>

