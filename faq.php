<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="styles_main2.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
	<script>
    function toggleExpansion(element) {
    var content = element.querySelector(".box-content");
  if (content.style.display === "none") {
   
    content.style.display = "block";
    element.classList.add("expanded");
   } else {
   
    content.style.display = "none";
    element.classList.remove("expanded");
    }
    }
	</script>
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
                
                <div class="white-div">
                    <br>
                    <h1 class="faq-title">Frequently Asked Questions</h1>
                    <ul class="breadcrumbs">
                        <li class="breadcrumbs-item">
                            <a href="standard_index.php" class="breadcrumbs_link">Home</a>
                        </li>
                        <li class="breadcrumbs-item">
                            <a href="faq.php" class="breadcrumbs_link--active" 
                            breadcrumbs_link--active>FAQS</a>
                        </li>
                    </ul>
                    <div id="faqPosts">

                    </div>
                      
                </div>
        </div>
    <!-- section for footer -->
    <div class="footer">
        <p><u>Make It All</u> <u>Acceptable Use Policy </u></p>
    </div>
    <script>


function createFAQ(category, title, content)
{
    let faqContainer = document.getElementById("faqPosts");
    let div = document.createElement('div');
div.classList.add('expandable-box');

// Create an h2 element with the text "title 1"
let h2 = document.createElement('h2');
h2.textContent = title;

// Create a p element with the class "box-content" and initial style "display: none;"
let p = document.createElement('p');
p.classList.add('box-content');
p.style.display = 'none';
p.textContent = content; // Add your content here
let h4=document.createElement('h4')
h4.style.display = 'none';
h4.textContent = category; // Add your content here
// Append the h2 and p elements to the div element
div.appendChild(h2);
div.appendChild(p);
div.appendChild(h4);
div.style.marginBottom = '30px';
// Add an onclick event listener to toggle the display of the p element
div.addEventListener('click', function() {
    p.style.display = p.style.display === 'none' ? 'block' : 'none';
    h4.style.display = h4.style.display === 'none' ? 'block' : 'none';
});
faqContainer.append(div);
}
      $(document).ready(function() {
                $.ajax({
                    url: "getFaq.php",
                    type: "GET",
		            dataType: 'json',
                    success: function (postsData) {
                        let len = postsData.posts.length;
                        for (let i = 0; i < len; i++) {
                            let category= postsData.posts[i].category;
                            let title= postsData.posts[i].title;
                            let content = postsData.posts[i].content;
                            createFAQ(category, title, content);
                            console.log(postsData)
               		 }
                    
                    },
                    error: function (e) {
                        console.log(e.message);
                    }
                });
    
            });



    //all the linking and nav bar functionality
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
        $('#KnowledgeButton').addClass("active");
        $('.arrow').css('display','block');
    });
</script>
<script src="/PKMS/link.js"></script>

</body>
</html>
