<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);



$host = "localhost";
$username = "team011";
$password = "JAEWyfUXpzqank7scpWm";
$dbname = "team011";

$conn = mysqli_connect($host, $username, $password, $dbname);

if (!$conn) {
    die(json_encode(['error' => 'Connection failed: ' . mysqli_connect_error()]));
}

$sql = "SELECT Topic_ID FROM Topics ";

$result = mysqli_query($conn, $sql);
$allData=array();

// Check if the query returned any rows or not
if (mysqli_num_rows($result) > 0) {
  // Loop through the result object and fetch each row as an associative array
  while($row = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
    // Display the data from each row using the column names as keys
   	$allData[]=$row["Topic_ID"];  
	}
}
// Close the connection
echo json_encode($allData);
mysqli_close($conn);
?>