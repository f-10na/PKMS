<?php

// Enable error reporting for debugging purposes
error_reporting(E_ALL);
ini_set("display_errors", 1);

// Retrieve parameters from the GET request
$email = $_GET['email'];
$topicid = $_GET['topic_ID'];
$postnum = $_GET['postnum'];

// Database connection details
$host = "localhost";
$username = "team011";
$password = "JAEWyfUXpzqank7scpWm";
$dbname = "team011";

// Establish a connection to the database
$conn = mysqli_connect($host, $username, $password, $dbname);

// Check if the connection is successful
if (!$conn) {
    // If connection fails, encode an error response and terminate the script
    die(json_encode(['error' => 'Connection failed: ' . mysqli_connect_error()]));
}

// SQL query to check if a post is in the user's favorites
$sql = "SELECT * FROM Favourites WHERE user_email = ? AND Topic_ID = ? AND Postnum = ?";

// Prepare the SQL statement
$stmt = $conn->prepare($sql);

// Check if the preparation of the statement is successful
if (!$stmt) {
    // If preparation fails, encode an error response and terminate the script
    die(json_encode(['error' => 'Error preparing statement: ' . $conn->error]));
}

// Bind parameters to the prepared statement
$stmt->bind_param("ssi", $email, $topicid, $postnum);

// Execute the SQL statement
$stmt->execute();

// Get the result set
$result = $stmt->get_result();

// Check if there are rows in the result set
if ($result->num_rows > 0) {
    // If there are rows, the post is in the user's favorites
    $response = ['status' => 'heart'];
} else {
    // If there are no rows, the post is not in the user's favorites
    $response = ['status' => 'noheart'];
}

// Close the prepared statement and the database connection
$stmt->close();
$conn->close();

// Encode the response as JSON and echo it
echo json_encode($response);

?>

