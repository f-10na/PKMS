<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

$topicid = $_POST['topic_ID'];
$postnum = $_POST['postnum'];

$host = "localhost";
$username = "team011";
$password = "JAEWyfUXpzqank7scpWm";
$dbname = "team011";

$conn = mysqli_connect($host, $username, $password, $dbname);

if (!$conn) {
    echo json_encode(['status' => 'error', 'message' => 'Connection failed: ' . mysqli_connect_error()]);
    exit;
}

$sql = "DELETE FROM Posts WHERE Topic_ID = ? AND PostNo = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => 'Error preparing statement: ' . $conn->error]);
    exit;
}

$stmt->bind_param("si", $topicid, $postnum);

if ($stmt->execute()) {
    // Check if any rows were affected
    if ($stmt->affected_rows > 0) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error']);
    }
} else {
    echo json_encode(['status' => 'error']);
}

$stmt->close();
$conn->close();

?>