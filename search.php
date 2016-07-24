<!DOCTYPE html>
<html>
    <head>
	<meta charset="UTF-8">
	<title>ANITA-3 Calibration Run Log</title>
	<link rel="stylesheet" type="text/css" href="logStyle.css">
	<script src="//code.jquery.com/jquery-1.10.2.js"></script>
	<script src="/jquery/jquery-1.3.2.min.js"></script>
	<script>
	 $(document).ready(function() {
	     $(".commentSubmit").click(function(event){
		 // Use button id...
		 var buttonName = event.target.id;

		 // ...to get form name
		 var formSelectorName = '#' + buttonName.replace('Button', '');

		 // Select correct form and submit using jQuery AJAX interface
		 $.post('submitComments.php', $(formSelectorName).serialize()).done(function(data) {
		     if(data.length > 0){ // Something went wrong
			 alert(data);
		     }
		     else{ // Everything was fine so refresh page to show new database entry
			 window.location.reload(true);
		     }
		 });
	     });
	 });
	 
	</script>

	<script>
	 function btn_click(runNumber) {
	     var rowName = "commentRow" + runNumber;
	     $('#'+rowName).fadeIn("slow");
	     return false;
	 }
	 function btn_click2(runNumber) {
	     var rowName = "commentRow" + runNumber;
	     $('#'+rowName).fadeOut("slow");
	     return false;
	 }
	</script>

    </head>
    <div id="header_container">
	<div id="header">
	    <header>
		<h1>ANITA-3 Calibration Run Log</h1>
		<form action="search.php" method="post">
		    <p>Enter search information (leave all fields blank to select all).
			<!-- Fancy spam reducing email encoding -->
			Questions or comments? <a href="&#109;&#97;&#105;&#108;&#116;&#111;&#58;%62.%73%74%72%75%74%74.%31%32%40%75%63%6C.%61%63.%75%6B">Email me</a>.
		    </p>
		    <table cellpadding=0 cellspacing=0 border=0 width="600" align center>
			<tr>
			    <td rowspan="3">
				<table cellpadding=0 cellspacing=0 border=0 width="200" align left>

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
				<table cellpadding=0 cellspacing=0 border=0 width="200" align left>
				    <tr>
					<td>Run Description</td>
				    </tr>
				    <tr>
					<td><input name =runDesc size=22 value="<?php echo $_POST["runDesc"];?>"></td>
				    </tr>
				</table>
			    </td>

			    <td rowspan="3">
				<table cellpadding=0 cellspacing=0 border=0 width="200" align left>
				    <tr>
					<td>Shifter Name</td>
				    </tr>
				    <tr>
					<td><input name =theShifter size=22 value="<?php echo $_POST["theShifter"];?>"></td>
				    </tr>
				</table>
			    </td>
			    <td rowspan="3">
				<table cellpadding=0 cellspacing=0 border=0 width="200" align left>
				    <tr>
					<td>Comments</td>
				    </tr>
				    <tr>
					<td><input name =comments size=22 value="<?php echo $_POST["comments"];?>"></td>
				    </tr>
				</table>
			    </td>

			    <td rowspan="3">
				<table cellpadding=0 cellspacing=0 border=0 width="200" allign left>
				    <tr>
					<td>Location</td>
				    </tr>
				    <tr>
					<td>
					    <select name="location">
						<option value="All" <?php if($_POST["location"]=="All") echo 'selected="selected"'; ?>>All</option>
						<option value="Antarctica" <?php if($_POST["location"]=="Antarctica") echo 'selected="selected"'; ?>>Antarctica</option> 
						<option value="Palestine" <?php if($_POST["location"]=="Palestine") echo 'selected="selected"'; ?>>Palestine/Hawaii</option>
					    </select>
					</td>
				    </tr>
				</table>
			    </td>

			    <td rowspan="3">
				<table cellpadding=0 cellspacing=0 border=0 width="200" allign left>
				    <tr>
					<td>Order</td>
				    </tr>
				    <tr>
					<td>
					    <select name="order">
						<option value="descending" <?php if($_POST["order"]=="descending") echo 'selected="selected"'; ?>>Descending</option> 
						<option value="ascending"<?php if($_POST["order"]=="ascending") echo 'selected="selected"'; ?>>Ascending</option>
					    </select>
					</td>

				    </tr>
				</table>
			    </td>
		    </table>
		    <br/>
		    <input type="submit" value="Search">
		</form>
	    </header>
	</div>
    </div>

    <body>
	<?php
	error_reporting(E_ALL);
	$firstRun = $_POST["firstRun"];
	if(strlen($firstRun)){
	    if(!is_numeric($firstRun)){
		die("Error! Run input must be numeric.");
	    }
	}
	$lastRun = $_POST["lastRun"];
	if(strlen($lastRun)){
	    if(!is_numeric($lastRun)){
		die("Error! Run input must be numeric.");
	    }
	}


	$runDescSearch = $_POST["runDesc"];
	$commentsSearch = $_POST["comments"];
	$theShifter = $_POST["theShifter"];

	$order = $_POST["order"];
	$aloc = $_POST["location"];

	$dbhost = "localhost";
	$dbuser = "anita";
	$dbpass = "PoorPass";
	$dbName = "runLog";

	$selRunNumber = " ";
	$location = " ";
	$startTime = " ";
	$endTime = " ";
	$firstEvent = " ";
	$lastEvent = " ";
	$shifterName = " ";
	$runDescription = " ";
	$commentName = " ";
	$comment = " ";

	$tableName = "runTable";
	$dbhost = "localhost";
	$dbuser = "anita";
	$dbpass = "S0uthP0l";
	$dbName = "anita";


	#$link = mysqli_connect('localhost', 'anita', 'PoorPass') or die('Error! Could not connect to server.');
	#$link = mysqli_connect('localhost', 'anita', 's0uthPol3') or die('Error! Could not connect to server.');
	#$db_selected = mysqli_select_db($link,'runLog') or die('Error! Could not find database.');

	$link = mysqli_connect($dbhost, $dbuser, $dbpass) or die('Error! Could not connect to server');
	$db_selected = mysqli_select_db($link,$dbName) or die('Error! Could not connect to database');


	if(!$lastRun){
	    $lastRun = 9999999999; # For now...
	}
	if(!$firstRun){
	    $firstRun = 1;
	}
	if($firstRun > $lastRun){
	    die('Error! Make sure first run is earlier than last run.');
	}
	if($theShifter){
	    $findShifter=$theShifter;
	}

	$runsFound=0;
	$startRun=0;
	$endRun=0;
	$iterater=0;
	if($order=="ascending"){
	    $orderCommand = "ASC";
	    $startRun=$firstRun;
	    $endRun=$lastRun;
	    $iterator=1;
	}
	else{
	    $orderCommand = "DESC";
	    $startRun=$lastRun;
	    $endRun=$firstRun;
	    $iterator=-1;
	}


	$result = mysqli_query($link,"SELECT * FROM runTable WHERE run>=$firstRun AND run<=$lastRun ORDER BY run $orderCommand");

	while($row = mysqli_fetch_row($result)){
	    $runNumber = $row[0];
	    $location = $row[1];
	    $startTime = $row[2];
	    $endTime = $row[3];
	    $firstEvent = $row[4];
	    $lastEvent = $row[5];
	    $shifterName = $row[6];
	    $runDescription = $row[7];
	    $comment = $row[8];

	    # By default there's a match
	    $foundComment=1;

	    #If we have search terms, then allow rows to not be matched...

	    # Shifter name match?
	    if($theShifter){
		if( !((strpos(strtolower($shifterName), strtolower($theShifter)) !== FALSE)) ){
		    $foundComment=0;
		}
	    }

	    # Run description match?
	    if($runDescSearch){
		if( !((strpos(strtolower($runDescription), strtolower($runDescSearch)) !== FALSE)) ){
		    $foundComment=0;
		}
	    }

	    # Comments by match?
	    if($commentsSearch){
		if( !((strpos(strtolower($comment), strtolower($commentsSearch)) !== FALSE)) ){
		    $foundComment=0;
		}
	    }

	    # Location match?
	    if(!($location==$aloc || $aloc == "All")){
		$foundComment=0;
	    }

	    # Run description match?
	    if($runDescSearch){
		if( !((strpos(strtolower($runDescription), strtolower($runDescSearch)) !== FALSE)) ){
		    $foundComment=0;
		}
	    }


	    if(!$row){
	    }
	    else if($foundComment==1){
		$runsFound++;

		# Print header row of table if this is the first entry
		if($runsFound==1){
		    echo "<hr />";

		    echo "<table cellpadding=10 cellspacing=1 border=1 width=100%>
  <tr>
    <td width=4%>Run</td>
    <td width=5%>Loc.</td>
    <td width=10%>Start Time</td>
    <td width=10%>End Time</td>
    <td width=5%>Shifter</td>
    <td width=16%>Run Description</td>
    <td width=23%>Comments</td>
    <td width=5%>Add comment</td>
    <td width=6%>First Event</td>
    <td width=6%>Last Event</td>
  </tr>";
		}

		echo "
  <tr>
    <td> $runNumber</td>
    <td> $location</td>
    <td> $startTime</td>
    <td> $endTime</td>
    <td> $shifterName</td>
    <td> $runDescription</td>
    <td> $comment</td>
    <td> <button onclick=\"btn_click($runNumber);\"> Add Comment</button>
    <td> $firstEvent</td>
    <td> $lastEvent</td>
  </tr>";		
		echo "
  <tr id=\"commentRow$runNumber\" style=\"display: none\">
    <form id=\"commentForm$runNumber\"action=\"submitComments.php\" method=\"post\" onsubmit=\"return confirm('Really add comment to run $runNumber?')\">
      <td> <input name=\"runNumber\" value=\"$runNumber\" style=\"display: none\">Name:</td>
      <td> <input name=\"commentsBy\" style=\"display:table-cell; width:100%\">
      <td> Comment:</td>
      <td colspan=\"5\"> <input name=\"comment\" style=\"display:table-cell; width:100%\" length=\"9999\">
      <!-- <td> <input type=\"submit\" value=\"Submit\"> -->
      <td> <input id=\"commentFormButton$runNumber\" type=\"button\" value=\"Submit\" class=\"commentSubmit\">
    </form>
      <td> <button onclick=\"btn_click2($runNumber);\">Cancel</button>
  </tr>
";
	    }
	}
	if($runsFound==0){
	    echo "<p>Sorry. No runs found matching your search criteria.<p/>";
	}

	echo "</table>";
	?>
    </body>
</html>
