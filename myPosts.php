<?php
session_start();

$email = $_SESSION['email'];
$role = $_SESSION['role'];
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Posts</title>
    <link rel="stylesheet" type="text/css" href="styles_main2.css" > 
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
	
    <script>
        var email = '<?php echo $email?>';
	    $(document).ready(function() {
            let filter="default";
            let pagenum= 1;
            $.ajax({
                url: "posts.php",
                type: "GET",
                data: { topic: "all", email:email,filter:filter,pagenum:pagenum},
                dataType: 'json',
                success: function (postsData) {
                    console.log(postsData);
                    let len = postsData.length;
                    for (let i = 0; i < len; i++) {
                        let id = postsData[i].Topic_ID +"-"+ postsData[i].PostNo +"-";
                        let title = postsData[i].title;
                        let date = postsData[i].date_updated;
                        let content = postsData[i].content;
                        createPostElement(id, title, content,date);
                    }
                },
                error: function (e) {
                    console.log(e.message);
                }
            });
            $.ajax({
                url: "getMyPosts.php",
                type: "GET",
                data: { topic: "all", email:email,filter:filter,pagenum:pagenum},
                dataType: 'json',
                success: function (response) {
                    let result = response.totalRows / 5;
                    let pages = Math.ceil(result);
                    for (let a = 1; a < pages+1; a++) {
                        const page = document.createElement("option");
                        page.setAttribute("id", page + a);
                        page.setAttribute("value", a);
                        page.innerHTML = "Page " + a;
                        document.getElementById("dropdownMenuPage").appendChild(page);
                    }
                }, error: function (e) {
                    console.log(e.responseText);
                }
            });
         });

    </script>

    <script>
        function createPostElement(id, title, content,date) {
            const postDiv = document.createElement("div");
            postDiv.setAttribute("id", id);
            postDiv.setAttribute("class", "posts");
                                

            const postTitle = document.createElement("h4");
            postTitle.innerHTML = title; // Correctly set the title
            postDiv.appendChild(postTitle);

            const y = document.createElement("div");
            y.setAttribute("class", "posts");
            y.innerHTML = content; // Correctly set the content
            postDiv.appendChild(y);

            const buttonContainer = document.createElement("div");
            buttonContainer.className = "button-container";

            // Create the delete button
            const deleteButton = document.createElement("button");
            deleteButton.id = id +"deleteButton";
            deleteButton.onclick = function() { openPopup('deletePopup',id,content,title); }; 
            const deleteImg = document.createElement("img");
            deleteImg.src = "images/delete.svg";
            deleteImg.alt = "Delete Icon";
            deleteButton.style.cursor = "pointer"; // Change cursor on hover
            deleteButton.appendChild(deleteImg);

            // Create the edit button
            const editButton = document.createElement("button");
            editButton.id = id +"editButton";
            editButton.onclick = function() { openPopup('editPopup',id,content,title); };
            const editImg = document.createElement("img");
            editImg.src = "images/edit.png";
            editImg.alt = "Edit Icon";
            editButton.style.cursor = "pointer"; // Change cursor on hover
            editButton.appendChild(editImg);

            // Append buttons to the container
            buttonContainer.appendChild(deleteButton);
            buttonContainer.appendChild(editButton);

            // Append the container to the body or another specific element
            postDiv.appendChild(buttonContainer);
            document.getElementById("postContainer").appendChild(postDiv);
        }
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const deleteButton = document.getElementById('deleteButtonSubmit');
            deleteButton.addEventListener('click', function() {
            // Code to execute when the button is clicked
                deletePost();
            });
        });
    
        function deletePost(element) {
            let id=document.getElementById('deleteButtonSubmit').getAttribute('data-item-id');
            let parts = id.split("-");
            let topic_ID = parts[0];
            let postnum = parts[1];
            $.ajax({
                url: "deletepost.php",
                type: "POST",
                data: {topic_ID: topic_ID, postnum: postnum },
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success') {
                        alert("Post successfully deleted");
                    } else if (response.status === 'error') {
                        //error message
                        alert("ERROR: Delete could not be done!");
                    }
                fetchAndUpdateMyPosts(document.getElementById('dropdownMenuPage').value);
                },
                error: function (e) {
                    console.log("Error:", e.responseText); // e.responseText for more detailed error information
                }
            });
        }
    
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const saveButton = document.getElementById('saveButton');
            saveButton.addEventListener('click', function() {
                // Code to execute when the button is clicked
                savePost();
            });
        });


        function getSubscribedUsers(topic_ID,postnum , title,content){
            $.ajax({
                url: "subscribers.php",
                type: "GET",
                data: {topic_ID: topic_ID, postnum: postnum },
                dataType: 'json',
                success: function (response) {
                if (response.status === 'success') {
                    console.log(response.emails);
                    // go to the post updates table and find all users that have an update for that specific post
                    //returns array of emails
                    // use emails to send emails to say the post is updated
                    if(response.emails.length>0){
                        let emailsString = response.emails.map(entry => entry.user_email).join(',');
                        let subject= "Post Update for "+title;
                        let newContent="Here is the updated post: %0A"+content +" %0A Yours Sincerely %0A Post Owner";
                        window.open('mailto:'+emailsString+'?subject='+subject+'&body='+newContent, '_self');
                    }
                } else if (response.status === 'failed') {
                    //error message
                    alert('unsuccessful');
                }
                fetchAndUpdateMyPosts(document.getElementById('dropdownMenuPage').value);
                closePopup('editPopup');
                },
                error: function (e) {
                    console.log("Error:", e.responseText); // e.responseText for more detailed error information
                }
            });
        }

    function savePost(){
        let id=document.getElementById('saveButton').getAttribute('data-item-id');
        let parts = id.split("-");
        let topic_ID = parts[0];
        let postnum = parts[1];
        let content=document.getElementById('postContent').value;
        let title=document.getElementById('postTitle').value;
        $.ajax({
            url: "editPost.php",
            type: "POST",
            data: {topic_ID: topic_ID, postnum: postnum, content:content,title:title },
            dataType: 'json',
            success: function (response) {
            if (response.status === 'success') {
                //success message
                console.log('success');
                alert('Your new edited post has been saved.');
                let text = "Would you like to update users subscribed to your post";
                
                if (confirm(text) === true) {
                    getSubscribedUsers(topic_ID,postnum , title,content);
                }
            } else if (response.status === 'failed') {
                //error message
                console.log('unsuccessful');
                alert("ERROR: Edit could not be saved!");
            }
            fetchAndUpdateMyPosts(document.getElementById('dropdownMenuPage').value);
            closePopup('editPopup');  
            },
            error: function (e) {
                console.log("Error:", e.responseText); //e.responseText for more detailed error information
            }
        });
    }
