<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../index.php");
    exit();
}

$job_id = $_GET['job_id'];
$user_id = $_SESSION['user']['id'];

// Get the other party from applications table
$stmt = $conn->prepare("SELECT freelancer_id, client_id FROM jobs JOIN applications ON jobs.id = applications.job_id WHERE job_id = ? LIMIT 1");
$stmt->bind_param("i", $job_id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if (!$row) {
    die("Chat not found.");
}

$other_id = ($row['freelancer_id'] == $user_id) ? $row['client_id'] : $row['freelancer_id'];

// Get messages
$msgs = $conn->prepare("SELECT * FROM messages WHERE job_id = ? AND ((sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)) ORDER BY sent_at ASC");
$msgs->bind_param("iiiii", $job_id, $user_id, $other_id, $other_id, $user_id);
$msgs->execute();
$messages = $msgs->get_result();

// Get the other user's name
$user_stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
$user_stmt->bind_param("i", $other_id);
$user_stmt->execute();
$other_user = $user_stmt->get_result()->fetch_assoc();

// If we couldn't get the other user's name, use a default
if (!$other_user) {
    $other_user = ['name' => 'Unknown User'];
}
?>
<?php
function formatFileSize($bytes) {
    if ($bytes >= 1048576) {
        return round($bytes / 1048576, 1) . ' MB';
    } elseif ($bytes >= 1024) {
        return round($bytes / 1024, 1) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Chat #<?= $job_id ?> | Freelance Platform</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #6c63ff;
            --primary-light: #837dff;
            --primary-dark: #564fd1;
            --secondary: #f8f9fa;
            --light: #ffffff;
            --dark: #2d3748;
            --gray: #718096;
            --light-gray: #e2e8f0;
            --success: #48bb78;
            --warning: #ed8936;
            --danger: #f56565;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fa;
            color: var(--dark);
            line-height: 1.6;
        }

        .chat-wrapper {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .chat-container {
            background-color: var(--light);
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            height: 80vh;
            display: flex;
            flex-direction: column;
        }

        .chat-header {
            background-color: var(--primary);
            color: var(--light);
            padding: 1.2rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .chat-header h2 {
            font-weight: 600;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .chat-header .job-id {
            background-color: rgba(255, 255, 255, 0.2);
            padding: 0.2rem 0.6rem;
            border-radius: 20px;
            font-size: 0.9rem;
        }

        .back-btn {
            color: var(--light);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            transition: all 0.2s ease;
        }

        .back-btn:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 1.5rem;
            background-color: #f8fafc;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .message {
            max-width: 75%;
            padding: 0.8rem 1.2rem;
            border-radius: 14px;
            position: relative;
            word-wrap: break-word;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .message-sent {
            align-self: flex-end;
            background-color: var(--primary);
            color: var(--light);
            border-bottom-right-radius: 4px;
            box-shadow: 0 2px 8px rgba(108, 99, 255, 0.2);
        }

        .message-received {
            align-self: flex-start;
            background-color: var(--light);
            border: 1px solid var(--light-gray);
            border-bottom-left-radius: 4px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .message-time {
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.7);
            margin-top: 0.5rem;
            display: block;
            text-align: right;
        }

        .message-received .message-time {
            color: var(--gray);
        }

        .chat-input {
            padding: 1.2rem;
            background-color: var(--light);
            border-top: 1px solid var(--light-gray);
        }

        .message-form {
            display: flex;
            gap: 0.8rem;
            align-items: flex-end;
        }

        .message-input {
            flex: 1;
            padding: 0.8rem 1.2rem;
            border: 1px solid var(--light-gray);
            border-radius: 8px;
            font-family: inherit;
            resize: none;
            transition: all 0.2s ease;
            min-height: 60px;
            max-height: 120px;
            background-color: #f8fafc;
        }

        .message-input:focus {
            outline: none;
            border-color: var(--primary-light);
            box-shadow: 0 0 0 3px rgba(108, 99, 255, 0.1);
            background-color: var(--light);
        }

        .send-btn {
            background-color: var(--primary);
            color: var(--light);
            border: none;
            border-radius: 8px;
            padding: 0.8rem 1.5rem;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            height: 44px;
        }

        .send-btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-1px);
        }

        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: var(--gray);
            text-align: center;
            padding: 2rem;
        }

        .empty-state i {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: var(--light-gray);
        }

        .empty-state p {
            font-size: 1.1rem;
            max-width: 300px;
        }

        /* Scrollbar styling */
        .chat-messages::-webkit-scrollbar {
            width: 8px;
        }

        .chat-messages::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .chat-messages::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 10px;
        }

        .chat-messages::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .chat-wrapper {
                margin: 0;
                padding: 0;
                height: 100vh;
            }
            
            .chat-container {
                border-radius: 0;
                height: 100vh;
            }
            
            .message {
                max-width: 85%;
            }
            
            .chat-header {
                padding: 1rem;
            }
        }
		
		  .message-input-container {
        position: relative;
        flex: 1;
        display: flex;
        align-items: center;
    }
    
    .file-upload-btn {
        position: absolute;
        right: 10px;
        bottom: 10px;
        color: var(--gray);
        cursor: pointer;
        padding: 8px;
        border-radius: 50%;
        transition: all 0.2s ease;
        background: transparent;
    }
    
    .file-upload-btn:hover {
        color: var(--primary);
        background: rgba(108, 99, 255, 0.1);
    }
    
    .file-upload-btn i {
        font-size: 1.2rem;
    }

	.attachment {
    background-color: rgba(0, 0, 0, 0.1);
    padding: 6px 10px;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-top: 8px;
}

