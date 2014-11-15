<html>
	<head>
		<title>MovieDB: Actor Info</title>
		<center><h1>MovieDB: Actor Info</h1></center>
	</head>	
	
	<center>
	<table border="0">
		<tr>
			<th BGCOLOR="lightgrey">
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
			<th BGCOLOR="yellow">
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
	<h2>Actor Details</h2>
	<?php
		//establish connection with the MySQL database
		$db_connection = mysql_connect("localhost", "cs143", "");
		
		//choose database to use
		mysql_select_db("CS143", $db_connection);

		//get the user's inputs
		$dbID=trim($_GET["id"]);
		
		if($dbID=="")
		{
			echo "Invalid actor ID.";
			echo "<br/><br/>";
		}
		else //if we have reached this clause, no errors were found; process the query normally
		{
			$dbQuery = "SELECT last, first, sex, dob, dod FROM Actor WHERE id=$dbID";
			
			//issue a query using database connection
			//if query is erroneous, produce error message "gracefully"
			$rs = mysql_query($dbQuery, $db_connection) or die(mysql_error());
			
			//output actor info
			$row = mysql_fetch_row($rs);

			echo "<b>Name:</b> ".$row[1]." ".$row[0]."<br/>"; //first and last name
			echo "<b>Sex:</b> ".$row[2]."<br/>"; //sex
			echo "<b>Date of Birth:</b> ".$row[3]."<br/>"; //dob
			
			if($row[4]!="")
				echo "<b>Date of Death:</b> ".$row[4]."<br/><br/>"; //dod
			else
				echo "<b>Date of Death:</b> N/A <br/><br/>"; //dod
				
			
			//free up query results
			mysql_free_result($rs);
			
			//set up movies header
			echo "<hr>";
			echo "<h2>Movies Involved</h2>";
	
			//this second query is for matching movies that correspond with selected actor
			//with newest movies first
			$dbQuery2 = "SELECT MA.role, M.title, M.year, M.id FROM MovieActor MA, Movie M WHERE MA.aid=$dbID AND MA.mid=M.id ORDER BY M.year DESC";
			$rs2 = mysql_query($dbQuery2, $db_connection) or die(mysql_error());
			
			//print movie links
			while ($row2 = mysql_fetch_assoc($rs2))
			{
				$titleLink = "<a href=\"showMovieInfo.php?id=".$row2["id"]."\">".$row2["title"]." (".$row2["year"].")</a>";
				echo "\"".$row2["role"]."\" in ".$titleLink."<br/>";
			}
			
			echo "<br/>";
			
			//free up query results
			mysql_free_result($rs2);
		}

		//close the database connection
		mysql_close($db_connection);
		
	?>
	</body>
</html>