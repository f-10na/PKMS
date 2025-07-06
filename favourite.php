<?php

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set("display_errors", 1);

// Retrieve email, topic ID, and post number from the GET request
$email = $_GET['email'];
$topicid = $_GET['topic_ID'];
$postnum = $_GET['postnum'];

// Database connection parameters
$host = "localhost";
$username = "team011";
$password = "JAEWyfUXpzqank7scpWm";
$dbname = "team011";

// Attempt to establish a connection to the database
$conn = mysqli_connect($host, $username, $password, $dbname);

// Check if connection was successful, if not, return error
if (!$conn) {
    die(json_encode(['error' => 'Connection failed: ' . mysqli_connect_error()]));
}

// SQL query to check if the favourite post already exists for the user
$sql = "SELECT * FROM Favourites WHERE user_email = ? AND Topic_ID = ? AND Postnum = ?";
// Prepare the SQL statement
$stmt = $conn->prepare($sql);
// Handle errors in statement preparation
if (!$stmt) {
    die(json_encode(['error' => 'Error preparing statement: ' . $conn->error]));
}

// Bind parameters to the prepared statement
$stmt->bind_param("ssi", $email, $topicid, $postnum);
// Execute the statement
$stmt->execute();
// Retrieve the result set from the statement
$result = $stmt->get_result();

// Initialize an array to store the response
$response = [];
// Check if any rows are returned indicating the post is already favourited
if ($result->num_rows > 0) {
    // Prepare to delete the favourite if it exists
    $sql2 = "DELETE FROM Favourites WHERE user_email = ? AND Topic_ID = ? AND Postnum = ?";
    // Prepare the delete statement
    $stmt2 = $conn->prepare($sql2);
    // Handle errors in delete statement preparation
    if (!$stmt2) {
        die(json_encode(['error' => 'Error preparing delete statement: ' . $conn->error]));
    }
    // Bind parameters and execute the delete statement
    $stmt2->bind_param("ssi", $email, $topicid, $postnum);
    if ($stmt2->execute()) {
        // Return a success status if the favourite was successfully removed
        $response['status'] = 'removed';
    } else {
        // Return an error if the favourite could not be removed
        $response['error'] = 'Failed to remove favorite';
    }
    // Close the delete statement
    $stmt2->close();
} else {
    // Prepare to add the favourite if it doesn't exist
    $sql2 = "INSERT INTO Favourites (user_email, Topic_ID, Postnum) VALUES (?, ?, ?)";
    // Prepare the insert statement
    $stmt2 = $conn->prepare($sql2);
    // Handle errors in insert statement preparation
    if (!$stmt2) {
        die(json_encode(['error' => 'Error preparing insert statement: ' . $conn->error]));
    }
    // Bind parameters and execute the insert statement
    $stmt2->bind_param("ssi", $email, $topicid, $postnum);
    if ($stmt2->execute()) {
        // Return a success status if the favourite was successfully added
        $response['status'] = 'added';
    } else {
        // Return an error if the favourite could not be added
        $response['error'] = 'Failed to add favorite';
    }
    // Close the insert statement
    $stmt2->close();
}

// Close the initial statement and the database connection
$stmt->close();
$conn->close();

// Encode the response as JSON and output it
echo json_encode($response);
?>
