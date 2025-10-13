<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.html");
    exit();
}

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

require_once "includes/database.php";

$userStories = [];
$allStories = [];
$continueStories = [];
$popularGenres = ['Fantasy', 'Romance', 'Mystery', 'Horror', 'Thriller', 'Sci-Fi', 'Comedy', 'Action'];

try {
    $db = new Database();
    
    $userStories = $db->select('stories', '*', ['user_id' => $_SESSION['user']['id']]);
    $allStories = $db->select('stories', '*', []);
    
    // Get reading progress for the current user - FIXED VERSION
    $readingProgress = [];
    try {
        // First get all reading progress records for this user
        $progressRecords = $db->select('reading_progress', '*', ['user_id' => $_SESSION['user']['id']]);
        
        if ($progressRecords) {
            // For each progress record, get the corresponding story details
            foreach ($progressRecords as $progress) {
                $story = $db->select('stories', '*', ['id' => $progress['story_id']]);
                if ($story) {
                    $story = $story[0]; // Get first result
                    // Merge progress data with story data
                    $readingProgress[] = array_merge($story, [
                        'current_chapter_index' => $progress['current_chapter_index'],
                        'progress_percentage' => $progress['progress_percentage'],
                        'last_read_at' => $progress['last_read_at']
                    ]);
                }
            }
            
            // Sort by last_read_at descending
            usort($readingProgress, function($a, $b) {
                return strtotime($b['last_read_at']) - strtotime($a['last_read_at']);
            });
            
            // Limit to 6 most recent
            $readingProgress = array_slice($readingProgress, 0, 6);
        }
    } catch (Exception $e) {
        error_log("Error fetching reading progress: " . $e->getMessage());
    }
    
    function formatStory($story) {
        $genre = [];
        if (isset($story['genre'])) {
            if (is_string($story['genre'])) {
                $genre_json = stripslashes($story['genre']);
                $genre = json_decode($genre_json, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $genre = ['Unknown'];
                }
            } else {
                $genre = $story['genre'];
            }
        }
        
        $chapters = [];
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
        }
        
        return [
            'id' => $story['id'] ?? null,
            'title' => $story['title'] ?? 'Untitled',
            'author' => $story['author'] ?? 'Unknown Author',
            'genre' => $genre,
            'cover_image' => $story['cover_image'] ?? 'https://images.unsplash.com/photo-1455390582262-044cdead277a?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
            'chapters' => $chapters,
            'reads' => $story['reads'] ?? 0,
            'rating' => $story['rating'] ?? 0,
            'created_at' => $story['created_at'] ?? date('Y-m-d H:i:s'),
            'current_chapter_index' => $story['current_chapter_index'] ?? 0,
            'progress_percentage' => $story['progress_percentage'] ?? 0
        ];
    }
    
    $userStories = array_map('formatStory', $userStories);
    $allStories = array_map('formatStory', $allStories);
    
    // Format continue reading stories with progress data
    $continueStories = [];
    foreach ($readingProgress as $progress) {
        $continueStories[] = formatStory($progress);
    }
    
} catch (Exception $e) {
    error_log("Error fetching stories from Supabase: " . $e->getMessage());
}

function getGenreColor($genre) {
    $colors = [
        'Fantasy' => 'bg-primary',
        'Thriller' => 'bg-success',
        'Horror' => 'bg-warning text-dark',
        'Mystery' => 'bg-info text-dark',
        'Action' => 'bg-danger',
        'Sci-Fi' => 'bg-dark',
        'Romance' => 'bg-danger',
        'Comedy' => 'bg-secondary',
        'Drama' => 'bg-light text-dark',
        'Adventure' => 'bg-success',
        'Historical' => 'bg-info text-dark'
    ];
    return $colors[$genre] ?? 'bg-primary';
}

function formatReads($reads) {
    if ($reads >= 1000000) {
        return round($reads / 1000000, 1) . 'M';
    } elseif ($reads >= 1000) {
        return round($reads / 1000, 1) . 'k';
    }
    return $reads;
}

