<?php
error_reporting(E_ALL);
ini_set("display_errors",1);

$topic = $_GET['topic']; // Assuming 'topic' is passed via the query string
 $email = $_GET['email'];
$filter = $_GET['filter'];
$pagenum = $_GET['pagenum'];

error_log("Topic: $topic, Email: $email, Filter: $filter, PageNum: $pagenum");
$host = "localhost"; // The hostname of your database server
$username = "team011"; // The username of your database user
$password = "JAEWyfUXpzqank7scpWm"; // The password of your database user
$dbname = "team011"; // The name of your database

// Create a connection object using mysqli_connect function
$conn = mysqli_connect($host, $username, $password, $dbname);

// Check if the connection was successful or not
if (!$conn) {
    echo "UNSUCCESSFUL";
    die("Connection failed: " . mysqli_connect_error());
}

if ($email == "none") {
		$totalRowsSql = "SELECT COUNT(*) FROM Posts WHERE topic LIKE ?";
		$totalRowsStmt = $conn->prepare($totalRowsSql);
		$totalRowsStmt->bind_param("s", $topic);
		$totalRowsStmt->execute();
		$totalRowsResult = $totalRowsStmt->get_result()->fetch_row();
		$totalRows = $totalRowsResult[0]; // Fetch the total rows count	
}
else {
	$totalRowsSql = "SELECT COUNT(*) FROM Posts WHERE user_email LIKE ?";
		$totalRowsStmt = $conn->prepare($totalRowsSql);
		$totalRowsStmt->bind_param("s", $email);
		$totalRowsStmt->execute();
		$totalRowsResult = $totalRowsStmt->get_result()->fetch_row();
		$totalRows = $totalRowsResult[0]; // Fetch the total rows count	}
	}



$conn->close(); // Close the statement

$response = [
    'totalRows' => $totalRows // Include the total number of rows in the response
];

// Set header to indicate the response is JSON
header('Content-Type: application/json');

// Encode the array as JSON and output it
echo json_encode($response);
?>