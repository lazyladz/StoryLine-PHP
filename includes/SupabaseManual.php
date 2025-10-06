<?php
class SupabaseManual {
    private $supabaseUrl;
    private $apiKey;
    
    public function __construct($supabaseUrl, $apiKey) {
        $this->supabaseUrl = rtrim($supabaseUrl, '/');
        $this->apiKey = $apiKey;
    }
    
    private function makeRequest($endpoint, $method = 'GET', $data = null) {
        $url = $this->supabaseUrl . '/rest/v1/' . $endpoint;
        
        $ch = curl_init();
        $headers = [
            'apikey: ' . $this->apiKey,
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json',
            'Prefer: return=representation'
        ];
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => false, // For local development
        ]);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        } elseif ($method === 'PATCH') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        } elseif ($method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception('CURL Error: ' . $error);
        }
        
        if ($httpCode >= 400) {
            throw new Exception('Supabase API Error (' . $httpCode . '): ' . $response);
        }
        
        return json_decode($response, true) ?: [];
    }
    
    // SELECT data from table
    public function select($table, $columns = '*', $filters = []) {
        $endpoint = $table;
        if ($columns !== '*') {
            $endpoint .= '?select=' . urlencode($columns);
        }
        
        // Add filters if provided
        if (!empty($filters)) {
            $queryParams = [];
            foreach ($filters as $column => $value) {
                $queryParams[] = $column . '=eq.' . urlencode($value);
            }
            $endpoint .= (strpos($endpoint, '?') !== false ? '&' : '?') . implode('&', $queryParams);
        }
        
        return $this->makeRequest($endpoint, 'GET');
    }
    
    // INSERT data into table
    public function insert($table, $data) {
        return $this->makeRequest($table, 'POST', $data);
    }
    
    // UPDATE data in table
    public function update($table, $data, $column, $value) {
        $endpoint = $table . '?' . $column . '=eq.' . urlencode($value);
        return $this->makeRequest($endpoint, 'PATCH', $data);
    }
    
    // DELETE data from table
    public function delete($table, $column, $value) {
        $endpoint = $table . '?' . $column . '=eq.' . urlencode($value);
        return $this->makeRequest($endpoint, 'DELETE');
    }
    
    // Simple test connection
    public function testConnection() {
        try {
            // Try to list tables (this is a common Supabase endpoint)
            $result = $this->makeRequest('', 'GET');
            return ['status' => 'success', 'message' => 'Connected to Supabase successfully!'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    // Add this method to the SupabaseManual class:
public function createTable($tableName, $columns) {
    // Note: Table creation via API is limited in Supabase
    // You need to create tables in the Supabase dashboard
    return "Please create table '$tableName' in Supabase Dashboard > Table Editor";
}
}
?>