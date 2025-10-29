<?php
// indexAdmin.php
session_start();
require_once "includes/database.php";

// Check if user is logged in and is admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.html");
    exit();
}

// Get real data from database
$db = new Database();

// Get total users count - Supabase compatible way
$users = $db->select('users', 'id', []);
$totalUsers = is_array($users) ? count($users) : 0;

// Get all stories for statistics
$stories = $db->select('stories', '*', []);
$totalStories = is_array($stories) ? count($stories) : 0;

// Calculate pending stories and genre stats
$pendingStories = 0;
$genreStats = [];
$totalReads = 0;
$topStories = [];

if (is_array($stories)) {
    foreach ($stories as $story) {
        // Count reads
        $reads = isset($story['reads']) ? intval($story['reads']) : 0;
        $totalReads += $reads;
        
        // Count as pending if has 0 reads
        if ($reads == 0) {
            $pendingStories++;
        }
        
        // Collect top stories for chart
        $topStories[] = [
            'title' => $story['title'] ?? 'Untitled',
            'reads' => $reads
        ];
        
        // Process genre data
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
        
        // Count genres
        if (is_array($genres)) {
            foreach ($genres as $genre) {
                if (!isset($genreStats[$genre])) {
                    $genreStats[$genre] = 0;
                }
                $genreStats[$genre]++;
            }
        } else {
            // If no genre specified
            if (!isset($genreStats['Unknown'])) {
                $genreStats['Unknown'] = 0;
            }
            $genreStats['Unknown']++;
        }
    }
}

// Sort genres by count and get top stories
arsort($genreStats);

// Sort top stories by reads and get top 10
usort($topStories, function($a, $b) {
    return $b['reads'] - $a['reads'];
});
$topStories = array_slice($topStories, 0, 10);

// Prepare chart data
$genreLabels = array_keys($genreStats);
$genreCounts = array_values($genreStats);

$storyTitles = array_column($topStories, 'title');
$storyReads = array_column($topStories, 'reads');

