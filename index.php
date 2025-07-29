<?php
// No PHP logic needed for this simple index page
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .dashboard-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: linear-gradient(135deg, #e0e7ff, #f0f4f8);
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            color: #1e3a8a;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        .link-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            padding: 20px;
        }
        .link-card {
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
            text-align: center;
            transition: transform 0.2s ease;
        }
        .link-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .link-card a {
            display: block;
            color: #2563eb;
            font-size: 1.2rem;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.2s ease;
        }
        .link-card a:hover {
            color: #1e40af;
        }
        @media (max-width: 640px) {
            .link-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="dashboard-container">
        <div class="header">
            <h1>Project Dashboard</h1>
        </div>
        <div class="link-grid">
            <div class="link-card">
                <a href="todolist.php" target="_blank">To-Do List</a>
            </div>
            <div class="link-card">
                <a href="gallery.php" target="_blank">Photo Gallery</a>
            </div>
            <div class="link-card">
                <a href="pokemon.php" target="_blank">Pokemon</a>
            </div>
            <div class="link-card">
                <a href="weather.php" target="_blank">Weather</a>
            </div>
            <div class="link-card">
                <a href="online_chat.php" target="_blank">Online Chat</a>
            </div>
            <div class="link-card">
                <a href="blob.php" target="_blank">Blob</a>
            </div>
        </div>
    </div>
</body>
</html>