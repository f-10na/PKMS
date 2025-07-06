<?php
// Database connection
$host = "localhost";
$username = "team011";
$password = "JAEWyfUXpzqank7scpWm";
$dbname = "team011";

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch topics
$topics = [];
$sql = "SELECT Topic_ID, topic FROM Topics";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $topics[] = $row;
    }
}

// Fetch employee emails
$employees = [];
$sql = "SELECT user_email FROM Users_Details WHERE role = 'Employee'";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $employees[] = $row['user_email'];
    }
}

$conn->close();

// Combine topics and employees in one array to send back to the frontend
$response = ['topics' => $topics, 'employees' => $employees];
echo json_encode($response);
?>
