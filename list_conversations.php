<?php 
// Setting up the database connection
include "database-connect.php";
$conn = mysqli_connect($dbservername, $dbusername, $dbpassword, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
header('Content-Type: application/json');

require_once __DIR__ . '/../../getJWT.php'; // Path may change depending on where you are working
$token_result = get_token(); // Function that determines if a token is set and gets it
if ($token_result['verified']) { // If the token is safe
    $user_data = $token_result['data']; // Putting the token data into the array $user_data
    $user_id = $user_data['userID'];

	$stmt = $conn->prepare("SELECT convo.conversation_id, title, last_message_id, message_text, updated_at FROM (SELECT conversations.conversation_id, title, last_message_id, message_text, updated_at FROM conversations LEFT JOIN message ON last_message_id = message_id) AS convo LEFT JOIN conversation_users ON convo.conversation_id = conversation_users.conversation_id WHERE user_id = ? AND left_date IS NULL ORDER BY updated_at DESC;");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    response(200, "Success", $data); // Succesfully got messages and can return to user
    $stmt->close();


} else { // Token isn't safe - handle error
    response (403, "Forbidden - Token not verified", "No worke");
}




// Gives a response to the user, informing of any errors that may have occured
function response($status,$status_message,$data){
	header("HTTP/1.1 ".$status);
	
	$response['status']=$status;
	$response['status_message']=$status_message;
	$response['data']=$data;
	
	echo json_encode($response);
}


$conn->close();
?>