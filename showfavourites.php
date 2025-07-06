<?php 
error_reporting(E_ALL);
ini_set("display_errors",1);
$email = $_GET['email'];
$pagenum = $_GET['pagenum'];

$limit = 5; // The maximum number of rows you want to retrieve
$offset = ($pagenum-1)*5 ; // Initialize a counter variable

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

$sql = "SELECT p.Topic_ID, p.PostNo, p.content, p.user_email, p.title, p.date_updated,p.topic FROM Posts p JOIN Favourites f
 ON p.Topic_ID = f.Topic_ID AND p.PostNo = f.PostNo WHERE f.user_email = ? LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);

		// Check if the statement was prepared correctly
if (!$stmt) {
    echo "Error preparing statement: " . $conn->error;
    	exit;
	}

		// Correctly use $topic instead of $topicname
		$stmt->bind_param("sii", $email, $limit, $offset);

		if (!$stmt->execute()) {
			echo "Error executing statement: " . $stmt->error;
		} else {
			$result = $stmt->get_result();
			$postsArray = [];
			while ($row = $result->fetch_assoc()) {
				$postsArray[] = $row;
			}
		
			// Process $postsArray or output it as needed
		}
		$sql2 = "SELECT COUNT(*) AS totalFavourites FROM Posts p JOIN Favourites f ON p.Topic_ID = f.Topic_ID AND p.PostNo = f.PostNo WHERE f.user_email = ?";
		$stmt2 = $conn->prepare($sql2);
		if (!$stmt2) {
			die("Error preparing statement: " . $conn->error);
		}
		
		$stmt2->bind_param("s", $email);
		$stmt2->execute();
		$result2 = $stmt2->get_result();
		$totalFavourites = $result2->fetch_assoc()['totalFavourites'];
		$stmt2->close();
		
		$conn->close();
		
		// Set header to indicate the response is JSON
		header('Content-Type: application/json');
		
		// Encode the array as JSON and output it, including total favourites
		echo json_encode(['posts' => $postsArray, 'totalFavourites' => $totalFavourites]);
?>