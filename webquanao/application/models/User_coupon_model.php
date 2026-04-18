<?php
defined('BASEPATH') or exit('No direct script access allowed');

class User_coupon_model extends MY_Model
{
	var $table = 'usercoupon';

	public function mark_coupon_used($user_id, $coupon_id)
	{
		$record = $this->db->get_where($this->table, [
			'user_id' => $user_id,
			'coupon_id' => $coupon_id
		])->row();
		if ($record) {
			// Cập nhật
			$this->db->where('id', $record->id);
			$success = $this->db->update($this->table, [
				'used_count' => $record->used_count + 1,
				'used_at' => date('Y-m-d H:i:s')
			]);
			return $success; // true nếu update thành công, false nếu thất bại
		} else {
			// Chèn mới
			$success = $this->db->insert($this->table, [
				'user_id' => $user_id,
				'coupon_id' => $coupon_id,
				'used_count' => 1,
				'used_at' => date('Y-m-d H:i:s')
			]);
			return $success; // true nếu insert thành công, false nếu thất bại
		}
	}

	public function mark_gift_code_used($user_id, $gift_code)
	{
		// Tìm coupon theo gift_code (code)
		$coupon = $this->db->get_where('coupon', ['code' => $gift_code])->row();

		if (!$coupon) {
			// Không tìm thấy mã quà tặng
			return false;
		}

		$coupon_id = $coupon->id;

		// Tìm bản ghi trong usercoupon
		$record = $this->db->get_where($this->table, [
			'user_id' => $user_id,
			'coupon_id' => $coupon_id
		])->row();

		if ($record) {
			// Cập nhật lượt dùng
			$this->db->where('id', $record->id);
			$success = $this->db->update($this->table, [
				'used_count' => $record->used_count + 1,
				'used_at' => date('Y-m-d H:i:s')
			]);
			return $success;
		} else {
			// Chèn mới
			$success = $this->db->insert($this->table, [
				'user_id' => $user_id,
				'coupon_id' => $coupon_id,
				'used_count' => 1,
				'used_at' => date('Y-m-d H:i:s')
			]);
			return $success;
		}
	}
	public function get_coupon_by_gift_code($gift_code)
	{
		$query = $this->db->get_where('coupon', [
			'code' => $gift_code,
			'type_coupon' => 1
		]);

		$coupon = $query->row(); // Trả về object hoặc null nếu không có
		return $coupon ? $coupon : null;
	}
}
