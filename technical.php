<?php
session_start();

$email = $_SESSION['email'];
$role = $_SESSION['role'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Topics</title>
    <link rel="stylesheet" type="text/css" href="styles_main2.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<?php
// Check if the 'category' variable exists in the query string and is not empty
if (isset($_GET['category']) && !empty($_GET['category'])) {
    $category = htmlspecialchars($_GET['category']); // Sanitize the input to prevent XSS attacks
} else {
    $category = "defaultCategory"; // Fallback value if 'topic' is not set 
}
?>

    <script>
    // Store the user's email in a JavaScript variable, pulled from PHP session variable
    var email = '<?php echo $_SESSION['email'];?>';

    // When the document is fully loaded, execute the following function
    $(document).ready(function() {
        // Retrieve the category from the PHP variable and store it in a JavaScript variable
        const category = "<?php echo $category; ?>";
        // Make an AJAX request to the 'topics.php' script, passing the 'category' as data
        $.ajax({
            url: "topics.php", // The URL of the PHP file that will process the request
            type: "GET", // The HTTP method to use for the request
            data: { category: category }, // Data to be sent to the server
            dataType: 'json', // Expect a JSON response from the server
            success: function (insertedHtml) {
                // This function is called if the request succeeds
                let len = insertedHtml.length; // Get the length of the response array
                for (let i = 0; i < len; i++) {
                    let name = insertedHtml[i].topic; // Extract the topic name from each object in the array
                    const x = document.createElement("div"); // Create a new div element for each topic
                    x.setAttribute("id", name); // Set the ID attribute of the div to the topic name
                    x.setAttribute("class", "topics"); // Set the class attribute of the div
                    x.innerHTML = name; // Set the inner HTML of the div to the topic name
                    // Make the div clickable
                    x.style.cursor = "pointer"; // Change cursor on hover
                    x.onclick = function() {
                        // Define what happens when the div is clicked: redirect to the posts page with query parameters
                        window.location.href = "postspage.php?category=" + encodeURIComponent(category) + "&topicname=" + encodeURIComponent(x.getAttribute("id")); // Redirect on click
                    };
                    document.getElementById("topicsContainer").appendChild(x); // Append the created div to the topics container
                }
                             
            },
            error: function (e) {
                // This function is called if the request fails
                console.log(e.message); // Log the error message to the console
            }
        });
    });
</script>

<script>

function generateTopicId(topicName) {
        const words = topicName.split(' ');
        let id = '';

        if (words.length > 1) {
            // For multi-word topics, use the first letter of the first two or three words
            id = words.slice(0, 3).map(word => word[0]).join('').toUpperCase();
            id = id.substring(0, 3); // Ensure no more than 3 characters
        } else {
            // For a single-word topic, use the first, potentially middle, and last letters to form 2 or 3 letters
            const word = words[0];
            if (word.length === 1) {
                // If only one letter, use it as is
                id = word.toUpperCase();
            } else if (word.length === 2) {
                // If two letters, use both
                id = word.toUpperCase();
            } else {
                // Use the first, middle (and next if available for 3 letters), and last letters
                const middleIndex = Math.floor(word.length / 2);
                id = word[0] + (word.length > 2 ? word.substring(middleIndex - 1, middleIndex + 1) : word[middleIndex]);
                id = id.substring(0, 3).toUpperCase(); // Ensure no more than 3 characters
            }
        }

        return id;
    }
</script>


<script>
// Function to send post data to the database using AJAX
function sendPostToDB(topicID) {
    let post = $("#postContent").val(); // Get post content from textarea
    const topic = $("#topicTitle").val(); // Get topic title from input field
    let category = "<?php echo $category; ?>"; // PHP variable passed to JavaScript
    let postno = 1; // Initialize post number
    let title = $("#postTitle").val(); // Get post title from input field
    console.log("value:", topic); // Log topic to the console for debugging
    event.preventDefault(); // Prevent form submission
    $.ajax({
        url: "createTopic.php", // URL to PHP script for creating topics
        type: "POST", // Method type
        // Data to send in the request
        data: { content: post, email: email, postno: postno, topic: topic, category: category, topicid: topicID, title: title },
        success: function(response) {
            console.log("Success response:", response); // Log success response
            alert(response); // Show response message as alert
            closePopup(); // Close the popup window
            // Fetch updated topics list
            $.ajax({
                url: "topics.php",
                type: "GET",
                data: { category: category },
                dataType: 'json',
                success: function(insertedHtml) {
                    console.log(insertedHtml); // Log the fetched topics
                    let len = insertedHtml.length;
                    document.getElementById("topicsContainer").innerHTML = ""; // Clear topics container
                    for (let i = 0; i < len; i++) {
                        let name = insertedHtml[i].topic; // Get topic name
                        const x = document.createElement("div"); // Create a new div for each topic
                        x.setAttribute("id", name);
                        x.setAttribute("class", "topics");
                        x.innerHTML = name;
                        x.style.cursor = "pointer"; // Make div clickable
                        x.onclick = function() {
                            // Redirect to posts page on click
                            window.location.href = "postspage.php?category=" + encodeURIComponent(category) + "&topicname=" + encodeURIComponent(x.getAttribute("id"));
                        };
                        document.getElementById("topicsContainer").appendChild(x); // Append div to container
                    }
                },
                error: function(e) {
                    console.log(e.message); // Log error message
                }
            });
        },
        error: function(xhr, status, error) {
            console.log("Error response:", xhr.responseText); // Log error response
            alert("Error: " + xhr.responseText); // Show error message as alert
        }
    });
}

// On document ready
$(document).ready(function() {
    // When the create topic form is submitted
    $("#createTopicForm").submit(function(event) {
        event.preventDefault(); // Prevent default form submission
        const topic = $("#topicTitle").val(); // Get topic from input field
        let topicid = generateTopicId(topic); // Generate a unique topic ID
        // Check permissions before creating topic
        $.ajax({
            url: "getPermissions.php",
            type: "GET",
            data: { email: email }, // Pass email to get permissions
            dataType: 'json',
            success: function(response) {
                console.log("Success response:", response); // Log permissions response
                if (response.permissions[0] === 1) { // If user has permission
                    // Check if topic ID already exists
                    $.ajax({
                        url: "findTopic.php",
                        type: "GET",
                        dataType: 'json',
                        success: function(response) {
                            console.log(response); // Log found topic IDs
                            let idExists = false; // Flag to check if ID exists
                            // Check if generated ID exists in response
                            for (let i = 0; i < response.length; i++) {
                                if (topicid === response[i]) {
                                    idExists = true; // ID exists
                                    break; // Exit loop
                                }
                            }
                            if (!idExists) {
                                // If ID does not exist, call creation function
                                sendPostToDB(topicid);
                            } else {
                                // If ID exists, attempt to generate a unique ID
                                let unique = false; // Flag for uniqueness
                                let newIDValid = true; // Flag for new ID validity
                                let index = 0; // Index for appending to ID
                                let NewTopicID = topicid + index; // Generate new ID
                                // Loop until a unique ID is found
                                while (!unique) {
                                    newIDValid = true; // Assume new ID is valid
                                    // Check if new ID is in response
                                        for (let i = 0; i < response.length; i++) {
                                        if (NewTopicID === response[i]) {
                                            newIDValid = false; // New ID is not valid
                                            break; // Exit loop
                                        }
                                    }
                                    if (!newIDValid) {
                                        // If new ID is not valid, increment index and generate new ID
                                        index++;
                                        NewTopicID = topicid + index;
                                    } else {
                                        // If new ID is valid, set unique to true
                                        unique = true;
                                    }
                                } // End while
                                console.log(NewTopicID); // Log the new unique ID
                                sendPostToDB(NewTopicID); // Send post to database with new ID
                            } // End else
                        },
                        error: function(e) {
                            console.log(e.message); // Log error message
                        }
                    });
                } else {
                    // If user does not have permission, show error message
                    alert("ERROR: Denied access. Speak to a manager");
                    closePopup(); // Close the popup window
                }
            }
        });
    });
});
</script>

<script>
$(document).ready(function() {
    $("#searchForm").submit(function(event) {
        event.preventDefault(); // Prevent the form from submitting through the browser
        var searchQuery = $('input[name="search"]').val();
        console.log("Search query:", searchQuery);
        // Here, you can call an AJAX function or any other function to handle the search
		let category = "<?php echo $category; ?>";
		const topic = "all";
                $.ajax({
                    url: "search.php",
                    type: "GET",
                    data: {topic:topic,category:category,searchQuery:searchQuery},
		            dataType: 'json',
                    success: function (response) {
                        document.getElementById("topicsContainer").innerHTML = "";
                         console.log("success");
                        console.log(response);
                        let len = response.length; 
                        for (let i = 0; i <len; i++) {
                            let name = response[i].Topic;
                             console.log("topic is",name);
                            const x = document.createElement("div");
                            x.setAttribute("id",name);
                            x.setAttribute("class","topics");
                            x.innerHTML = name;
                        // Make the div clickable
                               x.style.cursor = "pointer"; // Change cursor on hover
                               x.onclick = function() {
                               window.location.href = "postspage.php?category=" + encodeURIComponent(category) + "&topicname=" + encodeURIComponent(x.getAttribute("id")); // Redirect on click
                               };
                               document.getElementById("topicsContainer").appendChild(x);
                             }
                    },
                    error: function (e) {
                        console.log(e.message);
                    }
                });
    });
});
</script>




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
<!-- code for the MAIN CONTENT after header div -->
    <div class="main-content">
        <div class="white-div">  
            <div class="content" id="knowledgeContent" style="display: contents;">
                <!-- Content for the 'Knowledge' tab -->
                <div id="topicsNavbar" class="topicsNavbarContent">
                    <div class="dropdown-container">
                        <select id="dropdownMenuPage">
                            <option value="page1">Page 1</option>
                            <option value="page2">Page 2</option>
                        </select>
                    </div>
                   
                    <div id="search-container">
                        <form id="searchForm">
                            <div id="searchContent">
                                <input type="text" placeholder="Search..." name="search">
                                <button id="search-button" type="submit">
                                    <i class="fa fa-search" style="color: #ed992b;"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="addButtonContainer">
                        <button id="addButton" class="add-button" onclick="openPopup()">
                            <img src="images/add.png" alt="Add Icon">
                        </button>
                    </div>
                </div>
                <hr>
                <div class="topicHeader">
                    <h2 class="headerContent">TOPICS</h2>
                </div>
                <ul class="breadcrumbs">
                    <li class="breadcrumbs-item">
                        <a href="standard_index.php" class="breadcrumbs_link">Home</a>
                    </li>
                    <li class="breadcrumbs-item">
                        <a href="technical.php?category=tech" class="breadcrumbs_link--active"
                        breadcrumbs_link--active>
                        Technical</a>
                    </li>
                </ul>

                <div id="topicsContainer">
                                    </div>
                <div class="overlay" id="overlay"></div>
                <div class="popup" id="createTopicPopup">
                    <h2>Create Topic</h2>
                    <form id="createTopicForm">
                        <label for="postTitle">Topic Title:</label>
                        <input type="text" id="topicTitle" name="topicTitle" required style="width: 50vh;">
                        <br>
                        <label for="postTitle">Post Title:</label>
                        <input type="text" id="postTitle" name="postTitle" required style="width: 50vh;">
                        <textarea id="postContent" name="postContent" required style="height:40vh ;width:50vh">
                        </textarea>
                        <div  class="dropdown-container">
                            <select id="dropdownMenuTopic">
                                <option value="allPosts">Technical</option>
                                <option value="mostLiked">Non-Technical</option>
                            </select>
                        </div>
                        <div class="createTopicButton-container">
                            <button type="submit" id="createTopicButton">Create</button>
                             
                            <button type="button" id="cancelTopicButton" onclick="closePopup()">Cancel</button>
                        </div>
                    </form>
                    <button class="closePopup" onclick="closePopup()">&times;</button>
                </div>
            </div>
        </div>
    </div>
    <!-- section for footer -->
    <div class="footer">
        <p><u>makeitall.co.uk Acceptable Use Policy</u></p>
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


    function openPopup() {
        document.getElementById('overlay').style.display = 'block';
        document.getElementById('createTopicPopup').style.display = 'block';
    }

    function closePopup() {
        document.getElementById('overlay').style.display = 'none';
        document.getElementById('createTopicPopup').style.display = 'none';
    }

    $(document).ready(function (){
        $('#ManageContent').css('display','block');
        $('#KnowledgeButton').addClass("active");
        $('.arrow').css('display','block');
    });
</script>
<script src="/PKMS/link.js"></script>
</body>
</html>
