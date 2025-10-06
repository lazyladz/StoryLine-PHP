<?php
session_start();
require_once "includes/check-auth.php";
$user = checkAuth(); // Ensure user is logged in
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>My Stories - Storyline</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" />
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
                        d="M1 2.828c.885-.37 2.154-.769 3.388-.893 1.33-.134 2.458.063 3.112.752v9.746c-.935-.53-2.12-.603-3.213-.493-1.18.12-2.37.461-3.287.811V2.828zm7.5-.141c.654-.689 1.782-.886 3.112-.752 1.234.124 2.503.523 3.388.893v9.923c-.918-.35-2.107-.692-3.287-.81-1.094-.111-2.278-.039-3.213.492V2.687zM8 1.783C7.015.936 5.587.81 4.287.94c-1.514.153-3.042.672-3.994 1.105A.5.5 0 0 0 0 2.5v11a.5.5 0 0 0 .707.455c.882-.4 2.303-.881 3.68-1.02 1.409-.142 2.59.087 3.223.877a.5.5 0 0 0 .78 0c.633-.79 1.814-1.019 3.222-.877 1.378.139 2.8.62 3.681 1.02A.5.5 0 0 0 16 13.5v-11a.5.5 0 0 0 -.293-.455c-.952-.433-2.48-.952-3.994-1.105C10.413.809 8.985.936 8 1.783z" />
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
                    <li class="nav-item"><a class="nav-link active" href="mystories.php"><i class="fas fa-book me-1"></i>My Stories</a></li>
                    
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
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1><i class="fas fa-book me-2"></i>My Stories</h1>
                    <p>Manage and view all your published stories</p>
                </div>
                <div class="col-lg-6">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="stats-card">
                                <div class="stats-number" id="totalStories">0</div>
                                <div class="stats-label">Total Stories</div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="stats-card">
                                <div class="stats-number" id="totalChapters">0</div>
                                <div class="stats-label">Total Chapters</div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="stats-card">
                                <div class="stats-number" id="totalReads">0</div>
                                <div class="stats-label">Total Reads</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Page Content -->
    <div class="container mb-5">
        <!-- Filter Section -->
        <div class="filter-section">
            <h5 class="filter-title"><i class="fas fa-filter me-2"></i>Filter by Genre</h5>
            <div class="filter-tags">
                <span class="filter-tag active" data-genre="all">All</span>
                <span class="filter-tag" data-genre="Fantasy">Fantasy</span>
                <span class="filter-tag" data-genre="Thriller">Thriller</span>
                <span class="filter-tag" data-genre="Horror">Horror</span>
                <span class="filter-tag" data-genre="Mystery">Mystery</span>
                <span class="filter-tag" data-genre="Action">Action</span>
                <span class="filter-tag" data-genre="Sci-Fi">Sci-Fi</span>
                <span class="filter-tag" data-genre="Romance">Romance</span>
                <span class="filter-tag" data-genre="Comedy">Comedy</span>
                <span class="filter-tag" data-genre="Drama">Drama</span>
                <span class="filter-tag" data-genre="Adventure">Adventure</span>
                <span class="filter-tag" data-genre="Historical">Historical</span>
            </div>
        </div>

        <!-- Loading State -->
        <div id="loadingState" class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3">Loading your stories...</p>
        </div>

        <!-- Stories Grid -->
        <div class="row" id="storiesContainer" style="display: none;">
            <!-- Story cards will be injected here -->
        </div>
        
        <!-- Empty State -->
        <div id="noStories" class="empty-state d-none">
            <i class="fas fa-book-open"></i>
            <h4>No stories yet</h4>
            <p>You haven't published any stories. Start your writing journey today!</p>
            <a href="write.php" class="btn btn-main">Write Your First Story</a>
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
        // DOM Elements
        const storiesContainer = document.getElementById('storiesContainer');
        const noStories = document.getElementById('noStories');
        const loadingState = document.getElementById('loadingState');
        const filterTags = document.querySelectorAll('.filter-tag');
        let currentFilter = 'all';
        let allStories = [];

        console.log('DOM loaded, starting script...');

        // Load stories from Supabase
        async function loadStoriesFromSupabase() {
            try {
                console.log('Fetching stories from get-my-stories.php...');
                
                const response = await fetch('get-my-stories.php', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                    }
                });

                console.log('Response status:', response.status);
                const result = await response.json();
                console.log('API Response:', result);
                
                if (result.success) {
                    allStories = result.data || [];
                    console.log('Stories loaded successfully:', allStories.length, 'stories');
                    console.log('Stories data:', allStories);
                    renderStories();
                } else {
                    throw new Error(result.error || 'Failed to load stories');
                }
            } catch (error) {
                console.error('Error loading stories:', error);
                showError('Failed to load stories. Please try again.');
            } finally {
                loadingState.style.display = 'none';
                console.log('Loading state hidden');
            }
        }

        // Render stories - SIMPLIFIED VERSION FOR DEBUGGING
        function renderStories() {
            console.log('renderStories called with', allStories.length, 'stories');
            
            let filteredStories = [...allStories];
            
            // Apply filter if not "all"
            if (currentFilter !== 'all') {
                filteredStories = allStories.filter(story => {
                    if (Array.isArray(story.genre)) {
                        return story.genre.includes(currentFilter);
                    }
                    return story.genre === currentFilter;
                });
            }
            
            console.log('Filtered stories:', filteredStories.length);
            
            storiesContainer.innerHTML = '';
            storiesContainer.style.display = 'block';
            updateStats(allStories);

            if (filteredStories.length === 0) {
                console.log('No stories to display');
                if (currentFilter === 'all') {
                    noStories.classList.remove('d-none');
                    storiesContainer.style.display = 'none';
                } else {
                    noStories.innerHTML = `
                        <i class="fas fa-search"></i>
                        <h4>No stories found</h4>
                        <p>You don't have any stories in the ${currentFilter} genre.</p>
                        <a href="write.php" class="btn btn-primary">Write a New Story</a>
                    `;
                    noStories.classList.remove('d-none');
                    storiesContainer.style.display = 'none';
                }
                return;
            }

            noStories.classList.add('d-none');
            storiesContainer.style.display = 'flex';

            console.log('Creating story cards...');
            
            filteredStories.forEach((story, index) => {
                console.log(`Creating card for story ${index}:`, story);
                
                const col = document.createElement('div');
                col.classList.add('col-xl-4', 'col-lg-6', 'mb-4');
                
                const totalReads = story.reads || 0;
                const rating = story.rating || 0;
                const chapterCount = story.chapters ? story.chapters.length : 0;
                const firstChapterTitle = story.chapters && story.chapters.length > 0 ? story.chapters[0].title : 'No chapters yet';
                
                // SIMPLIFIED CARD - Remove complex genre handling for now
                col.innerHTML = `
                    <div class="card story-card h-100">
                        <img src="${story.cover_image || 'https://images.unsplash.com/photo-1455390582262-044cdead277a?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80'}" 
                             class="card-img-top story-img" alt="${story.title}" style="height: 200px; object-fit: cover;">
                        <div class="card-body">
                            <h5 class="card-title">${story.title || 'Untitled'}</h5>
                            <p class="card-text">by ${story.author || 'Unknown Author'}</p>
                            
                            <!-- Simple genre display -->
                            <div class="mb-2">
                                <span class="badge bg-primary">${Array.isArray(story.genre) ? story.genre[0] : (story.genre || 'Unknown')}</span>
                            </div>
                            
                            <div class="chapter-preview mb-2">
                                <small class="text-muted">${firstChapterTitle}</small>
                            </div>
                            
                            <div class="story-stats mb-3">
                                <small class="text-muted">${totalReads} reads • ${chapterCount} chapters</small>
                            </div>
                            
                            <div class="action-buttons">
                                <button class="btn btn-primary btn-sm me-1" onclick="viewStory('${story.id}')">
                                    <i class="fas fa-eye me-1"></i>View
                                </button>
                                <button class="btn btn-secondary btn-sm me-1" onclick="editStory('${story.id}')">
                                    <i class="fas fa-edit me-1"></i>Edit
                                </button>
                                <button class="btn btn-danger btn-sm" onclick="deleteStory('${story.id}')">
                                    <i class="fas fa-trash me-1"></i>Delete
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                storiesContainer.appendChild(col);
                console.log(`Card ${index} created and appended`);
            });
            
            console.log('All cards created');
        }

        // Update stats
        function updateStats(stories) {
            const totalStories = stories.length;
            const totalChapters = stories.reduce((sum, story) => sum + (story.chapters ? story.chapters.length : 0), 0);
            const totalReads = stories.reduce((sum, story) => sum + (story.reads || 0), 0);
            
            document.getElementById('totalStories').textContent = totalStories;
            document.getElementById('totalChapters').textContent = totalChapters;
            document.getElementById('totalReads').textContent = totalReads.toLocaleString();
            
            console.log('Stats updated:', { totalStories, totalChapters, totalReads });
        }

        // Show error message
        function showError(message) {
            console.error('Showing error:', message);
            const toast = document.createElement('div');
            toast.className = 'alert alert-danger position-fixed top-0 end-0 m-3';
            toast.style.zIndex = '9999';
            toast.innerHTML = `
                <i class="fas fa-exclamation-circle me-2"></i>
                ${message}
            `;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                document.body.removeChild(toast);
            }, 5000);
        }

        // Make functions globally available
        window.viewStory = function(storyId) {
            console.log('View story:', storyId);
            window.location.href = `stories.php?id=${storyId}`;
        };

        window.editStory = function(storyId) {
            console.log('Edit story:', storyId);
            window.location.href = `write.php?id=${storyId}`;
        };

        window.deleteStory = function(storyId) {
            console.log('Delete story:', storyId);
            if (confirm('Are you sure you want to delete this story? This action cannot be undone.')) {
                // For now, just remove from local array
                allStories = allStories.filter(story => story.id != storyId);
                renderStories();
                console.log('Story removed locally:', storyId);
            }
        };

        // Filter functionality
        filterTags.forEach(tag => {
            tag.addEventListener('click', function() {
                filterTags.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                currentFilter = this.dataset.genre;
                console.log('Filter changed to:', currentFilter);
                renderStories();
            });
        });

        // Initial load
        console.log('Starting initial load...');
        loadStoriesFromSupabase();
    });
</script>
</body>
</html>