</script>


<script>
document.addEventListener('DOMContentLoaded', function() {
    const deleteButton = document.getElementById('deleteButtonSubmit');

    deleteButton.addEventListener('click', function() {
        // Code to execute when the button is clicked
        console.log('Delete button clicked');
        // You can call your delete function here
        deletePost();
    });
});

function deletePost(element) {
    // Implementation of your delete logic
    console.log('Deleting post...');
    // Optionally, you could use AJAX here to call a server-side script to delete the post
    let id=document.getElementById('deleteButtonSubmit').getAttribute('data-item-id');
    let parts = id.split("-");
    let topic_ID = parts[0];
    let postnum = parts[1];
    console.log("topic id:", topic_ID);
    $.ajax({
        url: "deletepost.php",
        type: "POST",
        data: {topic_ID: topic_ID, postnum: postnum },
        dataType: 'json',
        success: function (response) {
            if (response.status === 'success') {
                //success message
                alert("Post successfully deleted");
            } else if (response.status === 'error') {
                //error message
                alert("Error: Delete could not be done!");
            }
            fetchAndUpdateMyPosts(document.getElementById('dropdownMenuPage').value);
            closePopup('deletePopup');
        },
        error: function (e) {
            console.log("Error:", e.responseText); // Note: Changed to e.responseText for more detailed error information
        }
    });
} 

</script>

