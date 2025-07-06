opened_conversation = 0;
number_messages = 100;

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.querySelector('.search-input');
    searchInput.addEventListener('input', function() {
        const filterSearch = this.value.toLowerCase();
        const chatListContacts = document.getElementById('chat-list');
        const conversationsDiv = chatListContacts.getElementsByClassName('contact');
        
        for (i=0; i<conversationsDiv.length; i++) {
            const txtValue = conversationsDiv[i].getElementsByClassName('conversation-title')[0].textContent;
            if (txtValue.toLowerCase().indexOf(filterSearch) > -1) {
                conversationsDiv[i].style.display = "";
            } else {
                conversationsDiv[i].style.display = "none";
            }
        }

    });
        

    // Create chat container - holds the whole chat
    const chatContainer = document.getElementById('chat');

    // Create contact name container
    const chatTitleContainer = document.createElement('div');
    chatTitleContainer.id = 'chatTitleContainer';
    chatTitleContainer.style = "display: flex; justify-content: space-between;"

    // Create contact name element
    const chatTitle = document.createElement('h2');
    chatTitle.id = 'chatTitle';
    chatTitle.textContent = '';

    const exitGroupButton = document.createElement('button');
    exitGroupButton.id  = "exitGroupButton";
    exitGroupButton.textContent = "Leave Chat";

    // Append contact name to contact name container
    chatTitleContainer.appendChild(chatTitle);

    chatTitleContainer.appendChild(exitGroupButton);

    // Append contact name container to chat container
    chatContainer.appendChild(chatTitleContainer);

    // Create messages container
    const messagesContainer = document.createElement('div');
    messagesContainer.id = "messages-container"
    messagesContainer.classList.add('messages-container');

    // Append messages container to chat container
    chatContainer.appendChild(messagesContainer);
        
    const sendDiv = document.createElement('div');
    sendDiv.id = "sendDiv";
    sendDiv.style = "padding-top: 10px";

    // Create message input textarea
    const messageInputDiv = document.createElement('div');
    messageInputDiv.innerHTML = `<input type="text" class="form-control" id='message-input' placeholder="Message">`
    messageInputDiv.style = "width: 100%"
    sendDiv.appendChild(messageInputDiv);

    messageInputDiv.addEventListener('keydown', function(event) {
        if (event.keyCode === 13) {
            sendMessage()
        }
    })
        
    // Create send button
    const sendButton = document.createElement('button');
    sendButton.id = 'send-button';
    sendButton.style.display = "block";
    sendButton.textContent = 'Send';
    sendButton.addEventListener('click', sendMessage); 
    sendDiv.appendChild(sendButton);
    chatContainer.appendChild(sendDiv);
    document.body.appendChild(chatContainer);
    
    
    function createContactElements(data) {
        const chatListDiv = document.getElementById('chat-list');
        chatListDiv.innerHTML = ""; 

        
        // Iterate over each conversation data
        data.forEach(function(conversationData) {
            // Extracting the relevant data from the JSON
            const conversationTitle = conversationData.title;
            const updatedAt = conversationData.updated_at;
            const lastMessageText = conversationData.message_text;

            const contactDiv = document.createElement('div');
            contactDiv.classList.add('contact');
            contactDiv.setAttribute('id' ,conversationTitle);
            contactDiv.name = conversationTitle;


            const topLineDiv = document.createElement('div');
            topLineDiv.style = "justify-content: space-between; display: flex; align-items: center"
    
            // Name of chat
            const contactName = document.createElement('div');
            contactName.classList.add('conversation-title');
            contactName.style = "font-size: 25px"
            contactName.textContent = conversationTitle;
            
            // Create heading element to show last updated time
            const lastMessage = document.createElement('div');
            
            lastMessage.textContent = lastMessageText;
    
            // Create heading element to show last updated time
            const lastUpdatedHeading = document.createElement('div');
            lastUpdatedHeading.style = "font-size: 20px";
            lastUpdatedHeading.textContent = compareDates(updatedAt);;

            topLineDiv.appendChild(contactName);
            topLineDiv.appendChild(lastUpdatedHeading);
    
            // Append elements to contact div
            contactDiv.appendChild(topLineDiv);
            contactDiv.appendChild(lastMessage);
 
    
            // Append contact div to contacts container
            chatListDiv.appendChild(contactDiv);
    

            // get the div elements
            const chatsDiv = document.querySelector('#chat');
            
            // add a click event listener to the div
            contactDiv.addEventListener('click', function() {
            // specify the action to take when the div is clicked
                
                opened_conversation = conversationData.conversation_id;
                number_messages = 100; // Reset back to default on click
                
                chatsDiv.style.display = 'block';
                const chatTitle = document.getElementById('chatTitle');
                
                chatTitle.textContent = conversationTitle;

                
                exitGroupButton.onclick = function() {
                    confirmExitGroup = confirm("Are you sure you want to leave this group?")
                    if (confirmExitGroup) {
                        fetch("/api/chat/conversation", {
                            method: "DELETE",
                            headers: {
                                "Content-Type": "application/json",
                                'Authorization': token
                            },
                            body: JSON.stringify({conversation_id: conversationData.conversation_id})
                        })
                        .then(response => response.json())
                        .then(data => {
                            refreshWholePage()
                            
                        })
                    }
                }
                

                const token = localStorage.getItem('jwt');
                fetch(`/api/chat/message?requested_conversation=${conversationData.conversation_id}&number_messages=100`, {
                    method: 'GET',
                    headers: {
                        "Content-Type": "application/json",
                        'Authorization': token 
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status == 200) {
                        populateChat(data.data);
                        scrollToBottom();
                        let sentDivs = document.querySelectorAll('.sent-messages-container');
                        // Loop through each sentDiv
                        sentDivs.forEach(sentDiv => {
                            // Attach event listener for mouseover
                            sentDiv.addEventListener('mouseover', function() {
                                showButtons(sentDiv.id);
                                
                            });
                            // Attach event listener for mouseout
                            sentDiv.addEventListener('mouseout', function() {
                                hideButtons(sentDiv.id); // Pass the id of the hovered element
                            });

                            
                        });
                    }
                })
                .catch(error => console.log('Error:', error));
            });
        });
    }
    
    const token = localStorage.getItem('jwt');
    function loadChats(){
        fetch('/api/chat/conversation', {
        method: 'GET',
        headers: {
            "Content-Type": "application/json",
            'Authorization': token
        }
        })
        .then(response => response.json())
        .then(data => {
            
            if (data.status == 200) {
                const chatListDiv = document.getElementById('chat-list');
                const chatSearchListDiv = document.getElementById('chat-search-list');
                chatListDiv.innerHTML ="";
                chatSearchListDiv.innerHTML ="";

                createContactElements(data.data)
            
            } else {
                console.log(data.status, data.status_message)
            }
        })
        .catch(error => console.log('Error:', error));
    }


    loadChats();
    

    function scrollToBottom() {
        const container = document.getElementById("messages-container");
        container.scrollTop = container.scrollHeight;
    }


    async function populateChat(data) {
        messagesContainer.innerHTML = "";

        const loadButtonContainer = document.createElement('div');
        loadButtonContainer.style = "display: flex; justify-content: center;"
        messagesContainer.appendChild(loadButtonContainer);

        const loadMoreChats = document.createElement('button');
        loadMoreChats.classList = "btn btn-outline-secondary";
        loadMoreChats.textContent = "Load more messages"
        loadMoreChats.onclick = function () {
            number_messages += 100;
            refreshChat(number_messages);
        };

        if (data.length >= 99) {
            loadButtonContainer.appendChild(loadMoreChats);
        }
        
        // Iterate through the messages data in reverse (from oldest to newest)
        data.slice().reverse().forEach(function(message) {
            
            // Creating a break accross the screen to separate days
            const index = data.slice().reverse().indexOf(message)
            if (index == 0) {
                const separatorContainer = document.createElement('div');
                separatorContainer.style = "text-align: center; color: #7a7a7a; margin: 10px"
                const weekday = ["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"];
                const months = ["placeholder", "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
                const newDayDiv = document.createElement('div');
                separatorContainer.appendChild(newDayDiv);
                const serverDate = parseDateString(message.time_sent);
                newDayDiv.innerHTML =  `<small>${weekday[serverDate.getDay()]}, ${message.time_sent.substring(8,10)} ${months[parseInt(message.time_sent.substring(5,7))]}</small>`;
                messagesContainer.appendChild(separatorContainer);


            } else if (data.slice().reverse()[index-1].time_sent.substring(5,10) < message.time_sent.substring(5,10)){
                const separatorContainer = document.createElement('div');
                separatorContainer.style = "text-align: center; color: #7a7a7a; margin: 10px"
                const weekday = ["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"];
                const months = ["placeholder", "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
                const horizontalSeparator = document.createElement('hr');
                separatorContainer.appendChild(horizontalSeparator);
                const newDayDiv = document.createElement('div');
                separatorContainer.appendChild(newDayDiv);
                const serverDate = parseDateString(message.time_sent);
                newDayDiv.innerHTML =  `<small>${weekday[serverDate.getDay()]}, ${message.time_sent.substring(8,10)} ${months[parseInt(message.time_sent.substring(5,7))]}</small>`;
                messagesContainer.appendChild(separatorContainer);
            }


            // Check if it's own message or received message and append accordingly
            if (message.own_message === 1) {
                // Create sent messages div
                const message_id = message.message_id;
                const sentMessagesDiv = document.createElement('div');
                sentMessagesDiv.classList.add('sent-messages-container');
                sentMessagesDiv.setAttribute('id', message_id);
                sentMessagesDiv.style = "display: flex; flex-direction: row;"
                const sentMessages = document.createElement('div');
                sentMessages.classList.add('sent-messages');
                const messageText = document.createElement('div');
                if (message.message_text == null) {
                    messageText.innerHTML = `<i>This message was deleted.</i><sub style="margin-left: 10px"><small>${message.time_sent.substring(11,16)}</small></sub>`;
                    messageText.classList.add("deletedMessage");
                } else {
                    if (message.edited_at != null) {
                        messageText.innerHTML = `<div style="display: inline-block;"><div class="messageTextHere" style="display: inline-block; width: fit-content">${message.message_text}</div>   <sub style="margin-left: 10px"><small>Edited ${message.time_sent.substring(11,16)}</small></sub></div>`;
                    } else {
                        messageText.innerHTML = `<div style="display: inline-block;"><div class="messageTextHere" style="display: inline-block; width: fit-content">${message.message_text}</div>   <sub style="margin-left: 10px"><small>${message.time_sent.substring(11,16)}</small></sub></div>`;

                    }
                    messageText.classList.add("messageText");
                }
                
                
                sentMessages.appendChild(messageText);
                // Set the ID attribute of the new div element
        
                const messageOptions = document.createElement('div');
                messageOptions.classList.add('button-Options');
                const deleteButtonMessage = document.createElement('button');
                deleteButtonMessage.classList.add('button');
                deleteButtonMessage.classList.add('delete-button'); 
                deleteButtonMessage.setAttribute('id', "deleteButton"+message_id);
                deleteButtonMessage.innerHTML = '<img src="delete.svg" alt="Delete Message">';
                const editButton = document.createElement('button');
                editButton.classList.add('button');
                editButton.classList.add('edit-button'); 
                editButton.setAttribute('id', "editButton"+message_id);
                editButton.innerHTML = '<img src="edit.svg" alt="Edit Message">';
                editButton.addEventListener('click', function() {
                    editMessage(message_id);
                });
                deleteButtonMessage.addEventListener('click', function() {
                    deleteMessage(message_id);
                });
                messageOptions.appendChild(editButton);
                messageOptions.appendChild(deleteButtonMessage);
                messageOptions.setAttribute('id', "buttons"+message_id);
                if (message.message_text != null) {
                    sentMessagesDiv.appendChild(messageOptions); 

                }
                sentMessagesDiv.appendChild(sentMessages);
        

                const index = data.slice().reverse().indexOf(message)

                if (index == 0 || data.slice().reverse()[index-1].time_sent.substring(5,10) < message.time_sent.substring(5,10)) {
                    sentMessages.classList.add('large-rounded-top-right-corner');
                } else if (data.slice().reverse()[index-1].owner_id == message.owner_id) {
                    sentMessages.classList.add('small-rounded-top-right-corner');
                } else {
                    sentMessages.classList.add('large-rounded-top-right-corner');
                }
                
                
                
                if (index+1 == data.length  || data.slice().reverse()[index+1].time_sent.substring(5,10) > message.time_sent.substring(5,10)) {
                    sentMessages.classList.add('large-rounded-bottom-right-corner');
                } else if (data.slice().reverse()[index+1].owner_id == message.owner_id) {
                    sentMessages.classList.add('small-rounded-bottom-right-corner');
                } else {
                    sentMessages.classList.add('large-rounded-bottom-right-corner');
                }

                

                sentMessages.classList.add('large-rounded-left')
                
                // Append sent and received messages to messages container
                messagesContainer.appendChild(sentMessagesDiv);

            } else {
                // Create received messages div
                const receivedMessages = document.createElement('div');
                receivedMessages.classList.add('received-messages');
                receivedMessages.classList.add('large-rounded-right');
                
                
                const index = data.slice().reverse().indexOf(message)

                if (index == 0  || data.slice().reverse()[index-1].time_sent.substring(5,10) < message.time_sent.substring(5,10)) {
                    receivedMessages.innerHTML = `<small style="color: salmon">${message.first_name+" "+message.second_name}</small><br>${message.message_text}<sub style="margin-left: 10px"><small>${message.time_sent.substring(11,16)}</small></sub>`;
                    receivedMessages.classList.add('large-rounded-top-left-corner');
                } else if (data.slice().reverse()[index-1].owner_id == message.owner_id) {
                    if (message.message_text == null) {
                        receivedMessages.innerHTML = `<i>This message was deleted.</i><sub style="margin-left: 10px"><small>${message.time_sent.substring(11,16)}</small></sub>`;
                    } else {
                        if (message.edited_at != null) {
                            receivedMessages.innerHTML = `${message.message_text}<sub style="margin-left: 10px"><small>Edited ${message.time_sent.substring(11,16)}</small></sub>`;

                        } else {
                            receivedMessages.innerHTML = `${message.message_text}<sub style="margin-left: 10px"><small>${message.time_sent.substring(11,16)}</small></sub>`;

                        }
                    }
                    receivedMessages.classList.add('small-rounded-top-left-corner');
                    

                } else {
                    if (message.message_text == null) {
                        receivedMessages.innerHTML = `<small style="color: salmon">${message.first_name+" "+message.second_name}</small><br><i>This message was deleted.</i><sub style="margin-left: 10px"><small>${message.time_sent.substring(11,16)}</small></sub>`;
                    } else {
                        if (message.edited_at != null) {
                            receivedMessages.innerHTML = `<small style="color: salmon">${message.first_name+" "+message.second_name}</small><br>${message.message_text}<sub style="margin-left: 10px"><small>Edited ${message.time_sent.substring(11,16)}</small></sub>`;
                        } else {
                            receivedMessages.innerHTML = `<small style="color: salmon">${message.first_name+" "+message.second_name}</small><br>${message.message_text}<sub style="margin-left: 10px"><small>${message.time_sent.substring(11,16)}</small></sub>`;

                    }

                    }
                    receivedMessages.classList.add('large-rounded-top-left-corner');
                    

                }

                if (index+1 == data.length  || data.slice().reverse()[index+1].time_sent.substring(5,10) > message.time_sent.substring(5,10)){
                    receivedMessages.classList.add('large-rounded-bottom-left-corner');
                } else if (data.slice().reverse()[index+1].owner_id == message.owner_id) {
                    receivedMessages.classList.add('small-rounded-bottom-left-corner');
                } else {
                    receivedMessages.classList.add('large-rounded-bottom-left-corner');
                }
                    
                                    
                
                messagesContainer.appendChild(receivedMessages);
            }
        });
    }


    async function sendMessage() {
        let messageInput = document.getElementById('message-input');
        let messageInputText = messageInput.value;
        try {
            let response = await fetch("/api/chat/message", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    'Authorization': token
                },
                body: JSON.stringify({ message: messageInputText, conversation_id: opened_conversation })
            });
            let data = await response.json();
            await refreshChat(number_messages);
            await loadChats();
            scrollToBottom();
        } catch (error) {
            console.log('Send Message Error:', error);
        }
        messageInput.value = "";
    }

    async function refreshChat(number_messages) {
        setTimeout(async () => { // Delay fetching to let the server update
            try {
                let response = await fetch(`/api/chat/message?requested_conversation=${opened_conversation}&number_messages=${number_messages}`, {
                    method: 'GET',
                    headers: {
                        "Content-Type": "application/json",
                        'Authorization': token
                    }
                });
                let data = await response.json();
    
                if (data.status == 200) {
                    await populateChat(data.data);
                    let sentDivs = document.querySelectorAll('.sent-messages-container');
                    // Loop through each sentDiv
                    sentDivs.forEach(sentDiv => {
                        // Attach event listener for mouseover
                        sentDiv.addEventListener('mouseover', function() {
                            showButtons(sentDiv.id);
                            
                        });
                    
                        sentDiv.addEventListener('mouseout', function() {
                            hideButtons(sentDiv.id);
                        });
                    });
                } else {
                    console.log(data.status, data.status_message);
                }
            } catch (error) {
                console.log('Refresh Chat Error:', error);
            }
        }, 1000);
    }



    
    usersNewChat = [];
    const nameTagInput = document.getElementById('tag-input');

    nameTagInput.addEventListener('keydown', function() {
        document.getElementById('dropdownNames').style.display = "block";
    });

    nameTagInput.addEventListener('click', function() {
        document.getElementById('dropdownNames').style.display = "block";        
    })

    document.getElementById('groups-button').addEventListener('click', async function() {
        let response = await fetch(`/api/chat/other_users`, {
            method: 'GET',
            headers: {
                "Content-Type": "application/json",
                'Authorization': token
            }
        });

        let data = await response.json();

        const dropdownNames = document.getElementById('dropdownNames')
        dropdownNames.innerHTML = "";
        data.data.forEach(username => {
            var opt = document.createElement("div");
            opt.value = username.user_id;
            opt.innerHTML = `${username.first_name} ${username.second_name}`;
            opt.classList.add("dropdown-item");
            opt.onclick = function () {
                addUsernameTag(username.user_id, `${username.first_name} ${username.second_name}`)
            };

            dropdownNames.appendChild(opt)
        });
    });

    function addUsernameTag(userID, userName){
        
        if (!(usersNewChat.includes(userID))){
            const nameBadge = document.createElement('div');
            nameBadge.classList.add('name-badge');
            nameBadge.style = 'display:inline-block; min-width: fit-content; cursor: pointer'
            nameBadge.id = `nameBadge${userID}`;
            const nameBadgeText = document.createElement('small');
            nameBadgeText.innerHTML = userName;
            nameBadgeText.style = 'display:inline-block'
            const nameBadgeRemove = document.createElement('div');
            nameBadgeRemove.innerHTML = "&#x2715";
            nameBadgeRemove.style = "padding-left: 10px; display:inline-block; cursor: pointer;";
            
            nameBadge.appendChild(nameBadgeText);
            nameBadge.appendChild(nameBadgeRemove);
            const nameBadgeContainer = document.getElementById('tags')
            nameBadgeContainer.appendChild(nameBadge);
            usersNewChat.push(userID);
            nameBadgeRemove.addEventListener('click', function(){
                removeNameBadge(userID)
            });
        }

        nameTagInput.value = "";
    }


    nameTagInput.addEventListener('input', function(event) {
        const filter = nameTagInput.value.toLowerCase();
        const dropdowndiv = document.getElementById('dropdownNames');
        const nameDropdownDivs = dropdowndiv.getElementsByTagName('div');

        
    
        for (let i = 0; i < nameDropdownDivs.length; i++) {
            const txtValue = nameDropdownDivs[i].textContent.toLowerCase();
            if (txtValue.includes(filter)) {
                nameDropdownDivs[i].style.display = "";
            } else {
                nameDropdownDivs[i].style.display = "none";
            }
        }
    });
    
    // Additional handler for handling key specific operations like Enter or Backspace
    nameTagInput.addEventListener('keydown', function(event) {
        const filter = nameTagInput.value.toLowerCase();
        const dropdowndiv = document.getElementById('dropdownNames');
        const nameDropdownDivs = dropdowndiv.getElementsByTagName('div');
        let nameCount = []

        for (i=0; i<nameDropdownDivs.length; i++) {
            txtValue = nameDropdownDivs[i].textContent;
            if (txtValue.toLowerCase().indexOf(filter) > -1) {
                nameCount.push(nameDropdownDivs[i]);
            }
        }


        if (event.keyCode === 8) { // Backspace key
            const filter = nameTagInput.value.toLowerCase();
            if (filter.length === 0) {
                event.preventDefault();
                removeNameBadge(usersNewChat[usersNewChat.length - 1]);
            }
        } else if (event.keyCode === 13) { // Enter key
            event.preventDefault();
            if (nameCount.length == 1) {
                addUsernameTag(nameCount[0].value, nameCount[0].textContent)
                for (i=0; i<nameDropdownDivs.length; i++) {
                    nameDropdownDivs[i].style.display = "";
                }
            }
        } 
    });
    

    function removeNameBadge(id){
        document.getElementById(`nameBadge${id}`).remove();
        const index = usersNewChat.indexOf(id);
        if (index > -1) {
            usersNewChat.splice(index, 1);
        }
    }
    
    function editMessage(messageId){
        const sendButtonMsg = document.getElementById('send-button');
        if (sendButtonMsg.style.display == "block") {
            const inputMessageDiv = document.getElementById('message-input');
            const currentMessage = document.getElementById(messageId).getElementsByClassName("messageText")[0].getElementsByClassName('messageTextHere')[0].textContent
            inputMessageDiv.value = currentMessage;
            
            sendButtonMsg.style.display = "none";
            const editButtonMsgConfirm = document.createElement('button');
            editButtonMsgConfirm.textContent = "Edit"
            const cancelEditButton = document.createElement('button');
            cancelEditButton.textContent = "Cancel"
            editButtonMsgConfirm.onclick = function() {
                const editMessageNewText = inputMessageDiv.value;
                fetch("/api/chat/message", {
                    method: "PATCH",
                    headers: {
                        "Content-Type": "application/json",
                        'Authorization': token
                    },
                    body: JSON.stringify({message_id: messageId, message: editMessageNewText})
                })
                .then(response => response.json())
                .then(data => {
                    editButtonMsgConfirm.remove()
                    cancelEditButton.remove()
                    sendButtonMsg.style.display = "block";
                    inputMessageDiv.value = "";
                    refreshWholePage();
                    
        
                })
            };
    
            cancelEditButton.onclick = function() {
                editButtonMsgConfirm.remove()
                cancelEditButton.remove()
                sendButtonMsg.style.display = "block";
                inputMessageDiv.value = "";
    
            };
    
            document.getElementById('sendDiv').appendChild(editButtonMsgConfirm);
            document.getElementById('sendDiv').appendChild(cancelEditButton);
        }        
    }
    
    function deleteMessage(messageID){
        let confirmDelete = confirm("Are you sure you want to delete this?");
        if (confirmDelete) {
            fetch("/api/chat/message", {
                method: "DELETE",
                headers: {
                    "Content-Type": "application/json",
                    'Authorization': token
                },
                body: JSON.stringify({message_id: messageID})
            })
            .then(response => response.json())
            .then(data => {
                refreshWholePage()
            })
        }
    }

    // Function to show buttons
    function showButtons(id) {
        const buttonsDiv = document.getElementById('buttons' + id);
        if (buttonsDiv) {
            buttonsDiv.style.display = 'block';
        }
        
    }

