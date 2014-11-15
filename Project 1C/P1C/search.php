<html>
	<head>
		<title>MovieDB: Search</title>
		<center><h1>MovieDB: Search</h1></center>
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
			<th BGCOLOR="lightgrey">
				<a href="showActorInfo.php">Show Actor Info</a>
			</th>
			<th BGCOLOR="lightgrey">
				<a href="showMovieInfo.php">Show Movie Info</a>
			</th>
			<th BGCOLOR="yellow">
				<a href="search.php">Search</a>
			</th>
		</tr>
	</table>
	</center>
	
	<body>
	<h2>Search Actors and Movies</h2>

<p>
	<form action="search.php" method="GET">
	
		<input type="text" name="search"><?php $_GET["search"]; ?></input>		
		<input type="submit" value="Search" />
	</form>
</p>

<hr>

<body BGCOLOR="#CCFFCC">

	<?php
		//fetch user's search input
		$dbSearch = $_GET["search"];
		
		//escape single-quotes in the inputs to make sure it doesn't break the string up
		$dbSearch = mysql_escape_string($dbSearch);
		
		//break search terms up into terms array
		$terms=explode(' ', $dbSearch);

		//establish connection with the MySQL database
		$db_connection = mysql_connect("localhost", "cs143", "");
		
		//choose database to use
		mysql_select_db("CS143", $db_connection);
		
		if(trim($dbSearch)!="")
		{
			echo "<h2>Found Actors</h2>";
			
			//query actor search results
			$dbQuery = "SELECT id, last, first, dob FROM Actor WHERE (first LIKE '%$terms[0]%' OR last LIKE '%$terms[0]%')";
			
			//add more WHERE clauses to query for each tokenized term
			for($i=1; $i<count($terms); $i++)
			{
				$term=$terms[$i];
				$dbQuery=$dbQuery."AND (first LIKE '%$terms[$i]%' OR last LIKE '%$terms[$i]%')";
			}
			
			//issue a query using database connection
			//if query is erroneous, produce error message "gracefully"
			$rs = mysql_query($dbQuery, $db_connection) or die(mysql_error());
			
			//generate link output
			while ($row = mysql_fetch_assoc($rs))
			{
				echo "<a href=\"showActorInfo.php?id=".$row["id"]."\">".$row["first"]." ".$row["last"]." (".$row["dob"].")</a><br/>";
			}
			
			//free up query results
			mysql_free_result($rs);
			
			echo "<br/>";
			echo "<hr>";
			echo "<h2>Found Movies</h2>";
			
			//query movie search results
			$dbQuery2 = "SELECT id, title, year FROM Movie WHERE title LIKE '%$terms[0]%'";
			
			//add more WHERE clauses to query for each tokenized term
			for($i=1; $i<count($terms); $i++)
			{
				$term=$terms[$i];
				$dbQuery2=$dbQuery2." AND title LIKE '%$terms[$i]%'";
			}
			
			//issue a query using database connection
			//if query is erroneous, produce error message "gracefully"
			$rs2 = mysql_query($dbQuery2, $db_connection) or die(mysql_error());

			//generate link output
			while ($row2 = mysql_fetch_assoc($rs2))
			{
				echo "<a href=\"showMovieInfo.php?id=".$row2["id"]."\">".$row2["title"]." (".$row2["year"].")</a><br/>";
			}
			
			echo "<br/>";
			
			//free up query results
			mysql_free_result($rs2);
		}
		
	?>	
	
	</body>
</html>