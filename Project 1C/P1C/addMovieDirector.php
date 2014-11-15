<html>
	<head>
		<title>MovieDB: Add Director to Movie</title>
		<center><h1>MovieDB: Add Director to Movie</h1></center>
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
			<th BGCOLOR="yellow">
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
	<h4>Add existing director to movie:</h4>
		<form action="./addMovieDirector.php" method="GET">	

<?php
		//establish connection with the MySQL database
		$db_connection = mysql_connect("localhost", "cs143", "");
		
		//choose database to use
		mysql_select_db("CS143", $db_connection);

		//select all movie ids, titles, and years and place as options into dropdown
		$movieRS=mysql_query("SELECT id, title, year FROM Movie ORDER BY title ASC", $db_connection) or die(mysql_error());
		$movieOptions="";
		while ($row=mysql_fetch_array($movieRS))
		{
			$id=$row["id"];
			$title=$row["title"];
			$year=$row["year"];
			$movieOptions.="<option value=\"$id\">".$title." [".$year."]</option>";
		}
		
		//select all movie ids, titles, and years and place as options into dropdown
		$directorRS=mysql_query("SELECT id, first, last, dob FROM Director ORDER BY first ASC", $db_connection) or die(mysql_error());
		$directorOptions="";
		while ($row=mysql_fetch_array($directorRS))
		{
			$id=$row["id"];
			$first=$row["first"];
			$last=$row["last"];
			$dob=$row["dob"];
			$directorOptions.="<option value=\"$id\">".$first." ".$last." [".$dob."]</option>";
		}
		
		//free up query results
		mysql_free_result($directorRS);
		
?>		
			Movie:	<select name="mid">
						<?=$movieOptions?>
					</select><br/>
			Director:	<select name="did">
						<?=$directorOptions?>
					</select><br/>
			<br/>
			<input type="submit" value="Link Director to Movie"/>
		</form>
		<hr/>

	<?php
		//MySQL database connection is already established
		
		//get the user's inputs
		$dbRole=trim($_GET["role"]);
		$dbMovie=$_GET["mid"];
		$dbDirector=$_GET["did"];
		
		//pass in user inputs
		if($dbMovie=="" && $dbDirector=="" && $dbRole=="")
		{
			//don't display a message, since no insert attempt was made (or the page just loaded)
		}
		else if($dbMovie=="")
		{
			echo "You must select a movie from the list.";
		}
		else if($dbDirector=="")
		{
			echo "You must select a director from the list.";
		}
		else //if we have reached this clause, no errors were found; process the query normally
		{
			//escape single-quotes in the inputs to make sure it doesn't break the string up
			$dbMovie = mysql_escape_string($dbMovie);
			$dbDirector = mysql_escape_string($dbDirector);
			
			$dbQuery = "INSERT INTO MovieDirector (mid, did) VALUES('$dbMovie', '$dbDirector')";
						
			//issue a query using database connection
			//if query is erroneous, produce error message "gracefully"
			$rs = mysql_query($dbQuery, $db_connection) or die(mysql_error());
			
			//present a success message`
			echo "Director linked with Movie successfully.";
		}
		
		//close the database connection
		mysql_close($db_connection);
	?>


		
	</body>
</html>