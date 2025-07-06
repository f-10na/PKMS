<?php
// Enable detailed error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Retrieve POST data sent from the form or API call
$content = $_POST['content'];
$email = $_POST['email'];
$topic = $_POST['topic'];
$category = $_POST['category'];
$topicid = $_POST['topicid'];
$postno = $_POST['postno'];
$title = $_POST['title'];
$post_date = date('Y-m-d'); // Current date in MySQL format

// Database connection details
$host = "localhost";
$username = "team011";
$password = "JAEWyfUXpzqank7scpWm";
$dbname = "team011";

// Establish a connection to the database
$conn = mysqli_connect($host, $username, $password, $dbname);

// Check if the connection was successful
if (!$conn) {
    echo "UNSUCCESSFUL";
    die("Connection failed: " . mysqli_connect_error());
}

// Prepare a SQL statement to check if the topic exists
$sql = "SELECT * FROM Topics WHERE Topic_ID = ?";
$stmt = $conn->prepare($sql);

// Check if the SQL statement was prepared correctly
if ($stmt === false) {
    echo json_encode(['status' => 'error', 'message' => 'Error preparing statement: ' . $conn->error]);
    exit;
}

// Bind the topic ID parameter to the prepared SQL statement
$stmt->bind_param("s", $topicid);
$stmt->execute();
$result = $stmt->get_result();

// Check if the topic already exists
if ($result->num_rows > 0) {
    // Topic exists; indicate this in the response
    echo json_encode(['status' => 'found']);
} else {
    // Topic does not exist; proceed to insert post and topic

    // SQL statement to insert the new post
    $sql = "INSERT INTO Posts (Topic_ID, PostNo, content, date_updated, topic, user_email, title, category) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    // Prepare the insert statement for the post
    $stmt1 = $conn->prepare($sql);
    if ($stmt1 === false) {
        echo "Error preparing statement: " . $conn->error;
        exit;
    }
    // Bind parameters and execute the insert statement for the post
    $stmt1->bind_param("sissssss", $topicid, $postno, $content, $post_date, $topic, $email, $title, $category);
    if (!$stmt1->execute()) {
        echo "Error: " . $stmt1->error;
        $stmt1->close();
        $conn->close();
        exit;
    }
    $stmt1->close(); // Close the post insert statement

    // SQL statement to insert the new topic
    $sql2 = "INSERT INTO Topics (Topic_ID, Topic, category, NumPosts) VALUES (?, ?, ?, 1)";

    // Prepare the insert statement for the topic
    $stmt2 = $conn->prepare($sql2);
    if ($stmt2 === false) {
        echo "Error preparing statement: " . $conn->error;
        $conn->close();
        exit;
    }
    // Bind parameters and execute the insert statement for the topic
    $stmt2->bind_param("sss", $topicid, $topic, $category);
    $stmt2->execute();
    echo json_encode(['status' => 'created']);
    $stmt2->close(); // Close the topic insert statement
}

// Close the database connection
$conn->close();
?>