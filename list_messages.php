<?php 
const MESSAGE_COUNT_TO_FETCH = 50;
$limit = MESSAGE_COUNT_TO_FETCH;

// Setting up the database connection
include "database-connect.php";
$conn = mysqli_connect($dbservername, $dbusername, $dbpassword, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
header('Content-Type: application/json');

// $user_id = 1; // temp variable untill sessions introduced
$requested_conversation = $_GET['requested_conversation'];
$number_messages = $_GET['number_messages'];

require_once __DIR__ . '/../../getJWT.php'; // Path may change depending on where you are working
$token_result = get_token(); // Function that determines if a token is set and gets it
if ($token_result['verified']) { // If the token is safe
    $user_data = $token_result['data']; // Putting the token data into the array $user_data
    $user_id = $user_data['userID'];


    if (!empty($requested_conversation) && !empty($number_messages)) {
        
        // Checking the user can access the requested conversation
        $stmt = $conn->prepare("SELECT user_id FROM conversation_users WHERE conversation_id = ?;");
        $stmt->bind_param('i', $requested_conversation);
        $stmt->execute();
        $result = $stmt->get_result();
    
        $data = [];
        while($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        $stmt->close();
    
        $users_with_access = array_column($data, 'user_id');
    
        if (in_array($user_id, $users_with_access)) { // User has access to the chat
            
            $stmt = $conn->prepare("SELECT message_id, 
                                    (CASE WHEN deleted_at IS NULL THEN message_text ELSE NULL END) as message_text, 
                                    first_name, second_name, owner_id, time_sent, edited_at, (owner_id = ?) AS own_message
                                    FROM message LEFT JOIN users ON owner_id = user_id 
                                    WHERE conversation_id = ? ORDER BY time_sent DESC LIMIT ?;");
            $stmt->bind_param('iii', $user_id, $requested_conversation, $number_messages);
            $stmt->execute();
            $result = $stmt->get_result();
    
            $data = [];
            while($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            $stmt->close();
    
            response(200, "Success", $data); // Succesfully got messages and can return to user
    
        } else { // If the user doesn't have access to the chat
            response(403, "Forbidden", $user_id);
        }
    
    } else { // If a required parameter wasn't provided
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