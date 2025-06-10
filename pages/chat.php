<?php
include '../views/headeradmin.php';
?>




 <!-- MAIN -->
 <link rel="stylesheet" href="../Admin-HTML/css/admin.css">
 <main>
            <div class="head-title">
                <div class="left">
                    <h1>Chat</h1>
                    <ul class="breadcrumb">
                        <li><a href="#">Dashboard</a></li>
                        <li><i class='bx bx-chevron-right'></i></li>
                        <li><a class="active" href="#">Chat</a></li>
                    </ul>
                </div>
            </div>

            <div class="chat-container">
                <div class="chat-list">
                    <h3>Recent Chats</h3>
                    <div class="chat-item active">
                        <img src="Maxwell-COC.jpg" alt="User">
                        <div class="chat-info">
                            <h4>Maxwell Salvador</h4>
                            <p>Last message...</p>
                        </div>
                    </div>
                    <!-- Add more chat items as needed -->
                </div>
                
                <div class="chat-messages">
                    <div class="chat-header">
                        <h3>Chat with Maxwell</h3>
                    </div>
                    <div class="messages" id="messageArea">
                        <!-- Messages will be loaded here -->
                    </div>
                    <div class="chat-input">
                        <input type="text" id="messageInput" placeholder="Type your message...">
                        <button id="sendMessage"><i class='bx bxs-send'></i></button>
                    </div>
                </div>
            </div>
        </main>



<?php
include '../views/footeradmin.php';
?>