<html>
<head><title>Calculator</title></head>
<body>

<h1>Calculator</h1>
(Project 1A by Nathan Tung)<br />
Type an expression in the following box (e.g., 10.5+20*3/25).

<p>
	<form action="calculator.php" method="GET">
		<input type="text" name="expr"><input type="submit" value="Calculate">
	</form>
</p>

<ul>
<li>Only numbers and +,-,* and / operators are allowed in the expression.
<li>The evaluation follows the standard operator precedence.
<li>The calculator does not support parentheses.
<li>The calculator handles invalid input "gracefully". It does not output PHP error messages.
</ul>
Here are some (but not limit to) reasonable test cases:
<ol>
  <li> A basic arithmetic operation: 3+4*5=23 </li>
  <li> An expression with floating point or negative sign: -3.2+2*4-1/3 = 4.46666666667, 3+-2.1*2 = -1.2 </li>
  <li> Some typos inside operation (e.g. alphabetic letter): Invalid input expression 2d4+1 </li>
</ol>

<?php
	$equ=$_GET["expr"]; //get the equation from form
	$equ_nospace=str_replace(' ', '', $equ); //remove spaces in equation
	$valid=preg_match("/^[-+*.\/, 0-9]+$/",$equ);
	$divide_by_zero=preg_match("/\/[0]/",$equ);
	
	if($equ_nospace=="") //if no equation, show nothing
	{
	}
	elseif($divide_by_zero) //if equation attempts division by zero, show error gracefully
	{
		?>
			<h2>Result</h2>
		<?php
		echo "Division by zero error.";
	}
	elseif($valid) //if valid characters in equation, run it through eval
	{
		?>
			<h2>Result</h2>
		<?php
		
		$error=@eval("\$ans=$equ;");
		
		if($error===FALSE) //if eval shows an error, show error gracefully
		{
			echo "Invalid input expression " . $equ . ".";
		}
		else
		{
			echo $equ . " = " . $ans; //if eval didn't have an error, output the answer
		}
	}
	else //otherwise, show error gracefully (Result header and invalid input message)
	{
?>
		<h2>Result</h2>
<?php
		echo "Invalid input expression " . $equ . ".";
	}
?>
</body>
</html>

