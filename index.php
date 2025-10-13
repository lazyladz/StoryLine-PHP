<?php
session_start();

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <title>Storyline - Where Stories Come Alive</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="css/styles.css">
</head>

<body>

  <!-- Navbar -->
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
        <ul class="navbar-nav ms-auto">
  <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
  <li class="nav-item"><a class="nav-link" href="#stories">Stories</a></li>
  <li class="nav-item"><a class="nav-link" href="#testimonials">Testimonials</a></li>
  
  <?php if (isset($_SESSION['user'])): ?>
    <!-- User is logged in - Show user dropdown -->
    <li class="nav-item dropdown">
      <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fas fa-user me-1"></i>
        <?php echo htmlspecialchars($_SESSION['user']['first_name']); ?>
      </a>
      <ul class="dropdown-menu">
        <li><a class="dropdown-item" href="dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
        <li><a class="dropdown-item" href="browse.php"><i class="fas fa-compass me-2"></i>Browse</a></li>
        <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>My Profile</a></li>
        <li><a class="dropdown-item" href="mystories.php"><i class="fas fa-book me-2"></i>My Stories</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
      </ul>
    </li>
  <?php else: ?>
    <!-- User is not logged in - Show login/signup/browse -->
    <li class="nav-item"><a class="nav-link" href="browse.php">Browse</a></li>
    <li class="nav-item"><a class="nav-link" href="login.html">Login</a></li>
    <li class="nav-item"><a class="btn btn-main ms-2" href="register.html">Sign Up</a></li>
  <?php endif; ?>
</ul>
      </div>
    </div>
  </nav>

  <!-- Hero Section -->
  <section class="hero">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-lg-6">
          <div class="hero-text">
            <h1>Where Stories Come Alive</h1>
            <p>Discover captivating tales, share your own narratives, and connect with a global community of readers and writers. Your next favorite story is just a click away.</p>
            <div class="hero-buttons mt-4">
  <?php if (isset($_SESSION['user'])): ?>
    <a href="dashboard.php" class="btn btn-main me-3">Go to Dashboard</a>
  <?php else: ?>
    <a href="browse.php" class="btn btn-main me-3">Browse Stories</a>
  <?php endif; ?>
  <a href="register.html" class="btn btn-secondary">Start Writing</a>
