<?php
	// Database conncetion
	$con = mysqli_connect("localhost","root","","it2910");
	// Check connection
	if (mysqli_connect_errno())
	{
		echo "Failed to connect to MySQL: " . mysqli_connect_error();
	}			  		
?>

<?php
	//The key for encrypting/decrypting data
	$key = md5('AzizAlmeqrin-InfoTc2910-FinalProject');
		
	//Function to give extra protection to input data
	function protect($string)
	{
		$string = mysql_real_escape_string(trim(strip_tags(addslashes($string))));
		return $string;
	}
	
	//Encryption function
	function encrypt($string, $key)
	{
		$string = rtrim(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $string, MCRYPT_MODE_ECB)));
		return $string;
	}
	
	//Decryption function
	function decrypt($string, $key)
	{
		$string = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, base64_decode($string), MCRYPT_MODE_ECB));
		return $string;
	}
	
	//Submit button
	if( isset($_POST['submit']) ) 
	{	//declaring variables to save the input data
		$cardName = protect($_POST['cardName']);
		$cardNumber = protect($_POST['cardNumber']);
		$cardCvv = protect($_POST['cardCvv']);
		$cardAddress = protect($_POST['cardAddress']);
		
		//Form validation and show error message
		if($cardName ==  NULL)
		{
			$message = 'Please enter a name';
			$color = 'red';
		}
		else
		{ 
			if($cardNumber ==  NULL)
			{
				$message = 'Please enter a card number';
				$color = 'red';
			}
			else
			{
				if($cardCvv ==  NULL)
				{
					$message = 'Please enter a cvv number';
					$color = 'red';
				}
				else
				{
					if($cardAddress ==  NULL)
					{
						$message = 'Please enter the address';
						$color = 'red';
					}
					else
					{ 	//MySqli transaction to insert data into multiple tables
						$sql =  mysqli_query($con, "INSERT INTO card (cardName, cardNumber, cardCvv, cardAddress)
						VALUES ('$cardName', '$cardNumber', '$cardCvv', '$cardAddress')");
						
						$cardID = mysqli_insert_id($con); //this function takes the last autoIncrement value, important for assigning IDs
										
						$sql =  mysqli_query($con, "INSERT INTO encrypted (ecardName, ecardNumber, ecardCvv, ecardAddress, id)
						VALUES ('".encrypt($cardName, $key)."', '".encrypt($cardNumber, $key)."', '".encrypt($cardCvv, $key)."', '".encrypt($cardAddress, $key)."', '$cardID')");
								
								
						// Commit transaction
						if($sql) //if success
						{
							mysqli_commit($con);
							$message = 'User is created';
							$color = 'green';
						}
						else // if failed
						{
							mysqli_rollback($con);
							$message = mysqli_error($con);
							$color = 'red';
						}
					}
				}
			}
		}
		//show success/failed message
		echo '<div class="'.$color.'">';
		echo $message;
		echo '</div>';
	}
	
	// Delete from database button
	if(isset($_POST['delete']))
	{	//MySqli transaction to empty data from multiple tables
		$sql =  mysqli_query($con, "TRUNCATE TABLE card");
		$sql =  mysqli_query($con, "TRUNCATE TABLE encrypted");
		
		if($sql) //if success
		{
			mysqli_commit($con);
			$message = 'Data is deleted from database';
			$color = 'green';
		}
		else //if failed
		{
			mysqli_rollback($con);
			$message = mysqli_error($con);
			$color = 'red';
		}
		//To show success/failed message
		echo '<div class="'.$color.'">';
		echo $message;
		echo '</div>';
	}
?>
<!DOCTYPE html>
<html>
	<head>
		<!-- Bootstrap, CSS, JavaScript, and jQuery implementation -->
		<link rel="stylesheet" type="text/css" media="all" href="js\themes\blue\style.css">
		<script type="text/javascript" src="js/jquery-latest.js"></script>
		<script type="text/javascript" src="js/jquery.tablesorter.js"></script>
		<link href="css/bootstrap.min.css" rel="stylesheet">
		<script>
			$(document).ready(function() //Sort tables
			{
				$("#table1").tablesorter(); 
				$("#table2").tablesorter();
				$("#table3").tablesorter();
			} 
			); 
		</script>

		<style>
			<!-- CSS to show success/error messages -->
			.red{
				border: 5px solid red;
			}
			.green{
				border: 5px solid lightgreen;
			}
		</style>
	</head>

	<body>
		<div align="center">
		<h2>Enter Credit Card Info:</h2>
		<br>
			<!-- Input Form -->
			<form action="<?php $_SERVER['PHP_SELF'] ?>" method="post">
				<table>
					<tr>
						<td>Name On the Card: </td>
						<td><input type="text" name="cardName" class="form-control" value="<?php if(isset($_POST['cardName'])) { echo $_POST['cardName']; }?>"></td>
					</tr>
					<tr>
						<td>Card Number: </td>
						<td><input type="number" class="form-control" max="9999999999999999" name="cardNumber" value="<?php if(isset($_POST['cardNumber'])) { echo $_POST['cardNumber']; }?>"></td>
					</tr>
					<tr>
						<td>CVV: </td>
						<td><input type="number" class="form-control" max="9999" name="cardCvv" value="<?php if(isset($_POST['cardCvv'])) { echo $_POST['cardCvv']; }?>"></td>
					</tr>
					<tr>
						<td>Address: </td>
						<td><input type="text"  class="form-control"name="cardAddress" value="<?php if(isset($_POST['cardAddress'])) { echo $_POST['cardAddress']; }?>"></td>
					</tr>
					<tr>
						<td></td>
						<td><input type="submit" value="Submit" name="submit" class="btn btn-success"></td>	
						<td><input type="submit" value="Delete Data" name="delete" id="delete" class="btn btn-success"></input></td>
					</tr>
				</table>			
			</form>
		</div>
		
		<!-- Show original data in table -->
		<h1 align="center">Original Data</h1>
		<div id="tableOrig">
			<table id="table1" class="tablesorter">
				<thead>
					<tr>
						<th>ID</th>
						<th>Name</th>
						<th>Number</th>
						<th>CVV</th>
						<th>Address</th>
					</tr>
				</thead>
				<tbody>
					<?php //get data from database
						$query = mysqli_query($con, "SELECT * FROM card");
						while($row = mysqli_fetch_array($query, MYSQLI_ASSOC))
						{ 
							echo '<tr>';
							echo '<td>'.$row['id'].'</td>';
							echo '<td>'.$row['cardName'].'</td>';
							echo '<td>'.$row['cardNumber'].'</td>';
							echo '<td>'.$row['cardCvv'].'</td>';
							echo '<td>'.$row['cardAddress'].'</td>';
							echo '</tr>';
						}
					?>
				</tbody>
			</table>
		</div>
		
		<!-- Show encrypted data in table -->
		<h1 align="center">Encrypted Data</h1>
		<div id="tableEncrypted">
			<table id="table2" class="tablesorter">
				<thead>
					<tr>
						<th>ID</th>
						<th>Name</th>
						<th>Number</th>
						<th>CVV</th>
						<th>Address</th>
					</tr>
				</thead>
				<tbody>
					<?php //get data from database
						$query = mysqli_query($con, "SELECT * FROM encrypted");
						while($row = mysqli_fetch_array($query, MYSQLI_ASSOC))
						{
							echo '<tr>';
							echo '<td>'.$row['id'].'</td>';
							echo '<td>'.$row['ecardName'].'</td>';
							echo '<td>'.$row['ecardNumber'].'</td>';
							echo '<td>'.$row['ecardCvv'].'</td>';
							echo '<td>'.$row['ecardAddress'].'</td>';
							echo '</tr>';
						}
					?>
				</tbody>
			</table>
		</div>
		
		<!-- Show decryptrd data in table -->
		<h1 align="center">Decrypted Data</h1>
		<div id="tableDEcrypted">
			<table id="table3" class="tablesorter">
				<thead>
					<tr>
						<th>ID</th>
						<th>Name</th>
						<th>Number</th>
						<th>CVV</th>
						<th>Address</th>
					</tr>
				</thead>
				<tbody>
					<?php //get data from database
						$query = mysqli_query($con, "SELECT * FROM encrypted");
						while($row = mysqli_fetch_array($query, MYSQLI_ASSOC))
						{
							echo '<tr>';
							echo '<td>'.$row['id'].'</td>';
							echo '<td>'.decrypt($row['ecardName'], $key).'</td>';
							echo '<td>'.decrypt($row['ecardNumber'], $key).'</td>';
							echo '<td>'.decrypt($row['ecardCvv'], $key).'</td>';
							echo '<td>'.decrypt($row['ecardAddress'], $key).'</td>';
							echo '</tr>';
						}
					?>
				</tbody>
			</table>
		</div>
	</body>
</html>