<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Catalog_model extends MY_Model {
	var $table = 'catalog';

	// Tạo hàm lấy các catalog có parent_id khác null
	public function get_catalogs_with_parent()
	{
		$this->db->select('*');
		$this->db->from($this->table);
		$this->db->where('parent_id IS NOT NULL');
		$query = $this->db->get();
		return $query->result();
	}
}