<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Setup extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }
    
    /**
     * Create user_cart table
     */
    /**
     * Setup shipping tracking
     */
    public function setup_shipping() {
        // Remove logic for adding 'shipping_info' to 'transaction' table
        // $fields = $this->db->field_data('transaction');
        // $shipping_info_exists = false;
        
        // foreach ($fields as $field) {
        //     if ($field->name === 'shipping_info') {
        //         $shipping_info_exists = true;
        //         break;
        //     }
        // }
        
        // if (!$shipping_info_exists) {
        //     $this->db->query("ALTER TABLE `transaction` ADD COLUMN `shipping_info` VARCHAR(255) NULL DEFAULT NULL AFTER `status`;");
        //     echo "Column 'shipping_info' added to transaction table successfully.<br/>";
        // } else {
        //     echo "Column 'shipping_info' already exists in transaction table.<br/>";
        // }
        if (!$this->db->table_exists('shipping_tracking')) {
            // Create shipping_tracking table
            $this->db->query("
                CREATE TABLE `shipping_tracking` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `tracking_id` varchar(50) NOT NULL,
                    `transaction_id` int(11) NOT NULL,
                    `customer_name` varchar(255) NOT NULL,
                    `shipping_address` text NOT NULL,
                    `status` varchar(20) NOT NULL DEFAULT 'processing',
                    `created_at` datetime NOT NULL,
                    `updated_at` datetime DEFAULT NULL,
                    `estimated_delivery` date DEFAULT NULL,
                    `callback_url` varchar(255) DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `tracking_id` (`tracking_id`),
                    KEY `transaction_id` (`transaction_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");
            echo "Table 'shipping_tracking' created successfully.<br/>";
        } else {
            echo "Table 'shipping_tracking' already exists.<br/>";
        }
        
        echo "<p>Shipping setup completed.</p>";
        echo "<p><a href='" . site_url('admin/setup') . "'>Back to Setup</a></p>";
    }
    
    /**
     * Test shipping API
     */
    public function test_shipping_api() {
        echo "<h2>Testing Shipping API</h2>";
        
        $urls_to_test = [
            'lowercase' => site_url('api/shippingprovider/register'),
            'uppercase_first' => site_url('api/ShippingProvider/register'),
            'all_lowercase' => site_url('api/shippingprovider/register'),
            'route_direct' => site_url('index.php/api/shippingprovider/register')
        ];
        
        echo "<h3>Testing URL Formats</h3>";
        echo "<table border='1' cellpadding='10'>";
        echo "<tr><th>URL Type</th><th>URL</th><th>Status</th></tr>";
        
        foreach ($urls_to_test as $type => $url) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            $status_class = $http_code == 200 || $http_code == 400 ? 'color:green;' : 'color:red;';
            
            echo "<tr>";
            echo "<td>{$type}</td>";
            echo "<td>{$url}</td>";
            echo "<td style='{$status_class}'>{$http_code}</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        echo "<h3>Testing API Functionality</h3>";
        
        $test_url = site_url('api/shippingprovider/register');
        
        $test_data = [
            'transaction_id' => 'TEST' . date('YmdHis'),
            'customer_name' => 'API Test User',
            'shipping_address' => '123 Test Street, Test City',
            'callback_url' => site_url('api/shipping/webhook')
        ];
        
        $ch = curl_init($test_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        echo "<div style='margin-top: 20px;'>";
        echo "<p><strong>POST URL:</strong> {$test_url}</p>";
        echo "<p><strong>POST Data:</strong> <pre>" . json_encode($test_data, JSON_PRETTY_PRINT) . "</pre></p>";
        
        if ($error) {
            echo "<p style='color:red;'><strong>Error:</strong> {$error}</p>";
        } else {
            echo "<p><strong>HTTP Code:</strong> {$http_code}</p>";
            echo "<p><strong>Response:</strong> <pre>" . (json_encode(json_decode($response), JSON_PRETTY_PRINT) ?: htmlspecialchars($response)) . "</pre></p>";
        }
        
        echo "</div>";
        
        echo "<h3>Testing Webhook Endpoint</h3>";
        
        $webhook_url = site_url('api/shipping/webhook');
        $ch = curl_init($webhook_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        $webhook_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        echo "<p><strong>Webhook URL:</strong> {$webhook_url}</p>";
        echo "<p><strong>Status Code:</strong> <span style='" . ($webhook_status == 200 ? 'color:green;' : 'color:red;') . "'>{$webhook_status}</span></p>";
        
        echo "<p><a href='" . site_url('admin/setup') . "'>Back to Setup</a></p>";
    }
    
    /**
     * Main setup page
     */
    public function index() {
        echo "<h1>Setup Utilities</h1>";
        echo "<ul>";
        echo "<li><a href='" . site_url('admin/setup/create_cart_table') . "'>Create User Cart Table</a></li>";
        echo "<li><a href='" . site_url('admin/setup/setup_shipping') . "'>Setup Shipping System</a></li>";
        echo "<li><a href='" . site_url('admin/setup/test_shipping_api') . "'>Test Shipping API</a></li>";
        echo "</ul>";
    }
}