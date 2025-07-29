<?php
$db = new SQLite3('gallery.db');
$db->exec('CREATE TABLE IF NOT EXISTS folders (id INTEGER PRIMARY KEY, name TEXT UNIQUE)');
$db->exec('CREATE TABLE IF NOT EXISTS images (id INTEGER PRIMARY KEY, title TEXT, url TEXT, folder_id INTEGER, FOREIGN KEY (folder_id) REFERENCES folders(id) ON DELETE SET NULL)');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_image'])) {
        $title = SQLite3::escapeString($_POST['title']);
        $url = SQLite3::escapeString($_POST['url']);
        $folder_name = trim($_POST['folder_name']);
        
        // Check if folder exists or create new one
        $folder_id = null;
        if ($folder_name) {
            $result = $db->querySingle("SELECT id FROM folders WHERE name = '$folder_name'");
            if (!$result) {
                $db->exec("INSERT INTO folders (name) VALUES ('$folder_name')");
                $folder_id = $db->lastInsertRowID();
            } else {
                $folder_id = $result;
            }
        }
        $db->exec("INSERT INTO images (title, url, folder_id) VALUES ('$title', '$url', $folder_id)");
    } elseif (isset($_POST['delete_image'])) {
        $id = (int)$_POST['id'];
        $db->exec("DELETE FROM images WHERE id = $id");
    }
}

$folders = $db->query('SELECT * FROM folders');
$images = $db->query('SELECT images.*, folders.name AS folder_name FROM images LEFT JOIN folders ON images.folder_id = folders.id');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Photo Gallery</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .gallery-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background: linear-gradient(135deg, #f0f4f8, #e0e7ff);
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            color: #1e3a8a;
            margin-bottom: 20px;
        }
        .form-group {
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }
        .input-field {
            padding: 10px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            width: 100%;
            max-width: 300px;
            margin-right: 10px;
        }
        .folder-input {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }
        .card {
            background: white;
            border-radius: 10px;
            padding: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
        .card img {
            max-width: 200px;
            height: auto;
            border-radius: 6px;
            margin-bottom: 10px;
        }
        .card p {
            margin: 5px 0;
            color: #374151;
        }
        .folder-label {
            color: #6b7280;
            font-size: 0.9em;
        }
        button {
            padding: 10px 20px;
            background: linear-gradient(90deg, #3b82f6, #60a5fa);
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        button:hover {
            background: linear-gradient(90deg, #2563eb, #3b82f6);
        }
        @media (max-width: 640px) {
            .card-grid {
                grid-template-columns: 1fr;
            }
            .folder-input {
                flex-direction: column;
                gap: 10px;
            }
            .input-field {
                max-width: 100%;
            }
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="gallery-container">
        <div class="header">
            <h1 class="text-3xl font-bold">Photo Gallery</h1>
        </div>

        <!-- Add Image Form -->
        <div class="form-group">
            <form method="POST" class="flex flex-col md:flex-row gap-4">
                <input type="text" name="title" placeholder="Image Title" required class="input-field">
                <input type="url" name="url" placeholder="Image URL" required class="input-field">
                <div class="folder-input">
                    <input type="text" name="folder_name" placeholder="Folder Name (optional)" class="input-field">
                    <button type="submit" name="add_image">Add Image</button>
                </div>
            </form>
        </div>

        <!-- Image Gallery -->
        <div class="card-grid">
            <?php while ($row = $images->fetchArray(SQLITE3_ASSOC)): ?>
                <div class="card">
                    <img src="<?php echo htmlspecialchars($row['url']); ?>" alt="<?php echo htmlspecialchars($row['title']); ?>">
                    <p class="font-semibold"><?php echo htmlspecialchars($row['title']); ?></p>
                    <p class="folder-label">Folder: <?php echo htmlspecialchars($row['folder_name'] ?? 'None'); ?></p>
                    <form method="POST" style="margin-top: 10px;">
                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                        <button type="submit" name="delete_image">Delete</button>
                    </form>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <script>
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', e => {
                const inputs = form.querySelectorAll('input[required]');
                for (let input of inputs) {
                    if (!input.value.trim()) {
                        e.preventDefault();
                        alert('Title and URL are required!');
                        return;
                    }
                }
            });
        });
    </script>
</body>
</html>
<?php $db->close(); ?>