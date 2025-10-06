<?php
require_once 'includes/database.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name']) && isset($_POST['email'])) {
    try {
        $db = new Database();
        $db->insert('test_table', [
            'name' => $_POST['name'],
            'email' => $_POST['email']
        ]);
        $message = "User added successfully!";
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Get existing users
try {
    $db = new Database();
    $users = $db->select('test_table');
} catch (Exception $e) {
    $users = [];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>StoryLine Web - Supabase Demo</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .container { max-width: 800px; margin: 0 auto; }
        .form-group { margin: 15px 0; }
        label { display: block; margin-bottom: 5px; }
        input[type="text"], input[type="email"] { 
            padding: 8px; width: 300px; border: 1px solid #ddd; 
        }
        button { padding: 10px 20px; background: #007acc; color: white; border: none; cursor: pointer; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #f5f5f5; }
        .success { color: green; background: #f0fff0; padding: 10px; border: 1px solid green; }
        .error { color: red; background: #fff0f0; padding: 10px; border: 1px solid red; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📝 StoryLine Web - Supabase Demo</h1>
        
        <?php if (isset($message)): ?>
            <div class="success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <h2>Add New User</h2>
        <form method="POST">
            <div class="form-group">
                <label>Name:</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" required>
            </div>
            <button type="submit">Add User</button>
        </form>
        
        <h2>Current Users (<?php echo count($users); ?>)</h2>
        <?php if (!empty($users)): ?>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Created At</th>
                </tr>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['id']); ?></td>
                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>No users found. Add some using the form above!</p>
        <?php endif; ?>
    </div>
</body>
</html>