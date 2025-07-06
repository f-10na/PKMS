<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

$topic = $_GET['topic']; // Assuming 'topic' is passed via the query string
$category = $_GET['category'];
$data = $_GET['searchQuery']; // The term you are searching for

$host = "localhost"; // The hostname of your database server
$username = "team011"; // The username of your database user
$password = "JAEWyfUXpzqank7scpWm"; // The password of your database user
$dbname = "team011"; // The name of your database

// Create a connection object using mysqli_connect function
$conn = mysqli_connect($host, $username, $password, $dbname);

// Check if the connection was successful or not
if (!$conn) {
    echo "UNSUCCESSFUL";
    die("Connection failed: " . mysqli_connect_error());
}

if ($topic == "all") {
    $sql1 = "SELECT Topic FROM Topics WHERE Topic LIKE ? AND Category = ?;";

    $stmt1 = $conn->prepare($sql1);

    // Construct the search term with percentage signs
    $searchTerm = "%" . $data . "%";

    // Bind the parameter
    $stmt1->bind_param("ss", $searchTerm, $category);

    // Execute the statement
    $stmt1->execute();

    // Get the result
    $result = $stmt1->get_result();

    // Initialize an array to hold the fetched data
$postsArray = [];

// Fetch the data
while ($row = $result->fetch_assoc()) {
    $postsArray[] = $row;
}

// Close the statement
$stmt1->close();

// Close the database connection
$conn->close();

// Set header to indicate the response is JSON
header('Content-Type: application/json');

// Encode the array as JSON and output it
echo json_encode($postsArray);


} else {
    $sql = "SELECT Posts.Topic_ID, Posts.PostNo, Posts.content, Users_Details.username, Posts.title, Posts.date_updated, Posts.update_count 
            FROM Posts JOIN Users_Details ON Posts.user_email = Users_Details.user_email WHERE Posts.content LIKE ? AND Posts.topic LIKE ?;";

    $stmt = $conn->prepare($sql);

    // Construct the search term with percentage signs
    $searchTerm = "%" . $data . "%";

    // Bind the parameter
    $stmt->bind_param("ss", $searchTerm, $topic);

    // Execute the statement
    $stmt->execute();

    // Get the result
    $result = $stmt->get_result();
    // Initialize an array to hold the fetched data
$postsArray = [];

// Fetch the data
while ($row = $result->fetch_assoc()) {
    $postsArray[] = $row;
}

// Close the statement
$stmt->close();

// Close the database connection
$conn->close();

// Set header to indicate the response is JSON
header('Content-Type: application/json');

// Encode the array as JSON and output it
echo json_encode($postsArray);
}

?>