<?php
$db = new SQLite3('todo.db');
$db->exec('CREATE TABLE IF NOT EXISTS tasks (id INTEGER PRIMARY KEY AUTOINCREMENT, task TEXT, status TEXT, category TEXT, due_date TEXT, priority TEXT)');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add'])) {
        $task = SQLite3::escapeString($_POST['task']);
        $category = SQLite3::escapeString($_POST['category']);
        $due_date = SQLite3::escapeString($_POST['due_date']);
        $priority = SQLite3::escapeString($_POST['priority']);
        $db->exec("INSERT INTO tasks (task, status, category, due_date, priority) VALUES ('$task', 'pending', '$category', '$due_date', '$priority')");
    } elseif (isset($_POST['delete'])) {
        $id = (int)$_POST['id'];
        $db->exec("DELETE FROM tasks WHERE id = $id");
    } elseif (isset($_POST['toggle'])) {
        $id = (int)$_POST['id'];
        $status = $_POST['status'] === 'completed' ? 'pending' : 'completed';
        $db->exec("UPDATE tasks SET status = '$status' WHERE id = $id");
    } elseif (isset($_POST['edit'])) {
        $id = (int)$_POST['id'];
        $task = SQLite3::escapeString($_POST['task']);
        $category = SQLite3::escapeString($_POST['category']);
        $due_date = SQLite3::escapeString($_POST['due_date']);
        $priority = SQLite3::escapeString($_POST['priority']);
        $db->exec("UPDATE tasks SET task = '$task', category = '$category', due_date = '$due_date', priority = '$priority' WHERE id = $id");
    }
}

$search = isset($_GET['search']) ? SQLite3::escapeString($_GET['search']) : '';
$query = $search ? "SELECT * FROM tasks WHERE task LIKE '%$search%' OR status LIKE '%$search%'" : "SELECT * FROM tasks";
$results = $db->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To-Do List</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .todo-container {
            max-width: 900px;
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
            max-width: 200px;
            margin-right: 10px;
            font-size: 1rem;
            font-family: 'Arial', sans-serif;
        }
        .task-card {
            background: white;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease;
        }
        .task-card:hover {
            transform: translateY(-2px);
        }
        .completed {
            background-color: #d4edda;
            text-decoration: line-through;
            color: #155724;
            font-style: italic;
        }
        .priority-high { border-left: 4px solid #ef4444; }
        .priority-medium { border-left: 4px solid #f59e0b; }
        .priority-low { border-left: 4px solid #10b981; }
        .custom-button {
            padding: 10px 20px;
            background: linear-gradient(90deg, #4f46e5, #9333ea);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        .custom-button:hover {
            background: linear-gradient(90deg, #4338ca, #7e22ce);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }
        .edit-form {
            display: none;
            margin-top: 10px;
        }
        .edit-form.active {
            display: block;
        }
        .task-card span {
            font-size: 1.1rem;
            color: #374151;
            font-family: 'Arial', sans-serif;
        }
        .task-card .text-gray-600 {
            font-size: 0.9rem;
            color: #6b7280;
        }
        @media (max-width: 640px) {
            .form-group {
                flex-direction: column;
            }
            .input-field {
                max-width: 100%;
                margin-bottom: 10px;
            }
            .custom-button {
                width: 100%;
            }
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="todo-container">
        <div class="header">
            <h1 class="text-3xl font-bold">To-Do List</h1>
        </div>

        <!-- Search Bar -->
        <div class="form-group mb-4">
            <form method="GET" class="flex items-center">
                <input type="text" name="search" placeholder="Search tasks..." value="<?php echo htmlspecialchars($search); ?>" class="input-field">
                <button type="submit" class="custom-button ml-2">Search</button>
            </form>
        </div>

        <!-- Add Task Form -->
        <div class="form-group">
            <form method="POST" class="flex flex-col md:flex-row gap-4">
                <input type="text" name="task" placeholder="New Task" required class="input-field">
                <input type="date" name="due_date" class="input-field">
                <select name="category" class="input-field">
                    <option value="Work">Work</option>
                    <option value="Personal">Personal</option>
                    <option value="Other">Other</option>
                </select>
                <select name="priority" class="input-field">
                    <option value="Low">Low</option>
                    <option value="Medium">Medium</option>
                    <option value="High">High</option>
                </select>
                <button type="submit" name="add" class="custom-button">Add Task</button>
            </form>
        </div>

        <!-- Task List -->
        <?php while ($row = $results->fetchArray(SQLITE3_ASSOC)): ?>
            <div class="task-card <?php echo $row['status'] === 'completed' ? 'completed' : ''; ?> <?php echo 'priority-' . strtolower($row['priority']); ?>">
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                    <input type="hidden" name="status" value="<?php echo $row['status']; ?>">
                    <span><?php echo htmlspecialchars($row['task']); ?> (<?php echo htmlspecialchars($row['status']); ?>)</span>
                    <span class="ml-2 text-gray-600">Due: <?php echo htmlspecialchars($row['due_date'] ?: 'N/A'); ?></span>
                    <span class="ml-2 text-gray-600">Cat: <?php echo htmlspecialchars($row['category']); ?></span>
                    <span class="ml-2 text-gray-600">Pri: <?php echo htmlspecialchars($row['priority']); ?></span>
                    <button type="submit" name="toggle" class="custom-button ml-2">Toggle</button>
                    <button type="submit" name="delete" class="custom-button ml-2">Delete</button>
                </form>
                <!-- Edit Form -->
                <form method="POST" class="edit-form" data-id="<?php echo $row['id']; ?>">
                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                    <input type="text" name="task" value="<?php echo htmlspecialchars($row['task']); ?>" required class="input-field mt-2">
                    <input type="date" name="due_date" value="<?php echo htmlspecialchars($row['due_date']); ?>" class="input-field">
                    <select name="category" class="input-field">
                        <option value="Work" <?php echo $row['category'] === 'Work' ? 'selected' : ''; ?>>Work</option>
                        <option value="Personal" <?php echo $row['category'] === 'Personal' ? 'selected' : ''; ?>>Personal</option>
                        <option value="Other" <?php echo $row['category'] === 'Other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                    <select name="priority" class="input-field">
                        <option value="Low" <?php echo $row['priority'] === 'Low' ? 'selected' : ''; ?>>Low</option>
                        <option value="Medium" <?php echo $row['priority'] === 'Medium' ? 'selected' : ''; ?>>Medium</option>
                        <option value="High" <?php echo $row['priority'] === 'High' ? 'selected' : ''; ?>>High</option>
                    </select>
                    <button type="submit" name="edit" class="custom-button mt-2">Save</button>
                </form>
                <button class="custom-button ml-2 edit-toggle" data-id="<?php echo $row['id']; ?>">Edit</button>
            </div>
        <?php endwhile; ?>
    </div>

    <script>
        document.querySelectorAll('.edit-toggle').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const form = document.querySelector(`.edit-form[data-id="${id}"]`);
                form.classList.toggle('active');
                this.textContent = form.classList.contains('active') ? 'Cancel' : 'Edit';
            });
        });

        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', e => {
                const inputs = form.querySelectorAll('input[required]');
                for (let input of inputs) {
                    if (!input.value.trim()) {
                        e.preventDefault();
                        alert('Task cannot be empty!');
                        return;
                    }
                }
            });
        });
    </script>
</body>
</html>
<?php $db->close(); ?>