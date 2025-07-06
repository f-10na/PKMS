<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);


$PostNo = $_GET['postnum'];
$Topic_ID = $_GET['topic_ID'];
// $operation= $_POST['operation'];
// $post_date = date('Y-m-d');

$host = "localhost";
$username = "team011";
$password = "JAEWyfUXpzqank7scpWm";
$dbname = "team011";

$conn = mysqli_connect($host, $username, $password, $dbname);

if (!$conn) {
    echo json_encode(['status' => 'error', 'message' => 'Connection failed: ' . mysqli_connect_error()]);
    exit;
}

// SQL query to select user emails for a specific post update
$sql = "SELECT user_email FROM Posts_Updates WHERE Postnum= ? AND Topic_ID= ?;";




$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => "Error preparing statement: " . $conn->error]);
    exit;
}

$stmt->bind_param("is",$PostNo,$Topic_ID);

if ($stmt->execute()) {
    $result = $stmt->get_result();
    $emails =[]; // Initialize an array to hold the fetched email addresses
    while ($row = $result->fetch_assoc()) {
        $emails[] = $row; // Add each row to the array
    }
    echo json_encode(['status' => 'success', 'emails' => $emails]);
} else {
    echo json_encode(['status' => 'failed', 'message' => "Error executing statement: " . $stmt->error]);
}

$stmt->close();
$conn->close();

?>