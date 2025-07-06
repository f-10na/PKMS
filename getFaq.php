<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

$host = "localhost";
$username = "team011";
$password = "JAEWyfUXpzqank7scpWm";
$dbname = "team011";

$conn = mysqli_connect($host, $username, $password, $dbname);

if (!$conn) {
    echo json_encode(['status' => 'error', 'message' => 'Connection failed: ' . mysqli_connect_error()]);
    exit;
}

//gets the first 5 posts with highest update count
$sql = "SELECT title, content, category FROM Posts ORDER BY update_count DESC LIMIT 5;";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => "Error preparing statement: " . $conn->error]);
    exit;
}

if ($stmt->execute()) {
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['status' => 'success', 'posts' => $rows]);
} else {
    echo json_encode(['status' => 'failed', 'message' => "Error executing statement: " . $stmt->error]);
}

$stmt->close();
$conn->close();

?>