</div>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="hero-img text-center">
            <!-- Using a placeholder image URL since we don't have the actual image -->
            <img src="https://images.unsplash.com/photo-1481627834876-b7833e8f5570?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80" alt="Books and reading">
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Features Section -->
  <section class="features" id="features">
    <div class="container">
      <h2 class="section-title">Why Choose Storyline?</h2>
      <div class="row">
        <div class="col-md-4">
          <div class="feature-item">
            <div class="feature-icon">
              <i class="fas fa-book-open"></i>
            </div>
            <h3>Read Unlimited Stories</h3>
            <p>Access thousands of stories across all genres. From romance to sci-fi, there's something for every reader.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="feature-item">
            <div class="feature-icon">
              <i class="fas fa-pen-nib"></i>
            </div>
            <h3>Write & Publish</h3>
            <p>Share your stories with our community. Get feedback, build an audience, and improve your writing skills.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="feature-item">
            <div class="feature-icon">
              <i class="fas fa-users"></i>
            </div>
            <h3>Join a Community</h3>
            <p>Connect with fellow readers and writers. Discuss stories, join writing groups, and participate in challenges.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Featured Stories -->
  <section class="container my-5" id="stories">
    <h2 class="section-title">Popular Stories</h2>
    <div class="row g-4">
      <div class="col-md-4">
        <div class="card">
          <!-- Using placeholder images from Unsplash -->
          <img src="https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=600&q=80" class="card-img-top" alt="Lost in the City">
          <div class="card-body">
            <h5 class="card-title">Lost in the City</h5>
            <p class="card-text">A thrilling adventure through a lost city full of mystery and ancient secrets waiting to be uncovered.</p>
            <div class="d-flex justify-content-between align-items-center">
              <a href="#" class="btn btn-main btn-sm">Read More</a>
              <small class="text-muted">4.8 ★</small>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card">
          <img src="https://images.unsplash.com/photo-1512820790803-83ca734da794?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=600&q=80" class="card-img-top" alt="Love & Letters">
          <div class="card-body">
            <h5 class="card-title">Love & Letters</h5>
            <p class="card-text">A heartwarming romance told through secret letters that change two lives forever.</p>
            <div class="d-flex justify-content-between align-items-center">
              <a href="#" class="btn btn-main btn-sm">Read More</a>
              <small class="text-muted">4.9 ★</small>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card">
          <img src="https://images.unsplash.com/photo-1506905925346-21bda4d32df4?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=600&q=80" class="card-img-top" alt="The Hidden Village">
          <div class="card-body">
            <h5 class="card-title">The Hidden Village</h5>
            <p class="card-text">Uncover mysteries of a forgotten place where legends live and secrets await discovery.</p>
            <div class="d-flex justify-content-between align-items-center">
              <a href="#" class="btn btn-main btn-sm">Read More</a>
              <small class="text-muted">4.7 ★</small>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="text-center mt-5">
      <a href="#" class="btn btn-secondary">Browse All Stories</a>
    </div>
  </section>

  <!-- Testimonials -->
  <section class="testimonials" id="testimonials">
    <div class="container">
      <h2 class="section-title">What Our Readers Say</h2>
      <div class="row">
        <div class="col-md-4">
          <div class="testimonial-card">
            <div class="testimonial-text">
              Storyline has completely changed how I discover new authors. The community is supportive and the stories are incredible!
            </div>
            <div class="testimonial-author">
              <!-- Using placeholder avatar images -->
              <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="Sarah Johnson" class="author-avatar">
              <div class="author-info">
                <h5>Sarah Johnson</h5>
                <p>Avid Reader</p>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="testimonial-card">
            <div class="testimonial-text">
              As a new writer, the feedback I've received on Storyline has been invaluable. My writing has improved so much!
            </div>
            <div class="testimonial-author">
              <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Michael Chen" class="author-avatar">
              <div class="author-info">
                <h5>Michael Chen</h5>
                <p>Emerging Writer</p>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="testimonial-card">
            <div class="testimonial-text">
              I love how easy it is to find stories that match my mood. The recommendation system is spot on!
            </div>
            <div class="testimonial-author">
              <img src="https://randomuser.me/api/portraits/women/68.jpg" alt="Emma Rodriguez" class="author-avatar">
              <div class="author-info">
                <h5>Emma Rodriguez</h5>
                <p>Book Enthusiast</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- CTA Section -->
  <section class="cta">
    <div class="container">
      <h2>Ready to Begin Your Story Journey?</h2>
      <p>Join thousands of readers and writers today. It's free to start!</p>
      <a href="register.html" class="btn btn-main">Create Your Account</a>
    </div>
  </section>

  <!-- Footer -->
  <footer>
    <div class="container">
      <div class="row">
        <div class="col-lg-4 mb-4 mb-lg-0">
          <a class="navbar-brand text-white mb-3 d-inline-block" href="index.php">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" class="bi bi-book me-2"
              viewBox="0 0 16 16">
              <path
                d="M1 2.828c.885-.37 2.154-.769 3.388-.893 1.33-.134 2.458.063 3.112.752v9.746c-.935-.53-2.12-.603-3.213-.493-1.18.12-2.37.461-3.287.811V2.828zm7.5-.141c.654-.689 1.782-.886 3.112-.752 1.234.124 2.503.523 3.388.893v9.923c-.918-.35-2.107-.692-3.287-.81-1.094-.111-2.278-.039-3.213.492V2.687zM8 1.783C7.015.936 5.587.81 4.287.94c-1.514.153-3.042.672-3.994 1.105A.5.5 0 0 0 0 2.5v11a.5.5 0 0 0 .707.455c.882-.4 2.303-.881 3.68-1.02 1.409-.142 2.59.087 3.223.877a.5.5 0 0 0 .78 0c.633-.79 1.814-1.019 3.222-.877 1.378.139 2.8.62 3.681 1.02A.5.5 0 0 0 16 13.5v-11a.5.5 0 0 0-.293-.455c-.952-.433-2.48-.952-3.994-1.105C10.413.809 8.985.936 8 1.783z" />
            </svg>
            Storyline
          </a>
          <p class="text-white-50">Where stories come alive. Discover new tales, write your own, and connect with readers everywhere.</p>
          <div class="social-links mt-3">
            <a href="#"><i class="fab fa-facebook-f"></i></a>
            <a href="#"><i class="fab fa-twitter"></i></a>
            <a href="#"><i class="fab fa-instagram"></i></a>
            <a href="#"><i class="fab fa-linkedin-in"></i></a>
          </div>
        </div>
        <div class="col-lg-2 col-md-4 mb-4 mb-md-0">
          <div class="footer-links">
            <h5>Explore</h5>
            <ul>
              <li><a href="#">Genres</a></li>
              <li><a href="#">Popular Stories</a></li>
              <li><a href="#">New Releases</a></li>
              <li><a href="#">Authors</a></li>
            </ul>
          </div>
        </div>
        <div class="col-lg-2 col-md-4 mb-4 mb-md-0">
          <div class="footer-links">
            <h5>Write</h5>
            <ul>
              <li><a href="#">Start Writing</a></li>
              <li><a href="#">Writing Tips</a></li>
              <li><a href="#">Community Guidelines</a></li>
              <li><a href="#">Author Resources</a></li>
            </ul>
          </div>
        </div>
        <div class="col-lg-2 col-md-4">
          <div class="footer-links">
            <h5>Company</h5>
            <ul>
              <li><a href="#">About Us</a></li>
              <li><a href="#">Careers</a></li>
              <li><a href="#">Contact</a></li>
              <li><a href="#">Help Center</a></li>
            </ul>
          </div>
        </div>
        <div class="col-lg-2 col-md-4">
          <div class="footer-links">
            <h5>Legal</h5>
            <ul>
              <li><a href="#">Terms of Service</a></li>
              <li><a href="#">Privacy Policy</a></li>
              <li><a href="#">Cookie Policy</a></li>
            </ul>
          </div>
        </div>
      </div>
      <div class="copyright">
        &copy; 2025 Storyline. All rights reserved.
      </div>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Navbar scroll effect
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