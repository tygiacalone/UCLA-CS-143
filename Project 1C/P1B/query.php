<html>
<head><title>Movie/Actor Database Query</title></head>
<body>

<h1>Movie/Actor DB Query</h1>
(Project 1B by Nathan Tung)<br /><br />
Please type a MySQL SELECT Query into the box below:

<p>
	<form action="query.php" method="GET">
		<textarea name="query" cols="60" rows="8"><?php echo htmlspecialchars($_GET['query']);?></textarea>
		<input type="submit" value="Submit" />
	</form>
</p>

<p><small>Note: tables and fields are case sensitive. Run "show tables" to see the list of available tables.</small></p>

<?php
	//establish connection with the MySQL database
	$db_connection = mysql_connect("localhost", "cs143", "");
	
	//choose database to use
	mysql_select_db("CS143", $db_connection);

	//get the user's query from form TEXTAREA
	$dbQuery=$_GET["query"];
	
	//issue a query using database connection
	//if query is erroneous, produce error message "gracefully"
	$rs = mysql_query($dbQuery, $db_connection) or die(mysql_error());
	
	//initialize array to hold all query results
	$data = array();
?>

	<h3>Results from MySQL:</h3>

	<html><body><table border=1 cellspacing=1 cellpadding=2><tr align="center">
<?php
	
	//print out first row of header fields
	$i = 0;
	while($i < mysql_num_fields($rs))
	{
		//meta holds column information (name, description, etc.)
		$meta = mysql_fetch_field($rs, $i);
		//output column names along with bold HTML tags via echo
		echo '<td><b>' . $meta->name . '</b></td>';
		$i = $i + 1;
	}
?>
	<tr>
<?php
	
	//print out the actual query results
	$i = 0;
	while($row = mysql_fetch_row($rs))
	{
		echo '<tr align="center">';
		$count = count($row);
		$y = 0;
		while($y < $count)
		{
			$c_row = current($row);
			
			//if any value is NULL (blank), replace it with N/A
			if($c_row==NULL)
				echo '<td>N/A</td>';
			else
				echo '<td>' . $c_row . '</td>';
				
			next($row);
			$y = $y + 1;
		}
		echo '</tr>';
		$i = $i + 1;
	}
?>
	</table></body></html>
<?php
	
	//free up query results
	mysql_free_result($rs);
	
	//close the database connection
	mysql_close($db_connection);
?>
</body>
</html>

