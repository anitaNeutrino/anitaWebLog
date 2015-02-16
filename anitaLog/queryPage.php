<!DOCTYPE html>
<html>
    <head>
	<meta charset="UTF-8">
	<title>ANITA-3 Calibration Run Log</title>
	<link rel="stylesheet" type="text/css" href="logStyle.css">
    </head>

    <div id="header_container">
	<div id="header">
	    <header>
		<h1>ANITA-3 Calibration Run Log</h1>
		<form action="queryPage.php" method="post">
		    <p>Enter search information (leave all fields blank to select all).
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
					<td>Comments By</td>
				    </tr>
				    <tr>
					<td><input name =commentsBy size=22 value="<?php echo $_POST["commentsBy"];?>"></td>
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
	$lastRun = $_POST["lastRun"];
	$runDescSearch = $_POST["runDesc"];
	$commentsBySearch = $_POST["commentsBy"];
	$commentsSearch = $_POST["comments"];
	$theShifter = $_POST["theShifter"];

	$order = $_POST["order"];
	$aloc = $_POST["location"];

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
	$commentName = " ";
	$comment = " ";

	// Make connection to sql database
	$link = mysql_connect('localhost', 'anita', 'IceRadi0') or die('Error! Could not connect to server.');
	$db_selected = mysql_select_db('runLog', $link) or die('Error! Could not find database.');

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

	$result = mysql_query("SELECT * FROM runTable WHERE run>=$firstRun AND run<=$lastRun ORDER BY run $orderCommand");
	while($row = mysql_fetch_assoc($result, MYSQL_NUM)){
	    $runNumber = $row[0];
	    $location = $row[1];
	    $startTime = $row[2];
	    $endTime = $row[3];
	    $firstEvent = $row[4];
	    $lastEvent = $row[5];
	    $shifterName = $row[6];
	    $runDescription = $row[7];
	    $commentName = $row[8];
	    $comment = $row[9];

	    # By default there's a match
	    $foundComment=1;

	    #If we have search terms, then allow rows to not be matched...

	    # Shifter name match?
	    if($theShifter){
		if( !((strpos($shifterName, $theShifter) !== FALSE)) ){
		    $foundComment=0;
		}
	    }

	    # Run description match?
	    if($runDescSearch){
		if( !((strpos($runDescription, $runDescSearch) !== FALSE)) ){
		    $foundComment=0;
		}
	    }

	    # Comments by match?
	    if($commentsBySearch){
		if( !((strpos($commentName, $commentsBySearch) !== FALSE)) ){
		    $foundComment=0;
		}
	    }

	    # Comments by match?
	    if($commentsSearch){
		if( !((strpos($comment, $commentsSearch) !== FALSE)) ){
		    $foundComment=0;
		}
	    }


	    # Location match?
	    if(!($location==$aloc || $aloc == "All")){
		$foundComment=0;
	    }

	    # Run description match?
	    if($runDescSearch){
		if( !((strpos($runDescription, $runDescSearch) !== FALSE)) ){
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
    <td width=3%>Run</td>
    <td width=5%>Loc.</td>
    <td width=12%>Start Time</td>
    <td width=12%>End Time</td>
    <td width=4%>Shifter</td>
    <td width=15%>Run Description</td>
    <td width=5%>Comments By</td>
    <td width=15%>Comments</td>
    <td width=6%>First Event</td>
    <td width=6%>Last Event</td>
  </tr>";
		}

		echo "
  <tr>
    <td width=3%>$runNumber</td>
    <td width=3%> $location</td>
    <td width=12%> $startTime</td>
    <td width=12%> $endTime</td>
    <td width=4%> $shifterName</td>
    <td width=15%> $runDescription</td>
    <td width=5%> $commentName</td>
    <td width=15%> $comment</td>
    <td width=6%> $firstEvent</td>
    <td width=6%> $lastEvent</td>

  </tr>";
	    }
	}
	if($runsFound==0){
	    echo "<p>Sorry. No runs found matching your search criteria.<p/>";
	}

	echo "</table>";
	?>

    </body>
</html>
