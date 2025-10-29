<?php
// manageStories.php
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

// Handle story actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        try {
            switch ($_POST['action']) {
                case 'delete_story':
                    $storyId = $_POST['story_id'] ?? '';
                    if ($storyId) {
                        $result = $db->delete('stories', ['id' => $storyId]);
                        if ($result) {
                            $message = "Story deleted successfully.";
                        } else {
                            $error = "Failed to delete story.";
                        }
                    }
                    break;
                    
                case 'update_story':
                    $storyId = $_POST['story_id'] ?? '';
                    $title = trim($_POST['title'] ?? '');
                    $author = trim($_POST['author'] ?? '');
                    $description = trim($_POST['description'] ?? '');
                    
                    if ($storyId && $title && $author) {
                        $updateData = [
                            'title' => $title,
                            'author' => $author,
                            'description' => $description
                        ];
                        
                        $result = $db->update('stories', $updateData, ['id' => $storyId]);
                        if ($result) {
                            $message = "Story updated successfully.";
                        } else {
                            $error = "Failed to update story.";
                        }
                    } else {
                        $error = "Title and author are required.";
                    }
                    break;
            }
        } catch (Exception $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}

// Get all stories from database
try {
    $stories = $db->select('stories', '*', []);
    $stories = is_array($stories) ? $stories : [];
} catch (Exception $e) {
    $stories = [];
    $error = "Failed to load stories: " . $e->getMessage();
}

// Calculate statistics
$totalStories = count($stories);
$totalReads = 0;
$genreStats = [];

foreach ($stories as $story) {
    $totalReads += intval($story['reads'] ?? 0);
    
    // Process genre data for stats
    $genres = [];
    if (isset($story['genre'])) {
        if (is_string($story['genre'])) {
            $genre_json = stripslashes($story['genre']);
            $genres = json_decode($genre_json, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $genres = ['Unknown'];
            }
        } else {
            $genres = $story['genre'];
        }
    }
    
    if (is_array($genres)) {
        foreach ($genres as $genre) {
            if (!isset($genreStats[$genre])) {
                $genreStats[$genre] = 0;
            }
            $genreStats[$genre]++;
        }
    }
}