// Function to hide buttons
    function hideButtons(id) {
        const buttonsDiv = document.getElementById('buttons' + id);
        if (buttonsDiv) {
            buttonsDiv.style.display = 'none';

        }
    }

    

    document.getElementById('data-button').addEventListener('click', function() {
        window.location.href = '../data-resources/IndividualData/DataAnalysisIndividualFrontEnd.html';
    });

    document.getElementById('logout-button').addEventListener('click', function() {
        let confirmLogout = confirm('Are you sure you want to log out?');
        
        if (confirmLogout) {
            localStorage.removeItem('jwt');
            window.location.href = '../LoginSystem/Login_System.php';
        }
    });


    function isClickInsideElement(event, element) {
        return element.contains(event.target);
    }

    // Hide dropdown when clicking outside
    document.addEventListener('click', function (event) {
        const dropdownNames = document.getElementById('dropdownNames');
        const tagInput = document.getElementById('tag-input');
        const isClickInsideDropdown = isClickInsideElement(event, dropdownNames);
        const isClickInsideNameInput = isClickInsideElement(event, tagInput)

        if (!(isClickInsideNameInput)) {
            dropdownNames.style.display = 'none';
        } else {
            dropdownNames.style.display = "block";
        }
    }, true);


    document.getElementById('buttonCreateNewChat').addEventListener('click', function() {
        const convo_title = document.getElementById('createGroupName').value
        if (usersNewChat.length >= 1 && convo_title != "") {
            fetch("/api/chat/conversation", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    'Authorization': token
                },
                body: JSON.stringify({conversation_title: convo_title, conversation_users: usersNewChat})
            })
            .then(response => response.json())
            .then(data => {
                loadChats();
                document.getElementById('createGroupName').value = "";
                while (usersNewChat.length > 0) {
                    removeNameBadge(usersNewChat.shift());
                }
                let myModalEl = document.getElementById('exampleModal')
                let modal = bootstrap.Modal.getInstance(myModalEl)
                modal.hide()

            })
        } else {
            console.log("One of the fields left blank, need both to complete");
        }
        
    });

    document.getElementById('buttonCancelCreateChat').addEventListener('click', function(){
        document.getElementById('createGroupName').value = "";
        while (usersNewChat.length > 0) {
            removeNameBadge(usersNewChat.shift());
        }
    })


    function refreshWholePage() {
        loadChats();
        refreshChat(number_messages);
    }

    setInterval(refreshWholePage, 5000);
});  

