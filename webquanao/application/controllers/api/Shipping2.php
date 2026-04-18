<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Simplified Shipping Provider API without database dependency
 */
class Shipping2 extends CI_Controller {

    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Index method
     */
    public function index() {
        echo "Shipping2 controller is working!";
    }
    
    /**
     * Register a new shipment
     */
    public function register() {
        log_message('info', 'Shipping2/register API endpoint hit.'); // Log endpoint access
        // Get raw POST data
        $raw_input = file_get_contents('php://input');
        
        // Log received data
        log_message('debug', 'Shipping2/register received raw data: ' . $raw_input);
        
        $data = json_decode($raw_input, true);
        
        // Check for JSON decoding errors
        if (json_last_error() !== JSON_ERROR_NONE) {
            log_message('error', 'Shipping2/register: Invalid JSON received. Error: ' . json_last_error_msg());
            header('Content-Type: application/json');
            http_response_code(400); // Bad Request
            echo json_encode(['status' => 'error', 'message' => 'Invalid JSON data: ' . json_last_error_msg()]);
            return;
        }
        
        // Validate required fields (example)
        if (!isset($data['transaction_id']) || !isset($data['customer_name'])) {
             log_message('error', 'Shipping2/register: Missing required fields. Data: ' . $raw_input);
             header('Content-Type: application/json');
             http_response_code(400); // Bad Request
             echo json_encode(['status' => 'error', 'message' => 'Missing required fields (transaction_id, customer_name).']);
             return;
        }

        // Generate a tracking ID
        $tracking_id = 'TRACK-' . date('YmdHis') . '-' . rand(1000, 9999);
        log_message('info', "Shipping2/register generated tracking ID: {$tracking_id} for transaction: " . $data['transaction_id']);

        // Return tracking ID
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'message' => 'Shipment registered successfully',
            'tracking_id' => $tracking_id,
            'estimated_delivery' => date('Y-m-d', strtotime('+3 days'))
        ]);
    }
    
    /**
     * Get shipment status
     */
    public function status($tracking_id = '') {
        // Return dummy status
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'processing',
            'tracking_id' => $tracking_id,
            'estimated_delivery' => date('Y-m-d', strtotime('+3 days'))
        ]);
    }
    
    /**
     * Update shipment status
     */
    public function update_status($tracking_id = '') {
        log_message('info', 'Shipping2/update_status called for tracking_id: ' . $tracking_id);
        
        if (empty($tracking_id)) {
            log_message('error', 'Shipping2/update_status: No tracking ID provided');
            $this->output->set_status_header(400);
            $this->output->set_content_type('application/json');
            $this->output->set_output(json_encode([
                'status' => 'error',
                'message' => 'Tracking ID is required'
            ]));
            return;
        }
        
        $status = $this->input->get('status', true) ?: 'processing';
        log_message('info', 'Shipping2/update_status: New status requested: ' . $status);
        
        // Valid statuses
        $valid_statuses = ['processing', 'in_transit', 'out_for_delivery', 'delivered'];
        if (!in_array($status, $valid_statuses)) {
            log_message('error', 'Shipping2/update_status: Invalid status: ' . $status);
            $this->output->set_status_header(400);
            $this->output->set_content_type('application/json');
            $this->output->set_output(json_encode([
                'status' => 'error',
                'message' => 'Invalid status. Valid statuses are: ' . implode(', ', $valid_statuses)
            ]));
            return;
        }
        
        // Send callback if status is delivered
        if ($status === 'delivered') {
            log_message('info', 'Shipping2/update_status: Sending delivered callback for tracking_id: ' . $tracking_id);
            
            // Extract transaction ID from tracking ID
            preg_match('/TRACK-\d{14}-(\d+)/', $tracking_id, $matches);
            $transaction_id = isset($matches[1]) ? $matches[1] : null;
            
            if ($transaction_id) {
                $callback_url = site_url('api/shipping/webhook');
                $callback_data = [
                    'transaction_id' => $transaction_id,
                    'tracking_id' => $tracking_id,
                    'status' => 3, // Delivered status
                    'timestamp' => date('Y-m-d H:i:s')
                ];
                
                // Send callback
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $callback_url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($callback_data));
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Accept: application/json'
                ]);
                
                $response = curl_exec($ch);
                $error = curl_error($ch);
                curl_close($ch);
                
                if ($error) {
                    log_message('error', 'Shipping2/update_status: Callback error: ' . $error);
                } else {
                    log_message('info', 'Shipping2/update_status: Callback sent successfully');
                }
            }
        }
        
        // Return success response
        $this->output->set_content_type('application/json');
        $this->output->set_output(json_encode([
            'status' => 'success',
            'message' => 'Status updated successfully',
            'shipping_status' => $status,
            'tracking_id' => $tracking_id,
            'updated_at' => date('Y-m-d H:i:s')
        ]));
    }

    /**
     * Carrier delivery confirmation endpoint
     */
    public function confirm_delivery($tracking_id = '') {
        log_message('info', 'Shipping2/confirm_delivery called for tracking_id: ' . $tracking_id);
        
        if (empty($tracking_id)) {
            log_message('error', 'Shipping2/confirm_delivery: No tracking ID provided');
            $this->output->set_status_header(400);
            $this->output->set_content_type('application/json');
            $this->output->set_output(json_encode([
                'status' => 'error',
                'message' => 'Tracking ID is required'
            ]));
            return;
        }
        
        // Extract transaction ID from tracking ID
        preg_match('/TRACK-\d{14}-(\d+)/', $tracking_id, $matches);
        $transaction_id = isset($matches[1]) ? $matches[1] : null;
        
        if (!$transaction_id) {
            log_message('error', 'Shipping2/confirm_delivery: Invalid tracking ID format');
            $this->output->set_status_header(400);
            $this->output->set_content_type('application/json');
            $this->output->set_output(json_encode([
                'status' => 'error',
                'message' => 'Invalid tracking ID format'
            ]));
            return;
        }
        
        // Send webhook to update transaction status
        $callback_url = site_url('api/shipping/webhook');
        $callback_data = [
            'transaction_id' => $transaction_id,
            'tracking_id' => $tracking_id,
            'status' => 3, // Delivered status
            'timestamp' => date('Y-m-d H:i:s'),
            'confirmed_by' => 'carrier'
        ];
        
        // Send callback
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $callback_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($callback_data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            log_message('error', 'Shipping2/confirm_delivery: Callback error: ' . $error);
            $this->output->set_status_header(500);
            $this->output->set_content_type('application/json');
            $this->output->set_output(json_encode([
                'status' => 'error',
                'message' => 'Failed to update delivery status'
            ]));
            return;
        }
        
        // Return success response
        $this->output->set_content_type('application/json');
        $this->output->set_output(json_encode([
            'status' => 'success',
            'message' => 'Delivery confirmed successfully',
            'tracking_id' => $tracking_id,
            'confirmed_at' => date('Y-m-d H:i:s')
        ]));
    }
} 