// Helper function to safely encode data for JavaScript
function js_escape($data) {
    if (is_array($data)) {
        return json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
    }
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Manage Stories - Storyline</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    body {
      background-color: #f8f9fb;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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
      transition: all 0.3s ease;
    }
    .sidebar a:hover, .sidebar a.active {
      background-color: #1c2333;
      color: #fff;
    }
    .story-row {
      background: #ffffff;
      border-radius: .5rem;
      padding: 0.75rem 1rem;
      margin-bottom: 0.5rem;
      border-left: 4px solid #0d6efd;
      transition: all 0.2s ease;
      cursor: pointer;
    }
    .story-row:hover {
      transform: translateX(4px);
      box-shadow: 0 2px 8px rgba(0,0,0,.1);
    }
    .top-card {
      background: #ffffff;
      border-radius: .75rem;
      box-shadow: 0 2px 8px rgba(0,0,0,.06);
      padding: 1.25rem;
    }
    .points {
      font-size: 1.5rem;
      font-weight: bold;
      color: #0d1321; 
    }
    .story-title {
      font-size: 1rem;
      font-weight: 600;
      margin: 0;
    }
    .story-author {
      font-size: 0.85rem;
      color: #6c757d;
      margin: 0;
    }
    .story-chapters {
      font-size: 0.85rem;
      color: #0d6efd;
      font-weight: 500;
    }
    .action-btn {
      margin-left: 0.5rem;
      font-size: 0.8rem;
      padding: 0.25rem 0.5rem;
    }
    .chapter-item {
      padding: 0.4rem 0;
      border-bottom: 1px solid #eee;
      font-size: 0.85rem;
    }
    .chapter-item:last-child {
      border-bottom: none;
    }
    .stats-number {
      font-size: 1.1rem;
      font-weight: bold;
    }
  </style>
</head>
<body>
<div class="container-fluid">
  <div class="row">
    <!-- Sidebar -->
    <nav class="col-md-3 col-lg-2 d-md-block sidebar p-3">
      <h4 class="text-white mb-4">Storyline</h4>
      <ul class="nav flex-column mt-4">
        <li class="nav-item"><a href="indexAdmin.php" class="nav-link">Dashboard</a></li>
        <li class="nav-item"><a href="manageUsers.php" class="nav-link">Manage Users</a></li>
        <li class="nav-item"><a href="manageStories.php" class="nav-link active">Manage Stories</a></li>
        <li class="nav-item"><a href="notifications.html" class="nav-link">Notifications</a></li>
        <li class="nav-item"><a href="settings.html" class="nav-link">Settings</a></li>
        <li class="nav-item"><a href="#" class="nav-link" id="logoutLink">Log Out</a></li>
      </ul>
      <div class="mt-auto pt-3">
        <button type="button" class="btn btn-outline-light w-100" style="font-size: 0.9rem;">
          <strong><?php echo $_SESSION['user']['first_name'] ?? 'Admin'; ?></strong><br>
          <small><?php echo $_SESSION['user']['email'] ?? 'Admin Email'; ?></small>
        </button>
      </div>
    </nav>

    <!-- Story Detail Modal -->
    <div class="modal fade" id="storyDetailModal" tabindex="-1" aria-labelledby="storyDetailLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="storyDetailLabel">Story Details</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <h4 id="detailTitle" class="mb-3"></h4>
            <div class="row mb-3">
              <div class="col-md-6">
                <p class="mb-2"><strong>Author:</strong> <span id="detailAuthor"></span></p>
                <p class="mb-2"><strong>Genre:</strong> <span id="detailGenre"></span></p>
                <p class="mb-2"><strong>Chapters:</strong> <span id="detailChaptersCount"></span></p>
              </div>
              <div class="col-md-6">
                <p class="mb-2"><strong>Reads:</strong> <span id="detailReads"></span></p>
                <p class="mb-2"><strong>Date Created:</strong> <span id="detailDate"></span></p>
                <p class="mb-2"><strong>Status:</strong> <span id="detailStatus" class="badge bg-success">Published</span></p>
              </div>
            </div>
            <div class="mb-3">
              <h6>Description</h6>
              <p id="detailDescription" class="text-muted"></p>
            </div>
            <div class="mb-3">
              <h6>Chapter List</h6>
              <div id="detailChapterList" class="bg-light p-3 rounded" style="max-height: 200px; overflow-y: auto;"></div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Story Modal -->
    <div class="modal fade" id="editStoryModal" tabindex="-1" aria-labelledby="editStoryLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <form method="POST">
            <div class="modal-header">
              <h5 class="modal-title" id="editStoryLabel">Edit Story</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <input type="hidden" name="action" value="update_story">
              <input type="hidden" name="story_id" id="editStoryId">
              
              <div class="mb-3">
                <label for="editTitle" class="form-label">Title</label>
                <input type="text" class="form-control" id="editTitle" name="title" required>
              </div>
              
              <div class="mb-3">
                <label for="editAuthor" class="form-label">Author</label>
                <input type="text" class="form-control" id="editAuthor" name="author" required>
              </div>
              
              <div class="mb-3">
                <label for="editDescription" class="form-label">Description</label>
                <textarea class="form-control" id="editDescription" name="description" rows="4"></textarea>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-dark" style="font-size: 1.5rem;">Manage Stories</h2>
        <span class="text-muted" style="font-size: 0.9rem;">Welcome back, <span class="fw-bold text-primary"><?php echo $_SESSION['user']['first_name'] ?? 'Admin'; ?></span>!</span>
      </div>
      
      <!-- Messages -->
      <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert" style="font-size: 0.9rem; padding: 0.75rem;">
          <i class="fas fa-check-circle me-2"></i><?php echo $message; ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>
      
      <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert" style="font-size: 0.9rem; padding: 0.75rem;">
          <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>

      <!-- Stats Cards -->
      <div class="row g-3 mb-4">
        <div class="col-md-3">
          <div class="top-card text-center">
            <h5 class="text-muted" style="font-size: 0.9rem;">Total Stories</h5>
            <div class="stats-number text-primary"><?php echo $totalStories; ?></div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="top-card text-center">
            <h5 class="text-muted" style="font-size: 0.9rem;">Total Reads</h5>
            <div class="stats-number text-success"><?php echo $totalReads; ?></div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="top-card text-center">
            <h5 class="text-muted" style="font-size: 0.9rem;">Genres</h5>
            <div class="stats-number text-info"><?php echo count($genreStats); ?></div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="top-card text-center">
            <h5 class="text-muted" style="font-size: 0.9rem;">Avg Reads</h5>
            <div class="stats-number text-warning"><?php echo $totalStories > 0 ? round($totalReads / $totalStories) : 0; ?></div>
          </div>
        </div>
      </div>

      <!-- Search and Filter Section -->
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-body" style="padding: 1rem;">
          <div class="row">
            <div class="col-md-8">
              <div class="input-group">
                <input type="text" class="form-control" placeholder="Search stories..." id="searchInput" style="border-radius: 6px;">
                <button class="btn btn-primary" type="button" onclick="searchStories()" style="border-radius: 6px; margin-left: 0.5rem;">
                  <i class="fas fa-search"></i>
                </button>
              </div>
            </div>
            <div class="col-md-4">
              <div class="d-flex justify-content-end">
                <select class="form-select" id="genreFilter" onchange="filterStories()" style="border-radius: 6px;">
                  <option value="all">All Genres</option>
                  <?php foreach ($genreStats as $genre => $count): ?>
                    <option value="<?php echo htmlspecialchars($genre); ?>"><?php echo htmlspecialchars($genre); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Stories List -->
      <div class="card shadow-sm border-0">
        <div class="card-body" style="padding: 1rem;">
          <h5 class="card-title mb-3 text-dark" style="font-size: 1.1rem;">Stories List</h5>
          
          <div id="storiesContainer">
            <?php if (!empty($stories)): ?>
              <?php foreach ($stories as $story): ?>
                <?php
                // Process story data
                $genres = [];
                if (isset($story['genre'])) {
                    if (is_string($story['genre'])) {
                        $genre_json = stripslashes($story['genre']);
                        $genres = json_decode($genre_json, true);
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            $genres = ['Unknown'];
                        }
                    } else {
                        $genres = $story['genre'];
                    }
                }
                
                $chapters = [];
                $chapterCount = 0;
                if (isset($story['chapters'])) {
                    if (is_string($story['chapters'])) {
                        $chapters_json = stripslashes($story['chapters']);
                        $chapters = json_decode($chapters_json, true);
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            $chapters = [];
                        }
                    } else {
                        $chapters = $story['chapters'];
                    }
                    $chapterCount = is_array($chapters) ? count($chapters) : 0;
                }
                
                // SAFELY prepare data for JavaScript
                $storyId = $story['id'];
                $storyTitle = js_escape($story['title']);
                $storyAuthor = js_escape($story['author']);
                $storyDescription = js_escape($story['description'] ?? '');
                $storyReads = $story['reads'] ?? 0;
                $storyDate = $story['created_at'] ?? '';
                $genresJson = js_escape($genres);
                $chaptersJson = js_escape($chapters);
                ?>
                <div class="story-row" 
                     onclick="viewStory(<?php echo $storyId; ?>, '<?php echo $storyTitle; ?>', '<?php echo $storyAuthor; ?>', '<?php echo $genresJson; ?>', <?php echo $storyReads; ?>, '<?php echo $storyDate; ?>', '<?php echo $storyDescription; ?>', '<?php echo $chaptersJson; ?>', <?php echo $chapterCount; ?>)"
                     data-title="<?php echo htmlspecialchars(strtolower($story['title'])); ?>" 
                     data-author="<?php echo htmlspecialchars(strtolower($story['author'])); ?>" 
                     data-genre="<?php echo htmlspecialchars(strtolower(implode(' ', $genres))); ?>">
                  <div class="row align-items-center">
                    <div class="col-md-6">
                      <div class="story-title"><?php echo htmlspecialchars($story['title']); ?></div>
                      <div class="story-author">by <?php echo htmlspecialchars($story['author']); ?></div>
                    </div>
                    <div class="col-md-3">
                      <div class="story-chapters">
                        <i class="fas fa-book me-1"></i><?php echo $chapterCount; ?> chapter<?php echo $chapterCount != 1 ? 's' : ''; ?>
                      </div>
                    </div>
                    <div class="col-md-3 text-end">
                      <button class="btn btn-sm btn-outline-primary action-btn" onclick="event.stopPropagation(); editStory(<?php echo $storyId; ?>, '<?php echo $storyTitle; ?>', '<?php echo $storyAuthor; ?>', '<?php echo $storyDescription; ?>')">
                        <i class="fas fa-edit"></i>
                      </button>
                      <form method="POST" class="d-inline" onsubmit="event.stopPropagation(); return confirm('Are you sure you want to delete this story? This action cannot be undone.');">
                        <input type="hidden" name="story_id" value="<?php echo $storyId; ?>">
                        <input type="hidden" name="action" value="delete_story">
                        <button type="submit" class="btn btn-sm btn-outline-danger action-btn">
                          <i class="fas fa-trash"></i>
                        </button>
                      </form>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="text-center py-4">
                <i class="fas fa-book fa-2x text-muted mb-2"></i>
                <p class="text-muted" style="font-size: 0.9rem;">No stories found in the database.</p>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </main>
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

