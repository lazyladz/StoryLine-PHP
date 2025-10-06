<?php
session_start();
require_once "includes/check-auth.php";
$user = checkAuth();

// Get story ID from URL
$story_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$story_id) {
    header("Location: mystories.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Storyline - Story Details</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/styles.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>

<body>

  <div class="reading-progress-container">  
  <div class="reading-progress">
    <div class="reading-progress-bar" id="readingProgress"></div>
  </div>

  
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
          <li class="nav-item"><a class="nav-link" href="mystories.php"><i class="fas fa-book me-1"></i>My Stories</a></li>
          
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

  <!-- Story Header -->
  <div class="story-header">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-lg-4 text-center text-lg-start mb-4 mb-lg-0">
          <img id="storyCover" src="" alt="Story Cover" class="story-cover" style="width: 250px !important; height: 350px !important; object-fit: cover;">
        </div>
        <div class="col-lg-8 text-center text-lg-start">
          <h1 class="story-title" id="storyTitle"></h1>
          <p class="story-author" id="storyAuthor"></p>
          
          <div class="story-meta">
            <div class="meta-item">
              <i class="fas fa-book-open meta-icon"></i>
              <span id="chapterCount">0 Chapters</span>
            </div>
            <div class="meta-item">
              <i class="fas fa-eye meta-icon"></i>
              <span id="readCount">0 Reads</span>
            </div>
            <div class="meta-item">
              <i class="fas fa-star meta-icon"></i>
              <span id="rating">Not Rated</span>
            </div>
          </div>
          
          <div id="storyGenres" class="mb-3"></div>
          
          <div class="d-flex gap-2 flex-wrap">
            <button class="btn btn-main" onclick="startReading()">
              <i class="fas fa-play me-1"></i>Start Reading
            </button>
            <a href="mystories.php" class="btn btn-secondary">
              <i class="fas fa-arrow-left me-1"></i>Back to My Stories
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Main Content -->
  <div class="container">
    <!-- Chapters List -->
    <div class="chapters-section">
      <h3 class="section-title"><i class="fas fa-list-ol me-2"></i>Chapters</h3>
      <ul class="chapter-list" id="storyChapters">
        <!-- Chapters will be populated here -->
      </ul>
    </div>

    <!-- Chapter Content (Initially Hidden) -->
    <div class="chapter-content-section d-none" id="chapterContentSection">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="section-title mb-0" id="chapterTitleDisplay"></h3>
        <button class="btn btn-secondary btn-sm" onclick="closeChapter()">
          <i class="fas fa-times me-1"></i>Close
        </button>
      </div>
      
      <div class="chapter-content" id="chapterText"></div>
      
      <div class="chapter-navigation">
        <button id="prevChapter" class="btn btn-secondary" onclick="navigateChapter(-1)">
          <i class="fas fa-chevron-left me-1"></i>Previous Chapter
        </button>
        
        <div class="chapter-progress">
          Chapter <span id="currentChapterIndex">1</span> of <span id="totalChapters">0</span>
        </div>
        
        <button id="nextChapter" class="btn btn-main" onclick="navigateChapter(1)">
          Next Chapter <i class="fas fa-chevron-right me-1"></i>
        </button>
      </div>
    </div>

    <!-- Comments Section -->
    <div class="comments-section">
      <h3 class="section-title"><i class="fas fa-comments me-2"></i>Comments</h3>
      
      <!-- Comment Form -->
      <div class="comment-form">
        <div class="mb-3">
          <label for="commentText" class="form-label">Add a Comment</label>
          <textarea class="form-control" id="commentText" rows="3" placeholder="Share your thoughts about this story..."></textarea>
        </div>
        <button class="btn btn-main" onclick="addComment()">
          <i class="fas fa-paper-plane me-1"></i>Post Comment
        </button>
      </div>
      
      <!-- Comments List -->
      <ul class="comment-list" id="storyComments">
        <!-- Comments will be populated here -->
      </ul>
      
      <!-- No Comments Message -->
      <div class="no-comments d-none" id="noComments">
        <i class="fas fa-comment-slash fa-2x mb-3"></i>
        <p>No comments yet. Be the first to share your thoughts!</p>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <footer>
    <div class="container">
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
          <p class="text-white-50">Where stories come alive. Discover new tales, write your own, and connect with readers everywhere.</p>
        </div>
        <div class="col-md-6 text-md-end">
          <p class="text-white-50">&copy; 2025 Storyline. All rights reserved.</p>
        </div>
      </div>
      <div class="copyright">
        <p class="mb-0">Made with <i class="fas fa-heart text-danger"></i> for storytellers</p>
      </div>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener("DOMContentLoaded", function () {
        const storyId = <?php echo $story_id; ?>;
        let currentStory = null;
        let currentChapterIndex = 0;

        // Load story from database
        async function loadStory() {
            try {
                const response = await fetch(`get-my-stories.php?id=${storyId}`);
                const result = await response.json();
                
                if (result.success) {
                    currentStory = result.data;
                    displayStory();
                } else {
                    throw new Error(result.error || 'Failed to load story');
                }
            } catch (error) {
                console.error('Error loading story:', error);
                alert('Failed to load story. Please try again.');
                window.location.href = 'mystories.php';
            }
        }

        function displayStory() {
            // Populate story details
            document.getElementById('storyCover').src = currentStory.cover_image || 'https://images.unsplash.com/photo-1455390582262-044cdead277a?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80';
            document.getElementById('storyTitle').textContent = currentStory.title;
            document.getElementById('storyAuthor').textContent = `by ${currentStory.author}`;
            document.getElementById('chapterCount').textContent = `${currentStory.chapters ? currentStory.chapters.length : 0} Chapters`;
            document.getElementById('readCount').textContent = `${currentStory.reads || 0} Reads`;
            document.getElementById('rating').textContent = currentStory.rating ? `${currentStory.rating} ★` : 'Not Rated';

            // Populate genres
            const genresContainer = document.getElementById('storyGenres');
            genresContainer.innerHTML = '';
            if (currentStory.genre && Array.isArray(currentStory.genre)) {
                currentStory.genre.forEach(g => {
                    const span = document.createElement('span');
                    span.className = `badge ${getGenreColor(g)} me-1 mb-1`;
                    span.textContent = g;
                    genresContainer.appendChild(span);
                });
            }

            // Populate chapters
            const chaptersContainer = document.getElementById('storyChapters');
            chaptersContainer.innerHTML = '';
            
            if (currentStory.chapters && currentStory.chapters.length > 0) {
                document.getElementById('totalChapters').textContent = currentStory.chapters.length;
                
                currentStory.chapters.forEach((ch, i) => {
                    const li = document.createElement('li');
                    li.className = 'chapter-item';
                    li.innerHTML = `
                        <div class="chapter-header">
                            <h4 class="chapter-title">${ch.title}</h4>
                            <button class="btn btn-main btn-sm" onclick="showChapterContent(${i})">
                                <i class="fas fa-book-open me-1"></i>Read
                            </button>
                        </div>
                        <p class="chapter-preview">${stripHtml(ch.content).substring(0, 100)}...</p>
                    `;
                    chaptersContainer.appendChild(li);
                });
            } else {
                chaptersContainer.innerHTML = '<li class="chapter-item text-center p-4"><p class="text-muted">No chapters available.</p></li>';
            }

            // Update comments display
            updateCommentsDisplay();
        }

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

        function stripHtml(html) {
            const tmp = document.createElement('div');
            tmp.innerHTML = html;
            return tmp.textContent || tmp.innerText || '';
        }

        window.showChapterContent = function (index) {
            currentChapterIndex = index;
            displayChapter(currentChapterIndex);
            
            document.getElementById('chapterContentSection').classList.remove('d-none');
            document.getElementById('chapterContentSection').scrollIntoView({ behavior: 'smooth' });
            updateReadingProgress();
        }

        window.startReading = function () {
            if (currentStory.chapters && currentStory.chapters.length > 0) {
                showChapterContent(0);
            } else {
                alert('This story has no chapters yet.');
            }
        }

        function displayChapter(index) {
            const chapter = currentStory.chapters[index];
            document.getElementById('chapterTitleDisplay').textContent = chapter.title;
            document.getElementById('chapterText').innerHTML = chapter.content;
            document.getElementById('currentChapterIndex').textContent = index + 1;
            
            document.getElementById('prevChapter').disabled = (index === 0);
            document.getElementById('nextChapter').disabled = (index === currentStory.chapters.length - 1);
            
            history.replaceState(null, null, `#chapter-${index+1}`);
        }

        window.navigateChapter = function (direction) {
            const newIndex = currentChapterIndex + direction;
            
            if (newIndex >= 0 && newIndex < currentStory.chapters.length) {
                currentChapterIndex = newIndex;
                displayChapter(currentChapterIndex);
                updateReadingProgress();
            }
        }

        window.closeChapter = function () {
            document.getElementById('chapterContentSection').classList.add('d-none');
            document.getElementById('storyChapters').scrollIntoView({ behavior: 'smooth' });
        }

        window.addComment = async function () {
            const commentText = document.getElementById('commentText').value.trim();
            
            if (!commentText) {
                alert('Please enter a comment');
                return;
            }

            try {
                const response = await fetch('add-comment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        story_id: storyId,
                        comment: commentText
                    })
                });

                const result = await response.json();
                
                if (result.success) {
                    document.getElementById('commentText').value = '';
                    showSuccess('Comment added successfully');
                    // Reload story to get updated comments
                    loadStory();
                } else {
                    throw new Error(result.error || 'Failed to add comment');
                }
            } catch (error) {
                console.error('Error adding comment:', error);
                showError('Failed to add comment. Please try again.');
            }
        }

        function updateCommentsDisplay() {
            const commentsContainer = document.getElementById('storyComments');
            const noComments = document.getElementById('noComments');
            
            if (currentStory.comments && currentStory.comments.length > 0) {
                commentsContainer.innerHTML = '';
                noComments.classList.add('d-none');
                
                currentStory.comments.forEach(c => {
                    const li = document.createElement('li');
                    li.className = 'comment-item';
                    li.innerHTML = `
                        <div class="comment-header">
                            <h5 class="comment-author">${c.author || 'Anonymous'}</h5>
                            <span class="comment-date">${formatDate(c.date)}</span>
                        </div>
                        <p class="comment-text">${c.text}</p>
                    `;
                    commentsContainer.appendChild(li);
                });
            } else {
                commentsContainer.innerHTML = '';
                noComments.classList.remove('d-none');
            }
        }

        function formatDate(dateString) {
            const options = { year: 'numeric', month: 'short', day: 'numeric' };
            return new Date(dateString).toLocaleDateString(undefined, options);
        }

        function updateReadingProgress() {
            if (currentStory.chapters && currentStory.chapters.length > 0) {
                const progress = ((currentChapterIndex + 1) / currentStory.chapters.length) * 100;
                document.getElementById('readingProgress').style.width = `${progress}%`;
            }
        }

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

        window.addEventListener('hashchange', function() {
            const hash = window.location.hash;
            if (hash.startsWith('#chapter-')) {
                const chapterIndex = parseInt(hash.replace('#chapter-', '')) - 1;
                if (chapterIndex >= 0 && chapterIndex < currentStory.chapters.length) {
                    showChapterContent(chapterIndex);
                }
            }
        });

        // Initialize
        loadStory();
    });
  </script>
</body>
</html>