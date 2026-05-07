<?php
	$servername = "localhost";
	$username = "u153769120_abutres";
	$password = "1OwysJd+k";
	$dbname = "u153769120_abutres";

	// Create connection
	$conn = new mysqli($servername, $username, $password, $dbname);

	// Check connection
	if ($conn->connect_error) {
	  die("Connection failed: " . $conn->connect_error);
	}
	//echo "Connected successfully";

	
?>