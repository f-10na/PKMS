<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);
?>

<?php
$host = "localhost";
$username = "team011";
$password = "JAEWyfUXpzqank7scpWm";
$dbname = "team011";

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$emailSent = false;
$emailExists = false;
 ?>

<script>
     // Function to send an email invitation
    function sendInvite(){
        // Retrieve the email address entered in the input field
        let email = document.querySelector('input[name="email"]').value;
    $.ajax({
            url: "checkEmail.php",
            type: "GET",
            data: {email: email},
            dataType: 'json',
            success: function(response) {
            console.log("Success response:", response);
            if (Array.isArray(response.emails)){
                $(document).ready(function() {
                // Prepare the subject and body for the email
                var subject = encodeURIComponent("Registration Link");
                var body = encodeURIComponent("Here is the attached registration link: [http://34.89.116.223/PKMS/PKMS_Complete/Onyedikachi's%20code/registration.php]");
                // Open the default email client with the prepared email draft
                window.open(`mailto:${email}?subject=${subject}&body=${body}`);
        });
            } else{
                alert("Request Denied.");
            }
        },
        error: function(xhr, status, error) {
            console.log("Error response:", xhr.responseText);
            alert("Error: " + xhr.responseText); // Or display this error message
        } 
        })}

</script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Email Invitation</title>
    <link rel="stylesheet" type="text/css" href="styles_main2.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>
<body>
<div class="header">
        <div class="logo">
            <img src="images/companylogo.png">
        </div>
        <div class="tabs">
            <button id = "ManageButton" class="tab">
                <img src="images/manage.png" alt="Manage Icon" height="20px" width="20px">
                Manage</button>
            <button id = "ProdButton" class="tab">
                <img src="images/productivity.png" alt="Productivity Icon" height="30px" width="30px">
                Productivity</button>
            <button id = "KnowledgeButton" class="tab">
                <img src="images/knowledge.png" alt="Knowledge Icon" height="20px" width="20px">
                Knowledge</button>
            <button id = "RemindersButton" class="tab">
                <img src="images/reminder.png" alt="Reminder Icon" height="20px" width="20px">
                Reminders</button>
            <button id = "InviteButton" class="tab">
                <img src="images/invite.png" alt="Invite Icon" height="20px" width="20px">
                Invite</button>
        </div>

        <div class="buttons">
            <button class="user-btn">
                <img src="images/user.png" class="user-pic" onclick="toggleMenu()">
            </button>
            <div class="sub-menu-wrap" id="subMenu">
                <div class="sub-menu">
                    <div class="user-info">
                        <h4><?php                            
                            $email = $_SESSION['email'];
                            $role = $_SESSION['role'];

                            $usernameQuery = "SELECT username
                            FROM `Users_Details`
                            WHERE user_email = '$email'";

                            $username = mysqli_query($conn, $usernameQuery);

                            if (mysqli_num_rows($username) > 0){
                                //output data of each row
                                while ($row = mysqli_fetch_array($username)){
                                    echo "Welcome ".$row[0];
                                }
                            }

                        ?></h4>
                    </div>
                    <hr>
                    
                    <a id =  "logoutB" href="#" class="sub-menu-link">
                    <img src="images/logout.png">
                    <p>logout</p>
                    <span>></span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="main-content">
    <div class="black-div">
        <h2>Send Invitation Email</h2>
        <form id="emailForm" method="post">
            <input type="email" name="email" placeholder="Enter recipient's email">
            <button type="button" id="sendEmailButton"onclick="sendInvite()">Send Email</button>
        </form>
        </div>
    </div>

    <!-- section for footer -->
    <div class="footer">
        <p><u>Make It All</u> <u>Acceptable Use Policy </u></p>
    </div>


    <script>
    let role = '<?php echo $role?>';

    let subMenu = document.getElementById("subMenu");
    let userButton = document.querySelector(".user-btn");

    function toggleMenu() {
        subMenu.classList.toggle("open-menu");

        if (subMenu.classList.contains("open-menu")) {
            // Add event listener to detect clicks outside the submenu
            document.addEventListener('click', closeMenuOutside);
        } else {
            // If menu is closed, remove the click event listener
            document.removeEventListener('click', closeMenuOutside);
        }
    }

    function closeMenuOutside(event) {
        // Check if the clicked element is outside the user button and submenu
        if (!subMenu.contains(event.target) && !userButton.contains(event.target)) {
            subMenu.classList.remove("open-menu");
            // Remove the click event listener after closing the submenu
            document.removeEventListener('click', closeMenuOutside);
        }
    }

    $(document).ready(function (){
        $('#ManageContent').css('display','block');
        $('#InviteButton').addClass("active");
        $('.arrow').css('display','block');
    });
</script>
<script src="/PKMS/link.js"></script>

</body>
</html>
