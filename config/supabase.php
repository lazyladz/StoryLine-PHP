<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Supabase\Postgrest\PostgrestClient;

class SupabaseConfig {
    private $client;
    
    public function __construct() {
        // Load Supabase configuration
        $config = require_once __DIR__ . '/supabase-config.php';
        
        $url = $config['supabase_url'];
        $key = $config['supabase_key'];
        
        // Check if credentials are still placeholders
        if (strpos($url, 'YOUR_ACTUAL') !== false || strpos($key, 'YOUR_ACTUAL') !== false) {
            throw new Exception('Please update your Supabase credentials in config/supabase-config.php');
        }
        
        $this->client = new PostgrestClient($url, $key, [
            'db' => ['schema' => 'public'],
            'global' => ['headers' => []]
        ]);
    }
    
    public function getClient() {
        return $this->client;
    }
}
?>