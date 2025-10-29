<?php
// manageUsers.php
session_start();
require_once "includes/database.php";

// Check if user is logged in and is admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.html");
    exit();
}

$db = new Database();
$message = '';
$error = '';

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        try {
            switch ($_POST['action']) {
                case 'delete_user':
                    $userId = $_POST['user_id'] ?? '';
                    if ($userId) {
                        // Don't allow admin to delete themselves
                        if ($userId == $_SESSION['user']['id']) {
                            $error = "You cannot delete your own account.";
                        } else {
                            $result = $db->delete('users', ['id' => $userId]);
                            if ($result) {
                                $message = "User deleted successfully.";
                            } else {
                                $error = "Failed to delete user.";
                            }
                        }
                    }
                    break;
                    
                case 'update_role':
                    $userId = $_POST['user_id'] ?? '';
                    $newRole = $_POST['role'] ?? '';
                    if ($userId && in_array($newRole, ['user', 'admin'])) {
                        // Don't allow admin to change their own role
                        if ($userId == $_SESSION['user']['id']) {
                            $error = "You cannot change your own role.";
                        } else {
                            $result = $db->update('users', ['role' => $newRole], ['id' => $userId]);
                            if ($result) {
                                $message = "User role updated successfully.";
                            } else {
                                $error = "Failed to update user role.";
                            }
                        }
                    }
                    break;
                    
                case 'add_user':
                    $firstName = trim($_POST['first_name'] ?? '');
                    $lastName = trim($_POST['last_name'] ?? '');
                    $email = trim($_POST['email'] ?? '');
                    $password = $_POST['password'] ?? '';
                    $role = $_POST['role'] ?? 'user';
                    
                    // Validation
                    if (!$firstName || !$lastName || !$email || !$password) {
                        $error = "All fields are required.";
                    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $error = "Please enter a valid email address.";
                    } elseif (strlen($password) < 8) {
                        $error = "Password must be at least 8 characters.";
                    } else {
                        // Check if email exists
                        $existingUser = $db->select('users', '*', ['email' => $email]);
                        if (!empty($existingUser)) {
                            $error = "Email is already registered.";
                        } else {
                            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                            $userData = [
                                'first_name' => $firstName,
                                'last_name' => $lastName,
                                'email' => $email,
                                'password' => $hashedPassword,
                                'role' => $role,
                                'created_at' => date('Y-m-d H:i:s')
                            ];
                            
                            $result = $db->insert('users', $userData);
                            if ($result) {
                                $message = "User created successfully.";
                            } else {
                                $error = "Failed to create user.";
                            }
                        }
                    }
                    break;
            }
        } catch (Exception $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}

// Get all users
try {
    $users = $db->select('users', '*', []);
    $users = is_array($users) ? $users : [];
} catch (Exception $e) {
    $users = [];
    $error = "Failed to load users: " . $e->getMessage();
}

