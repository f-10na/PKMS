<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);


$PostNo = $_POST['postnum'];
$Topic_ID = $_POST['topic_ID'];
$operation= $_POST['operation'];
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

if($operation=="increment")
{
    $sql = "UPDATE Posts SET update_count = update_count+1 ,date_updated= ? WHERE Topic_ID = ? AND PostNo = ?;";
}
else{
    $sql = "UPDATE Posts SET update_count = update_count-1 ,date_updated= ? WHERE Topic_ID = ? AND PostNo = ?;";
}



$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => "Error preparing statement: " . $conn->error]);
    exit;
}

$stmt->bind_param("ssi", $post_date,$Topic_ID, $PostNo );

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Record updated successfully']);
} else {
    echo json_encode(['status' => 'failed', 'message' => "Error executing statement: " . $stmt->error]);
}

$stmt->close();
$conn->close();

?>