<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Api extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();

        // Enable CORS
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        // Handle preflight requests
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            header('HTTP/1.1 200 OK');
            exit();
        }
    }

    // Giữ nguyên function index() cũ

    // function lấy dữ liệu từ một bảng product
    public function get_product()
    {
        try {
            header("Access-Control-Allow-Origin: *");
            header("Content-Type: application/json; charset=UTF-8");

            if (!$this->db->conn_id) {
                throw new Exception("Database connection failed");
            }

            $query = $this->db->get('product');
            if (!$query) {
                throw new Exception("Query failed for table: product");
            }

            $response = [
                "status" => "success",
                "data" => $query->result_array()
            ];

            echo json_encode($response, JSON_PRETTY_PRINT);
        } catch (Exception $e) {
            log_message('error', $e->getMessage());
            http_response_code(500);
            echo json_encode([
                "status" => "error",
                "message" => $e->getMessage()
            ]);
        }
    }

    // function lấy dữ liệu từ một bảng user
    public function get_user()
    {
        try {
            header("Access-Control-Allow-Origin: *");
            header("Content-Type: application/json; charset=UTF-8");

            if (!$this->db->conn_id) {
                throw new Exception("Database connection failed");
            }

            $query = $this->db->get('user');
            if (!$query) {
                throw new Exception("Query failed for table: user");
            }

            $response = [
                "status" => "success",
                "data" => $query->result_array()
            ];

            echo json_encode($response, JSON_PRETTY_PRINT);
        } catch (Exception $e) {
            log_message('error', $e->getMessage());
            http_response_code(500);
            echo json_encode([
                "status" => "error",
                "message" => $e->getMessage()
            ]);
        }
    }

    // function lấy dữ liệu từ một bảng order
    public function get_order()
    {
        try {
            header("Access-Control-Allow-Origin: *");
            header("Content-Type: application/json; charset=UTF-8");

            if (!$this->db->conn_id) {
                throw new Exception("Database connection failed");
            }

            $query = $this->db->get('order');
            if (!$query) {
                throw new Exception("Query failed for table: order");
            }

            $response = [
                "status" => "success",
                "data" => $query->result_array()
            ];

            echo json_encode($response, JSON_PRETTY_PRINT);
        } catch (Exception $e) {
            log_message('error', $e->getMessage());
            http_response_code(500);
            echo json_encode([
                "status" => "error",
                "message" => $e->getMessage()
            ]);
        }
    }

    // function lấy dữ liệu từ một bảng transaction
    public function get_transaction()
    {
        try {
            header("Access-Control-Allow-Origin: *");
            header("Content-Type: application/json; charset=UTF-8");

            if (!$this->db->conn_id) {
                throw new Exception("Database connection failed");
            }

            $query = $this->db->get('transaction');
            if (!$query) {
                throw new Exception("Query failed for table: transaction");
            }

            $response = [
                "status" => "success",
                "data" => $query->result_array()
            ];

            echo json_encode($response, JSON_PRETTY_PRINT);
        } catch (Exception $e) {
            log_message('error', $e->getMessage());
            http_response_code(500);
            echo json_encode([
                "status" => "error",
                "message" => $e->getMessage()
            ]);
        }
    }

    // function lấy dữ liệu từ một bảng catalog
    public function get_catalog()
    {
        try {
            header("Access-Control-Allow-Origin: *");
            header("Content-Type: application/json; charset=UTF-8");

            if (!$this->db->conn_id) {
                throw new Exception("Database connection failed");
            }

            $query = $this->db->get('catalog');
            if (!$query) {
                throw new Exception("Query failed for table: catalog");
            }

            $response = [
                "status" => "success",
                "data" => $query->result_array()
            ];

            echo json_encode($response, JSON_PRETTY_PRINT);
        } catch (Exception $e) {
            log_message('error', $e->getMessage());
            http_response_code(500);
            echo json_encode([
                "status" => "error",
                "message" => $e->getMessage()
            ]);
        }
    }
}