// Get user statistics
$totalUsers = count($users);
$adminUsers = array_filter($users, function($user) {
    return ($user['role'] ?? 'user') === 'admin';
});
$regularUsers = array_filter($users, function($user) {
    return ($user['role'] ?? 'user') === 'user';
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Storyline - Manage Users</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    body {
      background-color: #eef1f6;
    }
    .sidebar {
      min-height: 100vh;
      background-color: #0d1321;
      color: #fff;
    }
    .sidebar a {
      color: #adb5bd;
      text-decoration: none;
      display: block;
      padding: .75rem 1rem;
      border-radius: .375rem;
    }
    .sidebar a:hover {
      background-color: #1c2333;
      color: #fff;
    }
    .top-card, .list-card {
      background: #ffffff;
      border-radius: .75rem;
      box-shadow: 0 4px 12px rgba(0,0,0,.08);
      padding: 1.5rem;
    }
    .user-avatar {
      width: 40px;
      height: 40px;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-weight: bold;
    }
    .badge-admin {
      background-color: #dc3545;
    }
    .badge-user {
      background-color: #6c757d;
    }
    .table-hover tbody tr:hover {
      background-color: rgba(0,0,0,.02);
    }
  </style>
</head>
<body>
<div class="container-fluid">
  <div class="row">
    <!-- Sidebar -->
    <nav class="col-md-3 col-lg-2 d-md-block sidebar p-3">
      <h4 class="text-white">Storyline</h4>
      <ul class="nav flex-column mt-4">
        <li class="nav-item"><a href="indexAdmin.php" class="nav-link">Dashboard</a></li>
        <li class="nav-item"><a href="manageUsers.php" class="nav-link active">Manage Users</a></li>
        <li class="nav-item"><a href="manageStories.php" class="nav-link">Manage Stories</a></li>
        <li class="nav-item"><a href="notifications.html" class="nav-link">Notifications</a></li>
        <li class="nav-item"><a href="settings.html" class="nav-link">Settings</a></li>
        <li class="nav-item"><a href="#" class="nav-link" id="logoutLink">Log Out</a></li>
      </ul>
      <div class="mt-auto pt-3">
        <button type="button" class="btn btn-outline-light w-100">
          <strong><?php echo $_SESSION['user']['first_name'] ?? 'Admin'; ?></strong><br>
          <small><?php echo $_SESSION['user']['email'] ?? 'Admin Email'; ?></small>
        </button>
      </div>
    </nav>

    <!-- Main content -->
    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manage Users</h2>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
          <i class="fas fa-plus me-2"></i>Add New User
        </button>
      </div>

      <!-- Messages -->
      <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <i class="fas fa-check-circle me-2"></i><?php echo $message; ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>
      
      <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>

      <!-- User Statistics -->
      <div class="row g-3 mb-4">
        <div class="col-md-4">
          <div class="top-card">
            <h5>Total Users</h5>
            <div class="points"><?php echo $totalUsers; ?></div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="top-card">
            <h5>Administrators</h5>
            <div class="points"><?php echo count($adminUsers); ?></div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="top-card">
            <h5>Regular Users</h5>
            <div class="points"><?php echo count($regularUsers); ?></div>
          </div>
        </div>
      </div>

      <!-- Users Table -->
      <div class="list-card">
        <h5 class="mb-4">All Users</h5>
        
        <?php if (!empty($users)): ?>
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>User</th>
                  <th>Email</th>
                  <th>Role</th>
                  <th>Joined</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($users as $user): ?>
                  <tr>
                    <td>
                      <div class="d-flex align-items-center">
                        <div class="user-avatar me-3">
                          <?php echo strtoupper(substr($user['first_name'] ?? 'U', 0, 1)); ?>
                        </div>
                        <div>
                          <strong><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></strong>
                        </div>
                      </div>
                    </td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td>
                      <form method="POST" class="d-inline">
                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                        <select name="role" class="form-select form-select-sm" onchange="this.form.submit()" 
                                <?php echo $user['id'] == $_SESSION['user']['id'] ? 'disabled' : ''; ?>>
                          <option value="user" <?php echo ($user['role'] ?? 'user') === 'user' ? 'selected' : ''; ?>>User</option>
                          <option value="admin" <?php echo ($user['role'] ?? 'user') === 'admin' ? 'selected' : ''; ?>>Admin</option>
                        </select>
                        <input type="hidden" name="action" value="update_role">
                      </form>
                    </td>
                    <td><?php echo date('M j, Y', strtotime($user['created_at'] ?? 'now')); ?></td>
                    <td>
                      <?php if ($user['id'] != $_SESSION['user']['id']): ?>
                        <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">
                          <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                          <input type="hidden" name="action" value="delete_user">
                          <button type="submit" class="btn btn-danger btn-sm">
                            <i class="fas fa-trash"></i>
                          </button>
                        </form>
                      <?php else: ?>
                        <span class="text-muted">Current User</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div class="text-center py-5">
            <i class="fas fa-users fa-3x text-muted mb-3"></i>
            <p class="text-muted">No users found in the database.</p>
          </div>
        <?php endif; ?>
      </div>
    </main>
  </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header">
          <h5 class="modal-title" id="addUserModalLabel">Add New User</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="action" value="add_user">
          
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="first_name" class="form-label">First Name</label>
              <input type="text" class="form-control" id="first_name" name="first_name" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="last_name" class="form-label">Last Name</label>
              <input type="text" class="form-control" id="last_name" name="last_name" required>
            </div>
          </div>
          
          <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" required>
          </div>
          
          <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required minlength="8">
            <div class="form-text">Password must be at least 8 characters long.</div>
          </div>
          
          <div class="mb-3">
            <label for="role" class="form-label">Role</label>
            <select class="form-select" id="role" name="role">
              <option value="user">User</option>
              <option value="admin">Administrator</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Create User</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Logout functionality
  document.getElementById('logoutLink').addEventListener('click', function(e) {
    e.preventDefault();
    if (confirm('Are you sure you want to log out?')) {
      window.location.href = 'logout.php';
    }
  });

  // Auto-dismiss alerts after 5 seconds
  setTimeout(function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
      const bsAlert = new bootstrap.Alert(alert);
      bsAlert.close();
    });
  }, 5000);
</script>
</body>
</html>