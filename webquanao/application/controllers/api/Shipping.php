<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Shipping extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('transaction_model');
        $this->load->model('order_model');
        $this->load->model('product_model');
        $this->load->database();
    }
    
    public function webhook() {
        $post_data = file_get_contents('php://input');
        log_message('info', 'Shipping webhook received: ' . $post_data);
        
        if (empty($post_data) && $_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->output->set_content_type('application/json');
            $this->output->set_output(json_encode([
                'status' => 'success',
                'message' => 'Webhook endpoint is working. Use POST to send data.'
            ]));
            return;
        }
        
        $shipping_data = json_decode($post_data, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            log_message('error', 'Invalid JSON in webhook data: ' . json_last_error_msg());
            $this->output->set_status_header(400);
            $this->output->set_content_type('application/json');
            $this->output->set_output(json_encode([
                'status' => 'error', 
                'message' => 'Invalid JSON: ' . json_last_error_msg()
            ]));
            return;
        }
        
        if (!isset($shipping_data['transaction_id']) || !isset($shipping_data['status'])) {
            log_message('error', 'Invalid shipping webhook data: Missing required fields');
            $this->output->set_status_header(400);
             $this->output->set_content_type('application/json');
            $this->output->set_output(json_encode([
                'status' => 'error', 
                'message' => 'Invalid data: Missing required fields'
            ]));
            return;
        }
        
        $transaction_id = $shipping_data['transaction_id'];
        $status = intval($shipping_data['status']);
        $tracking_id_from_payload = isset($shipping_data['tracking_id']) ? $shipping_data['tracking_id'] : null; // Renamed for clarity
        
        log_message('info', "Processing shipping update: transaction_id={$transaction_id}, status={$status}, tracking_id_from_payload={$tracking_id_from_payload}");
        
        $this->db->trans_start();
        
        try {
            $transaction = $this->transaction_model->get_info($transaction_id);
            if (!$transaction) {
                throw new Exception('Transaction not found: ' . $transaction_id);
            }
            
            log_message('debug', "Current transaction status: " . $transaction->status);
            
            $data = ['status' => $status];
            
            $update_result = $this->transaction_model->update($transaction_id, $data);
            log_message('debug', "Transaction update result: " . ($update_result ? 'success' : 'failed'));

            if ($tracking_id_from_payload) {
                $shipping_tracking_update_data = ['updated_at' => date('Y-m-d H:i:s')];
                // You might need to map $shipping_data['status'] (if it's a string from provider)
                // to a status string for your shipping_tracking.status column.
                // For example, if $status from webhook means 'delivered':
                if ($status == 3) { // Assuming 3 means delivered
                    $shipping_tracking_update_data['status'] = 'delivered';
                } else if ($status == 2) { // Assuming 2 means shipping
                     // Potentially use a more specific status from $shipping_data if available
                    $shipping_tracking_update_data['status'] = 'shipping'; // Or 'in_transit', etc.
                }
                // Add more conditions as needed based on what the webhook sends

                if(isset($shipping_tracking_update_data['status'])) {
                    $this->db->where('tracking_id', $tracking_id_from_payload);
                    $this->db->update('shipping_tracking', $shipping_tracking_update_data);
                    log_message('info', "Shipping tracking '{$tracking_id_from_payload}' updated via webhook.");
                }
            }
            
            if ($status == 3) {
                log_message('info', "Processing delivered status for transaction {$transaction_id}");
                $orders = $this->order_model->get_list(['where' => ['transaction_id' => $transaction_id]]);
                log_message('debug', "Found " . count($orders) . " orders for transaction {$transaction_id}");
                
                foreach ($orders as $order) {
                    $product = $this->product_model->get_info($order->product_id);
                    if ($product) {
                        log_message('debug', "Updating purchase count for product {$product->id}: {$product->buyed} -> " . ($product->buyed + (int)$order->qty));
                        
                        $this->product_model->update($product->id, [
                            'buyed' => $product->buyed + (int)$order->qty // Ensure qty is int
                        ]);
                    } else {
                        log_message('warning', "Product not found: {$order->product_id}");
                    }
                }
            }
            
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Database transaction failed: ' . $this->db->error()['message']);
            }
            
            log_message('info', 'Transaction #' . $transaction_id . ' status updated to ' . $status);
            
            $this->output->set_content_type('application/json');
            $this->output->set_output(json_encode([
                'status' => 'success',
                'message' => 'Transaction status updated successfully',
                'transaction_id' => $transaction_id,
                'new_status' => $status
            ]));
            
        } catch (Exception $e) {
            $this->db->trans_rollback();
            log_message('error', 'Webhook error: ' . $e->getMessage());
            $this->output->set_status_header(500);
            $this->output->set_content_type('application/json');
            $this->output->set_output(json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]));
        }
    }
    
    public function test() {
        $this->output->set_content_type('application/json');
        $this->output->set_output(json_encode([
            'status' => 'success',
            'message' => 'Shipping API is working correctly'
        ]));
    }
}