function getStoriesByGenre($stories, $genre) {
    $genreStories = [];
    foreach ($stories as $story) {
        if (!empty($story['genre']) && is_array($story['genre']) && in_array($genre, $story['genre'])) {
            $genreStories[] = $story;
        }
    }
    return $genreStories;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Dashboard - Storyline</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="stylesheet" href="css/styles.css" />
  <style>
    .dashboard-content .container-fluid {
        margin-left: 0 !important;
        padding-left: 15px !important;
        padding-right: 15px !important;
        max-width: 100% !important;
    }

    .dashboard-content .row {
        margin-left: -12px !important;
        margin-right: -12px !important;
    }

    .dashboard-content .col-xl-2,
    .dashboard-content .col-lg-3,
    .dashboard-content .col-md-4,
    .dashboard-content .col-sm-6 {
        padding-left: 12px !important;
        padding-right: 12px !important;
    }

    .dashboard-content .section-title {
        justify-content: flex-start !important;
        text-align: left !important;
        padding-left: 0 !important;
        margin-left: 0 !important;
    }
    
    .progress-container {
        margin-top: 8px;
    }
    
    .progress-text {
        font-size: 0.75rem;
        color: #6c757d;
        margin-top: 4px;
    }
  </style>
</head>

<body>

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
          <li class="nav-item"><a class="nav-link active" href="dashboard.php"><i class="fas fa-th-large me-1"></i>Dashboard</a></li>
          <li class="nav-item"><a class="nav-link" href="write.php"><i class="fas fa-pen me-1"></i>Write</a></li>
          <li class="nav-item"><a class="nav-link" href="browse.php"><i class="fas fa-compass me-1"></i>Browse</a></li>
          <li class="nav-item"><a class="nav-link" href="mystories.php"><i class="fas fa-book me-1"></i>My Stories</a></li>
          
          <li class="nav-item dropdown ms-2">
            <a class="nav-link p-0" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <div class="rounded-circle overflow-hidden d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); color: white; font-weight: bold;">
                <span id="userInitial"><?php echo isset($_SESSION['user']) ? strtoupper(substr($_SESSION['user']['first_name'], 0, 1)) : 'U'; ?></span>
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

  <div class="dashboard-content" style="padding-top: 20px;">
    <div class="container-fluid">

      <h4 class="section-title"><i class="fas fa-bookmark"></i>My Stories</h4>
      <div class="row g-3" id="myStories">
        <?php if (!empty($userStories)): ?>
          <?php foreach (array_slice($userStories, 0, 6) as $story): ?>
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
              <div class="card h-100 story-card" data-story-id="<?php echo htmlspecialchars($story['id']); ?>">
                <img src="<?php echo htmlspecialchars($story['cover_image']); ?>" 
                     class="card-img-top story-img" alt="<?php echo htmlspecialchars($story['title']); ?>">
                <div class="card-body">
                  <h6 class="card-title"><?php echo htmlspecialchars($story['title']); ?></h6>
                  <small class="text-muted">by <?php echo htmlspecialchars($story['author']); ?></small>
                  <div class="mt-2">
                    <?php if (!empty($story['genre']) && is_array($story['genre'])): ?>
                      <?php foreach ($story['genre'] as $genre): ?>
                        <span class="badge <?php echo getGenreColor($genre); ?> me-1 mb-1">
                          <?php echo htmlspecialchars($genre); ?>
                        </span>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </div>
                  <div class="story-stats">
                    <span class="reads"><?php echo formatReads($story['reads']); ?> reads</span>
                    <span class="rating"><?php echo number_format($story['rating'], 1); ?> ★</span>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="col-12">
            <div class="empty-state">
              <i class="fas fa-book-open"></i>
              <h4>No stories yet</h4>
              <p>You haven't uploaded any stories. Start your writing journey today!</p>
              <a href="write.php" class="btn btn-main">Write Your First Story</a>
            </div>
          </div>
        <?php endif; ?>
      </div>

      <h4 class="section-title"><i class="fas fa-play-circle"></i>Continue Reading</h4>
      <div class="row g-3" id="continueReading">
        <?php if (!empty($continueStories)): ?>
          <?php foreach ($continueStories as $story): ?>
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
              <div class="card h-100 story-card" data-story-id="<?php echo htmlspecialchars($story['id']); ?>">
                <img src="<?php echo htmlspecialchars($story['cover_image']); ?>" 
                     class="card-img-top story-img" alt="<?php echo htmlspecialchars($story['title']); ?>">
                <div class="card-body">
                  <h6 class="card-title"><?php echo htmlspecialchars($story['title']); ?></h6>
                  <small class="text-muted">by <?php echo htmlspecialchars($story['author']); ?></small>
                  <div class="mt-2">
                    <?php if (!empty($story['genre']) && is_array($story['genre'])): ?>
                      <?php foreach (array_slice($story['genre'], 0, 1) as $genre): ?>
                        <span class="badge <?php echo getGenreColor($genre); ?>">
                          <?php echo htmlspecialchars($genre); ?>
                        </span>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </div>
                  <div class="story-stats">
                    <span class="reads"><?php echo formatReads($story['reads']); ?> reads</span>
                    <span class="rating"><?php echo number_format($story['rating'], 1); ?> ★</span>
                  </div>
                  <div class="progress-container">
                    <div class="progress" style="height: 4px;">
                      <div class="progress-bar" style="width: <?php echo $story['progress_percentage'] ?? 0; ?>%"></div>
                    </div>
                    <div class="progress-text">
                      Chapter <?php echo ($story['current_chapter_index'] ?? 0) + 1; ?> • <?php echo $story['progress_percentage'] ?? 0; ?>%
                    </div>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="col-12">
            <div class="empty-state">
              <i class="fas fa-book-reader"></i>
              <h4>No reading progress</h4>
              <p>Start reading stories to see them here!</p>
              <a href="browse.php" class="btn btn-main">Browse Stories</a>
            </div>
          </div>
        <?php endif; ?>
      </div>

      <!-- Rest of your genre sections remain the same -->
      <h4 class="section-title"><i class="fas fa-ghost"></i>Horror Stories</h4>
      <div class="row g-3">
        <?php 
        $horrorStories = getStoriesByGenre($allStories, 'Horror');
        if (!empty($horrorStories)): ?>
          <?php foreach (array_slice($horrorStories, 0, 6) as $story): ?>
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
              <div class="card h-100 story-card" data-story-id="<?php echo htmlspecialchars($story['id']); ?>">
                <img src="<?php echo htmlspecialchars($story['cover_image']); ?>" 
                     class="card-img-top story-img" alt="<?php echo htmlspecialchars($story['title']); ?>">
                <div class="card-body">
                  <h6 class="card-title"><?php echo htmlspecialchars($story['title']); ?></h6>
                  <small class="text-muted">by <?php echo htmlspecialchars($story['author']); ?></small>
                  <div class="mt-2">
                    <span class="badge bg-warning text-dark">Horror</span>
                  </div>
                  <div class="story-stats">
                    <span class="reads"><?php echo formatReads($story['reads']); ?> reads</span>
                    <span class="rating"><?php echo number_format($story['rating'], 1); ?> ★</span>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="col-12">
            <p class="text-muted">No horror stories available yet.</p>
          </div>
        <?php endif; ?>
      </div>

      <h4 class="section-title"><i class="fas fa-user-secret"></i>Thriller Stories</h4>
      <div class="row g-3">
        <?php 
        $thrillerStories = getStoriesByGenre($allStories, 'Thriller');
        if (!empty($thrillerStories)): ?>
          <?php foreach (array_slice($thrillerStories, 0, 6) as $story): ?>
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
              <div class="card h-100 story-card" data-story-id="<?php echo htmlspecialchars($story['id']); ?>">
                <img src="<?php echo htmlspecialchars($story['cover_image']); ?>" 
                     class="card-img-top story-img" alt="<?php echo htmlspecialchars($story['title']); ?>">
                <div class="card-body">
                  <h6 class="card-title"><?php echo htmlspecialchars($story['title']); ?></h6>
                  <small class="text-muted">by <?php echo htmlspecialchars($story['author']); ?></small>
                  <div class="mt-2">
                    <span class="badge bg-success">Thriller</span>
                  </div>
                  <div class="story-stats">
                    <span class="reads"><?php echo formatReads($story['reads']); ?> reads</span>
                    <span class="rating"><?php echo number_format($story['rating'], 1); ?> ★</span>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="col-12">
            <p class="text-muted">No thriller stories available yet.</p>
          </div>
        <?php endif; ?>
      </div>

      <h4 class="section-title"><i class="fas fa-heart"></i>Romance Stories</h4>
      <div class="row g-3">
        <?php 
        $romanceStories = getStoriesByGenre($allStories, 'Romance');
        if (!empty($romanceStories)): ?>
          <?php foreach (array_slice($romanceStories, 0, 6) as $story): ?>
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
              <div class="card h-100 story-card" data-story-id="<?php echo htmlspecialchars($story['id']); ?>">
                <img src="<?php echo htmlspecialchars($story['cover_image']); ?>" 
                     class="card-img-top story-img" alt="<?php echo htmlspecialchars($story['title']); ?>">
                <div class="card-body">
                  <h6 class="card-title"><?php echo htmlspecialchars($story['title']); ?></h6>
                  <small class="text-muted">by <?php echo htmlspecialchars($story['author']); ?></small>
                  <div class="mt-2">
                    <span class="badge bg-pink">Romance</span>
                  </div>
                  <div class="story-stats">
                    <span class="reads"><?php echo formatReads($story['reads']); ?> reads</span>
                    <span class="rating"><?php echo number_format($story['rating'], 1); ?> ★</span>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="col-12">
            <p class="text-muted">No romance stories available yet.</p>
          </div>
        <?php endif; ?>
      </div>

      <h4 class="section-title"><i class="fas fa-laugh"></i>Comedy Stories</h4>
      <div class="row g-3">
        <?php 
        $comedyStories = getStoriesByGenre($allStories, 'Comedy');
        if (!empty($comedyStories)): ?>
          <?php foreach (array_slice($comedyStories, 0, 6) as $story): ?>
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
              <div class="card h-100 story-card" data-story-id="<?php echo htmlspecialchars($story['id']); ?>">
                <img src="<?php echo htmlspecialchars($story['cover_image']); ?>" 
                     class="card-img-top story-img" alt="<?php echo htmlspecialchars($story['title']); ?>">
                <div class="card-body">
                  <h6 class="card-title"><?php echo htmlspecialchars($story['title']); ?></h6>
                  <small class="text-muted">by <?php echo htmlspecialchars($story['author']); ?></small>
                  <div class="mt-2">
                    <span class="badge bg-secondary">Comedy</span>
                  </div>
                  <div class="story-stats">
                    <span class="reads"><?php echo formatReads($story['reads']); ?> reads</span>
                    <span class="rating"><?php echo number_format($story['rating'], 1); ?> ★</span>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="col-12">
            <p class="text-muted">No comedy stories available yet.</p>
          </div>
        <?php endif; ?>
      </div>

    </div>
  </div>

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

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    document.addEventListener("DOMContentLoaded", function () {
      document.querySelectorAll('.story-card').forEach(card => {
        card.style.cursor = 'pointer';
        card.addEventListener('click', function(e) {
          if (e.target.tagName === 'BUTTON' || e.target.tagName === 'A' || e.target.closest('button') || e.target.closest('a')) {
            return;
          }
          
          const storyId = this.getAttribute('data-story-id');
          if (storyId) {
            window.location.href = `stories.php?id=${storyId}`;
          }
        });
      });
    });
  </script>
</body>
</html>