<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Product_model extends MY_Model {
	var $table = 'product';

	public function get_products_with_discount($input = []) {

        $this->db->select('product.*', false);
		$this->db->select('
			CASE
				WHEN discount.status = 1 
					AND product.price >= discount.min_price 
					AND NOW() BETWEEN discount.start_date AND discount.end_date THEN
					CASE
						WHEN discount.measure = 0 THEN discount.value
						WHEN discount.measure = 1 THEN product.price * (discount.value / 100)
						ELSE 0
					END
				ELSE 0
			END AS discount
		', false);
		$this->db->from('product');
		$this->db->join('discount', 'product.discount_id = discount.id', 'left');

		if (isset($input['where'])) {
			if (is_string($input['where'])) {
				$this->db->where($input['where'], null, false);
			} elseif (is_array($input['where'])) {
				$this->db->where($input['where']);
			}
		}
		
		if (isset($input['where_in']) && is_array($input['where_in'])) {
			foreach ($input['where_in'] as $column => $values) {
				$this->db->where_in($column, $values);
			}
		}

		if (isset($input['where_not_in']) && is_array($input['where_not_in'])) {
			foreach ($input['where_not_in'] as $column => $values) {
				$this->db->where_not_in($column, $values);
			}
		}

        if (isset($input['order']) && is_array($input['order'])) {
            $this->db->order_by($input['order'][0], $input['order'][1]);
        }
        if (isset($input['limit']) && is_array($input['limit'])) {
            $this->db->limit($input['limit'][0], $input['limit'][1]);
        }

        return $this->db->get()->result();
    }

	public function get_products_with_discount_catalog() {
		$this->db->select('product.*', false);
		$this->db->select('
			CASE
				WHEN discount.status = 1 
					AND product.price >= discount.min_price 
					AND NOW() BETWEEN discount.start_date AND discount.end_date THEN
					CASE
						WHEN discount.measure = 0 THEN discount.value
						WHEN discount.measure = 1 THEN product.price * (discount.value / 100)
						ELSE 0
					END
				ELSE 0
			END AS discount
		', false);
		$this->db->select('catalog.name AS namecatalog', false);
		$this->db->from('product');
		$this->db->join('catalog', 'catalog.id = product.catalog_id', 'left');
		$this->db->join('discount', 'product.discount_id = discount.id', 'left');

		return $this->db->get()->result();
	}

	public function get_product_with_discount($id) {
		$this->db->select('
			product.*,
			CASE
				WHEN discount.status = 1 
					AND product.price >= discount.min_price 
					AND NOW() BETWEEN discount.start_date AND discount.end_date THEN
					CASE
						WHEN discount.measure = 0 THEN discount.value
						WHEN discount.measure = 1 THEN product.price * (discount.value / 100)
						ELSE 0
					END
				ELSE 0
			END AS discount
		', false);

		$this->db->from('product');
		$this->db->join('discount', 'product.discount_id = discount.id', 'left');
		$this->db->where('product.id', $id);


		return $this->db->get()->row();
	}

	public function get_products_by_images_with_discount($image_names) {
		$this->db->distinct();
		$this->db->select('
			product.*,
			CASE
				WHEN discount.status = 1 
					AND product.price >= discount.min_price 
					AND NOW() BETWEEN discount.start_date AND discount.end_date THEN
					CASE
						WHEN discount.measure = 0 THEN discount.value
						WHEN discount.measure = 1 THEN product.price * (discount.value / 100)
						ELSE 0
					END
				ELSE 0
			END AS discount
		', false);
	
		$this->db->from('product');
		$this->db->join('discount', 'product.discount_id = discount.id', 'left');
	
		$this->db->group_start();
		$this->db->where_in('product.image_link', $image_names);
		foreach ($image_names as $image_name) {
			$this->db->or_like('product.image_list', $image_name);
		}
		$this->db->group_end();
	
		$query = $this->db->get();
		$product_list = $query->result_array();
	
		$sorted_product_list = [];
		$added_products = [];
		foreach ($image_names as $image_name) {
			foreach ($product_list as $product) {
				if (($product['image_link'] == $image_name || strpos($product['image_list'], $image_name) !== false) && !in_array($product['id'], $added_products)) {
					$sorted_product_list[] = $product;
					$added_products[] = $product['id'];
					break;
				}
			}
		}
	
		return array_map(function($item) {
			return (object) $item;
		}, $sorted_product_list);
	}

	public function fuzzy_search($keyword, $products) {
		$results = [];
		$keyword_parts = explode(' ', mb_strtolower($keyword));
	
		foreach ($products as $product) {
			$product_name_parts = explode(' ', mb_strtolower($product->name));
			$match_score = 0;
	
			foreach ($keyword_parts as $keyword_part) {
				foreach ($product_name_parts as $product_name_part) {
					similar_text($keyword_part, $product_name_part, $percent);
					$match_score += $percent;
				}
			}
	
			similar_text(strtolower($keyword), mb_strtolower($product->name), $overall_percent);
	
			$average_score = ($match_score / (count($keyword_parts) * count($product_name_parts)) + $overall_percent) / 2;
	
			if ($average_score > 25) {
				$product->similarity = $average_score;
				$results[] = $product;
			}
		}
	
		usort($results, function($a, $b) {
			return $b->similarity <=> $a->similarity;
		});
	
		return $results;
	}
}

