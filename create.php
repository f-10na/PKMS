<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Retrieve data using POST method
$content = $_POST['content'];
$email = $_POST['email'];
$topic = $_POST['topic'];
$category = $_POST['category'];
$topicid = $_POST['topicid']; 
$title = $_POST['title'];
$post_date = date('Y-m-d'); // Ensure this is in 'YYYY-MM-DD' format for MySQL

$host = "localhost";
$username = "team011";
$password = "JAEWyfUXpzqank7scpWm";
$dbname = "team011";

// Create a connection
$conn = mysqli_connect($host, $username, $password, $dbname);

// Check connection
if (!$conn) {
    echo "UNSUCCESSFUL";
    die("Connection failed: " . mysqli_connect_error());
}

$sql1 = "SELECT COUNT(*) FROM Posts WHERE topic = ?";
$stmt1 = $conn->prepare($sql1);
if ($stmt1 === false) {
    echo "Error preparing statement: " . $conn->error;
    exit;
}
$stmt1->bind_param("s", $topic);
// Execute the statement
$stmt1->execute();

// Store the result so we can fetch the count
$result1 = $stmt1->get_result();

// Fetching the count from the result
$row = $result1->fetch_array();
$count = $row[0]; // The count is now stored in $count

$newCount = $count + 1;


// Close the statement
$stmt1->close();


// Use prepared statements to prevent SQL injection
$sql = "INSERT INTO Posts (Topic_ID, PostNo, content, date_updated, topic, user_email, title, category) VALUES (?,?, ?, ?, ?, ?, ?, ?)";

// Prepare statement
$stmt = $conn->prepare($sql);

// Check if the statement was prepared correctly
if ($stmt === false) {
    echo "Error preparing statement: " . $conn->error;
    exit;
}

// Assuming post_ID is an integer; adjust the bind_param accordingly


// The 's' and 'i' denote the types of the variables: 's' for string, 'i' for integer.
$stmt->bind_param("sissssss", $topicid,$newCount, $content, $post_date, $topic, $email, $title, $category);
$stmt->error;
// Execute the statement
if ($stmt->execute()) {
    echo "New record created successfully";
} else {
    echo "Error: " . $stmt->error;
}

// Close statement and connection
$stmt->close();
$conn->close();
?>