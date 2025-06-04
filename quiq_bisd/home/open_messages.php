<?php
include("../include/database.php");

function getChatMessages($sender_id, $receiver_id, $last_id = 0) {
    global $conection;
    $query = $conection->prepare("
        SELECT m.*, u.name_user as sender_name 
        FROM messages m
        JOIN user u ON m.sender_id = u.id_user
        WHERE ((m.sender_id = :sender_id AND m.receiver_id = :receiver_id) 
            OR (m.sender_id = :receiver_id AND m.receiver_id = :sender_id))
            AND m.id > :last_id
        ORDER BY m.created_at ASC
    ");
    $query->execute([
        ":sender_id" => $sender_id,
        ":receiver_id" => $receiver_id,
        ":last_id" => $last_id
    ]);
    return $query->fetchAll(PDO::FETCH_ASSOC);
}

$user_info = json_decode($_COOKIE["user_information"], true);
$current_user_id = $user_info["id_user"];

$receiver_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
$auction_id = isset($_GET['auction_id']) ? intval($_GET['auction_id']) : 0;

if ($receiver_id === 0) {
    echo "Please specify the user to chat with.";
    exit;
}

$get_receiver = $conection->prepare("SELECT name_user, image_user FROM user WHERE id_user = :id");
$get_receiver->execute([":id" => $receiver_id]);
$receiver = $get_receiver->fetch(PDO::FETCH_ASSOC);

if (!$receiver) {
    echo "User not found.";
    exit;
}

// Query to get the auction name if available
$auction_name = "";
if ($auction_id > 0) {
    $get_auction = $conection->prepare("SELECT product_name FROM auctions WHERE id = :id");
    $get_auction->execute([":id" => $auction_id]);
    $auction = $get_auction->fetch(PDO::FETCH_ASSOC);
    if ($auction) {
        $auction_name = $auction['product_name'];
    }
}

// Handle AJAX requests
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    // Handle AJAX message send
    if (isset($_POST['action']) && $_POST['action'] === 'send_message') {
        $response = ['success' => false];
        
        if (!empty($_POST['message'])) {
            $message = trim($_POST['message']);
            $send_query = $conection->prepare("
                INSERT INTO messages (sender_id, receiver_id, message, auction_id, created_at) 
                VALUES (:sender_id, :receiver_id, :message, :auction_id, NOW())
            ");
            $send_query->execute([
                ":sender_id" => $current_user_id,
                ":receiver_id" => $receiver_id,
                ":message" => $message,
                ":auction_id" => $auction_id
            ]);
            
            // Update user's last activity
            $update_activity = $conection->prepare("
                UPDATE user_activity SET last_active = NOW() WHERE user_id = :user_id
            ");
            $update_activity->execute([":user_id" => $current_user_id]);
            
            // Get current user data for the response
            $get_current_user = $conection->prepare("SELECT name_user FROM user WHERE id_user = :id");
            $get_current_user->execute([":id" => $current_user_id]);
            $current_user = $get_current_user->fetch(PDO::FETCH_ASSOC);
            
            $message_id = $conection->lastInsertId();
            $response = [
                'success' => true,
                'message' => [
                    'id' => $message_id,
                    'sender_id' => $current_user_id,
                    'sender_name' => $current_user['name_user'],
                    'message' => $message,
                    'time' => date('h:i A')
                ]
            ];
        }
        
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
    
    // Handle AJAX message fetch (long polling)
    if (isset($_POST['action']) && $_POST['action'] === 'get_messages') {
        $last_id = isset($_POST['last_id']) ? intval($_POST['last_id']) : 0;
        $max_wait_time = 30; // Maximum time in seconds to wait for new messages
        $wait_interval = 1; // Check interval in seconds
        $waited = 0;
        
        // Update user's last activity
        $update_activity = $conection->prepare("
            INSERT INTO user_activity (user_id, last_active) 
            VALUES (:user_id, NOW())
            ON DUPLICATE KEY UPDATE last_active = NOW()
        ");
        $update_activity->execute([":user_id" => $current_user_id]);
        
        // Check for online status of the other user
        $check_status = $conection->prepare("
            SELECT 
                CASE 
                    WHEN last_active >= NOW() - INTERVAL 2 MINUTE THEN 'online'
                    ELSE 'offline'
                END as status
            FROM user_activity 
            WHERE user_id = :user_id
        ");
        $check_status->execute([":user_id" => $receiver_id]);
        $status_result = $check_status->fetch(PDO::FETCH_ASSOC);
        $user_status = $status_result ? $status_result['status'] : 'offline';
        
        // Check for new messages immediately
        $new_messages = getChatMessages($current_user_id, $receiver_id, $last_id);
        
        // If no new messages, keep checking until we find some or timeout
        while (empty($new_messages) && $waited < $max_wait_time) {
            sleep($wait_interval);
            $waited += $wait_interval;
            
            // Check again
            $new_messages = getChatMessages($current_user_id, $receiver_id, $last_id);
            
            // Also check for typing status
            $check_typing = $conection->prepare("
                SELECT is_typing, UNIX_TIMESTAMP(typing_updated) as typing_time
                FROM user_typing 
                WHERE user_id = :user_id AND chat_with = :chat_with
            ");
            $check_typing->execute([
                ":user_id" => $receiver_id,
                ":chat_with" => $current_user_id
            ]);
            $typing_status = $check_typing->fetch(PDO::FETCH_ASSOC);
            
            // If there's typing activity or new messages, break the loop
            if (($typing_status && $typing_status['is_typing'] == 1 && 
                 (time() - $typing_status['typing_time']) < 5) || !empty($new_messages)) {
                break;
            }
        }
        
        $formatted_messages = [];
        foreach ($new_messages as $msg) {
            $formatted_messages[] = [
                'id' => $msg['id'],
                'sender_id' => $msg['sender_id'],
                'sender_name' => $msg['sender_name'],
                'message' => $msg['message'],
                'time' => date('h:i A', strtotime($msg['created_at']))
            ];
        }
        
        // Get the latest typing status
        $check_typing = $conection->prepare("
            SELECT is_typing, UNIX_TIMESTAMP(typing_updated) as typing_time
            FROM user_typing 
            WHERE user_id = :user_id AND chat_with = :chat_with
        ");
        $check_typing->execute([
            ":user_id" => $receiver_id,
            ":chat_with" => $current_user_id
        ]);
        $typing_status = $check_typing->fetch(PDO::FETCH_ASSOC);
        
        $is_typing = false;
        if ($typing_status && $typing_status['is_typing'] == 1) {
            // Only consider typing if updated within the last 5 seconds
            $is_typing = (time() - $typing_status['typing_time']) < 5;
        }
        
        header('Content-Type: application/json');
        echo json_encode([
            'messages' => $formatted_messages,
            'user_status' => $user_status,
            'is_typing' => $is_typing
        ]);
        exit;
    }
    
    // Handle typing status update
    if (isset($_POST['action']) && $_POST['action'] === 'update_typing') {
        $is_typing = isset($_POST['is_typing']) ? (int)$_POST['is_typing'] : 0;
        
        // Insert or update typing status
        $update_typing = $conection->prepare("
            INSERT INTO user_typing (user_id, chat_with, is_typing, typing_updated) 
            VALUES (:user_id, :chat_with, :is_typing, NOW())
            ON DUPLICATE KEY UPDATE is_typing = :is_typing, typing_updated = NOW()
        ");
        $update_typing->execute([
            ":user_id" => $current_user_id,
            ":chat_with" => $receiver_id,
            ":is_typing" => $is_typing
        ]);
        
        echo json_encode(['success' => true]);
        exit;
    }
}

// Get chat messages for initial page load
$messages = getChatMessages($current_user_id, $receiver_id);

// Get current user data
$get_current_user = $conection->prepare("SELECT name_user, image_user FROM user WHERE id_user = :id");
$get_current_user->execute([":id" => $current_user_id]);
$current_user = $get_current_user->fetch(PDO::FETCH_ASSOC);

// Get the last message ID for polling
$last_message_id = 0;
if (!empty($messages)) {
    $last_message = end($messages);
    $last_message_id = $last_message['id'];
}

// Check online status of the receiver
$check_status = $conection->prepare("
    SELECT 
        CASE 
            WHEN last_active >= NOW() - INTERVAL 2 MINUTE THEN 'online'
            ELSE 'offline'
        END as status
    FROM user_activity 
    WHERE user_id = :user_id
");
$check_status->execute([":user_id" => $receiver_id]);
$status_result = $check_status->fetch(PDO::FETCH_ASSOC);
$user_status = $status_result ? $status_result['status'] : 'offline';

// Update current user's activity
$update_activity = $conection->prepare("
    INSERT INTO user_activity (user_id, last_active) 
    VALUES (:user_id, NOW())
    ON DUPLICATE KEY UPDATE last_active = NOW()
");
$update_activity->execute([":user_id" => $current_user_id]);
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-RMTZ216R9L"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-RMTZ216R9L');
</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat with <?php echo htmlspecialchars($receiver['name_user']); ?></title>
        <link rel="icon" type="image/jpeg" href="../image/images.jpeg">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Almarai:wght@300;400;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/open_messages.css">
    <style>
        /* Additional CSS for typing indicator and status */
        .typing-indicator {
            padding: 5px 10px;
            font-size: 0.8rem;
            color: #666;
            font-style: italic;
            margin-top: 5px;
        }
        
        .status-indicator {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 5px;
        }
        
        .status-online {
            background-color: #4CAF50;
        }
        
        .status-offline {
            background-color: #9e9e9e;
        }
    </style>
</head>

    <?php include("../include/navbar.php"); ?>
    <div class="chat-wrapper">
        <div class="chat-container">
            <div class="chat-header">
                <div class="chat-user-info">
                    <img src="../<?php echo !empty($receiver['image_user']) ? htmlspecialchars($receiver['image_user']) : '../assets/images/default-avatar.png'; ?>" alt="User Image" class="chat-avatar">
                    <div class="chat-user-details">
                        <h1><?php echo htmlspecialchars($receiver['name_user']); ?></h1>
                        <div class="user-status">
                            <span id="status-indicator" class="status-indicator <?php echo $user_status === 'online' ? 'status-online' : 'status-offline'; ?>"></span>
                            <span id="status-text"><?php echo $user_status === 'online' ? 'Online Now' : 'Offline'; ?></span>
                        </div>
                    </div>
                </div>
                <div class="chat-actions">
                    <a href="messages.php" class="action-button">
                        <i class="fas fa-home"></i>
                    </a>
                </div>
            </div>
            
            <?php if (!empty($auction_name)): ?>
            <div class="auction-details">
                <h3><i class="fas fa-gavel"></i> Regarding Auction: <?php echo htmlspecialchars($auction_name); ?></h3>
            </div>
            <?php endif; ?>
            
            <div class="messages-container" id="chatMessages">
                <?php if (empty($messages)): ?>
                    <div class="empty-chat" id="emptyChat">
                        <i class="far fa-comments"></i>
                        <p>Start the conversation now!</p>
                        <span>You can send the first message to begin chatting</span>
                    </div>
                <?php else: ?>
                    <?php foreach ($messages as $msg): ?>
                        <div class="message-row <?php echo ($msg['sender_id'] == $current_user_id) ? 'sent' : 'received'; ?>" data-message-id="<?php echo htmlspecialchars($msg['id']); ?>">
                            <div class="message-bubble">
                                <div class="message-sender">
                                    <?php echo htmlspecialchars($msg['sender_name']); ?>
                                </div>
                                <div class="message-content">
                                    <?php echo htmlspecialchars($msg['message']); ?>
                                </div>
                                <div class="message-time">
                                    <?php echo htmlspecialchars(date('h:i A', strtotime($msg['created_at']))); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <div class="chat-input-area">
                <div id="typingIndicator" class="typing-indicator" style="display:none">
                    <span><?php echo htmlspecialchars($receiver['name_user']); ?> is typing...</span>
                </div>
                <form id="chatForm" class="chat-form">
                    <div class="form-group">
                        <textarea name="message" id="messageInput" class="chat-textarea" rows="2" placeholder="Type your message here..." required></textarea>
                    </div>
                    <button type="submit" class="send-button ripple">
                        <i class="fas fa-paper-plane"></i>
                        Send
                    </button>
                </form>
            </div>
        </div>
    </div>

    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            let lastMessageId = <?php echo $last_message_id; ?>;
            let isPolling = false;
            let typingTimer;
            let isTyping = false;
            
            // Initial scroll to bottom
            scrollToBottom();
            
            // Function to send a message
            $('#chatForm').on('submit', function(e) {
                e.preventDefault();
                
                const messageText = $('#messageInput').val().trim();
                if (messageText === '') return;
                
                // Clear input
                $('#messageInput').val('');
                
                // Send via AJAX
                $.ajax({
                    url: window.location.href,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'send_message',
                        message: messageText
                    },
                    success: function(response) {
                        if (response.success) {
                            // Add message to chat
                            addNewMessage(response.message, 'sent');
                            lastMessageId = response.message.id;
                            
                            // Remove empty chat notification if it exists
                            $('#emptyChat').remove();
                        }
                    }
                });
                
                // Update typing status
                clearTimeout(typingTimer);
                updateTypingStatus(false);
                isTyping = false;
            });
            
            // Function to add a message to the chat display
            function addNewMessage(message, type) {
                const messageHtml = `
                    <div class="message-row ${type}" data-message-id="${message.id}">
                        <div class="message-bubble">
                            <div class="message-sender">
                                ${message.sender_name}
                            </div>
                            <div class="message-content">
                                ${message.message}
                            </div>
                            <div class="message-time">
                                ${message.time}
                            </div>
                        </div>
                    </div>
                `;
                
                $('#chatMessages').append(messageHtml);
                scrollToBottom();
            }
            
            // Function to scroll to the bottom of the chat
            function scrollToBottom() {
                const chatMessages = document.getElementById('chatMessages');
                if (chatMessages) {
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                }
            }
            
            // Function to start polling for new messages
            function startPolling() {
                if (isPolling) return;
                isPolling = true;
                
                function poll() {
                    if (!isPolling) return;
                    
                    $.ajax({
                        url: window.location.href,
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            action: 'get_messages',
                            last_id: lastMessageId
                        },
                        success: function(response) {
                            // Handle new messages
                            if (response.messages && response.messages.length > 0) {
                                response.messages.forEach(function(msg) {
                                    const messageType = msg.sender_id == <?php echo $current_user_id; ?> ? 'sent' : 'received';
                                    addNewMessage(msg, messageType);
                                    lastMessageId = msg.id;
                                    
                                    // Remove empty chat notification if it exists
                                    $('#emptyChat').remove();
                                });
                            }
                            
                            // Handle typing indicator
                            if (response.is_typing) {
                                $('#typingIndicator').show();
                            } else {
                                $('#typingIndicator').hide();
                            }
                            
                            // Handle online status
                            updateUserStatus(response.user_status);
                        },
                        complete: function() {
                            if (isPolling) {
                                poll(); // Continue polling
                            }
                        }
                    });
                }
                
                poll(); // Start the polling process
            }
            
            // Function to update typing status
            function updateTypingStatus(typing) {
                $.ajax({
                    url: window.location.href,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'update_typing',
                        is_typing: typing ? 1 : 0
                    }
                });
            }
            
            // Function to update user status display
            function updateUserStatus(status) {
                if (status === 'online') {
                    $('#status-text').text('Online Now');
                    $('#status-indicator').removeClass('status-offline').addClass('status-online');
                } else {
                    $('#status-text').text('Offline');
                    $('#status-indicator').removeClass('status-online').addClass('status-offline');
                }
            }
            
            // Handle typing indicator events
            $('#messageInput').on('input', function() {
                if (!isTyping) {
                    isTyping = true;
                    updateTypingStatus(true);
                }
                
                clearTimeout(typingTimer);
                typingTimer = setTimeout(function() {
                    isTyping = false;
                    updateTypingStatus(false);
                }, 3000);
            });
            
            // Start polling when the page loads
            startPolling();
            
            // Handle page visibility changes
            document.addEventListener('visibilitychange', function() {
                if (document.visibilityState === 'visible') {
                    // Page is visible, start polling
                    startPolling();
                } else {
                    // Page is hidden, stop polling
                    isPolling = false;
                }
            });
            
            // Handle page unload
            $(window).on('beforeunload', function() {
                // Update typing status to false when leaving
                updateTypingStatus(false);
                isPolling = false;
            });
        });

        $('#messageInput').on('keydown', function(e) {
    // Check if Enter key was pressed without Shift key (Shift+Enter will allow for new lines)
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault(); // Prevent default behavior (new line)
        $('#chatForm').submit(); // Submit the form
    }
});

// Modify the send message function inside your $(document).ready block

$('#chatForm').on('submit', function(e) {
    e.preventDefault();
    
    const messageText = $('#messageInput').val().trim();
    if (messageText === '') return;
    
    // Clear input
    $('#messageInput').val('');
    
    // Send via AJAX
    $.ajax({
        url: window.location.href,
        type: 'POST',
        dataType: 'json',
        data: {
            action: 'send_message',
            message: messageText
        },
        success: function(response) {
            if (response.success) {
                // Refresh the page after successfully sending the message
                window.location.reload();
            }
        }
    });
    
    // Update typing status
    clearTimeout(typingTimer);
    updateTypingStatus(false);
    isTyping = false;
});


    </script>
</body>
</html>