function parseDateString(dateString) {
    return new Date(dateString);
}


// Function to compare two dates and check differences
function compareDates(serverDateString) {
    const weekday = ["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"];
    

    const isoCurrentDate = new Date().toISOString();
    const currentDate = new Date();
    const serverDate = parseDateString(serverDateString);

    const diff = currentDate.getTime() - serverDate.getTime();
    const minutes = Math.floor(diff / (1000 * 60));
    const hours = Math.floor(diff / (1000 * 60 * 60));

    serverDateAsInt = parseInt(serverDateString.substring(0,4) + serverDateString.substring(5,7) + serverDateString.substring(8,10))
    currentDateAsInt = parseInt(isoCurrentDate.substring(0,4) + isoCurrentDate.substring(5,7) + isoCurrentDate.substring(8,10))

    if (currentDateAsInt > serverDateAsInt + 4) {
        return serverDateString.substring(8,10) + "/" + serverDateString.substring(5,7);
    } else if (currentDateAsInt > serverDateAsInt + 1) {
        return weekday[serverDate.getDay()];
    } else if (currentDateAsInt == serverDateAsInt + 1) {
        return "Yesterday";
    } else if (hours >= 1) {
        return serverDateString.substring(11,16);
    } else if (minutes >= 2) {
        return `${minutes}m`
    } else {
        return "now";
    }
}

