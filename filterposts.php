<?php
$topic = $_GET['topic']; // Assuming 'topic' is passed via the query string
$filter = $_GET['filter'];

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

if ($filter == "recent") {
	$sql = "SELECT Topic_ID, content, user_email, title FROM Posts WHERE topic LIKE ?";

	$stmt = $conn->prepare($sql);

	// Check if the statement was prepared correctly
	if (!$stmt) {
    		echo "Error preparing statement: " . $conn->error;
    		exit;
		}

	// Correctly use $topic instead of $topicname
	$stmt->bind_param("s", $topic);

	$stmt->execute(); // Execute the statement

	$result = $stmt->get_result();  // Get the result
	$postsArray = []; // Initialize an array to hold the fetched data
} else if($filter == "liked") {
	$sql = "SELECT Topic_ID, content, user_email, title FROM Posts WHERE user_email LIKE ?";

	$stmt = $conn->prepare($sql); 

	// Check if the statement was prepared correctly
	if (!$stmt) {
    		echo "Error preparing statement: " . $conn->error;
    		exit;
		}

	$stmt->bind_param("s", $email);

	$stmt->execute(); // Execute the statement 

	$result = $stmt->get_result();  // Get the result
	$postsArray = []; // Initialize an array to hold the fetched data
}


// Fetch the data
while ($row = $result->fetch_assoc()) {
    $postsArray[] = $row; // Add each row to the array
}

$stmt->close(); // Close the statement

// Set header to indicate the response is JSON
header('Content-Type: application/json');

// Encode the array as JSON and output it
echo json_encode($postsArray);