// Search stories
function searchStories() {
  const searchTerm = document.getElementById('searchInput').value.toLowerCase();
  const storyRows = document.querySelectorAll('.story-row');
  let visibleCount = 0;
  
  storyRows.forEach(row => {
    const title = row.getAttribute('data-title');
    const author = row.getAttribute('data-author');
    const genre = row.getAttribute('data-genre');
    
    if (title.includes(searchTerm) || author.includes(searchTerm) || genre.includes(searchTerm)) {
      row.style.display = 'flex';
      visibleCount++;
    } else {
      row.style.display = 'none';
    }
  });
}

// Filter stories by genre
function filterStories() {
  const genreFilter = document.getElementById('genreFilter').value.toLowerCase();
  const storyRows = document.querySelectorAll('.story-row');
  
  storyRows.forEach(row => {
    const genre = row.getAttribute('data-genre');
    
    if (genreFilter === 'all' || genre.includes(genreFilter)) {
      row.style.display = 'flex';
    } else {
      row.style.display = 'none';
    }
  });
}

// View story details - FIXED to handle JSON strings
function viewStory(id, title, author, genresJson, reads, date, description, chaptersJson, chapterCount) {
  try {
    // Parse JSON strings back to objects
    const genres = JSON.parse(genresJson);
    const chapters = JSON.parse(chaptersJson);
    
    // Set modal content
    document.getElementById('detailTitle').textContent = title;
    document.getElementById('detailAuthor').textContent = author;
    document.getElementById('detailReads').textContent = reads;
    document.getElementById('detailChaptersCount').textContent = chapterCount + ' chapter' + (chapterCount != 1 ? 's' : '');
    
    // Format date
    const formattedDate = new Date(date).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });
    document.getElementById('detailDate').textContent = formattedDate || 'Unknown date';
    
    // Set genres
    const genreContainer = document.getElementById('detailGenre');
    genreContainer.innerHTML = '';
    if (genres && genres.length > 0) {
      genres.forEach(genre => {
        const badge = document.createElement('span');
        badge.className = 'badge bg-primary me-1';
        badge.textContent = genre;
        genreContainer.appendChild(badge);
      });
    } else {
      genreContainer.textContent = 'No genres specified';
    }
    
    // Set description
    document.getElementById('detailDescription').textContent = description || 'No description available.';
    
    // Set chapters list
    const chapterListContainer = document.getElementById('detailChapterList');
    chapterListContainer.innerHTML = '';
    
    if (chapters && chapters.length > 0) {
      chapters.forEach((chapter, index) => {
        const chapterDiv = document.createElement('div');
        chapterDiv.className = 'chapter-item';
        chapterDiv.innerHTML = `
          <strong>Chapter ${index + 1}:</strong> ${chapter.title || 'Untitled Chapter'}
        `;
        chapterListContainer.appendChild(chapterDiv);
      });
    } else {
      chapterListContainer.innerHTML = '<p class="text-muted">No chapters available.</p>';
    }
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('storyDetailModal'));
    modal.show();
  } catch (error) {
    console.error('Error displaying story:', error);
    alert('Error loading story details. Please try again.');
  }
}

// Edit story
function editStory(id, title, author, description) {
  document.getElementById('editStoryId').value = id;
  document.getElementById('editTitle').value = title;
  document.getElementById('editAuthor').value = author;
  document.getElementById('editDescription').value = description || '';
  
  const modal = new bootstrap.Modal(document.getElementById('editStoryModal'));
  modal.show();
}

// Auto-dismiss alerts after 5 seconds
setTimeout(function() {
  const alerts = document.querySelectorAll('.alert');
  alerts.forEach(alert => {
    const bsAlert = new bootstrap.Alert(alert);
    bsAlert.close();
  });
}, 5000);

// Add Enter key support for search
document.getElementById('searchInput').addEventListener('keypress', function(e) {
  if (e.key === 'Enter') {
    searchStories();
  }
});
</script>
</body>
</html>