// Truncate long story titles for chart display
$truncatedTitles = array_map(function($title) {
    return strlen($title) > 20 ? substr($title, 0, 20) . '...' : $title;
}, $storyTitles);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Storyline - Admin Dashboard</title>
  <!-- Bootstrap 5 CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
    .top-card, .list-card, .chart-card {
      background: #ffffff;
      border-radius: .75rem;
      box-shadow: 0 4px 12px rgba(0,0,0,.08);
      padding: 1.5rem;
    }
    .points {
      font-size: 1.5rem;
      font-weight: bold;
      color: #0d1321; 
    }
    .progress {
      background-color: #e6e9f0; 
      border-radius: 10px;
      overflow: hidden;
    }
    .progress-bar {
      border-radius: 10px;
    }
    .chart-container {
      position: relative;
      height: 300px;
      width: 100%;
    }
    .stat-card {
      transition: transform 0.2s;
    }
    .stat-card:hover {
      transform: translateY(-2px);
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
        <li class="nav-item"><a href="indexAdmin.php" class="nav-link active">Dashboard</a></li>
        <li class="nav-item"><a href="manageUser.php" class="nav-link">Manage Users</a></li>
        <li class="nav-item"><a href="manageStories.php" class="nav-link">Manage Stories</a></li>
        <li class="nav-item"><a href="notifications.html" class="nav-link">Notifications</a></li>
        <li class="nav-item"><a href="settings.html" class="nav-link">Settings</a></li>
        <li class="nav-item"><a href="#" class="nav-link" id="logoutLink">Log Out</a></li>
      </ul>
      <div class="mt-auto pt-3">
        <button type="button" class="btn btn-outline-light w-100" data-bs-toggle="modal" data-bs-target="#userProfileModal">
          <strong id="sidebarUserName"><?php echo $_SESSION['user']['first_name'] ?? 'Admin'; ?></strong><br>
          <small id="sidebarUserEmail"><?php echo $_SESSION['user']['email'] ?? 'Admin Email'; ?></small>
        </button>
      </div>
    </nav>

    <!-- Main content -->
    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Admin Dashboard</h2>
        <small class="text-muted">Last updated: <?php echo date('F j, Y g:i A'); ?></small>
      </div>

      <!-- Top stats -->
      <div class="row g-3 mb-4">
        <div class="col-md-3">
          <div class="top-card stat-card">
            <h5><i class="fas fa-users text-primary me-2"></i>Total Users</h5>
            <div class="points"><?php echo $totalUsers; ?></div>
            <small class="text-muted">Registered users</small>
          </div>
        </div>
        <div class="col-md-3">
          <div class="top-card stat-card">
            <h5><i class="fas fa-book text-success me-2"></i>Total Stories</h5>
            <div class="points"><?php echo $totalStories; ?></div>
            <small class="text-muted">Published stories</small>
          </div>
        </div>
        <div class="col-md-3">
          <div class="top-card stat-card">
            <h5><i class="fas fa-clock text-warning me-2"></i>Pending Stories</h5>
            <div class="points"><?php echo $pendingStories; ?></div>
            <small class="text-muted">Stories with 0 reads</small>
          </div>
        </div>
        <div class="col-md-3">
          <div class="top-card stat-card">
            <h5><i class="fas fa-eye text-info me-2"></i>Total Reads</h5>
            <div class="points"><?php echo $totalReads; ?></div>
            <small class="text-muted">Across all stories</small>
          </div>
        </div>
      </div>

      <!-- Charts Row -->
      <div class="row mb-4">
        <!-- Genre Distribution Chart -->
        <div class="col-md-6">
          <div class="chart-card">
            <h5 class="mb-4"><i class="fas fa-chart-pie me-2"></i>Stories by Genre</h5>
            <div class="chart-container">
              <canvas id="genreChart"></canvas>
            </div>
          </div>
        </div>
        
        <!-- Reads Distribution Chart -->
        <div class="col-md-6">
          <div class="chart-card">
            <h5 class="mb-4"><i class="fas fa-chart-bar me-2"></i>Top Stories by Reads</h5>
            <div class="chart-container">
              <canvas id="readsChart"></canvas>
            </div>
          </div>
        </div>
      </div>

      <!-- Genre Statistics with Progress Bars -->
      <div class="row mb-4">
        <div class="col-12">
          <div class="list-card">
            <h5 class="mb-4"><i class="fas fa-list me-2"></i>Genre Breakdown</h5>
            <?php if (!empty($genreStats)): ?>
              <?php 
              $maxGenreCount = max($genreStats);
              foreach ($genreStats as $genre => $count): 
                $percentage = $totalStories > 0 ? ($count / $totalStories) * 100 : 0;
                $progressWidth = $maxGenreCount > 0 ? ($count / $maxGenreCount) * 100 : 0;
              ?>
                <div class="mb-3">
                  <div class="d-flex justify-content-between">
                    <span class="fw-medium"><?php echo htmlspecialchars($genre); ?></span>
                    <span class="fw-bold"><?php echo $count; ?> stories</span>
                  </div>
                  <div class="progress" style="height: 18px;">
                    <div class="progress-bar" 
                         role="progressbar" 
                         style="width: <?php echo $progressWidth; ?>%; background-color: <?php echo getGenreColor($genre); ?>"
                         aria-valuenow="<?php echo $count; ?>" 
                         aria-valuemin="0" 
                         aria-valuemax="<?php echo $maxGenreCount; ?>">
                    </div>
                  </div>
                  <small class="text-muted"><?php echo number_format($percentage, 1); ?>% of total stories</small>
                </div>
              <?php endforeach; ?>
              
              <div class="mt-4 text-end">
                <span class="fw-bold">Total Stories: <?php echo $totalStories; ?></span>
              </div>
            <?php else: ?>
              <div class="text-center text-muted py-4">
                <i class="fas fa-book fa-2x mb-2"></i>
                <p>No stories found in the database.</p>
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
  document.addEventListener('DOMContentLoaded', function() {
    // Genre Chart Data from PHP
    const genreData = {
      labels: <?php echo json_encode($genreLabels); ?>,
      datasets: [{
        data: <?php echo json_encode($genreCounts); ?>,
        backgroundColor: [
          '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
          '#FF9F40', '#FF6384', '#C9CBCF', '#4BC0C0', '#FF6384',
          '#36A2EB', '#FFCE56'
        ],
        borderWidth: 2,
        borderColor: '#fff'
      }]
    };

    // Reads Chart Data (Top stories by reads)
    const readsData = {
      labels: <?php echo json_encode($truncatedTitles); ?>,
      datasets: [{
        label: 'Reads',
        data: <?php echo json_encode($storyReads); ?>,
        backgroundColor: '#36A2EB',
        borderColor: '#36A2EB',
        borderWidth: 1
      }]
    };

    // Initialize Genre Pie Chart
    const genreCtx = document.getElementById('genreChart').getContext('2d');
    new Chart(genreCtx, {
      type: 'pie',
      data: genreData,
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'right',
          },
          tooltip: {
            callbacks: {
              label: function(context) {
                const label = context.label || '';
                const value = context.raw || 0;
                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                const percentage = Math.round((value / total) * 100);
                return `${label}: ${value} stories (${percentage}%)`;
              }
            }
          }
        }
      }
    });

    // Initialize Reads Bar Chart
    const readsCtx = document.getElementById('readsChart').getContext('2d');
    new Chart(readsCtx, {
      type: 'bar',
      data: readsData,
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: {
            beginAtZero: true,
            title: {
              display: true,
              text: 'Number of Reads'
            }
          },
          x: {
            ticks: {
              maxRotation: 45,
              minRotation: 45
            }
          }
        },
        plugins: {
          legend: {
            display: false
          },
          tooltip: {
            callbacks: {
              title: function(tooltipItems) {
                // Show full title in tooltip
                const index = tooltipItems[0].dataIndex;
                return <?php echo json_encode($storyTitles); ?>[index] || '';
              }
            }
          }
        }
      }
    });

    // Logout functionality
    document.getElementById('logoutLink').addEventListener('click', function(e) {
      e.preventDefault();
      if (confirm('Are you sure you want to log out?')) {
        window.location.href = 'logout.php';
      }
    });
  });
</script>
</body>
</html>

<?php
// Helper function to get genre colors
function getGenreColor($genre) {
    $colors = [
        'Fantasy' => '#36A2EB',
        'Thriller' => '#FF6384', 
        'Horror' => '#9966FF',
        'Mystery' => '#4BC0C0',
        'Action' => '#FF9F40',
        'Sci-Fi' => '#FFCE56',
        'Romance' => '#FF6384',
        'Comedy' => '#C9CBCF',
        'Drama' => '#4BC0C0',
        'Adventure' => '#36A2EB',
        'Historical' => '#9966FF',
        'Unknown' => '#666666'
    ];
    return $colors[$genre] ?? '#36A2EB';
}
?>