.message-sent .attachment {
    background-color: rgba(255, 255, 255, 0.2);
}

.attachment a {
    text-decoration: none;
    color: inherit;
    display: flex;
    align-items: center;
    gap: 6px;
}

.attachment i {
    font-size: 0.9rem;
}

/* File display styles */
.file-display-container {
    padding: 0 1.2rem 0.5rem;
}

.file-selected {
    background-color: rgba(0, 0, 0, 0.05);
    padding: 8px 12px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 8px;
    font-size: 0.9rem;
}

.file-name {
    flex: 1;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.remove-file {
    color: var(--danger);
    cursor: pointer;
    font-size: 1.2rem;
    margin-right: 8px;
    line-height: 1;
}

.remove-file:hover {
    color: var(--danger-dark);
}
    </style>
</head>
<body>
    <div class="chat-wrapper">
        <div class="chat-container">
            <div class="chat-header">
                <h2>
                    <span>Chat with <?= htmlspecialchars($other_user['name']) ?></span>
                    <!--<span class="job-id">Job #<?= $job_id ?></span>-->
                </h2>
                <a href="../dashboard.php" class="back-btn">
                    <i class="fas fa-chevron-left"></i> Dashboard
                </a>
            </div>
            
           <div class="chat-messages">
    <?php if ($messages->num_rows > 0): ?>
        <?php while ($m = $messages->fetch_assoc()): ?>
            <div class="message <?= $m['sender_id'] == $user_id ? 'message-sent' : 'message-received' ?>">
                <?php if (!empty($m['message'])): ?>
                    <?= nl2br(htmlspecialchars($m['message'])) ?>
                <?php endif; ?>
                
                <?php if (!empty($m['attachment_path'])): ?>
                    <div class="attachment">
                        <a href="<?= $m['attachment_path'] ?>" target="_blank" download>
                            <i class="fas fa-paperclip"></i> 
                            <?= basename($m['attachment_path']) ?>
                            <span class="file-size">
                                <?php 
                                if (file_exists($m['attachment_path'])) {
                                    $size = filesize($m['attachment_path']);
                                    echo formatFileSize($size);
                                }
                                ?>
                            </span>
                        </a>
                    </div>
                <?php endif; ?>
                
                <span class="message-time"><?= date('M j, Y g:i A', strtotime($m['sent_at'])) ?></span>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="empty-state">
            <i class="far fa-comments"></i>
            <p>No messages yet. Send your first message to start the conversation!</p>
        </div>
    <?php endif; ?>
</div>
            
<div class="chat-input">
    <form action="send_message.php" method="POST" class="message-form" enctype="multipart/form-data">
        <input type="hidden" name="job_id" value="<?= $job_id ?>">
        <input type="hidden" name="receiver_id" value="<?= $other_id ?>">
        <div class="message-input-container">
            <textarea name="message" class="message-input" placeholder="Write your message here..."></textarea>
            <label for="file-upload" class="file-upload-btn" title="Attach file">
                <i class="fas fa-paperclip"></i>
                <input type="file" id="file-upload" name="attachment" style="display: none;">
            </label>
        </div>
		<div class="file-display-container" style="display: none;">
    <!-- Selected files will appear here -->
</div>
        <button type="submit" class="send-btn">
            <i class="fas fa-paper-plane"></i> Send
        </button>
    </form>
</div>
        </div>
    </div>
	<!--✅ Delivered Files Section--> 
    <?php
    $deliveries = $conn->prepare("SELECT * FROM deliveries WHERE job_id = ?");
    $deliveries->bind_param("i", $job_id);
    $deliveries->execute();
    $delRes = $deliveries->get_result();

    if ($delRes->num_rows > 0): ?>
        <div class="chat-wrapper" style="margin-top: 20px;">
            <div class="chat-container">
                <div class="chat-header" style="background-color: var(--success);">
                    <h2>
                        <i class="fas fa-check-circle"></i> Delivered Files
                    </h2>
                </div>
                <div style="padding: 1.5rem;">
                    <?php while ($d = $delRes->fetch_assoc()): ?>
                        <div style="margin-bottom: 1rem; padding: 1rem; background-color: #f0f9f0; border-radius: 8px;">
                            <p style="margin-bottom: 0.5rem;">
                                <strong>Message:</strong> <?= htmlspecialchars($d['message']) ?>
                            </p>
                            <p style="margin-bottom: 0.5rem;">
                                <strong>File:</strong> 
                                <a href="<?= $d['file_path'] ?>" download style="color: var(--primary); text-decoration: none;">
                                    <i class="fas fa-download"></i> <?= basename($d['file_path']) ?>
                                </a>
                                (<?= file_exists($d['file_path']) ? formatFileSize(filesize($d['file_path'])) : 'File not found' ?>)
                            </p>
                            <p style="color: var(--gray); font-size: 0.9rem;">
                                Delivered on <?= date('M j, Y g:i A', strtotime($d['delivered_at'])) ?>
                            </p>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <script>
        // Auto-scroll to bottom of chat
        const chatMessages = document.querySelector('.chat-messages');
        chatMessages.scrollTop = chatMessages.scrollHeight;
        
        // Focus the message input on load
        document.querySelector('.message-input').focus();
        
        // Auto-resize textarea
        const textarea = document.querySelector('.message-input');
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
		
		// Optional: Show filename when file is selected
		document.getElementById('file-upload').addEventListener('change', function(e) {
        const fileName = e.target.files[0]?.name;
        if (fileName) {
            let fileDisplay = document.querySelector('.file-selected');
            if (!fileDisplay) {
                fileDisplay = document.createElement('span');
                fileDisplay.className = 'file-selected';
                document.querySelector('.message-input-container').appendChild(fileDisplay);
            }
            fileDisplay.textContent = fileName;
        }
		});
		
		// Show filename when file is selected
// Show filename when file is selected
document.getElementById('file-upload').addEventListener('change', function(e) {
    const fileName = e.target.files[0]?.name;
    const fileDisplayContainer = document.querySelector('.file-display-container');
    
    if (fileName) {
        // Create or update the file display element
        let fileDisplay = document.querySelector('.file-selected');
        if (!fileDisplay) {
            fileDisplay = document.createElement('div');
            fileDisplay.className = 'file-selected';
            fileDisplayContainer.appendChild(fileDisplay);
        }
        
        // Create remove button
        const removeBtn = document.createElement('span');
        removeBtn.innerHTML = '&times;';
        removeBtn.className = 'remove-file';
        removeBtn.title = 'Remove file';
        removeBtn.addEventListener('click', function() {
            e.target.value = ''; // Clear file input
            fileDisplay.remove();
        });
        
        // Update file display
        fileDisplay.innerHTML = '<span class="file-name">${fileName}</span>';
        fileDisplay.prepend(removeBtn);
        
        // Make sure container is visible
        fileDisplayContainer.style.display = 'block';
    } else {
        // Hide container if no file selected
        fileDisplayContainer.style.display = 'none';
    }
});

// Clear file selection when form is submitted
document.querySelector('.message-form').addEventListener('submit', function() {
    document.querySelector('.file-display-container').style.display = 'none';
});
    </script>
</body>
</html>