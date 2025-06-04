<?php
// conversations.php - Conversations page
include("../include/database.php");

// Verify session and retrieve current user ID
$user_info = json_decode($_COOKIE["user_information"], true); // Convert JSON to Array

$current_user_id = $user_info["id_user"];

// Query to get all conversations of the user
$query = $conection->prepare("
    SELECT 
        m.*, 
        u.name_user, 
        u.image_user, 
        a.product_name,
        (SELECT COUNT(*) FROM messages WHERE 
            sender_id = u.id_user AND 
            receiver_id = :user_id AND 
            read_at IS NULL
        ) as unread_count
    FROM messages m
    JOIN user u ON (
        (m.sender_id = u.id_user AND m.receiver_id = :user_id) OR 
        (m.receiver_id = u.id_user AND m.sender_id = :user_id)
    )
    LEFT JOIN auctions a ON m.auction_id = a.id
    WHERE 
        m.id IN (
            SELECT MAX(id) FROM messages
            WHERE sender_id = :user_id OR receiver_id = :user_id
            GROUP BY 
                CASE 
                    WHEN sender_id = :user_id THEN receiver_id 
                    ELSE sender_id 
                END
        )
    ORDER BY m.created_at DESC
");
$query->execute([":user_id" => $current_user_id]);
$conversations = $query->fetchAll(PDO::FETCH_ASSOC);
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
    <title>My Conversations</title>
        <link rel="icon" type="image/jpeg" href="../image/images.jpeg">

    <link rel="stylesheet" href="../css/messages.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <?php include("../include/navbar.php"); ?>

    <div class="conversations-container">
        <h1>My Conversations</h1>
        
        <div class="conversations-list">
            <?php if (empty($conversations)): ?>
                <div class="empty-state">
                    <i class="fas fa-comment-slash"></i>
                    <p>No conversations yet</p>
                </div>
            <?php else: ?>
                <?php $loop_index = 0; ?>
                <?php foreach ($conversations as $conv): 
                    $other_user_id = ($conv['sender_id'] == $current_user_id) ? $conv['receiver_id'] : $conv['sender_id'];
                    $loop_index++;
                ?>
                    <div class="conversation-card" style="--animation-order: <?php echo $loop_index; ?>">
                        <div class="card-header">
                            <div class="user-info">
                                <img class="user-avatar" src="../<?php echo htmlspecialchars($conv['image_user']); ?>" alt="User Image">
                                <div class="user-details">
                                    <div class="user-name"><?php echo htmlspecialchars($conv['name_user']); ?></div>
                                    <?php if ($conv['unread_count'] > 0): ?>
                                        <span class="unread-badge"><?php echo $conv['unread_count']; ?> new messages</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card-body">
                            <div class="message-preview">
                                <div class="message-text">
                                    <?php echo htmlspecialchars(substr($conv['message'], 0, 50)) . (strlen($conv['message']) > 50 ? '...' : ''); ?>
                                </div>
                                <span class="message-time"><?php echo htmlspecialchars($conv['created_at']); ?></span>
                            </div>
                            
                            <?php if (!empty($conv['product_name'])): ?>
                                <div class="auction-info">
                                    <div class="auction-icon">
                                        <i class="fas fa-gavel"></i>
                                    </div>
                                    <div class="auction-name"><?php echo htmlspecialchars($conv['product_name']); ?></div>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="card-footer">
                            <a href="open_messages.php?user_id=<?php echo $other_user_id; ?>&auction_id=<?php echo $conv['auction_id']; ?>" class="action-button">
                                <i class="fas fa-comments"></i> Open Chat
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

