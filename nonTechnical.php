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
    var email = '<?php echo $_SESSION['email'];?>';

	$(document).ready(function() {
        const category = "<?php echo $category; ?>";
                $.ajax({
                    url: "topics.php",
                    type: "GET",
                    data: { category:category},
		            dataType: 'json',
                    success: function (insertedHtml) {
                        let len = insertedHtml.length;
                        for (let i = 0; i < len; i++) {
                            let name = insertedHtml[i].topic;
                            const x = document.createElement("div");
			                x.setAttribute("id",name);
	                        x.setAttribute("class","topics");
                            x.innerHTML = name ;
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


function sendPostToDB(topicID)
{
      let post = $("#postContent").val();
                const topic =  $("#topicTitle").val();
                let category = "<?php echo $category; ?>";
               
                let postno = 1;
                let title = $("#postTitle").val();
                console.log("value:", topic);
                event.preventDefault();
                $.ajax({
    			url: "createTopic.php",
    			type: "POST",
                //sessions needed here
    			data: {content: post, email:email,postno:postno,topic: topic, category: category, topicid: topicID, title: title},
    			success: function(response) {
        			console.log("Success response:", response);
        			alert(response); // Or display this message in your HTML
				    
				    closePopup()
                    $.ajax({
                    url: "topics.php",
                    type: "GET",
                    data: { category:category},
		            dataType: 'json',
                    success: function (insertedHtml) {
                        console.log(insertedHtml)
                        let len = insertedHtml.length;
                        document.getElementById("topicsContainer").innerHTML="";
                        for (let i = 0; i < len; i++) {
                            let name = insertedHtml[i].topic;
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
    			},
    			error: function(xhr, status, error) {
        			console.log("Error response:", xhr.responseText);
        			alert("Error: " + xhr.responseText); // Or display this message in your HTML
                    
    			} 
});;
}


    $(document).ready(function () {
        $("#createTopicForm").submit(function (event) {
            event.preventDefault()
            const topic =  $("#topicTitle").val();
            let topicid = generateTopicId(topic);
            $.ajax ({
                url: "getPermissions.php",
                type: "GET",
                //sessions needed here
                data: {email: email},
                dataType: 'json',
                success: function(response) {
        		console.log("Success response:", response);
                if (response.permissions[0] === 1){
                    $.ajax({
                        url: "findTopic.php",
                        type: "GET",
                        dataType: 'json',
                        success: function (response) {
                            console.log(response);
                            let idExists=false;
                            for (let i=0;i<response.length;i++){
                                if(topicid===response[i]){
                                    idExists=true;
                                    break;
                                }
                                }if(idExists===false){
                                    //call creation function
                                    sendPostToDB(topicid)
                                }
                                else{
                                    let unique=false
                                    let newIDValid=true
                                    let index=0
                                    let NewTopicID=topicid+index
                                    while (!unique) {
                                        newIDValid = true; // Reset the flag for each iteration
                                        for (let i = 0; i < response.length; i++) {
                                            if (NewTopicID === response[i]) {
                                                newIDValid = false; // Found a match, ID is not unique
                                                break; // Exit the for loop
                                            }
                                        }
                                        if (!newIDValid) {
                                            index++; // Increment index if the ID was not unique
                                            NewTopicID = topicid + index; // Generate a new ID
                                        } else {
                                            unique = true; // If no match was found, the ID is unique
                                        }
                                    }//while
                                    console.log(NewTopicID)
                                    sendPostToDB(NewTopicID)
                                }//else
                            },
                            error: function (e) {
                                console.log(e.message);
                            }
                        });
                    }else{
                        alert("ERROR: Denied access. Speak to a manager");
                        closePopup();
                    };
                    }});
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
                        <a href="nonTechnical.php?category=non-tech" class="breadcrumbs_link--active" 
                        breadcrumbs_link--active>Non-Technical</a>
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
                        <textarea id="postContent" name="postContent" required style="height:30vh ;width:30vh">
                        </textarea>
                        <!-- <div  class="dropdown-container">
                            <select id="dropdownMenuTopic">
                                <option value="allPosts">Technical</option>
                                <option value="mostLiked">Non-Technical</option>
                            </select>
                        </div> -->
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
    <footer class="footer">
        <p><u>Make It All</u> <u>Acceptable Use Policy </u></p>
    </footer>


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