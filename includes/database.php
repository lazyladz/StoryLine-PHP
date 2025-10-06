<?php
require_once __DIR__ . '/SupabaseManual.php';

class Database {
    private $supabase;
    
    public function __construct() {
        $config = include __DIR__ . '/../config/supabase-config.php';
        
        $url = $config['supabase_url'];
        $key = $config['supabase_key'];
        
        $this->supabase = new SupabaseManual($url, $key);
    }
    
    public function testConnection() {
        return $this->supabase->testConnection();
    }
    
    public function select($table, $columns = '*', $filters = []) {
        return $this->supabase->select($table, $columns, $filters);
    }
    
    public function insert($table, $data) {
        return $this->supabase->insert($table, $data);
    }
    
    public function update($table, $data, $column, $value) {
        return $this->supabase->update($table, $data, $column, $value);
    }
    
    public function delete($table, $column, $value) {
        return $this->supabase->delete($table, $column, $value);
    }
}
?>