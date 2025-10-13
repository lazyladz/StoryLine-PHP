<?php
session_start();
require_once "includes/database.php";

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $db = new Database();
    
    // Get ALL stories from database (no user filter)
    $result = $db->select('stories', '*', []);
    
    // Check if we have any stories
    if (is_array($result) && !empty($result)) {
        $stories = array_values($result);
        $stories = array_filter($stories, 'is_array');
        
        if (!empty($stories)) {
            $formattedStories = [];
            
            foreach ($stories as $story) {
                // Handle genre data
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
                
                // Handle chapters data
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
                
                // Format the story data
                $formattedStory = [
                    'id' => $story['id'] ?? null,
                    'title' => $story['title'] ?? 'Untitled',
                    'author' => $story['author'] ?? 'Unknown Author',
                    'genre' => $genre,
                    'cover_image' => $story['cover_image'] ?? 'https://images.unsplash.com/photo-1455390582262-044cdead277a?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
                    'chapters' => $chapters,
                    'reads' => $story['reads'] ?? 0,
                    'rating' => $story['rating'] ?? 0,
                    'created_at' => $story['created_at'] ?? date('Y-m-d H:i:s')
                ];
                
                $formattedStories[] = $formattedStory;
            }
            
            echo json_encode(['success' => true, 'data' => $formattedStories]);
            
        } else {
            echo json_encode(['success' => false, 'error' => 'No stories found']);
        }
    } else {
        echo json_encode(['success' => true, 'data' => []]);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>