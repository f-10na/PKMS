<?php

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set("display_errors", 1);

// Retrieve parameters from the GET request
$email = $_GET['email'];
$topicID = $_GET['topic_ID'];
$postNum = $_GET['postnum'];

// Database connection details
$host = "localhost";
$username = "team011";
$password = "JAEWyfUXpzqank7scpWm";
$dbname = "team011";

// Establish a connection to the database
$conn = mysqli_connect($host, $username, $password, $dbname);

// Check if the connection is successful
if (!$conn) {
    // If the connection fails, return a JSON error response and set HTTP response code to 500 (Internal Server Error)
    http_response_code(500);
    die(json_encode(['error' => 'Connection failed: ' . mysqli_connect_error()]));
}

// Prepare and execute a SELECT query to check for records in the Posts_Updates table
$sql = "SELECT * FROM Posts_Updates WHERE user_email = ? AND Topic_ID = ? AND Postnum = ?";
$stmt = $conn->prepare($sql);

// Check if the statement preparation is successful
if (!$stmt) {
    // If the preparation fails, return a JSON error response and set HTTP response code to 500 (Internal Server Error)
    http_response_code(500);
    die(json_encode(['error' => 'Error preparing statement: ' . $conn->error]));
}

// Bind parameters and execute the prepared statement
$stmt->bind_param("ssi", $email, $topicID, $postNum);
$stmt->execute();

// Get the result of the query
$result = $stmt->get_result();

// Close the prepared statement and the database connection
$stmt->close();
$conn->close();

// Check if there are records in the result
if ($result->num_rows > 0) {
    // If there are records, return a JSON response indicating an update
    echo json_encode(['status' => 'update']);
} else {
    // If there are no records, return a JSON response indicating no update
    echo json_encode(['status' => 'noUpdate']);
}

?>
