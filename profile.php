<?php
session_start();
require_once "includes/check-auth.php";
$user = checkAuth();

require_once "includes/database.php";

// Get user stats and stories from database
try {
    $db = new Database();
    
    // Get user data including profile image
    $user_data = $db->select('users', '*', ['id' => $_SESSION['user']['id']]);
    $profile_image = 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80';
    
    if (is_array($user_data) && !empty($user_data) && isset($user_data[0]['profile_image'])) {
        $profile_image = $user_data[0]['profile_image'];
    }
    
    // Get user's stories count and total reads
    $stories_result = $db->select('stories', '*', ['user_id' => $_SESSION['user']['id']]);
    $total_stories = 0;
    $total_reads = 0;
    
    if (is_array($stories_result) && !empty($stories_result)) {
        $stories = array_values($stories_result);
        $stories = array_filter($stories, 'is_array');
        $total_stories = count($stories);
        
        foreach ($stories as $story) {
            $total_reads += $story['reads'] ?? 0;
        }
    }
    
} catch (Exception $e) {
    $total_stories = 0;
    $total_reads = 0;
    $profile_image = 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>User Profile - Storyline</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <style>
    :root {
      --primary: #6d28d9;
      --primary-dark: #5b21b6;
      --secondary: #f59e0b;
      --light: #f8fafc;
      --dark: #1e293b;
      --gray: #64748b;
      --success: #10b981;
    }
    
    body {
      font-family: 'Segoe UI', system-ui, sans-serif;
      background-color: #f8fafc;
      color: var(--dark);
    }
    
    /* Navbar */
    .navbar {
      background-color: white;
      box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
      padding: 1rem 0;
    }
    
    .navbar-brand {
      font-weight: 700;
      font-size: 1.5rem;
      color: var(--primary);
      display: flex;
      align-items: center;
    }
    
    .navbar-brand svg {
      color: var(--primary);
    }
    
    .nav-link {
      font-weight: 500;
      margin: 0 0.5rem;
      color: var(--dark);
      transition: color 0.3s;
    }
    
    .nav-link:hover, .nav-link.active {
      color: var(--primary);
    }
    
    .btn-main {
      background-color: var(--primary);
      color: white;
      padding: 0.75rem 1.5rem;
      border-radius: 8px;
      font-weight: 600;
      transition: all 0.3s;
      border: none;
    }
    
    .btn-main:hover {
      background-color: var(--primary-dark);
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(109, 40, 217, 0.3);
    }
    
    .btn-secondary {
      background-color: transparent;
      color: var(--primary);
      border: 2px solid var(--primary);
      padding: 0.75rem 1.5rem;
      border-radius: 8px;
      font-weight: 600;
      transition: all 0.3s;
    }
    
    .btn-secondary:hover {
      background-color: rgba(109, 40, 217, 0.1);
      transform: translateY(-2px);
    }
    
    /* Profile Header */
    .profile-header {
      background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
      color: white;
      padding: 3rem 0 2rem;
      margin-bottom: 2rem;
    }
    
    .profile-avatar {
      width: 150px;
      height: 150px;
      border-radius: 50%;
      object-fit: cover;
      border: 5px solid rgba(255, 255, 255, 0.2);
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }
    
    .profile-name {
      font-weight: 700;
      margin-bottom: 0.5rem;
      font-size: 2.5rem;
    }
    
    .profile-bio {
      font-size: 1.1rem;
      opacity: 0.9;
      margin-bottom: 1.5rem;
      max-width: 600px;
    }
    
    /* Stats Section */
    .stats-section {
      background-color: white;
      border-radius: 12px;
      padding: 2rem;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
      margin-bottom: 2rem;
    }
    
    .stat-item {
      text-align: center;
      padding: 1.5rem;
      transition: all 0.3s;
      border-radius: 8px;
    }
    
    .stat-item:hover {
      background-color: rgba(109, 40, 217, 0.05);
      transform: translateY(-5px);
    }
    
    .stat-icon {
      font-size: 2.5rem;
      color: var(--primary);
      margin-bottom: 1rem;
    }
    
    .stat-number {
      font-size: 2rem;
      font-weight: 700;
      margin-bottom: 0.5rem;
      color: var(--dark);
    }
    
    .stat-label {
      font-size: 0.9rem;
      color: var(--gray);
      text-transform: uppercase;
      letter-spacing: 1px;
    }
    
    /* Stories Section */
    .stories-section {
      background-color: white;
      border-radius: 12px;
      padding: 2rem;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
      margin-bottom: 2rem;
    }
    
    .section-title {
      font-weight: 700;
      margin-bottom: 1.5rem;
      position: relative;
      padding-bottom: 0.5rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    
    .section-title::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      width: 50px;
      height: 3px;
      background: linear-gradient(to right, var(--primary), var(--secondary));
      border-radius: 2px;
    }
    
    .stories-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 1.5rem;
    }
    
    .story-card {
      border: none;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
      transition: all 0.3s;
      height: 100%;
    }
    
    .story-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    }
    
    .story-image {
      height: 180px;
      object-fit: cover;
      transition: transform 0.5s;
    }
    
    .story-card:hover .story-image {
      transform: scale(1.05);
    }
    
    .story-content {
      padding: 1.5rem;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      height: calc(100% - 180px);
    }
    
    .story-title {
      font-weight: 600;
      margin-bottom: 0.5rem;
      font-size: 1.1rem;
      line-height: 1.3;
    }
    
    .story-meta {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1rem;
      font-size: 0.85rem;
    }
    
    .badge {
      font-size: 0.7rem;
      padding: 0.3rem 0.6rem;
      border-radius: 20px;
    }
    
    .story-stats {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-top: 0.75rem;
      font-size: 0.8rem;
    }
    
    .reads {
      color: var(--gray);
    }
    
    .rating {
      color: var(--secondary);
      font-weight: 600;
    }
    
    .action-buttons {
      display: flex;
      gap: 0.5rem;
      margin-top: 1rem;
    }
    
    .btn-action {
      flex: 1;
      padding: 0.5rem;
      font-size: 0.8rem;
      border-radius: 6px;
      transition: all 0.3s;
    }
    
    .btn-view {
      background-color: var(--primary);
      color: white;
      border: none;
    }
    
    .btn-view:hover {
      background-color: var(--primary-dark);
    }
    
    .btn-edit {
      background-color: var(--secondary);
      color: white;
      border: none;
    }
    
    .btn-edit:hover {
      background-color: #e58e0b;
    }
    
    .btn-delete {
      background-color: #ef4444;
      color: white;
      border: none;
    }
    
    .btn-delete:hover {
      background-color: #dc2626;
    }
    
    /* Empty State */
    .empty-state {
      text-align: center;
      padding: 3rem 1rem;
      background: #f8fafc;
      border-radius: 12px;
      border: 2px dashed #e2e8f0;
    }
    
    .empty-state i {
      font-size: 3rem;
      color: var(--gray);
      margin-bottom: 1rem;
    }
    
    .empty-state h4 {
      color: var(--dark);
      margin-bottom: 0.5rem;
    }
    
    .empty-state p {
      color: var(--gray);
      margin-bottom: 1.5rem;
    }
    
    /* Footer */
    footer {
      background-color: var(--dark);
      color: white;
      padding: 2rem 0 1rem;
      margin-top: 3rem;
    }
    
    .copyright {
      text-align: center;
      padding-top: 1.5rem;
      margin-top: 1.5rem;
      border-top: 1px solid rgba(255, 255, 255, 0.1);
      color: rgba(255, 255, 255, 0.6);
    }
    
    /* Edit Profile Modal */
    .modal-content {
      border: none;
      border-radius: 12px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }
    
    .modal-header {
      border-bottom: 1px solid #e2e8f0;
      padding: 1.5rem;
    }
    
    .modal-title {
      font-weight: 700;
      color: var(--dark);
    }
    
    .modal-body {
      padding: 1.5rem;
    }
    
    .modal-footer {
      border-top: 1px solid #e2e8f0;
      padding: 1.5rem;
    }
    
    /* Responsive Adjustments */
    @media (max-width: 768px) {
      .profile-header {
        padding: 2rem 0 1.5rem;
      }
      
      .profile-name {
        font-size: 2rem;
      }
      
      .profile-avatar {
        width: 120px;
        height: 120px;
      }
      
      .stories-grid {
        grid-template-columns: 1fr;
      }
      
      .action-buttons {
        flex-direction: column;
      }
    }
  </style>
