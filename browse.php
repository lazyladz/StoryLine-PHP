<?php
session_start();

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

require_once "includes/database.php";

$allStories = [];
$popularGenres = ['Fantasy', 'Romance', 'Mystery', 'Horror', 'Thriller', 'Sci-Fi', 'Comedy', 'Action'];

try {
    $db = new Database();
    $allStories = $db->select('stories', '*', []);
    
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
            'description' => $story['description'] ?? 'An engaging story waiting to be discovered.',
            'created_at' => $story['created_at'] ?? date('Y-m-d H:i:s')
        ];
    }
    
    $allStories = array_map('formatStory', $allStories);
    
} catch (Exception $e) {
    error_log("Error fetching stories from database: " . $e->getMessage());
}

function getGenreColor($genre) {
    $colors = [
        'Fantasy' => 'bg-primary',
        'Thriller' => 'bg-success',
        'Horror' => 'bg-warning text-dark',
        'Mystery' => 'bg-info text-dark',
        'Action' => 'bg-danger',
        'Sci-Fi' => 'bg-dark',
        'Romance' => 'bg-pink',
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
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <title>Browse Stories - Storyline</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="css/styles.css">
</head>

<body>

  <nav class="navbar navbar-expand-lg fixed-top">
    <div class="container">
      <a class="navbar-brand" href="index.php">
        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" class="bi bi-book me-2"
          viewBox="0 0 16 16">
          <path
            d="M1 2.828c.885-.37 2.154-.769 3.388-.893 1.33-.134 2.458.063 3.112.752v9.746c-.935-.53-2.12-.603-3.213-.493-1.18.12-2.37.461-3.287.811V2.828zm7.5-.141c.654-.689 1.782-.886 3.112-.752 1.234.124 2.503.523 3.388.893v9.923c-.918-.35-2.107-.692-3.287-.81-1.094-.111-2.278-.039-3.213.492V2.687zM8 1.783C7.015.936 5.587.81 4.287.94c-1.514.153-3.042.672-3.994 1.105A.5.5 0 0 0 0 2.5v11a.5.5 0 0 0 .707.455c.882-.4 2.303-.881 3.68-1.02 1.409-.142 2.59.087 3.223.877a.5.5 0 0 0 .78 0c.633-.79 1.814-1.019 3.222-.877 1.378.139 2.8.62 3.681 1.02A.5.5 0 0 0 16 13.5v-11a.5.5 0 0 0-.293-.455c-.952-.433-2.48-.952-3.994-1.105C10.413.809 8.985.936 8 1.783z" />
        </svg>
        Storyline
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
        aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto align-items-center">
          <li class="nav-item"><a class="nav-link" href="index.php#features">Features</a></li>
          <li class="nav-item"><a class="nav-link" href="index.php#stories">Stories</a></li>
          <li class="nav-item"><a class="nav-link" href="index.php#testimonials">Testimonials</a></li>
          
          <?php if (isset($_SESSION['user'])): ?>
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
<?php else: ?>
    <li class="nav-item"><a class="nav-link active" href="browse.php">Browse</a></li>
    <li class="nav-item"><a class="nav-link" href="login.html">Login</a></li>
    <li class="nav-item"><a class="btn btn-main ms-2" href="register.html">Sign Up</a></li>
<?php endif; ?>
        </ul>
      </div>
    </div>
  </nav>

  <div class="container-fluid" style="padding-top: 100px;">
    <div class="container">
      <h2 class="mb-3">Browse Stories</h2>
      <p class="text-muted mb-4">Discover adventures, mysteries, and community tales from our growing collection</p>
      
      <?php if (isset($_SESSION['user'])): ?>
        <div class="alert alert-info">
          Welcome back, <?php echo htmlspecialchars($_SESSION['user']['first_name']); ?>! Explore stories from our community.
        </div>
      <?php else: ?>
        <div class="alert alert-warning">
          You're browsing as a guest. You can read all stories but will need to <a href="register.html" class="alert-link">sign up</a> to comment, rate, or save your progress!
        </div>
      <?php endif; ?>

      <div class="row mb-4">
        <div class="col-md-8">
          <div class="input-group">
            <input type="text" class="form-control" placeholder="Search stories by title or author..." id="searchInput">
            <button class="btn btn-main" type="button" id="searchButton">
              <i class="fas fa-search"></i> Search
            </button>
          </div>
        </div>
        <div class="col-md-4">
          <select class="form-select" id="genreFilter">
            <option value="">All Genres</option>
            <?php foreach ($popularGenres as $genre): ?>
              <option value="<?php echo htmlspecialchars($genre); ?>"><?php echo htmlspecialchars($genre); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="row g-4" id="storiesGrid">
        <?php if (!empty($allStories)): ?>
          <?php foreach ($allStories as $story): ?>
            <div class="col-xl-3 col-lg-4 col-md-6">
              <div class="card h-100 story-card" data-story-id="<?php echo htmlspecialchars($story['id']); ?>">
                <img src="<?php echo htmlspecialchars($story['cover_image']); ?>" 
                     class="card-img-top story-img" 
                     alt="<?php echo htmlspecialchars($story['title']); ?>"
                     style="height: 250px; object-fit: cover;">
                <div class="card-body d-flex flex-column">
                  <h5 class="card-title"><?php echo htmlspecialchars($story['title']); ?></h5>
                  <p class="card-text text-muted small">by <?php echo htmlspecialchars($story['author']); ?></p>
                  
                  <div class="mb-2">
                    <?php if (!empty($story['genre']) && is_array($story['genre'])): ?>
                      <?php foreach (array_slice($story['genre'], 0, 2) as $genre): ?>
                        <span class="badge <?php echo getGenreColor($genre); ?> me-1 mb-1">
                          <?php echo htmlspecialchars($genre); ?>
                        </span>
                      <?php endforeach; ?>
                      <?php if (count($story['genre']) > 2): ?>
                        <span class="badge bg-light text-dark">+<?php echo count($story['genre']) - 2; ?> more</span>
                      <?php endif; ?>
                    <?php endif; ?>
                  </div>
                  
                  <p class="card-text flex-grow-1"><?php echo htmlspecialchars($story['description']); ?></p>
                  
                  <div class="story-stats d-flex justify-content-between align-items-center mt-auto">
                    <div>
                      <small class="text-muted">
                        <i class="fas fa-eye me-1"></i><?php echo formatReads($story['reads']); ?>
                      </small>
                      <small class="text-muted ms-2">
                        <i class="fas fa-star me-1"></i><?php echo number_format($story['rating'], 1); ?>
                      </small>
                      <small class="text-muted ms-2">
                        <i class="fas fa-book me-1"></i><?php echo count($story['chapters']); ?>
                      </small>
                    </div>
                    <button class="btn btn-main btn-sm" onclick="viewStory(<?php echo $story['id']; ?>)">
                      Read Story
                    </button>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="col-12">
            <div class="text-center py-5">
              <i class="fas fa-book-open fa-3x text-muted mb-3"></i>
              <h4 class="text-muted">No stories available yet</h4>
              <p class="text-muted">Be the first to share your story with the community!</p>
              <?php if (isset($_SESSION['user'])): ?>
                <a href="write.php" class="btn btn-main">Write Your First Story</a>
              <?php else: ?>
                <a href="register.html" class="btn btn-main">Join Now to Write</a>
              <?php endif; ?>
            </div>
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
    document.addEventListener('DOMContentLoaded', function() {
      document.querySelectorAll('.story-card').forEach(card => {
        card.style.cursor = 'pointer';
        card.addEventListener('click', function(e) {
          if (e.target.tagName === 'BUTTON' || e.target.tagName === 'A' || 
              e.target.closest('button') || e.target.closest('a')) {
            return;
          }
          
          const storyId = this.getAttribute('data-story-id');
          if (storyId) {
            viewStory(storyId);
          }
        });
      });

      const searchInput = document.getElementById('searchInput');
      const searchButton = document.getElementById('searchButton');
      const genreFilter = document.getElementById('genreFilter');
      
      function filterStories() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedGenre = genreFilter.value;
        
        document.querySelectorAll('.story-card').forEach(card => {
          const title = card.querySelector('.card-title').textContent.toLowerCase();
          const author = card.querySelector('.text-muted').textContent.toLowerCase();
          const genres = Array.from(card.querySelectorAll('.badge')).map(badge => badge.textContent);
          
          const matchesSearch = title.includes(searchTerm) || author.includes(searchTerm);
          const matchesGenre = !selectedGenre || genres.includes(selectedGenre);
          
          card.closest('.col-xl-3').style.display = (matchesSearch && matchesGenre) ? 'block' : 'none';
        });
      }
      
      if (searchInput) searchInput.addEventListener('input', filterStories);
      if (searchButton) searchButton.addEventListener('click', filterStories);
      if (genreFilter) genreFilter.addEventListener('change', filterStories);
    });

    function viewStory(storyId) {
      window.location.href = `stories.php?id=${storyId}`;
    }

    window.addEventListener('scroll', function() {
      const navbar = document.querySelector('.navbar');
      if (window.scrollY > 50) {
        navbar.style.padding = '0.5rem 0';
        navbar.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.1)';
      } else {
        navbar.style.padding = '1rem 0';
        navbar.style.boxShadow = '0 2px 15px rgba(0, 0, 0, 0.1)';
      }
    });

    window.onpageshow = function(event) {
      if (event.persisted) {
        window.location.reload();
      }
    };
  </script>
</body>
</html>