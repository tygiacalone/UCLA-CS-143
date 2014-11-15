<html>
	<head>
		<title>MovieDB: Add Actor/Director</title>
		<center><h1>MovieDB: Add Actor/Director</h1></center>
	</head>
	
		<center>
	<table border="0">
		<tr>
			<th BGCOLOR="yellow">
				<a href="addActorDirector.php">Add Actor or Director</a>
			</th>
			<th BGCOLOR="lightgrey">
				<a href="addMovieInfo.php">Add Movie Info</a>
			</th>
			<th BGCOLOR="lightgrey">
				<a href="addMovieComment.php">Add Movie Comment</a>
			</th>
			<th BGCOLOR="lightgrey">
				<a href="addMovieActor.php">Add Movie Actor</a>
			</th>
			<th BGCOLOR="lightgrey">
				<a href="addMovieDirector.php">Add Movie Director</a>
			</th>
			<th BGCOLOR="lightgrey">
				<a href="showActorInfo.php">Show Actor Info</a>
			</th>
			<th BGCOLOR="lightgrey">
				<a href="showMovieInfo.php">Show Movie Info</a>
			</th>
			<th BGCOLOR="lightgrey">
				<a href="search.php">Search</a>
			</th>
		</tr>
	</table>
	</center>
	
	<body BGCOLOR="#CCFFCC">
	<h4>Add new actor/director to database:</h4>
		<form action="./addActorDirector.php" method="GET">
			Type:	<input type="radio" name="type" value="Actor" <?php echo (htmlspecialchars($_GET['type'])=='Actor')?'checked':''?>>Actor
					<input type="radio" name="type" value="Director" <?php echo (htmlspecialchars($_GET['type'])=='Director')?'checked':''?>>Director<br/>
			First Name:	<input type="text" name="first" maxlength="20" value="<?php echo htmlspecialchars($_GET['first']);?>"><br/>
			Last Name:	<input type="text" name="last" maxlength="20" value="<?php echo htmlspecialchars($_GET['last']);?>"><br/>
			Sex:	<input type="radio" name="sex" value="Male" <?php echo (htmlspecialchars($_GET['sex'])=='Male')?'checked':''?>>Male
					<input type="radio" name="sex" value="Female" <?php echo (htmlspecialchars($_GET['sex'])=='Female')?'checked':''?>>Female<br/>
			Date of Birth:	<input type="text" name="dob" maxlength="10" value="<?php echo htmlspecialchars($_GET['dob']);?>"> (YYYY-MM-DD)<br/>
			Date of Death:	<input type="text" name="dod" maxlength="10" value="<?php echo htmlspecialchars($_GET['dod']);?>"> (YYYY-MM-DD, if applicable)<br/>
			<br/>
			<input type="submit" value="Add Person"/>
		</form>
		<hr/>

	<?php
		//establish connection with the MySQL database
		$db_connection = mysql_connect("localhost", "cs143", "");
		
		//choose database to use
		mysql_select_db("CS143", $db_connection);

		//get the user's inputs
		$dbType=trim($_GET["type"]);
		$dbFirst=trim($_GET["first"]);
		$dbLast=trim($_GET["last"]);
		$dbSex=trim($_GET["sex"]);
		$dbDOB=trim($_GET["dob"]);
		$dbDOD=trim($_GET["dod"]);
		
		$dateDOB = date_parse($dbDOB);
		$dateDOD = date_parse($dbDOD);
		
		//determine current maximum person ID and calculate the next
		$maxIDrs = mysql_query("SELECT MAX(id) FROM MaxPersonID", $db_connection) or die(mysql_error());
		$maxIDArray = mysql_fetch_array($maxIDrs);

		$maxID = $maxIDArray[0];
		$newMaxID = $maxID + 1;
		
		//pass in user inputs
		if($dbType=="" && $dbFirst=="" && $dbLast=="" && $dbSex=="" && $dbDOB=="" && $dbDOD=="") //everything is empty
		{
			//don't display a message, since no insert attempt was made (or the page just loaded)
		}
		else if($dbType=="")
		{
			echo "You must select either Actor or Director to input.";
		}
		else if($dbFirst=="" || $dbLast=="")
		{
			echo "You must enter a valid first and last name.";
		}
		else if(preg_match('/[^A-Za-z\s\'-]/', $dbFirst) || preg_match('/[^A-Za-z\s\'-]/', $dbLast))
		{
			echo "Only letters, spaces, single-quotes, and hyphens are allowed in the $dbType name.";
		}
		else if($dbType=='Actor' && $dbSex=="")
		{
			echo "You must specify the Actor's sex.";
		}
		else if($dbDOB=="" || !checkdate($dateDOB["month"], $dateDOB["day"], $dateDOB["year"]))
		{
			echo "You must specify a valid date of birth.";
		}
		else if($dbDOD!="" && !checkdate($dateDOD["month"], $dateDOD["day"], $dateDOD["year"]))
		{
			echo "If you specify a date of death, it must be valid.";
		}
		else //if we have reached this clause, no errors were found; process the query normally
		{
			//escape single-quotes in the inputs to make sure it doesn't break the string up
			$dbLast = mysql_escape_string($dbLast);
			$dbFirst = mysql_escape_string($dbFirst);
		
			if($dbType=="Actor")
			{
				if($dbDOD=="")
					$dbQuery = "INSERT INTO Actor (id, last, first, sex, dob, dod) VALUES('$newMaxID', '$dbLast', '$dbFirst', '$dbSex', '$dbDOB', NULL)";
				else
					$dbQuery = "INSERT INTO Actor (id, last, first, sex, dob, dod) VALUES('$newMaxID', '$dbLast', '$dbFirst', '$dbSex', '$dbDOB', '$dbDOD')";
			}
			else //Director
			{
				if($dbDOD=="")
					$dbQuery = "INSERT INTO Director (id, last, first, dob, dod) VALUES('$newMaxID', '$dbLast', '$dbFirst', '$dbDOB', NULL)";
				else
					$dbQuery = "INSERT INTO Director (id, last, first, dob, dod) VALUES('$newMaxID', '$dbLast', '$dbFirst', '$dbDOB', '$dbDOD')";
			}
			
			//issue a query using database connection
			//if query is erroneous, produce error message "gracefully"
			$rs = mysql_query($dbQuery, $db_connection) or die(mysql_error());
			
			//update the max person ID
			mysql_query("UPDATE MaxPersonID SET id=$newMaxID WHERE id=$maxID", $db_connection) or die(mysql_error());
			
			//present a success message`
			echo "New $dbType added (with id=$newMaxID).";
		}
		
		//close the database connection
		mysql_close($db_connection);
	?>


		
	</body>
</html>
