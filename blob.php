<?php
$db = new SQLite3('blog.db');
$db->exec('CREATE TABLE IF NOT EXISTS posts (id INTEGER PRIMARY KEY, title TEXT, content TEXT, created_at TEXT)');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {https://user-images.githubusercontent.com/22878736/133997220-41182022-03cf-4697-ac0c-2e7a38a35c93.png
    if (isset($_POST['add'])) {
        $title = SQLite3::escapeString($_POST['title']);
        $content = SQLite3::escapeString($_POST['content']);
        $db->exec("INSERT INTO posts (title, content, created_at) VALUES ('$title', '$content', datetime('now'))");
    } elseif (isset($_POST['delete'])) {
        $id = (int)$_POST['id'];
        $db->exec("DELETE FROM posts WHERE id = $id");
    }
}

$results = $db->query('SELECT * FROM posts');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Blog System</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 20px auto; }
        .post { margin: 10px 0; padding: 10px; border: 1px solid #ccc; }
        input, textarea { width: 100%; padding: 8px; margin: 5px 0; }
        button { padding: 8px; }
    </style>
</head>
<body>
    <h1>Blog System</h1>
    <form method="POST">
        <input type="text" name="title" placeholder="Post title" required>
        <textarea name="content" placeholder="Post content" required></textarea>
        <button type="submit" name="add">Add Post</button>
    </form>
    <?php while ($row = $results->fetchArray(SQLITE3_ASSOC)): ?>
        <div class="post">
            <h3><?php echo htmlspecialchars($row['title']); ?></h3>
            <p><?php echo htmlspecialchars($row['content']); ?></p>
            <p><small><?php echo $row['created_at']; ?></small></p>
            <form method="POST" style="display:inline;">
                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                <button type="submit" name="delete">Delete</button>
            </form>
        </div>
    <?php endwhile; ?>
    <script>
        document.querySelector('form').addEventListener('submit', e => {
            if (!e.target.title.value.trim() || !e.target.content.value.trim()) {
                e.preventDefault();
                alert('Title and content cannot be empty!');
            }
        });
    </script>
</body>
</html>
<?php $db->close(); ?>