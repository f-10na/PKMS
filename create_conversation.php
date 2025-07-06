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

$conversation_title = $message_data['conversation_title'];
$conversation_users = $message_data['conversation_users'];

require_once __DIR__ . '/../../getJWT.php'; // Path may change depending on where you are working
$token_result = get_token(); // Function that determines if a token is set and gets it
if ($token_result['verified']) { // If the token is safe
    $user_data = $token_result['data']; // Putting the token data into the array $user_data
    // Code should go here

    $user_id = $user_data['userID'];

    $placeholders = implode(',', array_fill(0, count($conversation_users), '?'));

    $stmt = $conn->prepare("SELECT user_id FROM users WHERE user_id IN ($placeholders);");
    $types_a = str_repeat('i', count($conversation_users));
    $stmt->bind_param($types_a, ...$conversation_users);

    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    $stmt->close();


    $all_user_ids = array_column($data, 'user_id');

    if (!empty($all_user_ids) && !empty($conversation_title)) {
        $stmt = $conn->prepare("INSERT INTO conversations (`title`, `updated_at`) VALUES (?, CURRENT_TIMESTAMP);");
        $stmt->bind_param('s', $conversation_title);
        $stmt->execute();
        $newConversationID = $conn->insert_id;
        $stmt->close();

        $types = '';
        $params = [];
        $placeholder = [];
        foreach ($all_user_ids as $user) {
            $placeholder[] = "(?,?)";
            $types .= 'ii';
            $params[] = $newConversationID;
            $params[] = $user;
        }

        // Adding the user who initated the request to the group as well
        $placeholder[] = "(?,?)";
        $types .= 'ii';
        $params[] = $newConversationID;
        $params[] = $user_id;

        $sql = "INSERT INTO conversation_users (conversation_id, user_id) VALUES " . implode(', ', $placeholder);
        $stmt = $conn->prepare($sql);

        $bindParams = [];
        $bindParams[] = &$types; 

        foreach ($params as $key => $value) {
            $bindParams[] = &$params[$key];
        }

        call_user_func_array([$stmt, 'bind_param'], $bindParams);


        if ($stmt->execute()) {
            response(200, "Success", NULL);
        } else {
            response(500, "Unknown error", $stmt->error);
        }
        $stmt->close();
    
    } else { // If the requested_conversation id wasn't provided
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