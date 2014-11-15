<html>
	<head>
		<title>MovieDB: Add Movie Info</title>
		<center><h1>MovieDB: Add Movie Info</h1></center>
	</head>	
	
		<center>
	<table border="0">
		<tr>
			<th BGCOLOR="lightgrey">
				<a href="addActorDirector.php">Add Actor or Director</a>
			</th>
			<th BGCOLOR="yellow">
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
	<h4>Add new movie to database:</h4>
		<form action="./addMovieInfo.php" method="GET">			
			Title : <input type="text" name="title" maxlength="20" value="<?php echo htmlspecialchars($_GET['title']);?>"><br/>
			Company: <input type="text" name="company" maxlength="50" value="<?php echo htmlspecialchars($_GET['company']);?>"><br/>
			Year : <input type="text" name="year" maxlength="4" value="<?php echo htmlspecialchars($_GET['year']);?>"><br/>	<!-- Todo: validation-->	
			MPAA Rating : <select name="mpaarating">
				<option value="G" <?php echo (htmlspecialchars($_GET['mpaarating'])=='G')?'selected':''?>>G</option>
				<option value="NC-17" <?php echo (htmlspecialchars($_GET['mpaarating'])=='NC-17')?'selected':''?>>NC-17</option>
				<option value="PG" <?php echo (htmlspecialchars($_GET['mpaarating'])=='PG')?'selected':''?>>PG</option>
				<option value="PG-13" <?php echo (htmlspecialchars($_GET['mpaarating'])=='PG-13')?'selected':''?>>PG-13</option>
				<option value="R" <?php echo (htmlspecialchars($_GET['mpaarating'])=='R')?'selected':''?>>R</option>
				<option value="surrendere" <?php echo (htmlspecialchars($_GET['mpaarating'])=='surrendere')?'selected':''?>>surrendere</option>
			</select><br/>
			Genre :
			<table border="0" style="width:600px">
				<tr>
					<td><input type="checkbox" name="genre[]" value="Action">Action</input></td>
					<td><input type="checkbox" name="genre[]" value="Adult">Adult</input></td>
					<td><input type="checkbox" name="genre[]" value="Adventure">Adventure</input></td>
					<td><input type="checkbox" name="genre[]" value="Animation">Animation</input></td>
					<td><input type="checkbox" name="genre[]" value="Comedy">Comedy</input></td>
				</tr>
				<tr>
					<td><input type="checkbox" name="genre[]" value="Crime">Crime</input></td>
					<td><input type="checkbox" name="genre[]" value="Documentary">Documentary</input</td>
					<td><input type="checkbox" name="genre[]" value="Drama">Drama</input></td>
					<td><input type="checkbox" name="genre[]" value="Family">Family</input></td>
					<td><input type="checkbox" name="genre[]" value="Fantasy">Fantasy</input></td>
				</tr>
				<tr>
					<td><input type="checkbox" name="genre[]" value="Horror">Horror</input></td>
					<td><input type="checkbox" name="genre[]" value="Musical">Musical</input></td>
					<td><input type="checkbox" name="genre[]" value="Mystery">Mystery</input></td>
					<td><input type="checkbox" name="genre[]" value="Romance">Romance</input></td>
					<td><input type="checkbox" name="genre[]" value="Sci-Fi">Sci-Fi</input></td>
				</tr>
				<tr>
					<td><input type="checkbox" name="genre[]" value="Short">Short</input></td>
					<td><input type="checkbox" name="genre[]" value="Thriller">Thriller</input></td>
					<td><input type="checkbox" name="genre[]" value="War">War</input></td>
					<td><input type="checkbox" name="genre[]" value="Western">Western</input></td>
				</tr>
			</table> 

			<br/>
			<input type="submit" value="Add Movie"/>
		</form>
		<hr/>

	<?php
		//establish connection with the MySQL database
		$db_connection = mysql_connect("localhost", "cs143", "");
		
		//choose database to use
		mysql_select_db("CS143", $db_connection);

		//get the user's inputs
		$dbTitle=trim($_GET["title"]);
		$dbCompany=trim($_GET["company"]);
		$dbYear=$_GET["year"];
		$dbRating=$_GET["mpaarating"];
		$dbGenre=$_GET["genre"];
		
		//determine current maximum movie ID and calculate the next
		$maxIDrs = mysql_query("SELECT MAX(id) FROM MaxMovieID", $db_connection) or die(mysql_error());
		$maxIDArray = mysql_fetch_array($maxIDrs);

		$maxID = $maxIDArray[0];
		$newMaxID = $maxID + 1;
		
		//pass in user inputs
		if($dbTitle=="" && $dbCompany=="" && $dbYear=="")
		{
			//don't display a message, since no insert attempt was made (or the page just loaded)
		}
		else if ($dbTitle=="")
		{
			echo "You must enter a valid movie title.";

		}
		else if($dbYear=="" || $dbYear<=1800 || $dbYear>=2100)
		{
			echo "You must enter a valid movie production year.";
		}
		
		else //if we have reached this clause, no errors were found; process the query normally
		{
			//escape single-quotes in the inputs to make sure it doesn't break the string up
			$dbTitle = mysql_escape_string($dbTitle);
			$dbCompany = mysql_escape_string($dbCompany);
		
			if($dbCompany=="")
				$dbQuery = "INSERT INTO Movie (id, title, year, rating, company) VALUES('$newMaxID', '$dbTitle', '$dbYear', '$dbRating', '$dbCompany')";
			else
				$dbQuery = "INSERT INTO Movie (id, title, year, rating, company) VALUES('$newMaxID', '$dbTitle', '$dbYear', '$dbRating', '$dbCompany')";
			
			//issue a query using database connection
			//if query is erroneous, produce error message "gracefully"
			$rs = mysql_query($dbQuery, $db_connection) or die(mysql_error());
			
			//update the max movie ID
			mysql_query("UPDATE MaxMovieID SET id=$newMaxID WHERE id=$maxID", $db_connection) or die(mysql_error());
			
			for($i=0; $i < count($dbGenre); $i++)
			{
				$genreQuery = "INSERT INTO MovieGenre (mid, genre) VALUES ('$newMaxID', '$dbGenre[$i]')";
				$genreRS = mysql_query($genreQuery, $db_connection) or die(mysql_error());
			}
			
			//present a success message`
			echo "New movie added (with id=$newMaxID).";
		}
		
		//close the database connection
		mysql_close($db_connection);
	?>


		
	</body>
</html>