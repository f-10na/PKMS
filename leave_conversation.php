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

require_once __DIR__ . '/../../getJWT.php'; // Path may change depending on where you are working
$token_result = get_token(); // Function that determines if a token is set and gets it
if ($token_result['verified']) { // If the token is safe
    $user_data = $token_result['data']; // Putting the token data into the array $user_data
    // Code should go here

    $user_id = $user_data['userID'];

    if (!empty($conversation_id)) {
        // Checking the user already had access to the conversation
        $stmt = $conn->prepare("SELECT user_id FROM conversation_users WHERE conversation_id = ? AND user_id = ?;");
        $stmt->bind_param('ii', $conversation_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
    
        $data = [];
        while($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        $stmt->close();
    
        if (sizeof($data) > 0) { // User is the owner of the message
            
            $current_timestamp = date('Y-m-d H:i:s');
            $stmt = $conn->prepare("UPDATE conversation_users SET left_date = ? WHERE conversation_id = ? AND user_id = ?;");
            $stmt->bind_param('sii', $current_timestamp, $conversation_id, $user_id);
            $stmt->execute();
            if ($stmt->affected_rows > 0) { // Succesfully processed request and maked the message as deleted
                response(200, "Success", NULL); 
    
            // May be redundant as it should always affect the table
            } else { // If request processed successfully but didn't affect the database
                response(200, "Success", "Request processed successfully, no rows affected");
    
            }
            $stmt->close();
            // echo "hi";
    
        } else { // If the user isn't the owner of the message
            response(403, "Forbidden", NULL);
        }
    
    } else { // If the needed infomation wasn't provided in the body
        response(400, "Invalid Request", NULL);
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