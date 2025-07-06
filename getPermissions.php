<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

// Get the user's email from the query string
$email = $_GET['email'];


$host = "localhost";
$username = "team011";
$password = "JAEWyfUXpzqank7scpWm";
$dbname = "team011";

$conn = mysqli_connect($host, $username, $password, $dbname);

if (!$conn) {
    echo json_encode(['status' => 'error', 'message' => 'Connection failed: ' . mysqli_connect_error()]);
    exit;
}

// SQL query to select the write permission for the given email address
$sql = "SELECT can_write FROM Users_Details WHERE user_email = ?";


// Bind the user's email address to the prepared SQL statement
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => "Error preparing statement: " . $conn->error]);
    exit;
}

$stmt->bind_param("s",$email);
// console.log($stmt)

if ($stmt->execute()) {

    echo json_encode(['status' => 'success', 'permissions' => $stmt->get_result()->fetch_row() ]);
} else {
    echo json_encode(['status' => 'failed', 'message' => "Error executing statement: " . $stmt->error]);
}

// Close the prepared statement and the database connection
$stmt->close();
$conn->close();

?>