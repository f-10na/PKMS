<?php
session_start();
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

// SQL query to select users with non-empty usernames and passwords
$sql = "SELECT username, can_read, can_write, user_email
FROM Users_Details
WHERE username IS NOT NULL AND username != '' 
  AND password IS NOT NULL AND password != '';
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Permissions</title>
    <link rel="stylesheet" type="text/css" href="styles_main2.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>
<body>
     <!-- code for the black header div -->
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
                            //set up the connection to the data base
                            $username = "team011";
                            $password = "JAEWyfUXpzqank7scpWm";
                            $servername = "localhost";
                            $dbname = "team011";
                    
                            // Create connection
                            $conn = mysqli_connect($servername, $username, $password, $dbname);
                    
                            // Check connection
                            if (!$conn) {
                                die("Connection failed: " . mysqli_connect_error());
                            }
                            
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
    <div class="manage-controls-div">
        <ul class="breadcrumbs">
             <li class="breadcrumbs-item">
                <a href="/PKMS/PKMS(Manager)/ManagerLanding.php" class="breadcrumbs_link">Manage</a>
            </li>
            <li class="breadcrumbs-item">
                <a href="manage_controls.php"  class="breadcrumbs_link--active"
                    breadcrumbs_link--active>Knowledge Restrictions</a>
                </li>
            </ul>
        <table class="permissions-table">
        <thead>
    <tr>
        <th>Username</th>
        <th>Read</th>
        <th>Write</th>
    </tr>
</thead>

            <tbody>
            <?php if ($result->num_rows > 0): ?>
    <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo htmlspecialchars($row["username"]); ?></td>
            <td><input type="checkbox" <?php echo $row["can_read"] ? 'checked' : ''; ?> disabled></td> <!-- Display a checkbox for read permission, checked if the user has permission, always disabled -->
            <td><input type="checkbox" class="write-permission" data-email="<?php echo ($row["user_email"]); ?>" <?php echo $row["can_write"] ? 'checked' : ''; ?>></td> <!-- Checkbox for write permission -->
        </tr>
    <?php endwhile; ?>
<?php else: ?>
    <tr><td colspan="3">No users found.</td></tr>
<?php endif; ?>
            </tbody>
        </table>
    </div>
    </div>
    <!-- section for footer -->
    <div class="footer">
        <p><u>Make It All</u> <u>Acceptable Use Policy </u></p>
    </div>

<script>
    let role = '<?php echo $role?>'; 

    $(document).ready(function() {
        // Retrieve the email address stored in the data-email attribute of the changed checkbox
        $('.write-permission').change(function() {
            var email = $(this).data('email');
            // Determine the new write permission state based on the checkbox's checked property: 1 for checked (enabled), 0 for unchecked (disabled)
            var canWrite = $(this).is(':checked') ? 1 : 0;
            // Send an AJAX POST request to the 'setRestrictions.php'
            $.ajax({
                url: 'setRestrictions.php', // The PHP script that updates the database
                type: 'POST',
                data: {
                    'user_email': email,
                    'can_write': canWrite
                },
                success: function(response) {
                    console.log(response);
                    alert( "Record updated successfully");
                },
                error: function(xhr, status, error) {
                    alert("ERROR: Failed to change user write permissions")
                    console.error("Error: " + status + " - " + error);
                }
            });
        });
    });

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

    const tabs = document.querySelectorAll('.tab');
    const arrow = document.querySelector('.arrow');
    const contents = document.querySelectorAll('.content'); // Get all content divs

    function positionArrow() {
        const activeTab = document.querySelector('.tab.active');
        if (activeTab) {
            const tabRect = activeTab.getBoundingClientRect();
            const arrowWidth = arrow.offsetWidth;
            arrow.style.left = `${tabRect.left + (activeTab.offsetWidth - arrowWidth) / 1.7}px`;
        }
    }

    

    $(document).ready(function (){
        //$('#ManageContent').css('display','block');
        $('#ManageButton').addClass("active");
        $('.arrow').css('display','block');
    });
</script>
<script src="/PKMS/link.js"></script>

</body>
</html>

<?php
$conn->close();
?>
