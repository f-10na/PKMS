<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

// Get parameters from the query string
$email = $_GET['email'];
$topic_id = $_GET['topic_ID'];
$postnum = $_GET['postnum'];

$host = "localhost";
$username = "team011";
$password = "JAEWyfUXpzqank7scpWm";
$dbname = "team011";

$conn = mysqli_connect($host, $username, $password, $dbname);

if (!$conn) {
    echo json_encode(['status' => 'error', 'message' => 'Connection failed: ' . mysqli_connect_error()]);
    exit;
}

// If you want to check if the provided email matches the creator's email for the post
$sql = "SELECT user_email FROM Posts WHERE Posts.user_email = ? AND Posts.Topic_ID = ? AND Posts.PostNo = ?";

// Bind parameters to the prepared SQL statement
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => "Error preparing statement: " . $conn->error]);
    exit;
}

// Bind parameters to the prepared statement
$stmt->bind_param("sss", $email, $topic_id, $postnum);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'post' => $stmt->get_result()->fetch_row()]);
} else {
    echo json_encode(['status' => 'failed', 'message' => "Error executing statement: " . $stmt->error]);
}

// Close the prepared statement and the database connection
$stmt->close();
$conn->close();

?>
