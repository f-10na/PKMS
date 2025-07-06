<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);



$topicID = $_POST['topic_ID']; // Assuming 'topic' is passed via the query string
$postnum = $_POST['postnum'];
$title = $_POST['title'];
$content = $_POST['content'];
$post_date = date('Y-m-d');

$host = "localhost";
$username = "team011";
$password = "JAEWyfUXpzqank7scpWm";
$dbname = "team011";

$conn = mysqli_connect($host, $username, $password, $dbname);

if (!$conn) {
    echo json_encode(['status' => 'error', 'message' => 'Connection failed: ' . mysqli_connect_error()]);
    exit;
}

$sql = "UPDATE Posts SET title = ?, content = ? ,date_updated= ? WHERE Topic_ID = ? AND PostNo = ?;";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => "Error preparing statement: " . $conn->error]);
    exit;
}

$stmt->bind_param("ssssi", $title, $content,$post_date, $topicID, $postnum);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Record updated successfully']);
} else {
    echo json_encode(['status' => 'failed', 'message' => "Error executing statement: " . $stmt->error]);
}

$stmt->close();
$conn->close();

?>