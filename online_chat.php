<?php
$db = new SQLite3('chat.db');
$db->exec('CREATE TABLE IF NOT EXISTS messages (id INTEGER PRIMARY KEY, user TEXT, message TEXT, timestamp TEXT)');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = SQLite3::escapeString($_POST['user']);
    $message = SQLite3::escapeString($_POST['message']);
    if (!empty($user) && !empty($message)) {
        $db->exec("INSERT INTO messages (user, message, timestamp) VALUES ('$user', '$message', datetime('now'))");
    }
}

$results = $db->query('SELECT * FROM messages ORDER BY timestamp ASC');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Chat</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .chat-container {
            max-width: 800px;
            margin: 20px auto;
            height: 80vh;
            display: flex;
            flex-direction: column;
        }
        .messages {
            flex-grow: 1;
            overflow-y: auto;
            padding: 10px;
            background-color: #f9fafb;
            border-radius: 8px;
            margin-bottom: 10px;
        }
        .message {
            margin: 10px 0;
            padding: 10px;
            border-radius: 5px;
            background-color: #fff;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        .message .avatar {
            width: 30px;
            height: 30px;
            background-color: #3b82f6;
            color: white;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 10px;
        }
        .input-area {
            display: flex;
            gap: 10px;
        }
        @media (max-width: 640px) {
            .input-area {
                flex-direction: column;
            }
            .input-area input, .input-area textarea {
                width: 100%;
            }
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="chat-container bg-white p-4 rounded-lg shadow-lg">
        <h1 class="text-2xl font-bold text-center mb-4">Simple Chat</h1>
        <div class="messages" id="messageArea">
            <?php while ($row = $results->fetchArray(SQLITE3_ASSOC)): ?>
                <?php
                $initial = strtoupper(substr($row['user'], 0, 1));
                ?>
                <div class="message flex items-start">
                    <div class="avatar"><?php echo htmlspecialchars($initial); ?></div>
                    <div>
                        <p><strong><?php echo htmlspecialchars($row['user']); ?>:</strong> <?php echo htmlspecialchars($row['message']); ?></p>
                        <p class="text-xs text-gray-500"><?php echo $row['timestamp']; ?></p>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
        <form method="POST" id="chatForm" class="input-area">
            <input type="text" name="user" placeholder="Your name" required class="border p-2 rounded">
            <textarea name="message" placeholder="Your message" required class="border p-2 rounded flex-grow"></textarea>
            <button type="submit" class="bg-blue-500 text-white p-2 rounded">Send</button>
        </form>
    </div>
    <script>
        const messageArea = document.getElementById('messageArea');
        const chatForm = document.getElementById('chatForm');

        chatForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const user = chatForm.user.value.trim();
            const message = chatForm.message.value.trim();
            if (!user || !message) {
                alert('Name and message cannot be empty!');
                return;
            }
            fetch(window.location.href, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `user=${encodeURIComponent(user)}&message=${encodeURIComponent(message)}`
            })
            .then(response => response.text())
            .then(() => {
                chatForm.message.value = '';
                loadMessages();
            });
        });

        function loadMessages() {
            fetch(window.location.href + '?t=' + new Date().getTime())
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newMessages = doc.getElementById('messageArea').innerHTML;
                    messageArea.innerHTML = newMessages;
                    messageArea.scrollTop = messageArea.scrollHeight;
                });
        }

        // Poll for new messages every 2 seconds
        setInterval(loadMessages, 2000);

        // Initial load and scroll to bottom
        window.onload = () => {
            messageArea.scrollTop = messageArea.scrollHeight;
        };
    </script>
</body>
</html>
<?php $db->close(); ?>