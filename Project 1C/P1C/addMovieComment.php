<html>
	<head>
		<title>MovieDB: Add Movie Comment</title>
		<center><h1>MovieDB: Add Movie Comment</h1></center>
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
			<th BGCOLOR="yellow">
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
	<h4>Add new comment to movie:</h4>
		<form action="./addMovieComment.php" method="GET">	

<?php
		//establish connection with the MySQL database
		$db_connection = mysql_connect("localhost", "cs143", "");
		
		//choose database to use
		mysql_select_db("CS143", $db_connection);

		//select all movie ids, titles, and years and place as options into dropdown
		$movieRS=mysql_query("SELECT id, title, year FROM Movie ORDER BY title ASC", $db_connection) or die(mysql_error());
		$movieOptions="";
		
		$urlID=$_GET['id'];
		
		while ($row=mysql_fetch_array($movieRS))
		{
			$id=$row["id"];
			$title=$row["title"];
			$year=$row["year"];
			
			//if movie ID matches the GET id specified in the URL, select that option by default
			if($id==$urlID)
				$movieOptions.="<option value=\"$id\" selected>".$title." [".$year."]</option>";
			else
				$movieOptions.="<option value=\"$id\">".$title." [".$year."]</option>";	
		}
?>		
		
			Movie:	<select name="id">
						<?=$movieOptions?>
					</select><br/>
			Your Name:	<input type="text" name="name" value="<?php echo htmlspecialchars($_GET['name']);?>" maxlength="20"><br/>
			Rating:	<select name="rating">
						<option value="5"> 5 out of 5 </option>
						<option value="4"> 4 out of 5 </option>
						<option value="3"> 3 out of 5 </option>
						<option value="2"> 2 out of 5 </option>
						<option value="1"> 1 out of 5 </option>
					</select><br/>
			Comments: <br/><textarea name="comment" cols="80" rows="10" value=><?php echo htmlspecialchars($_GET['comment']);?></textarea><br/>
			<br/>
			<input type="submit" value="Submit Comment"/>
		</form>
		<hr/>

	<?php
		//MySQL database connection is already established
		
		//get the user's inputs
		$dbName=trim($_GET["name"]);
		$dbMovie=$_GET["id"];
		$dbRating=$_GET["rating"];
		$dbComment=trim($_GET["comment"]);
		
		//pass in user inputs
		if($dbName=="" && $dbMovie=="" && $dbRating=="" && $dbComment=="")
		{
			//don't display a message, since no insert attempt was made (or the page just loaded)
		}
		else if($dbMovie=="")
		{
			echo "You must select a movie from the list.";
		}
		else if ($dbRating=="" || $dbRating>5 || $dbRating<1)
		{
			echo "You must select a valid rating.";
		}
		else //if we have reached this clause, no errors were found; process the query normally
		{
			//if reviewer left name blank, show as Anonymous
			if($dbName=="")
				$dbName = "Anonymous";
			
			//escape single-quotes in the inputs to make sure it doesn't break the string up
			$dbName = mysql_escape_string($dbName);
			$dbMovie = mysql_escape_string($dbMovie);
			$dbComment = mysql_escape_string($dbComment);
			
			$dbQuery = "INSERT INTO Review (name, time, mid, rating, comment) VALUES('$dbName', now(), '$dbMovie', '$dbRating', '$dbComment')";
						
			//issue a query using database connection
			//if query is erroneous, produce error message "gracefully"
			$rs = mysql_query($dbQuery, $db_connection) or die(mysql_error());
			
			//present a success message`
			echo "Thanks! Movie review added successfully.<br/>";
			echo "<a href=\"showMovieInfo.php?id=".$dbMovie."\">Back to Movie</a>";
		}
		
		//close the database connection
		mysql_close($db_connection);
	?>


		
	</body>
</html>