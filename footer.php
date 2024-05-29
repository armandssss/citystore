<div class="circular-button" onclick="toggleChat()">
    <i class="fas fa-robot"></i>
</div>

<div class="chatbox" id="chatbox">
    <button class="close-btn" onclick="toggleChat()">&times;</button>
    <div class="chat-header">
        Welcome to Support Chat
    </div>
    <div class="chat-body" id="chat-body">
        <div class="messages" id="messages-container"></div>
        <div class="chat-footer">
            <form id="chat-form" onsubmit="submitChat(event)" style="display: flex; width: 100%;">
                <textarea id="message" class="chat-message" name="message" required placeholder="Type your message" onkeydown="if (event.key === 'Enter') submitChat(event)"></textarea>
                <input type="submit" id="submitButton" class="submit-button" value="Send">
            </form>
        </div>
    </div>
    <div class="chat-start-form" id="chat-start-form">
        <div class="start-chat-body">
            <form id="start-chat-form" onsubmit="startChat(event)">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" required>

                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>

                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" required>

                <input type="submit" value="Start Chat">
            </form>
        </div>
    </div>
</div>
<div class="content-spacer"></div>
<div class="footer-container">
    <div class="footer-content">
        <div class="contact-info">
            <h3>CONTACT US</h3>
            <a href="mailto:citystore.help@gmail.com">citystore.help@gmail.com</a>
        </div>
        <div class="social-icons">
            <h3>FOLLOW US</h3>
            <a href="https://www.facebook.com" target="_blank" class="icon-link">
                <i class="fab fa-facebook-f"></i>
                <span class="icon-text">Facebook</span>
            </a>
            <a href="https://www.instagram.com" target="_blank" class="icon-link">
                <i class="fab fa-instagram"></i>
                <span class="icon-text">Instagram</span>
            </a>
            <a href="mailto:citystore.help@gmail.com" class="icon-link">
                <i class="fas fa-envelope"></i>
                <span class="icon-text">Email</span>
            </a>
        </div>
        <div class="about-us">
            <h3>ABOUT US</h3>
            <p>CityStore is your go-to online shop for the latest products and trends. Our mission is to provide the best shopping experience.</p>
        </div>
        <div class="quick-links">
            <h3>CITYSTORE</h3>
            <ul>
                <li><a href="about.php">About</a></li>
                <li><a href="contacts.php">Contact</a></li>
                <li><a>FAQ</a></li>
                <li><a>Terms of Service</a></li>
                <li><a>Privacy Policy</a></li>
            </ul>
        </div>
        <div class="newsletter">
            <h3>NEWS</h3>
            <p>Subscribe to our newsletter to get the latest updates and offers.</p>
            <form action="#" method="post">
                <input type="email" name="email" placeholder="Your email address" required>
                <input type="submit" value="Subscribe" style="margin:0px;"></input>
            </form>
        </div>
    </div>
</div>
<script>
function adjustMessagesContainerHeight() {
    var chatFormHeight = document.getElementById('chat-form').offsetHeight;
    var chatFooterHeight = document.querySelector('.chat-footer').offsetHeight;
    var messagesContainerHeight = chatFormHeight - chatFooterHeight - 20;
    document.getElementById('messages-container').style.height = messagesContainerHeight + 'px';
}

function toggleChat() {
    var chatbox = document.getElementById('chatbox');
    chatbox.classList.toggle('show');
    if (chatbox.classList.contains('show')) {
        adjustMessagesContainerHeight();
    }
}
function checkFooterPosition() {
    var chatFormBottom = document.getElementById('chat-form').getBoundingClientRect().bottom;
    var chatFooterTop = document.querySelector('.chat-footer').getBoundingClientRect().top;
    if (chatFormBottom <= chatFooterTop) {
        document.querySelector('.chat-footer').style.position = 'absolute';
        document.querySelector('.chat-footer').style.bottom = '0';
    } else {
        document.querySelector('.chat-footer').style.position = 'relative';
    }
}

window.addEventListener('resize', function() {
    adjustMessagesContainerHeight();
    checkFooterPosition();
});


function startChat(event) {
    event.preventDefault();

    var chatBody = document.getElementById('chat-body');
    var startChatBody = document.querySelector('.start-chat-body');
    var chatFooter = document.querySelector('.chat-footer');

    startChatBody.style.display = 'none';
    chatBody.style.display = 'flex';
    chatFooter.style.display = 'flex';

    simulateBotResponse("Welcome to the support chat! How can I assist you today?");
}

function submitChat(event) {
        event.preventDefault();

        var messageTextarea = document.getElementById('message');
        var message = messageTextarea.value.trim();


        if (message !== '') {

            appendUserMessage(message, '<?php echo $profile_picture; ?>');

            setTimeout(function() {
                simulateBotResponse("Hello, can you please explain your problem?");
            }, 1000);

            messageTextarea.value = '';
        }
    }


function appendUserMessage(message, profilePicture) {
    var messageContainer = document.createElement('div');
    messageContainer.classList.add('message-container', 'message-user');
    var avatarContainer = document.createElement('div');
    avatarContainer.classList.add('avatar-container');
    var avatarImg = document.createElement('img');
    avatarImg.src = profilePicture;
    avatarImg.classList.add('profile-picture');
    avatarContainer.appendChild(avatarImg);
    messageContainer.appendChild(avatarContainer);
    var messageElement = document.createElement('div');
    messageElement.textContent = message;
    messageElement.classList.add('message');
    messageContainer.appendChild(messageElement);
    document.getElementById('messages-container').appendChild(messageContainer);
    scrollToBottom();
}

function appendBotMessage(message) {
    var messageContainer = document.createElement('div');
    messageContainer.classList.add('message-container', 'message-bot');
    var avatarContainer = document.createElement('div');
    avatarContainer.classList.add('avatar-container');
    var avatarImg = document.createElement('img');
    avatarImg.src = 'uploads/support-chat.jpg';
    avatarImg.classList.add('profile-picture');
    avatarContainer.appendChild(avatarImg);
    messageContainer.appendChild(avatarContainer);
    var messageElement = document.createElement('div');
    messageElement.textContent = message;
    messageElement.classList.add('message');
    messageContainer.appendChild(messageElement);
    document.getElementById('messages-container').appendChild(messageContainer);
    scrollToBottom();
}

    function simulateBotResponse(message) {
        appendBotMessage(message);
    }

    function handleUserMessage(message) {
    appendUserMessage(message, '<?php echo $profile_picture; ?>');

    if (message.toLowerCase().includes('help')) {
        simulateBotResponse("Sure, I'm here to help! What do you need assistance with?");
    } else if (message.toLowerCase().includes('pricing')) {
        simulateBotResponse("Our pricing plans vary depending on the product. Could you specify which product you're interested in?");
    } else {
        simulateBotResponse("I'm sorry, I didn't quite catch that. Could you please rephrase your message?");
    }
}

    document.getElementById('chat-form').addEventListener('submit', function(event) {
        event.preventDefault();
        var messageTextarea = document.getElementById('message');
        var message = messageTextarea.value.trim();
        if (message !== '') {
            handleUserMessage(message);
            messageTextarea.value = '';
        }
    });

    function scrollToBottom() {
        var messagesContainer = document.getElementById('messages-container');
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
</script>

