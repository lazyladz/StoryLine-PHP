<?php
session_start();
require_once "includes/check-auth.php";
$user = checkAuth(); // Ensure user is logged in

// Check if we're editing an existing story
$isEditMode = false;
$existingStory = null;
$storyId = null;

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $storyId = intval($_GET['id']);
    $isEditMode = true;
    
    // Fetch existing story data
    try {
        require_once "includes/database.php";
        $db = new Database();
        $result = $db->select('stories', '*', ['id' => $storyId, 'user_id' => $_SESSION['user']['id']]);
        if ($result && is_array($result) && count($result) > 0) {
            $existingStory = $result[0];
            
            // Decode JSON fields
            if (isset($existingStory['genre'])) {
                $existingStory['genre'] = json_decode($existingStory['genre'], true) ?? [];
            }
            if (isset($existingStory['chapters'])) {
                $existingStory['chapters'] = json_decode($existingStory['chapters'], true) ?? [];
            }
            if (isset($existingStory['description'])) {
                $existingStory['description'] = $existingStory['description'];
            }
        } else {
            // Story not found or doesn't belong to user
            $isEditMode = false;
            $existingStory = null;
        }
    } catch (Exception $e) {
        error_log("Error loading story for editing: " . $e->getMessage());
        $isEditMode = false;
        $existingStory = null;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?php echo $isEditMode ? 'Edit Story' : 'Write a Story'; ?> - Storyline</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="stylesheet" href="css/styles.css" />
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
          <li class="nav-item"><a class="nav-link active" href="write.php"><i class="fas fa-pen me-1"></i>Write</a></li>
          
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

  <!-- Page Header -->
  <div class="page-header">
    <div class="container">
      <h1><i class="fas fa-pen-fancy me-2"></i><?php echo $isEditMode ? 'Edit Story' : 'Write a New Story'; ?></h1>
      <p><?php echo $isEditMode ? 'Update your story and make it even better' : 'Create your masterpiece and share it with the world'; ?></p>
    </div>
  </div>

  <!-- Page Content -->
  <div class="container mb-5">
    <div class="row">
      <!-- Form Section -->
      <div class="col-lg-7">
        <div class="form-section">
          <form id="storyForm">
            <!-- Hidden field for story ID in edit mode -->
            <?php if ($isEditMode): ?>
            <input type="hidden" id="storyId" value="<?php echo $storyId; ?>">
            <?php endif; ?>

            <!-- Story Title -->
            <div class="mb-4">
              <label for="storyTitle" class="form-label">Story Title</label>
              <input type="text" class="form-control" id="storyTitle" placeholder="Enter your captivating story title" 
                     value="<?php echo $isEditMode && isset($existingStory['title']) ? htmlspecialchars($existingStory['title']) : ''; ?>" required>
            </div>

            <!-- Author Name -->
            <div class="mb-4">
              <label for="storyAuthor" class="form-label">Author Name</label>
              <input type="text" class="form-control" id="storyAuthor" placeholder="Your pen name or real name" required>
            </div>

            <!-- Story Description -->
            <div class="mb-4">
              <label for="storyDescription" class="form-label">Story Description</label>
              <textarea class="form-control" id="storyDescription" rows="4" placeholder="Write a brief description of your story to captivate readers..." maxlength="500"><?php echo $isEditMode && isset($existingStory['description']) ? htmlspecialchars($existingStory['description']) : ''; ?></textarea>
              <div class="form-text text-end">
                <span id="descriptionCount">0</span>/500 characters
              </div>
            </div>

            <!-- Genre Selection -->
            <div class="mb-4">
              <label class="form-label">Story Genre</label>
              <div class="genre-tags">
                <span class="genre-tag" data-genre="Fantasy">Fantasy</span>
                <span class="genre-tag" data-genre="Thriller">Thriller</span>
                <span class="genre-tag" data-genre="Horror">Horror</span>
                <span class="genre-tag" data-genre="Mystery">Mystery</span>
                <span class="genre-tag" data-genre="Action">Action</span>
                <span class="genre-tag" data-genre="Sci-Fi">Sci-Fi</span>
                <span class="genre-tag" data-genre="Romance">Romance</span>
                <span class="genre-tag" data-genre="Comedy">Comedy</span>
                <span class="genre-tag" data-genre="Drama">Drama</span>
                <span class="genre-tag" data-genre="Adventure">Adventure</span>
                <span class="genre-tag" data-genre="Historical">Historical</span>
              </div>
              <input type="hidden" id="selectedGenres" name="selectedGenres" value="<?php echo $isEditMode && isset($existingStory['genre']) ? htmlspecialchars(implode(',', $existingStory['genre'])) : ''; ?>">
              <small class="form-text text-muted">Click to select one or more genres for your story.</small>
            </div>

            <!-- Cover Image Upload -->
            <div class="mb-4">
              <label class="form-label">Cover Image</label>
              <div class="image-upload-container" id="imageUploadContainer">
                <?php if ($isEditMode && isset($existingStory['cover_image'])): ?>
                  <img src="<?php echo htmlspecialchars($existingStory['cover_image']); ?>" id="existingCoverImage" style="max-width: 100%; max-height: 200px; margin-bottom: 10px;">
                <?php else: ?>
                  <div class="image-upload-icon">
                    <i class="fas fa-cloud-upload-alt"></i>
                  </div>
                  <h5>Upload Cover Image</h5>
                  <p class="text-muted">Drag & drop or click to browse</p>
                <?php endif; ?>
                <input class="d-none" type="file" id="storyImage" accept="image/*">
              </div>
            </div>

            <hr class="my-4">

            <!-- Chapters Section -->
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h5 class="mb-0"><i class="fas fa-book-open me-2"></i>Chapters</h5>
              <button type="button" class="btn btn-main" id="addChapterBtn"><i class="fas fa-plus me-1"></i>Add Chapter</button>
            </div>
            
            <div id="chaptersContainer">
              <!-- Chapters will be added here -->
            </div>

            <!-- Form Actions -->
            <div class="d-flex justify-content-between mt-4">
              <a href="mystories.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i>Back to Stories</a>
              <button type="submit" class="btn btn-main">
                <i class="fas fa-save me-1"></i><?php echo $isEditMode ? 'Update Story' : 'Publish Story'; ?>
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Preview Section -->
      <div class="col-lg-5">
        <div class="preview-section">
          <h4 class="mb-3"><i class="fas fa-eye me-2"></i>Story Preview</h4>
          <div class="card preview-card">
            <img src="<?php echo $isEditMode && isset($existingStory['cover_image']) ? htmlspecialchars($existingStory['cover_image']) : 'https://images.unsplash.com/photo-1455390582262-044cdead277a?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80'; ?>" 
                 class="card-img-top story-img-preview" id="previewImage" alt="Cover Preview">
            <div class="card-body">
              <h5 class="preview-title" id="previewTitle"><?php echo $isEditMode && isset($existingStory['title']) ? htmlspecialchars($existingStory['title']) : 'Your Story Title'; ?></h5>
              <p class="preview-author" id="previewAuthor">by Author Name</p>
              <p class="preview-description text-muted mb-3" id="previewDescription">
                <?php echo $isEditMode && isset($existingStory['description']) ? htmlspecialchars($existingStory['description']) : 'Your story description will appear here...'; ?>
              </p>
              <div id="previewGenre" class="mb-3">
                <?php if ($isEditMode && isset($existingStory['genre']) && is_array($existingStory['genre'])): ?>
                  <?php foreach ($existingStory['genre'] as $genre): ?>
                    <span class="badge bg-primary me-1"><?php echo htmlspecialchars($genre); ?></span>
                  <?php endforeach; ?>
                <?php else: ?>
                  <span class="badge bg-primary">Genre</span>
                <?php endif; ?>
              </div>
              <div id="previewChapters">
                <p class="text-muted">Chapters will appear here as you add them...</p>
              </div>
            </div>
          </div>
        </div>
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

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
  <script>
    document.addEventListener("DOMContentLoaded", function () {
      // DOM Elements
      const storyTitle = document.getElementById('storyTitle');
      const storyAuthor = document.getElementById('storyAuthor');
      const storyDescription = document.getElementById('storyDescription');
      const descriptionCount = document.getElementById('descriptionCount');
      const storyImage = document.getElementById('storyImage');
      const imageUploadContainer = document.getElementById('imageUploadContainer');
      const chaptersContainer = document.getElementById('chaptersContainer');
      const previewChapters = document.getElementById('previewChapters');
      const genreTags = document.querySelectorAll('.genre-tag');
      const selectedGenresInput = document.getElementById('selectedGenres');
      
      let chapterCount = 0;
      let quillEditors = [];
      let selectedGenres = [];

      // Check if we're in edit mode
      const isEditMode = <?php echo $isEditMode ? 'true' : 'false'; ?>;
      const existingStory = <?php echo $isEditMode && $existingStory ? json_encode($existingStory) : 'null'; ?>;

      // Set author name from session
      storyAuthor.value = "<?php echo htmlspecialchars($_SESSION['user']['first_name'] . ' ' . $_SESSION['user']['last_name']); ?>";

      // If editing, pre-select genres
      if (isEditMode && existingStory && existingStory.genre) {
        selectedGenres = Array.isArray(existingStory.genre) ? existingStory.genre : [existingStory.genre];
        selectedGenresInput.value = selectedGenres.join(',');
        
        // Activate genre tags
        genreTags.forEach(tag => {
          if (selectedGenres.includes(tag.getAttribute('data-genre'))) {
            tag.classList.add('active');
          }
        });
      }

      // Initialize description count
      updateDescriptionCount();

      // Genre selection
      genreTags.forEach(tag => {
        tag.addEventListener('click', function() {
          const genre = this.getAttribute('data-genre');
          
          // Toggle the active class
          this.classList.toggle('active');
          
          // Update selected genres array
          if (this.classList.contains('active')) {
            if (!selectedGenres.includes(genre)) {
              selectedGenres.push(genre);
            }
          } else {
            selectedGenres = selectedGenres.filter(g => g !== genre);
          }
          
          // Update hidden input
          selectedGenresInput.value = selectedGenres.join(',');
          
          // Update preview
          updatePreview();
        });
      });

      // Description character count
      storyDescription.addEventListener('input', updateDescriptionCount);
      storyDescription.addEventListener('input', updatePreview);

      function updateDescriptionCount() {
        const count = storyDescription.value.length;
        descriptionCount.textContent = count;
        
        // Change color when approaching limit
        if (count > 450) {
          descriptionCount.classList.add('text-warning');
        } else if (count > 490) {
          descriptionCount.classList.add('text-danger');
        } else {
          descriptionCount.classList.remove('text-warning', 'text-danger');
        }
      }

      // Image upload handling
      imageUploadContainer.addEventListener('click', () => {
        storyImage.click();
      });
      
      imageUploadContainer.addEventListener('dragover', (e) => {
        e.preventDefault();
        imageUploadContainer.style.borderColor = 'var(--primary)';
        imageUploadContainer.style.backgroundColor = 'rgba(109, 40, 217, 0.1)';
      });
      
      imageUploadContainer.addEventListener('dragleave', () => {
        imageUploadContainer.style.borderColor = '#e2e8f0';
        imageUploadContainer.style.backgroundColor = '#f8fafc';
      });
      
      imageUploadContainer.addEventListener('drop', (e) => {
        e.preventDefault();
        imageUploadContainer.style.borderColor = '#e2e8f0';
        imageUploadContainer.style.backgroundColor = '#f8fafc';
        
        if (e.dataTransfer.files.length) {
          storyImage.files = e.dataTransfer.files;
          handleImageUpload(e.dataTransfer.files[0]);
        }
      });
      
      storyImage.addEventListener('change', (e) => {
        if (e.target.files.length) {
          handleImageUpload(e.target.files[0]);
        }
      });
      
      function handleImageUpload(file) {
        if (file) {
          const reader = new FileReader();
          reader.onload = function(e) {
            document.getElementById('previewImage').src = e.target.result;
            // Remove existing image and show upload success
            const existingImage = document.getElementById('existingCoverImage');
            if (existingImage) {
              existingImage.remove();
            }
            imageUploadContainer.innerHTML = `
              <div class="image-upload-icon text-success">
                <i class="fas fa-check-circle"></i>
              </div>
              <h5>Image Uploaded</h5>
              <p class="text-muted">Click to change image</p>
              <input class="d-none" type="file" id="storyImage" accept="image/*">
            `;
            // Re-attach event listeners
            imageUploadContainer.addEventListener('click', () => {
              storyImage.click();
            });
          };
          reader.readAsDataURL(file);
        }
      }

      // Add Chapter Button
      document.getElementById('addChapterBtn').addEventListener('click', addChapter);

      function addChapter(title = '', content = '') {
        chapterCount++;
        const chapterDiv = document.createElement('div');
        chapterDiv.classList.add('chapter-container');
        chapterDiv.innerHTML = `
          <div class="chapter-header">
            <h6 class="chapter-title">Chapter ${chapterCount}</h6>
            <span class="remove-chapter"><i class="fas fa-times me-1"></i>Remove</span>
          </div>
          <div class="mb-3">
            <label class="form-label">Chapter Title</label>
            <input type="text" class="form-control chapter-title-input" placeholder="Enter chapter title" value="${title}" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Chapter Content</label>
            <div id="editor-${chapterCount}"></div>
          </div>
        `;
        chaptersContainer.appendChild(chapterDiv);

        // Initialize Quill editor
        const quill = new Quill(`#editor-${chapterCount}`, {
          theme: 'snow',
          placeholder: 'Write your chapter content here...',
          modules: {
            toolbar: [
              [{ 'header': [1, 2, 3, false] }],
              ['bold', 'italic', 'underline'],
              [{ 'list': 'ordered'}, { 'list': 'bullet' }],
              ['link', 'blockquote'],
              ['clean']
            ]
          }
        });
        
        // Set content if provided
        if (content) {
          quill.root.innerHTML = content;
        }
        
        quillEditors.push(quill);

        // Remove chapter functionality
        chapterDiv.querySelector('.remove-chapter').addEventListener('click', () => {
          if (confirm('Are you sure you want to remove this chapter?')) {
            chaptersContainer.removeChild(chapterDiv);
            const index = quillEditors.indexOf(quill);
            if (index > -1) {
              quillEditors.splice(index, 1);
            }
            updatePreview();
          }
        });

        // Update preview on content change
        quill.on('text-change', updatePreview);
        chapterDiv.querySelector('.chapter-title-input').addEventListener('input', updatePreview);

        updatePreview();
      }

      // Load existing chapters if in edit mode
      if (isEditMode && existingStory && existingStory.chapters) {
        existingStory.chapters.forEach((chapter, index) => {
          addChapter(chapter.title || `Chapter ${index + 1}`, chapter.content || '');
        });
      } else {
        // Add first chapter for new story
        addChapter();
      }

      // Preview updates
      storyTitle.addEventListener('input', updatePreview);
      storyAuthor.addEventListener('input', updatePreview);

      function updatePreview() {
        document.getElementById('previewTitle').textContent = storyTitle.value || 'Your Story Title';
        document.getElementById('previewAuthor').textContent = storyAuthor.value ? `by ${storyAuthor.value}` : 'by Author Name';

        // Update description preview
        const previewDescription = document.getElementById('previewDescription');
        if (storyDescription.value.trim()) {
          previewDescription.textContent = storyDescription.value;
          previewDescription.classList.remove('text-muted');
        } else {
          previewDescription.textContent = 'Your story description will appear here...';
          previewDescription.classList.add('text-muted');
        }

        // Update genre badges
        const previewGenre = document.getElementById('previewGenre');
        previewGenre.innerHTML = '';
        
        if (selectedGenres.length === 0) {
          previewGenre.innerHTML = '<span class="badge bg-primary">Genre</span>';
        } else {
          selectedGenres.forEach(genre => {
            const badge = document.createElement('span');
            badge.textContent = genre;
            badge.className = `badge me-1 ${getGenreColor(genre)}`;
            previewGenre.appendChild(badge);
          });
        }

        // Update chapters preview
        previewChapters.innerHTML = '';
        const chapterContainers = document.querySelectorAll('.chapter-container');
        
        if (chapterContainers.length === 0) {
          previewChapters.innerHTML = '<p class="text-muted">Chapters will appear here as you add them...</p>';
        } else {
          chapterContainers.forEach((chapter, idx) => {
            const titleInput = chapter.querySelector('.chapter-title-input');
            const title = titleInput ? titleInput.value : `Chapter ${idx + 1}`;
            const content = quillEditors[idx] ? quillEditors[idx].root.innerHTML : '';

            const chapterDiv = document.createElement('div');
            chapterDiv.classList.add('chapter-collapse');
            chapterDiv.innerHTML = `
              <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0">${title}</h6>
                <button class="collapse-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseChapter${idx}" aria-expanded="false">
                  <i class="fas fa-chevron-down"></i>
                </button>
              </div>
              <div class="collapse mt-2" id="collapseChapter${idx}">
                <div class="collapse-content">
                  ${content || '<p class="text-muted">Chapter content will appear here...</p>'}
                </div>
              </div>
              ${idx < chapterContainers.length - 1 ? '<hr class="my-3">' : ''}
            `;
            previewChapters.appendChild(chapterDiv);
          });
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
          'Romance': 'bg-danger',
          'Comedy': 'bg-secondary',
          'Drama': 'bg-light text-dark',
          'Adventure': 'bg-success',
          'Historical': 'bg-info text-dark'
        };
        return colors[genre] || 'bg-primary';
      }

      // Form submission
      document.getElementById('storyForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        
        // Validate form
        if (!storyTitle.value.trim()) {
          alert('Please enter a story title');
          return;
        }
        
        if (!storyAuthor.value.trim()) {
          alert('Please enter an author name');
          return;
        }
        
        if (selectedGenres.length === 0) {
          alert('Please select at least one genre');
          return;
        }
        
        if (quillEditors.length === 0) {
          alert('Please add at least one chapter');
          return;
        }

        // Show loading state
        const submitBtn = e.target.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = isEditMode ? 'Updating...' : 'Publishing...';
        submitBtn.disabled = true;

        try {
          // Prepare story data
          const storyData = {
            title: storyTitle.value,
            author: storyAuthor.value,
            description: storyDescription.value,
            genre: selectedGenres,
            cover_image: document.getElementById('previewImage').src,
            chapters: quillEditors.map((q, i) => ({
              title: document.querySelectorAll('.chapter-title-input')[i].value || `Chapter ${i + 1}`,
              content: q.root.innerHTML
            })),
            user_id: <?php echo $_SESSION['user']['id']; ?>
          };

          // Add story ID if in edit mode
          if (isEditMode) {
            storyData.id = document.getElementById('storyId').value;
          }

          console.log('Sending story data:', storyData);

          // Determine the endpoint based on mode
          const endpoint = isEditMode ? 'update-story.php' : 'save-story.php';

          // Send to Supabase via PHP
          const response = await fetch(endpoint, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
            },
            body: JSON.stringify(storyData)
          });

          const result = await response.json();
          console.log('Server response:', result);

          if (result.success) {
            alert(isEditMode ? 'Story updated successfully!' : 'Story published successfully!');
            window.location.href = 'mystories.php';
          } else {
            alert('Error ' + (isEditMode ? 'updating' : 'saving') + ' story: ' + (result.error || 'Unknown error'));
          }

        } catch (error) {
          console.error('Error:', error);
          alert('Error ' + (isEditMode ? 'updating' : 'publishing') + ' story. Please try again.');
        } finally {
          // Reset button state
          submitBtn.textContent = originalText;
          submitBtn.disabled = false;
        }
      });
    });
  </script>
</body>
</html>