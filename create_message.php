<?php 
// Setting up the database connection
include "database-connect.php";
$conn = mysqli_connect($dbservername, $dbusername, $dbpassword, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
header('Content-Type: application/json');

$rawData = file_get_contents("php://input");
$message_data = json_decode($rawData, true);

$conversation_id = $message_data['conversation_id'];
$message_text = $message_data['message'];

require_once __DIR__ . '/../../getJWT.php'; // Path may change depending on where you are working
$token_result = get_token(); // Function that determines if a token is set and gets it
if ($token_result['verified']) { // If the token is safe
    $user_data = $token_result['data']; // Putting the token data into the array $user_data
    // Code should go here

    $user_id = $user_data['userID'];

    if (!empty($conversation_id) && !empty($message_text)) {
        // Checking the user can access the requested conversation
        $stmt = $conn->prepare("SELECT user_id FROM conversation_users WHERE conversation_id = ?;");
        $stmt->bind_param('i', $conversation_id);
        $stmt->execute();
        $result = $stmt->get_result();
    
        $data = [];
        while($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        $stmt->close();
    
        $users_with_access = array_column($data, 'user_id');
    
        if (in_array($user_id, $users_with_access)) { // User has access to the chat
            
            $stmt = $conn->prepare("INSERT INTO message (message_text, owner_id, conversation_id) VALUES (?, ?, ?)");
            $stmt->bind_param('sis', $message_text, $user_id, $conversation_id);
            $stmt->execute();
            if ($stmt->affected_rows > 0) { // Succesfully processed request and stored the message
                response(200, "Success", NULL); 
    
            // May be redundant as it should always affect the table
            } else { // If request processed successfully but didn't affect the database
                response(200, "Success", "Request processed successfully, no rows affected");
    
            }
            $stmt->close();
    
        } else { // If the user doesn't have access to the chat
            response(403, "Forbidden", NULL);
        }
    
    } else { // If the requested_conversation id wasn't provided
        response(400, "Invalid Request", $message_text);
    }
    
} else { // Token isn't safe - handle error
    echo "Token not verified";
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