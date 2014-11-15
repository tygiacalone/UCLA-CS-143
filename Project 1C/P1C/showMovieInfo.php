<html>
	<head>
		<title>MovieDB: Movie Info</title>
		<center><h1>MovieDB: Movie Info</h1></center>
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
			<th BGCOLOR="yellow">
				<a href="showMovieInfo.php">Show Movie Info</a>
			</th>
			<th BGCOLOR="lightgrey">
				<a href="search.php">Search</a>
			</th>
		</tr>
	</table>
	</center>
	
	<body BGCOLOR="#CCFFCC">
	<h2>Movie Details</h2>
	<?php
		//establish connection with the MySQL database
		$db_connection = mysql_connect("localhost", "cs143", "");
		
		//choose database to use
		mysql_select_db("CS143", $db_connection);

		//get the user's inputs
		$dbID=trim($_GET["id"]);
		
		if($dbID=="")
		{
			echo "Invalid movie ID.";
			echo "<br/><br/>";
		}
		else //if we have reached this clause, no errors were found; process the query normally
		{
			$dbQuery = "SELECT title, year, rating, company FROM Movie WHERE id=$dbID";
			
			//issue a query using database connection
			//if query is erroneous, produce error message "gracefully"
			$rs = mysql_query($dbQuery, $db_connection) or die(mysql_error());
			
			//output movie info
			$row = mysql_fetch_row($rs);

			echo "<b>Title:</b> ".$row[0]." (".$row[1].")<br/>"; //movie title and year
			echo "<b>MPAA Rating:</b> ".$row[2]."<br/>"; //rating
			
			if($row[3]!="")
				echo "<b>Producer:</b> ".$row[3]."<br/>"; //producer
			else
				echo "<b>Producer:</b> N/A <br/>"; //no company
				
			//free up query results
			mysql_free_result($rs);
			
			echo "<b>Director(s):</b> ";
			
			//query for director info on movie
			$dbQuery2 = "SELECT D.last, D.first FROM MovieDirector MD, Director D WHERE MD.mid=$dbID AND MD.did=D.id";
			$rs2 = mysql_query($dbQuery2, $db_connection) or die(mysql_error());

			$firstInList=true;
			
			//print directors
			while($row2 = mysql_fetch_assoc($rs2))
			{
				if(!$firstInList)
					echo ", ";
				else
					$firstInList=false;
				echo $row2["first"]." ".$row2["last"];
			}
			
			if($firstInList) //no directors have been outputted
			{
				echo "N/A";
			}
			
			echo "<br/>";
			//free up query results
			mysql_free_result($rs2);

			echo "<b>Genre(s):</b> ";
			
			//query for genres on movie
			$dbQuery1 = "SELECT genre from MovieGenre WHERE mid=$dbID";
			$rs1 = mysql_query($dbQuery1, $db_connection) or die(mysql_error());

			$firstInList=true;
			
			while ($row1 = mysql_fetch_assoc($rs1))
			{
				if(!$firstInList)
					echo ", ";
				else
					$firstInList=false;
					
				echo $row1["genre"];
			}
			
			if($firstInList) //no directors have been outputted
			{
				echo "N/A";
			}
			
			//free up query results
			mysql_free_result($rs1);
			
			echo "<br/><br/>";

			echo "<hr>";
			echo "<h2>Related Actors</h2>";

			//query for movie info in no particular order
			$dbQuery3 = "SELECT MA.aid, MA.role, A.last, A. first FROM MovieActor MA, Actor A WHERE MA.mid=$dbID AND MA.aid=A.id";
			$rs3 = mysql_query($dbQuery3, $db_connection) or die(mysql_error());
			
			//print role and movie links
			while ($row3 = mysql_fetch_assoc($rs3))
			{
				$nameLink = "<a href=\"showActorInfo.php?id=".$row3["aid"]."\">".$row3["first"]." ".$row3["last"]."</a>";
				echo $nameLink." as ".$row3["role"]."<br/>";
			}
			
			echo "<br/>";
			
			//free up query results
			mysql_free_result($rs3);
		
			echo "<hr>";
			echo "<h2>User Reviews</h2>";
			echo "<b>Average Rating:</b>";
		
			//query for count and average rating
			$dbQuery4 = "SELECT AVG(rating), COUNT(rating) FROM Review WHERE mid=$dbID";
			$rs4 = mysql_query($dbQuery4, $db_connection) or die(mysql_error());
			$row4 = mysql_fetch_row($rs4);
			if($row4[0]=="")
			{
				echo " N/A<br/><br/>";
				echo "Be the first to <a href=\"addMovieComment.php?id=".$dbID."\">submit a review</a> now!<br/><br/>";
			}
			else
			{
				$formattedAvgRating = $row4[0] + 0;
				echo " $formattedAvgRating out of 5<br/>";
				echo "Reviewed $row4[1] times. <a href=\"addMovieComment.php?id=".$dbID."\">Add your comment</a> now!<br/><br/>";
			}
			
			//query for reviews with latest first
			$dbQuery4 = "SELECT time, name, rating, comment FROM Review WHERE mid=$dbID ORDER BY time DESC";
			$rs4 = mysql_query($dbQuery4, $db_connection) or die(mysql_error());
			
			//keep track of review number
			$count=mysql_num_rows($rs4);
			
			//print reviews
			while ($row4 = mysql_fetch_assoc($rs4))
			{
				echo "<b>Review #".$count."</b> written on ".$row4["time"]."<br/>";
				echo $row4["name"]."'s rating: ".$row4["rating"]."<br/>";
				echo "Comment: ".$row4["comment"]."<br/>";
				echo "<br/>";
				$count--;
			}
			
			echo "<br/>";
			
			//free up query results
			mysql_free_result($rs4);
		}

		//close the database connection
		mysql_close($db_connection);
?>
	
	</body>
</html>