</head>

<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg">
    <div class="container">
      <a class="navbar-brand" href="index.php">
        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" class="bi bi-book me-2"
          viewBox="0 0 16 16">
          <path
            d="M1 2.828c.885-.37 2.154-.769 3.388-.893 1.33-.134 2.458.063 3.112.752v9.746c-.935-.53-2.12-.603-3.213-.493-1.18.12-2.37.461-3.287.811V2.828zm7.5-.141c.654-.689 1.782-.886 3.112-.752 1.234.124 2.503.523 3.388.893v9.923c-.918-.35-2.107-.692-3.287-.81-1.094-.111-2.278-.039-3.213.492V2.687zM8 1.783C7.015.936 5.587.81 4.287.94c-1.514.153-3.042.672-3.994 1.105A.5.5 0 0 0 0 2.5v11a.5.5 0 0 0 .707.455c.882-.4 2.303-.881 3.68-1.02 1.409-.142 2.59.087 3.223.877a.5.5 0 0 0 .78 0c.633-.79 1.814-1.019 3.222-.877 1.378.139 2.8.62 3.681 1.02A.5.5 0 0 0 16 13.5v-11a.5.5 0 0 0-.293-.455c-.952-.433-2.48-.952-3.994-1.105C10.413.809 8.985.936 8 1.783z" />
        </svg>
        Storyline
      </a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent"
        aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarContent">
        <ul class="navbar-nav ms-auto align-items-center">
          <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="fas fa-th-large me-1"></i>Dashboard</a></li>
          <li class="nav-item"><a class="nav-link" href="browse.php"><i class="fas fa-compass me-1"></i>Browse</a></li>
          <li class="nav-item"><a class="nav-link" href="write.php"><i class="fas fa-pen me-1"></i>Write</a></li>
          
          <!-- User dropdown -->
          <li class="nav-item dropdown ms-2">
            <a class="nav-link p-0" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <div class="rounded-circle overflow-hidden d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); color: white; font-weight: bold;">
                <span id="userInitial"><?php echo strtoupper(substr($_SESSION['user']['first_name'], 0, 1)); ?></span>
              </div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>My Profile</a></li>
              <li><a class="dropdown-item" href="mystories.php"><i class="fas fa-book me-2"></i>My Stories</a></li>
              <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Settings</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
            </ul>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Profile Header -->
  <div class="profile-header">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-lg-3 text-center text-lg-start mb-4 mb-lg-0">
          <img src="<?php echo htmlspecialchars($profile_image); ?>" 
               alt="User Avatar" class="profile-avatar" id="profileAvatar">
          <div class="mt-3">
            <button class="btn btn-main" data-bs-toggle="modal" data-bs-target="#editProfileModal">
              <i class="fas fa-edit me-1"></i>Edit Profile
            </button>
          </div>
        </div>
        <div class="col-lg-9 text-center text-lg-start">
          <h1 class="profile-name" id="profileName"><?php echo htmlspecialchars($_SESSION['user']['first_name'] . ' ' . $_SESSION['user']['last_name']); ?></h1>
          <p class="profile-bio" id="profileBio">
            Passionate storyteller and avid reader. Exploring worlds one story at a time. 
            Writing fantasy and sci-fi adventures with a touch of mystery.
          </p>
          <div class="d-flex gap-3 flex-wrap">
            <span class="text-white-80"><i class="fas fa-envelope me-1"></i><?php echo htmlspecialchars($_SESSION['user']['email']); ?></span>
            <span class="text-white-80"><i class="fas fa-calendar-alt me-1"></i> Joined <?php echo date('F Y'); ?></span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Main Content -->
  <div class="container">
    <!-- Stats Section -->
    <div class="stats-section">
      <div class="row">
        <div class="col-md-4 col-6">
          <div class="stat-item">
            <i class="fas fa-book-open stat-icon"></i>
            <div class="stat-number" id="statsStories"><?php echo $total_stories; ?></div>
            <div class="stat-label">Stories</div>
          </div>
        </div>
        <div class="col-md-4 col-6">
          <div class="stat-item">
            <i class="fas fa-eye stat-icon"></i>
            <div class="stat-number" id="statsReads"><?php echo $total_reads; ?></div>
            <div class="stat-label">Total Reads</div>
          </div>
        </div>
        <div class="col-md-4 col-6">
          <div class="stat-item">
            <i class="fas fa-star stat-icon"></i>
            <div class="stat-number" id="statsRating">0</div>
            <div class="stat-label">Avg. Rating</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Stories Section -->
    <div class="stories-section">
      <h3 class="section-title">
        <span><i class="fas fa-book me-2"></i>My Stories</span>
        <a href="write.php" class="btn btn-main btn-sm">
          <i class="fas fa-plus me-1"></i>New Story
        </a>
      </h3>
      
      <div class="stories-grid" id="profileStoriesContainer">
        <!-- Stories will be populated here -->
      </div>
      
      <div id="profileNoStories" class="empty-state d-none">
        <i class="fas fa-book-open"></i>
        <h4>No stories yet</h4>
        <p>You haven't published any stories. Start your writing journey today!</p>
        <a href="write.php" class="btn btn-main">Write Your First Story</a>
      </div>
    </div>
  </div>

  <!-- Footer (Dashboard Style) -->
  <footer class="bg-dark text-white py-4">
    <div class="container-fluid">
      <div class="row">
        <div class="col-md-6">
          <a class="navbar-brand text-white mb-3 d-inline-block" href="index.php">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" class="bi bi-book me-2"
              viewBox="0 0 16 16">
              <path
                d="M1 2.828c.885-.37 2.154-.769 3.388-.893 1.33-.134 2.458.063 3.112.752v9.746c-.935-.53-2.12-.603-3.213-.493-1.18.12-2.37.461-3.287.811V2.828zm7.5-.141c.654-.689 1.782-.886 3.112-.752 1.234.124 2.503.523 3.388.893v9.923c-.918-.35-2.107-.692-3.287-.81-1.094-.111-2.278-.039-3.213.492V2.687zM8 1.783C7.015.936 5.587.81 4.287.94c-1.514.153-3.042.672-3.994 1.105A.5.5 0 0 0 0 2.5v11a.5.5 0 0 0 .707.455c.882-.4 2.303-.881 3.68-1.02 1.409-.142 2.59.087 3.223.877a.5.5 0 0 0 .78 0c.633-.79 1.814-1.019 3.222-.877 1.378.139 2.8.62 3.681 1.02A.5.5 0 0 0 16 13.5v-11a.5.5 0 0 0-.293-.455c-.952-.433-2.48-.952-3.994-1.105C10.413.809 8.985.936 8 1.783z" />
            </svg>
            Storyline
          </a>
          <p class="text-white-50">
            Where stories come alive. Discover new tales, write your own, and connect with readers everywhere.
          </p>
        </div>
        <div class="col-md-6 text-md-end">
          <p class="text-white-50 mb-1">&copy; 2025 Storyline. All rights reserved.</p>
          <p class="mb-0">Made with <i class="fas fa-heart text-danger"></i> for storytellers</p>
        </div>
      </div>
    </div>
  </footer>

  <!-- Edit Profile Modal -->
  <div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editProfileModalLabel"><i class="fas fa-user-edit me-2"></i>Edit Profile</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="editProfileForm">
            <div class="row">
              <div class="col-md-4 text-center mb-3">
                <img src="<?php echo htmlspecialchars($profile_image); ?>" 
                     alt="User Avatar" class="profile-avatar mb-3" id="modalAvatar">
                <div>
                  <input type="file" id="profileImageInput" accept="image/*" style="display: none;">
                  <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('profileImageInput').click()">
                    <i class="fas fa-camera me-1"></i>Change Photo
                  </button>
                </div>
              </div>
              <div class="col-md-8">
                <div class="mb-3">
                  <label for="editFirstName" class="form-label">First Name</label>
                  <input type="text" class="form-control" id="editFirstName" value="<?php echo htmlspecialchars($_SESSION['user']['first_name']); ?>">
                </div>
                <div class="mb-3">
                  <label for="editLastName" class="form-label">Last Name</label>
                  <input type="text" class="form-control" id="editLastName" value="<?php echo htmlspecialchars($_SESSION['user']['last_name']); ?>">
                </div>
                <div class="mb-3">
                  <label for="editEmail" class="form-label">Email</label>
                  <input type="email" class="form-control" id="editEmail" value="<?php echo htmlspecialchars($_SESSION['user']['email']); ?>">
                </div>
                <div class="mb-3">
                  <label for="editBio" class="form-label">Bio</label>
                  <textarea class="form-control" id="editBio" rows="4">Passionate storyteller and avid reader. Exploring worlds one story at a time. Writing fantasy and sci-fi adventures with a touch of mystery.</textarea>
                </div>
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-main" onclick="saveProfile()" id="saveProfileBtn">Save Changes</button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    document.addEventListener("DOMContentLoaded", function () {
      console.log('Profile page loaded');
      
      // DOM Elements
      const profileStoriesContainer = document.getElementById('profileStoriesContainer');
      const profileNoStories = document.getElementById('profileNoStories');
      
      // Test if modal elements exist
      const editModal = document.getElementById('editProfileModal');
      const saveBtn = document.getElementById('saveProfileBtn');
      console.log('Modal found:', !!editModal);
      console.log('Save button found:', !!saveBtn);
      
      // Add click event listener as backup
      if (saveBtn) {
        saveBtn.addEventListener('click', function() {
          console.log('Save button clicked via event listener');
          saveProfile();
        });
      }

      // Photo upload functionality
      const profileImageInput = document.getElementById('profileImageInput');
      const modalAvatar = document.getElementById('modalAvatar');
      const profileAvatar = document.getElementById('profileAvatar');
      
      if (profileImageInput) {
        profileImageInput.addEventListener('change', function(e) {
          const file = e.target.files[0];
          if (file) {
            // Validate file type
            if (!file.type.startsWith('image/')) {
              showError('Please select a valid image file');
              return;
            }
            
            // Validate file size (max 5MB)
            if (file.size > 5 * 1024 * 1024) {
              showError('Image size should be less than 5MB');
              return;
            }
            
            // Create preview
            const reader = new FileReader();
            reader.onload = function(e) {
              const imageUrl = e.target.result;
              modalAvatar.src = imageUrl;
              profileAvatar.src = imageUrl;
              console.log('Image preview updated');
            };
            reader.readAsDataURL(file);
          }
        });
      }

      // Load stories from database
      async function loadUserStories() {
        try {
          const response = await fetch('get-my-stories.php');
          const result = await response.json();
          
          if (result.success) {
            displayStories(result.data || []);
          } else {
            throw new Error(result.error || 'Failed to load stories');
          }
        } catch (error) {
          console.error('Error loading stories:', error);
          displayStories([]);
        }
      }

      // Genre color mapping
      function getGenreColor(genre) {
        const colors = {
          'Fantasy': 'bg-primary',
          'Thriller': 'bg-success',
          'Horror': 'bg-warning text-dark',
          'Mystery': 'bg-info text-dark',
          'Action': 'bg-danger',
          'Sci-Fi': 'bg-dark',
          'Romance': 'bg-pink',
          'Comedy': 'bg-secondary',
          'Drama': 'bg-light text-dark',
          'Adventure': 'bg-success',
          'Historical': 'bg-info text-dark'
        };
        return colors[genre] || 'bg-primary';
      }

      // Display stories
      function displayStories(stories) {
        profileStoriesContainer.innerHTML = '';

        if (stories.length === 0) {
          profileNoStories.classList.remove('d-none');
          return;
        } else {
          profileNoStories.classList.add('d-none');
        }

        stories.forEach((story) => {
          const card = document.createElement('div');
          card.classList.add('story-card');
          
          const totalReads = story.reads || 0;
          const rating = story.rating || 0;
          const chapterCount = story.chapters ? story.chapters.length : 0;
          const genres = Array.isArray(story.genre) ? story.genre : [story.genre];
          
          card.innerHTML = `
            <img src="${story.cover_image || 'https://images.unsplash.com/photo-1455390582262-044cdead277a?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80'}" 
                 alt="${story.title}" class="story-image"/>
            <div class="story-content">
              <div>
                <h4 class="story-title">${story.title}</h4>
                <div class="story-meta">
                  <div>
                    ${genres.map(g => `<span class="badge me-1 ${getGenreColor(g)}">${g}</span>`).join('')}
                  </div>
                  <span class="text-muted">${chapterCount} ch</span>
                </div>
                <div class="story-stats">
                  <span class="reads">${totalReads.toLocaleString()} reads</span>
                  <span class="rating">${rating > 0 ? rating.toFixed(1) + ' ★' : 'Not rated'}</span>
                </div>
              </div>
              <div class="action-buttons">
                <button class="btn btn-action btn-view" onclick="viewStory('${story.id}')">
                  <i class="fas fa-eye me-1"></i>View
                </button>
                <button class="btn btn-action btn-edit" onclick="editStory('${story.id}')">
                  <i class="fas fa-edit me-1"></i>Edit
                </button>
                <button class="btn btn-action btn-delete" onclick="deleteStory('${story.id}')">
                  <i class="fas fa-trash me-1"></i>Delete
                </button>
              </div>
            </div>
          `;
          profileStoriesContainer.appendChild(card);
        });
      }

      // Story actions
      window.viewStory = function(storyId) {
        window.location.href = `stories.php?id=${storyId}`;
      }

      window.editStory = function(storyId) {
        window.location.href = `write.php?id=${storyId}`;
      }

      window.deleteStory = async function(storyId) {
        if (confirm('Are you sure you want to delete this story? This action cannot be undone.')) {
          try {
            const response = await fetch('delete-story.php', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
              },
              body: JSON.stringify({ story_id: storyId })
            });

            const result = await response.json();
            
            if (result.success) {
              // Reload stories
              loadUserStories();
              
              // Show success message
              showSuccess('Story deleted successfully');
            } else {
              throw new Error(result.error || 'Failed to delete story');
            }
          } catch (error) {
            console.error('Error deleting story:', error);
            showError('Failed to delete story. Please try again.');
          }
        }
      }

      // Save profile changes
      window.saveProfile = async function() {
        console.log('Save profile function called');
        
        const formData = {
          first_name: document.getElementById('editFirstName').value,
          last_name: document.getElementById('editLastName').value,
          email: document.getElementById('editEmail').value,
          bio: document.getElementById('editBio').value
        };

        // Add profile image if changed
        const profileImageInput = document.getElementById('profileImageInput');
        if (profileImageInput.files && profileImageInput.files[0]) {
          const reader = new FileReader();
          reader.onload = function(e) {
            formData.profile_image = e.target.result;
            sendProfileUpdate(formData);
          };
          reader.readAsDataURL(profileImageInput.files[0]);
        } else {
          sendProfileUpdate(formData);
        }
      }

      async function sendProfileUpdate(formData) {
        console.log('Form data:', formData);

        try {
          console.log('Sending request to update-profile.php');
          const response = await fetch('update-profile.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
            },
            body: JSON.stringify(formData)
          });

          console.log('Response status:', response.status);
          
          // Check if response is JSON
          const responseText = await response.text();
          console.log('Raw response:', responseText);
          
          let result;
          try {
            result = JSON.parse(responseText);
          } catch (e) {
            throw new Error('Server returned invalid JSON: ' + responseText.substring(0, 100));
          }
          
          console.log('Response result:', result);
          
          if (result.success) {
            // Update profile display
            document.getElementById('profileName').textContent = `${formData.first_name} ${formData.last_name}`;
            document.getElementById('profileBio').textContent = formData.bio;
            document.getElementById("userInitial").textContent = formData.first_name.charAt(0).toUpperCase();
            
            // Clear the file input
            const profileImageInput = document.getElementById('profileImageInput');
            if (profileImageInput) {
              profileImageInput.value = '';
            }
            
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('editProfileModal'));
            if (modal) {
              modal.hide();
            }
            
            showSuccess('Profile updated successfully');
          } else {
            throw new Error(result.error || 'Failed to update profile');
          }
        } catch (error) {
          console.error('Error updating profile:', error);
          showError('Failed to update profile: ' + error.message);
        }
      }

      // Utility functions
      function showSuccess(message) {
        const toast = document.createElement('div');
        toast.className = 'alert alert-success position-fixed top-0 end-0 m-3';
        toast.style.zIndex = '9999';
        toast.innerHTML = `<i class="fas fa-check-circle me-2"></i>${message}`;
        document.body.appendChild(toast);
        setTimeout(() => document.body.removeChild(toast), 3000);
      }

      function showError(message) {
        const toast = document.createElement('div');
        toast.className = 'alert alert-danger position-fixed top-0 end-0 m-3';
        toast.style.zIndex = '9999';
        toast.innerHTML = `<i class="fas fa-exclamation-circle me-2"></i>${message}`;
        document.body.appendChild(toast);
        setTimeout(() => document.body.removeChild(toast), 5000);
      }

      // Initialize
      loadUserStories();
    });
  </script>
</body>
</html>