<script>
    function fetchAndUpdateMyPosts(pagenum) {
        //sessions needed here
		let filter="default";
        $.ajax({
            url: "posts.php",
            type: "GET",
            data: { topic: "all", email:email,filter:filter,pagenum:pagenum },
            dataType: 'json',
            success: function(postsData) {
                
                // Clear existing posts before appending new ones to avoid duplication
                document.getElementById("postContainer").innerHTML = "";
                for (let i = 0; i < postsData.length; i++) {
                    let id = postsData[i].Topic_ID +"-"+ postsData[i].PostNo +"-";
                    let title = postsData[i].title;
                    let date = postsData[i].date_updated;
                    let content = postsData[i].content;
                            
                    createPostElement(id, title, content,date);
                }
            },
            error: function (e) {
                console.error("Could not fetch posts:", e.message);
            }
        });
    }
    </script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectElement = document.getElementById('dropdownMenuPage');
        //sessions needed here
        let filter="default";
        selectElement.addEventListener('change', function() {
        let pagenum= this.value;
        console.log(pagenum);
        console.log({ email, filter, pagenum });
             $.ajax({
            url: "posts.php",
            type: "GET",
            data: { topic: "all", email: email,pagenum:pagenum, filter:filter},
            dataType: 'json',
            success: function(postsData) {
            console.log(postsData);
                // Clear existing posts before appending new ones to avoid duplication
                document.getElementById("postContainer").innerHTML = "";
             // Total number of rows available
                fetchAndUpdateMyPosts(pagenum);
            },
           error: function(xhr, status, error) {
        console.error("Could not fetch posts:", xhr.statusText);
        alert("Error: " + xhr.statusText + "\nStatus: " + status + "\nError: " + error);
    }    });
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
        <div class="white-div">
            <div class="content" id="knowledgeContent" style="display: block;">
                <!-- Content for the 'Knowledge' tab -->
                <div id="myPostsNavbar" class="myPostsNavbarContent">
                     <div class="dropdown-container">
                         <select id="dropdownMenuPage">
                        </select>
                    </div>
                    <div  class="dropdown-container">
                        <select id="dropdownMenuFilter">
                            <option value="allPosts">All posts</option>
                            <option value="mostLiked">Most Liked</option>
                            <option value="recent">Recent</option>
                        </select>
                    </div>
                </div>
                <hr> 
                <ul class="breadcrumbs">
                    <li class="breadcrumbs-item">
                        <a href="standard_index.php" class="breadcrumbs_link">Home</a>
                    </li>
                    <li class="breadcrumbs-item">
                        <a href="contentHub.php" class="breadcrumbs_link">My Hub</a>
                    </li>
                    <li class="breadcrumbs-item">
                        <a href="myPosts.php" class="breadcrumbs_link--active" 
                        breadcrumbs_link--active>My Posts</a>
                    </li>
                </ul>
                <div id="postContainer" class="posts"></div> 
                <!-- Delete Popup HTML -->
                <div class="overlay" id="overlay"></div>
                <div class="deletePopup-container" id="deletePopup">
                    <div class="icon">&#x1F5D1;</div>
                    <div class="confirmation-message">Are you sure you want to delete?</div>
                    <div class="button-container">
                        <button id="deleteButtonSubmit">Delete</button>
                        <button id="cancelButtonSubmit" onclick="closePopup('deletePopup')">Cancel</button>
                    </div>
                    <button class="closePopup" onclick="closePopup('deletePopup')">&times;</button>
                </div>

                <!-- Edit Popup HTML -->
                <div class="overlay" id="overlay"></div>
                <div class="popup" id="editPopup">
                    <h2>Edit Post</h2>
                    <form id="editPostForm">
                        <label for="postTitle">Post Title:</label>
                        <input type="text" id="postTitle" name="postTitle" required style="width: 30vw;">
                        <textarea id="postContent" name="postContent" required style="height:30vh; width: 50vh;"></textarea>
                        <div class="button-container">
                            <button type="button" id="saveButton">Save</button>
                            <button type="button" id="cancelButton" onclick="closePopup('editPopup')">Cancel</button>
                        </div>
                    </form>
                    <button class="closePopup" onclick="closePopup('editPopup')">&times;</button>
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

    function openPopup(popupId,itemId,content,title) {
        document.getElementById('overlay').style.display = 'block';
        document.getElementById(popupId).style.display = 'block';
        if (popupId=="deletePopup"){
            var deleteButton = document.getElementById('deleteButtonSubmit');
            deleteButton.setAttribute('data-item-id', itemId);
        } else if (popupId=="editPopup"){
            var saveButton = document.getElementById('saveButton');
            saveButton.setAttribute('data-item-id', itemId);
            document.getElementById("postContent").value=content;
            document.getElementById("postTitle").value =title;
        }
    }

    function closePopup(popupId) {
        document.getElementById('overlay').style.display = 'none';
        document.getElementById(popupId).style.display = 'none';
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