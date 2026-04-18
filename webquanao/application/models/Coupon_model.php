<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Coupon_model extends MY_Model
{
    var $table = 'coupon';

    public function get_coupons_with_user_and_used_total($user_id = null, $where = array())
    {
        // Subquery tính tổng used_count theo coupon_id (tổng tất cả users)
        $subquery = $this->db
            ->select('coupon_id, SUM(used_count) as used_total')
            ->from('usercoupon')
            ->group_by('coupon_id')
            ->get_compiled_select();

        $this->db->select('c.*, uc.used_count, ut.used_total, catalog.name as catalog_name');
        $this->db->from($this->table . ' c');

        // Nếu có user_id thì join bảng usercoupon và lọc user_id
        if ($user_id !== null) {
            $this->db->join('usercoupon uc', "uc.coupon_id = c.id AND uc.user_id = " . intval($user_id), 'left');
        } else {
            // Nếu không truyền user_id thì join bình thường (lấy used_count của tất cả user - có thể nhiều dòng)
            $this->db->join('usercoupon uc', 'uc.coupon_id = c.id', 'left');
        }

        // Join bảng subquery chứa tổng used_count
        $this->db->join("($subquery) ut", 'ut.coupon_id = c.id', 'left');

        // Join bảng catalog để lấy tên ngành hàng
        $this->db->join('catalog', 'catalog.id = c.catalog_id', 'left');

        if (!empty($where)) {
            $this->db->where($where);
        }

        $query = $this->db->get();
        return $query->result();
    }

	public function get_coupons_with_catalog($input = [])
	{
		$this->db->select('c.*, catalog.name as catalog_name');
		$this->db->from($this->table . ' c');
		$this->db->join('catalog', 'catalog.id = c.catalog_id', 'left');

		if (isset($input['order']) && is_array($input['order'])) {
            $this->db->order_by($input['order'][0], $input['order'][1]);
        }

		$query = $this->db->get();
		return $query->result();
	}
}
